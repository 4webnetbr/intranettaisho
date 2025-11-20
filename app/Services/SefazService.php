<?php

namespace App\Services;

use App\Models\Config\ConfigEmpresaModel;
use App\Models\Estoqu\EstoquNfeEntradaModel;
use App\Models\Estoqu\EstoquNfeEntradaProdutosModel;
use Config\Services;
use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Common\Standardize;

class SefazService
{
    /** @var ConfigEmpresaModel */
    protected $empresaModel;

    /** @var EstoquNfeEntradaModel */
    protected $nfeEntradaModel;

    /** @var EstoquNfeEntradaProdutosModel */
    protected $nfeProdutoModel;

    /** @var array */
    protected $empresa;

    /** @var Tools */
    protected $tools;


    public function __construct()
    {
        $this->empresaModel    = new ConfigEmpresaModel();
        $this->nfeEntradaModel = new EstoquNfeEntradaModel();
        $this->nfeProdutoModel = new EstoquNfeEntradaProdutosModel();
    }

    /**
     * Método PRINCIPAL a ser chamado:
     * $service->consultarNSU($emp_id)
     */
    public function consultarNSU(int $emp_id): array
    {
        // 👇 Carrega dados da empresa
        $this->empresa = $this->empresaModel->find($emp_id);

        if (!$this->empresa) {
            throw new \RuntimeException("Empresa ID {$emp_id} não encontrada.");
        }

        // 👇 Inicializa Tools e certificado
        $this->initTools();

        // 👇 Começa o processamento
        return $this->processarNSU();
    }


    /**
     * Monta Tools do NFePHP para essa empresa
     */
    protected function initTools(): void
    {
        $config = [
            'atualizacao' => date('Y-m-d H:i:s'),
            'tpAmb'       => (int) ($this->empresa['emp_sefaz_ambiente'] ?? 2),
            'razaosocial' => $this->empresa['emp_nome'] ?? '',
            'siglaUF'     => $this->empresa['emp_uf'] ?? '',
            'cnpj'        => preg_replace('/\D/', '', $this->empresa['emp_cnpj'] ?? ''),
            'schemes'     => 'PL_009_V4',
            'versao'      => '4.00',
            'tokenIBPT'   => '',
            'CSC'         => '',
            'CSCid'       => '',
        ];

        $configJson = json_encode($config, JSON_UNESCAPED_UNICODE);

        // Caminho do PFX
        $pfxPath = WRITEPATH . 'certificados/' . $this->empresa['emp_id'] . '/certificado.pfx';

        if (!file_exists($pfxPath)) {
            throw new \RuntimeException("Certificado A1 não encontrado em: {$pfxPath}");
        }

        $pfx = file_get_contents($pfxPath);

        // Descriptografa senha
        $enc = Services::encrypter();
        $senha = $enc->decrypt(base64_decode($this->empresa['emp_cert_senha']));

        // Lê certificado
        $cert = Certificate::readPfx($pfx, $senha);

        // Monta tools
        $this->tools = new Tools($configJson, $cert);
        $this->tools->model('55');
        $this->tools->setEnvironment((int) ($this->empresa['emp_sefaz_ambiente'] ?? 2));
    }


    /**
     * Realiza a consulta NSU e processa os retornos
     */
    protected function processarNSU(): array
    {
        $ultimoNSU = (int) ($this->empresa['emp_sefaz_ult_nsu'] ?? 0);

        $resumo = [
            'emp_id'            => $this->empresa['emp_id'],
            'ultNSU_anterior'   => $ultimoNSU,
            'ultNSU_novo'       => null,
            'docsProcessados'   => 0,
            'notasNovas'        => 0,
            'notasAtualizadas'  => 0,
            'erros'             => [],
        ];

        try {
            $xml = $this->tools->sefazDistDFe($ultimoNSU);
            debug($xml, true);

            $std = simplexml_load_string($xml);

            $cStat = (int) $std->cStat;
            $novoUltNSU = (int) $std->ultNSU;

            // $std = (new Standardize($xml))->toStd();

            // $cStat = (int) ($std->cStat ?? 0);
            // $novoUltNSU = (int) ($std->ultNSU ?? $ultimoNSU);
            $resumo['ultNSU_novo'] = $novoUltNSU;

            // Se não tem documentos
            if ($cStat == 137) {
                $this->atualizarUltimoNsu($novoUltNSU);
                return $resumo;
            }

            // Tem documentos → processa docZip
            $dom = new \DOMDocument();
            $dom->loadXML($xml);

            $docs = $dom->getElementsByTagName('docZip');

            foreach ($docs as $doc) {
                $resumo['docsProcessados']++;

                $schema = $doc->getAttribute('schema');
                $conteudo = gzdecode(base64_decode($doc->nodeValue));

                if (
                    stripos($schema, 'procNFe') !== false ||
                    stripos($schema, 'nfe_proc') !== false
                ) {
                    $ret = $this->gravarNfeEntrada($conteudo);

                    if ($ret === 'novo') $resumo['notasNovas']++;
                    if ($ret === 'atualizado') $resumo['notasAtualizadas']++;
                }
            }

            // Atualiza NSU da empresa
            $this->atualizarUltimoNsu($novoUltNSU);

        } catch (\Throwable $e) {
            $resumo['erros'][] = $e->getMessage();
        }

        return $resumo;
    }


    /**
     * Atualiza emp_sefaz_ult_nsu
     */
    protected function atualizarUltimoNsu(int $nsu): void
    {
        $this->empresaModel->update(
            $this->empresa['emp_id'],
            ['emp_sefaz_ult_nsu' => $nsu]
        );
    }


    /**
     * Recebe o XML procNFe e grava nas tabelas est_nfe_entrada e est_nfe_entrada_produtos
     */
    protected function gravarNfeEntrada(string $xml): ?string
    {
        $std = (new Standardize($xml))->toStd();

        if (!isset($std->NFe) || !isset($std->protNFe)) {
            return null;
        }

        $inf = $std->NFe->infNFe;
        $prot = $std->protNFe->infProt;

        $chave = $prot->chNFe ?? '';

        // Verifica se já existe
        $existente = $this->nfeEntradaModel
            ->where('emp_id', $this->empresa['emp_id'])
            ->where('nfe_chave', $chave)
            ->first();

        $dados = [
            'emp_id'          => $this->empresa['emp_id'],
            'nfe_chave'       => $chave,
            'nfe_numero'      => $inf->ide->nNF ?? 0,
            'nfe_serie'       => $inf->ide->serie ?? 0,
            'nfe_modelo'      => $inf->ide->mod ?? 0,
            'nfe_tp_nf'       => $inf->ide->tpNF ?? 0,
            'nfe_emissao'     => substr($inf->ide->dhEmi ?? '', 0, 19),
            'nfe_entrada'     => substr($prot->dhRecbto ?? '', 0, 19),
            'nfe_cnpj_emit'   => $inf->emit->CNPJ ?? '',
            'nfe_razao_emit'  => $inf->emit->xNome ?? '',
            'nfe_vl_total'    => $inf->total->ICMSTot->vNF ?? 0,
            'nfe_xml'         => $xml,
        ];

        $acao = 'novo';

        if ($existente) {
            $dados['nfe_id'] = $existente['nfe_id'];
            $acao = 'atualizado';
        }

        // Salva cabeçalho
        $this->nfeEntradaModel->save($dados);

        $nfe_id = $existente
            ? $existente['nfe_id']
            : $this->nfeEntradaModel->getInsertID();

        // Remove itens antigos
        $this->nfeProdutoModel->where('nfe_id', $nfe_id)->delete();

        // Grava itens
        $det = $inf->det ?? [];

        if (!is_array($det)) {
            $det = [$det];
        }

        foreach ($det as $item) {
            $p = $item->prod;

            $this->nfeProdutoModel->insert([
                'nfe_id'        => $nfe_id,
                'nfp_codigo'    => $p->cProd ?? '',
                'nfp_descricao' => $p->xProd ?? '',
                'nfp_ncm'       => $p->NCM ?? '',
                'nfp_cfop'      => $p->CFOP ?? '',
                'nfp_unidade'   => $p->uCom ?? '',
                'nfp_qtde'      => $p->qCom ?? 0,
                'nfp_vr_unit'   => $p->vUnCom ?? 0,
                'nfp_vr_total'  => $p->vProd ?? 0,
            ]);
        }

        return $acao;
    }
}
