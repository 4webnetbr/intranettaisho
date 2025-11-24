<?php

namespace App\Services;

use App\Models\Estoqu\EstoquNfeEntradaModel;
use App\Models\Estoqu\EstoquNfeEntradaProdutosModel;
use App\Models\Estoqu\EstoquFornecedorModel;
use CodeIgniter\I18n\Time;

class ImportacaoErpService
{
    protected $nfeEntradaModel;
    protected $nfeProdutosModel;
    protected $fornecedorModel;

    public function __construct()
    {
        $this->nfeEntradaModel   = new EstoquNfeEntradaModel();
        $this->nfeProdutosModel  = new EstoquNfeEntradaProdutosModel();
        $this->fornecedorModel   = new EstoquFornecedorModel();
    }

    /**
     * IMPORTA TODAS AS NFEs DO ERP
     */
    public function importar(int $emp_id, object $apiResult): array
    {
        $resumo = [
            'empresa'          => $emp_id,
            'total'            => 0,
            'novas'            => 0,
            'atualizadas'      => 0,
            'erros'            => [],
        ];

        // Validação básica
        if (
            !is_object($apiResult) ||
            !isset($apiResult->ResultSet) ||
            !isset($apiResult->ResultSet->Compras) ||
            !is_array($apiResult->ResultSet->Compras)
        ) {
            $resumo['erros'][] = "Retorno inválido da API";
            return $resumo;
        }

        foreach ($apiResult->ResultSet->Compras as $compra) {
            $resumo['total']++;

            try {
                $ret = $this->importarCompra($emp_id, $compra);

                if ($ret === 'novo') {
                    $resumo['novas']++;
                } elseif ($ret === 'atualizado') {
                    $resumo['atualizadas']++;
                }
            } catch (\Throwable $e) {
                $resumo['erros'][] = "Erro ao importar NF-e " .
                    ($compra->ChaveNF ?? '(sem chave)') . ': ' . $e->getMessage();
            }
        }

        return $resumo;
    }

    /**
     * IMPORTA UMA ÚNICA NFE
     */
    protected function importarCompra(int $emp_id, object $c): string
    {
        //  🔹 Chave pode vir vazia → não quebra mais
        $chave = $c->ChaveNF ?? null;

        //  🔹 Datas — formato YYYYMMDD
        $dataCompra   = $this->formatarData($c->DataCompra ?? null);
        $dataEntrada  = $dataCompra; // entrada = compra, porque o ERP não manda dhRecbto

        // 🔹 Busca fornecedor
        $cnpjFor = preg_replace('/\D/', '', ($c->CPFCNPJFornecedor ?? ''));

        $for = null;
        if (!empty($cnpjFor)) {
            $for = $this->fornecedorModel->getFornecedorCNPJ($cnpjFor);
        }

        // 🔹 Dados do cabeçalho
        $dados = [
            'emp_id'               => $emp_id,
            'nfe_chave'            => $chave,
            'nfe_modelo'           => 55, // o ERP já traz somente NF-e
            'nfe_serie'            => null,
            'nfe_numero'           => $c->NrDoc ?? null,
            'nfe_data_emissao'     => $dataCompra,
            'nfe_data_entrada'     => $dataEntrada,
            'for_id'               => $for['for_id'] ?? null,
            'nfe_valor_total'      => $c->VlrTotal ?? $c->VlrTotalItens ?? 0,
            'nfe_xml'              => null,     // não vem da API
            'nfe_resumo'           => json_encode($c, JSON_UNESCAPED_UNICODE),
            'nfe_protocolo'        => null,
            'nfe_tipo_evento'      => null,
            'nfe_status'           => 1,
            'nfe_fornecedor_nome'  => $c->Fornecedor ?? '',
            'nfe_fornecedor_cnpj'  => $cnpjFor,
        ];

        // 🔹 Verifica se já existe
        $existente = null;
        if ($chave) {
            $existente = $this->nfeEntradaModel
                ->where('emp_id', $emp_id)
                ->where('nfe_chave', $chave)
                ->first();
        }

        $acao = 'novo';

        if ($existente) {
            $dados['nfe_id'] = $existente['nfe_id'];
            $acao = 'atualizado';
        }

        // 🔹 Salva cabeçalho
        $this->nfeEntradaModel->save($dados);

        $nfe_id = $existente
            ? $existente['nfe_id']
            : $this->nfeEntradaModel->getInsertID();

        // 🔹 Remove itens antigos
        $this->nfeProdutosModel
            ->where('nfe_id', $nfe_id)
            ->delete();

        // 🔹 Grava itens
        if (isset($c->Itens) && is_array($c->Itens)) {
            foreach ($c->Itens as $item) {
                $this->salvarItem($nfe_id, $item);
            }
        }

        return $acao;
    }

    /**
     * SALVA ITEM DA NOTA
     */
    protected function salvarItem(int $nfe_id, object $i): void
    {
        $this->nfeProdutosModel->insert([
            'nfe_id'             => $nfe_id,
            'nfp_numero_item'    => $i->NrItem ?? null,
            'nfp_codigo_produto' => $i->CodBarras ?? '',
            'nfp_descricao'      => $i->ItemCompra ?? '',
            'nfp_ncm'            => $i->NCM ?? null,
            'nfp_cfop'           => $i->CFOP ?? null,
            'nfp_unidade'        => $i->UndMedidaCompra ?? '',
            'nfp_quantidade'     => $i->Qtde ?? 0,
            'nfp_valor_unitario' => $i->VlrUnit ?? $i->VlrUnitIntegrado ?? 0,
            'nfp_valor_total'    => $i->VlrBruto ?? $i->ValorTotal ?? 0,
            'nfp_origem'         => null,
            'nfp_cst'            => null,
            'nfp_csosn'          => null,
            'nfp_icms'           => null,
            'nfp_ipi'            => null,
            'nfp_pis'            => null,
            'nfp_cofins'         => null,
        ]);
    }

    /**
     * FORMATA DATA YYYYMMDD → YYYY-MM-DD
     */
    protected function formatarData(?string $data): ?string
    {
        if (!$data || strlen($data) !== 8) {
            return null;
        }

        $ano  = substr($data, 0, 4);
        $mes  = substr($data, 4, 2);
        $dia  = substr($data, 6, 2);

        return "$ano-$mes-$dia 00:00:00";
    }
}
