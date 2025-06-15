<?php namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CommonModel;
use App\Models\Config\ConfigApiModel;
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
                        $sv_cupom = $this->common->insertReg('default', 'cfy_cuponsvenda',$cupom);
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
                                $sv_pagam = $this->common->insertReg('default', 'cfy_pagamentocupom',$pagamentos[$p]);
                                // debug($pagamentos[$p]);
                            }
                            for($pr=0;$pr<count($produtos);$pr++){
                                $pro = $produtos[$pr];
                                $produtos[$pr]->NrEmpresa = $cupom->NrEmpresa;
                                $produtos[$pr]->NrFilial  = $cupom->NrFilial;
                                $produtos[$pr]->NrCupom   = $cupom->NrCupom;
                                $produtos[$pr]->DataHoraLancamento   = numTodataBrToDb($pro->DataHoraLancamento);
                                $sv_produt = $this->common->insertReg('default', 'cfy_produtocupom',$produtos[$pr]);
                                // debug($produtos[$p]);
                            }
                        }
                    }
                    // debug($cupom);
                }
                // debug($cupom, true);
            }
            //     $dia = '';
            //     $total = 0;
            //         // debug($cupom);
            //         if($dia != $cupom->DataMovimento){
            //             if($dia != ''){
            //                 $this->data['empresa'][$e]['faturamento']['dia'] = $dia;
            //                 $this->data['empresa'][$e]['faturamento']['total'] = $total;
            //             }
            //             $total = 0;
            //             $dia = $cupom->DataMovimento;
            //         }
            //         $total += floatval($cupom->VlrTotal);
            //     }
            //     $this->data['empresa'][$e]['faturamento']['dia'] = $dia;
            //     $this->data['empresa'][$e]['faturamento']['total'] = $total;
            // }
        }
        debug('Acabou');
    }
}