<?php namespace App\Controllers;

use App\Models\CommonModel;
use App\Controllers\BaseController;
use App\Models\Config\ConfigApiModel;
use App\Services\ImportacaoErpService;
use App\Models\Config\ConfigEmpresaModel;


class IntegraCfy extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $empresa;
    public $apis;
    public $common;


	public function __construct(){
		// $this->data  = session()->getFlashdata('dados_tela');
        $this->empresa    = new  ConfigEmpresaModel();
        $this->apis       = new  ConfigApiModel();
        $this->common     = new  CommonModel();
	}

	public function index()
	{
        $dados_emp = $this->empresa->getEmpresa();
        $this->data['empresa'] = $dados_emp;
        debug('Iniciou');

        $apis = $this->apis->getApi();
        $this->data['empresas'] = $dados_emp;
        for ($e = 0; $e < count($dados_emp); $e++) {
            $emp = $dados_emp[$e];
            $api = $apis[0];
            $cupons = get_cloudfy_curl($api, $emp);
            if(isset($cupons->Error))
                debug($cupons->Error);
            if($cupons->Status != -1) {
                $cuponsvenda = $cupons->ResultSet->CuponsVenda;
                // debug($cuponsvenda);
                if(count((array)$cuponsvenda)>0){
                    for($c=0;$c<count((array)$cuponsvenda);$c++){
                        $cupom = $cuponsvenda[$c];
                        $cupom->DataMovimento = numTodataBrToDb($cupom->DataMovimento);
                        debug($cupom->DataMovimento);
                        $cupom->HrAbertura    = numTohora_db($cupom->HrAbertura);
                        $cupom->HrFechamento  = numTohora_db($cupom->HrFechamento);
                        debug($cupom->NrCupom);
                        $pagamentos = $cupom->Pagamentos;
                        unset($cupom->Pagamentos);
                        $produtos   = $cupom->Produtos;
                        unset($cupom->Produtos);
                        $sv_cupom = $this->common->insertReg('default', 'cfy_cuponsvenda',$cupom, false);
                        if($sv_cupom) {
                            debug('Gravou Cupom');
                            for($p=0;$p<count($pagamentos);$p++){
                                $pag = $pagamentos[$p];
                                $pagamentos[$p]->NrEmpresa = $cupom->NrEmpresa;
                                $pagamentos[$p]->NrFilial  = $cupom->NrFilial;
                                $pagamentos[$p]->NrCupom   = $cupom->NrCupom;
                                $pagamentos[$p]->DataPagto   = numTodataBrToDb($pag->DataPagto);
                                $pagamentos[$p]->DataCorte   = numTodataBrToDb($pag->DataCorte);
                                $pagamentos[$p]->DataPrevRecebimento   = numTodataBrToDb($pag->DataPrevRecebimento);
                                $pagamentos[$p]->DataHoraAutorizacao   = numTodataBrToDb($pag->DataHoraAutorizacao);
                                $sv_pagam = $this->common->insertReg('default', 'cfy_pagamentocupom',$pagamentos[$p], false);
                                // debug($pagamentos[$p]);
                            }
                            for($pr=0;$pr<count($produtos);$pr++){
                                $pro = $produtos[$pr];
                                $produtos[$pr]->NrEmpresa = $cupom->NrEmpresa;
                                $produtos[$pr]->NrFilial  = $cupom->NrFilial;
                                $produtos[$pr]->NrCupom   = $cupom->NrCupom;
                                $produtos[$pr]->DataHoraLancamento   = numTodataBrToDb($pro->DataHoraLancamento);
                                $sv_produt = $this->common->insertReg('default', 'cfy_produtocupom',$produtos[$pr], false);
                                // debug($produtos[$p]);
                            }
                        }
                    }
                    // debug($cupom);
                }
                // debug($cupom, true);
            }
        }

        // INTEGRAR COMPRAS
        $api['api_nome']        = 'CFYCC892';
        $api['api_acckey']      = $apis[0]['api_acckey'];
        $api['api_tokenkey']    = $apis[0]['api_tokenkey'];
        $api['api_login']       = $apis[0]['api_login'];
        $api['api_usuario']     = $apis[0]['api_usuario'];
        $emp = $dados_emp[0];
        $inicio = '20251101';
        $fim = '20251124';

        // Chama a API do ERP
        $compras = get_cloudfy_curl($api, $emp, $inicio, $fim);
        // debug($compras);
            
        $service = new ImportacaoErpService();

            // Verificações mínimas do retorno
            if (
                empty($compras) ||
                !isset($compras->ResultSet) ||
                !isset($compras->ResultSet->Compras) ||
                !is_array($compras->ResultSet->Compras)
            ) {
                echo "Nenhuma compra encontrada.";
                return;
            }

            foreach ($compras->ResultSet->Compras as $compra) {

                // Importa UMA nota por vez
                $ret = $service->importar($emp['emp_id'], $compra);

                $chaveLog = isset($ret['ChaveNF']) ?? '(sem chave)';

                if ($ret['sucesso']) {

                    log_message(
                        'info',
                        "NF-e {$chaveLog} importada com sucesso ({$ret['acao']})."
                    );

                    debug("NF-e {$chaveLog} importada com sucesso ({$ret['acao']}).");

                } else {

                    $erro = implode(' | ', $ret['erros']);
                    debug($compra);
                    log_message(
                        'error',
                        "Erro ao importar NF-e {$chaveLog}: {$erro}"
                    );

                    debug("Erro ao importar NF-e {$chaveLog}: {$erro}");
                }
            }

        debug('Acabou');
    }
}