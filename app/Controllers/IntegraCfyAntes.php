<?php namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\Config\ConfigApiModel;
use App\Models\Config\ConfigEmpresaModel;


class IntegraCfyAntes extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $empresa;
    public $apis;
    public $common;
    


	public function __construct(){
        $this->empresa    = new  ConfigEmpresaModel();
        $this->apis       = new  ConfigApiModel();
        $this->common     = new  CommonModel();
	}

	public function index()
	{
        $this->def_campos();
        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->integ_periodo;
        $campos[0][1] = $this->botao;

        $this->data['controler'] = 'IntegraCfyAntes';
        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'integracao';
        return view('vw_edicao_limpa', $this->data);
    }

    public function def_campos(){
        $periodo =  new MyCampo();
        $periodo->nome        = 'periodo'; 
        $periodo->id          = 'periodo';
        $periodo->label       = 'Informe o Período';
        $periodo->valor       = '';
        $periodo->size        = 30;
        $periodo->tamanho     = 30;
        $periodo->dispForm    = '3col';
        $this->integ_periodo    = $periodo->crDaterange();

        $bot = new MyCampo();
        $bot->nome = 'Processar';
        $bot->id    = 'Processar';
        $bot->label = 'Processar';
        $bot->place = 'Processar';
        $bot->i_cone = "<i class='fa-solid fa-play'></i> Processar";
        $bot->classep = 'btn btn-primary';
        $bot->tipo = 'submit';
        $this->botao = $bot->crBotao();
    }
    
    public function integracao(){
        $dados = $this->request->getPost();
        $inicio = date('Ymd',strtotime(dataBrToDb(substr($dados['periodo'],0,10))));
        $fim    = date('Ymd',strtotime(dataBrToDb(substr($dados['periodo'],-10)))); 
        debug($inicio);
        debug($fim);
        
        $dados_emp = $this->empresa->getEmpresa();
        $this->data['empresa'] = $dados_emp;
        debug('Iniciou');

        $apis = $this->apis->getApi();
        $this->data['empresas'] = $dados_emp;
        for ($e = 0; $e < count($dados_emp); $e++) {
            $emp = $dados_emp[$e];
            $api = $apis[0];
            // debug($api);
            $cupons = get_cloudfy_curl($api, $emp, $inicio, $fim);
            // debug($cupons);
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
                        // debug($cupom);
                        $sv_cupom = $this->common->insertReg('default', 'cfy_cuponsvenda',$cupom);
                        // debug($sv_cupom);
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