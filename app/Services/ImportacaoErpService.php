<?php

namespace App\Services;

use App\Models\Estoqu\EstoquNfeEntradaModel;
use App\Models\Estoqu\EstoquNfeEntradaProdutosModel;
use App\Models\Estoqu\EstoquFornecedorModel;

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
     * Importa UMA NF-e
     */
    public function importar(int $emp_id, object $compra): array
    {
        $chave = $compra->ChaveNF ?? null;

        try {
            $acao = $this->importarCompra($emp_id, $compra);

            return [
                'sucesso' => true,
                'acao'    => $acao,
                'chave'   => $chave,
                'erros'   => [],
            ];
        } 
        catch (\Throwable $e) {

            log_message('error', 
                "ERRO AO IMPORTAR NF-e: ".$e->getMessage().
                " | COMPRA: " . json_encode($compra, JSON_UNESCAPED_UNICODE)
            );
            debug( 
                "ERRO AO IMPORTAR NF-e: ".$e->getMessage()
                // " | COMPRA: " . json_encode($compra, JSON_UNESCAPED_UNICODE)
            );

            return [
                'sucesso' => false,
                'acao'    => null,
                'chave'   => $chave,
                'erros'   => [$e->getMessage()],
            ];
        }
    }


    /**
     * Lógica interna da importação
     */
    protected function importarCompra(int $emp_id, object $c): string
    {
        try {

            // 1️⃣ Chave NF-e segura
            $chave = $c->ChaveNF ?? null;

            // 2️⃣ Datas
            $dataCompra   = $this->formatarData($c->DataCompra ?? null);
            $dataEntrada  = $dataCompra;

            // 3️⃣ Fornecedor
            debug('CNPJ '.$c->CPFCNPJFornecedor);
            $cnpjFor = preg_replace('/\D/', '', ($c->CPFCNPJFornecedor ?? ''));

            try {
                $for = (!empty($cnpjFor))
                    ? $this->fornecedorModel->getFornecedorCNPJ($cnpjFor)
                    : null;
            }
            catch (\Throwable $e) {
                log_message('error',
                    "ERRO AO BUSCAR FORNECEDOR: ".$e->getMessage().
                    " | CNPJ: $cnpjFor"
                );
                debug(
                    "ERRO AO BUSCAR FORNECEDOR: ".$e->getMessage().
                    " | CNPJ: $cnpjFor"
                );
                $for = null;
            }

            // 4️⃣ Cabeçalho completo
            try {
                $dados = [
                    'emp_id'               => $emp_id,
                    'nfe_chave'            => $chave,
                    'nfe_modelo'           => 55,
                    'nfe_serie'            => null,
                    'nfe_numero'           => $c->NrDoc ?? null,
                    'nfe_data_emissao'     => $dataCompra,
                    'nfe_data_entrada'     => $dataEntrada,
                    'for_id'               => $for[0]['for_id'] ?? null,
                    'nfe_valor_total'      => $c->VlrTotal ?? $c->VlrTotalItens ?? 0,
                    'nfe_xml'              => null,
                    'nfe_resumo'           => json_encode($c, JSON_UNESCAPED_UNICODE),
                    'nfe_protocolo'        => null,
                    'nfe_tipo_evento'      => null,
                    'nfe_status'           => 1,
                    'nfe_fornecedor_nome'  => $c->Fornecedor ?? '',
                    'nfe_fornecedor_cnpj'  => $cnpjFor,
                ];
            }
            catch (\Throwable $e) {
                throw new \Exception("ERRO AO MONTAR CABEÇALHO NFE: ".$e->getMessage());
            }

            // 5️⃣ Verifica existente
            try {
                $existente = null;

                if ($chave) {
                    $existente = $this->nfeEntradaModel
                        ->where('emp_id', $emp_id)
                        ->where('nfe_chave', $chave)
                        ->first();
                }
            }
            catch (\Throwable $e) {
                throw new \Exception("ERRO AO CONSULTAR NOTA EXISTENTE: ".$e->getMessage());
            }


            // 6️⃣ Insere ou atualiza cabeçalho
            try {

                $acao = 'novo';

                if ($existente && isset($existente['nfe_id'])) {
                    $dados['nfe_id'] = $existente['nfe_id'];
                    $acao = 'atualizado';
                }

                $this->nfeEntradaModel->save($dados);

                $nfe_id = $existente['nfe_id'] 
                        ?? $this->nfeEntradaModel->getInsertID();

            }
            catch (\Throwable $e) {
                throw new \Exception(
                    "ERRO AO SALVAR CABEÇALHO NFE: ".$e->getMessage() .
                    " | DADOS: " . json_encode($dados, JSON_UNESCAPED_UNICODE)
                );
            }


            // 7️⃣ Remove itens antigos
            debug($nfe_id);
            try {
                $this->nfeProdutosModel
                    ->where('nfe_id', $nfe_id)
                    ->delete();
            }
            catch (\Throwable $e) {
                throw new \Exception("ERRO AO LIMPAR ITENS ANTIGOS: ".$e->getMessage());
            }


            // 8️⃣ Insere novos itens
            if (!empty($c->Itens) && is_array($c->Itens)) {

                foreach ($c->Itens as $item) {

                    try {
                        $this->salvarItem($nfe_id, $item);
                    }
                    catch (\Throwable $e) {
                        throw new \Exception(
                            "ERRO AO SALVAR ITEM DA NF-e: ".$e->getMessage().
                            " | ITEM: " . json_encode($item, JSON_UNESCAPED_UNICODE)
                        );
                    }
                }
            }

            return $acao;
        }
        catch (\Throwable $e) {

            log_message('error',
                "ERRO GERAL AO PROCESSAR COMPRA: ".$e->getMessage() .
                " | COMPRA: " . json_encode($c, JSON_UNESCAPED_UNICODE)
            );
            debug(
                "ERRO GERAL AO PROCESSAR COMPRA: ".$e->getMessage()
                //  .
                // " | COMPRA: " . json_encode($c, JSON_UNESCAPED_UNICODE)
            );

            throw $e;
        }
    }


    /**
     * SALVA UM ITEM
     */
    protected function salvarItem(int $nfe_id, object $i): void
    {
        try {

            // normalização do valor total
            $valorTotal = $i->VlrBruto
                ?? ($i->{'Valor total'} ?? null)
                ?? ($i->ValorTotal ?? 0);

            $dadosItem = [
                'nfe_id'             => $nfe_id,
                'nfp_numero_item'    => $i->NrItem ?? null,
                'nfp_codigo_produto' => $i->CodBarras ?? '',
                'nfp_descricao'      => $i->ItemCompra ?? '',
                'nfp_ncm'            => $i->NCM ?? null,
                'nfp_cfop'           => $i->CFOP ?? null,
                'nfp_unidade'        => $i->UndMedidaCompra ?? '',
                'nfp_quantidade'     => $i->Qtde ?? 0,
                'nfp_valor_unitario' => $i->VlrUnit ?? $i->VlrUnitIntegrado ?? 0,
                'nfp_valor_total'    => $valorTotal,
                'nfp_origem'         => null,
                'nfp_cst'            => null,
                'nfp_csosn'          => null,
                'nfp_icms'           => null,
                'nfp_ipi'            => null,
                'nfp_pis'            => null,
                'nfp_cofins'         => null,
            ];

            $this->nfeProdutosModel->insert($dadosItem);
        }
        catch (\Throwable $e) {

            throw new \Exception(
                "Erro ao inserir item: ".$e->getMessage().
                " | DADOS: ".json_encode($dadosItem ?? [], JSON_UNESCAPED_UNICODE)
            );
        }
    }


    /**
     * Formata data YYYYMMDD
     */
    protected function formatarData(?string $data): ?string
    {
        try {
            if (!$data || strlen($data) !== 8) {
                return null;
            }

            return substr($data, 0, 4) . "-" .
                   substr($data, 4, 2) . "-" .
                   substr($data, 6, 2) . " 00:00:00";
        }
        catch (\Throwable $e) {
            log_message('error', "ERRO AO FORMATAR DATA: ".$e->getMessage()." | DATA RAW: $data");
            debug("ERRO AO FORMATAR DATA: ".$e->getMessage()." | DATA RAW: $data");
            return null;
        }
    }
}
