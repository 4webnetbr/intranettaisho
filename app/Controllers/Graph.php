<?php

namespace App\Controllers;
use App\Controllers\BaseController;
use App\Libraries\Campos; 
use App\Libraries\MyCampo;
use App\Models\Config\ConfigApiModel;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\DelivModel;
use App\Models\GerenteModel;
use CodeIgniter\Controller;


class Graph extends BaseController
{
	public $data = [];
    public $empresa;
    public $deliv;
    public $gerente;
    public $apis;


	public function __construct(){
        $this->empresa    = new  ConfigEmpresaModel();
        $this->deliv      = new  DelivModel();
        $this->gerente    = new  GerenteModel();
        $this->apis       = new  ConfigApiModel();
	}
    public function index(){ 
		$empresa        = session()->empresa;
        $inicio         = dataBrToDb(session()->inicio); 
        $fim            = dataBrToDb(session()->fim); 
        $this->data['busca']       = session()->busca;
        $this->data['cols']        = session()->colunas;
        $this->data['nome']        = session()->nome;
        $this->data['titulo']      = session()->titulo;
        $this->data['tipo']        = session()->tipo;
        if($this->data['tipo'] == ''){
            $this->data['tipo'] = 'b_';
        }
        if($this->data['tipo'] == 'l_'){
            $this->data['tipodesc'] = 'linhas';
        } else if($this->data['tipo'] == 'b_'){
            $this->data['tipodesc'] = 'barras';
        } else if($this->data['tipo'] == 'p_'){
            $this->data['tipodesc'] = 'pizza';
        } else if($this->data['tipo'] == 'd_'){
            $this->data['tipodesc'] = 'doughnut';
        } 
        $this->data['periodo']    = 'de '.dataDbToBr($inicio).' até '.dataDbToBr($fim);
        // debug($empresa);
        if(gettype($empresa[0]) == 'array'){
            $emp = $this->empresa->getEmpresa($empresa);
            $this->data['nomeempresa'] = 'Comparativo de Empresas';
        } else{
            $emp = $this->empresa->getEmpresa($empresa)[0];
            $this->data['nomeempresa'] = $emp['emp_apelido'];
        }

        // $emp = $this->empresa->getEmpresa($empresa)[0];        
        // $this->data['nomeempresa'] = $emp['emp_apelido'];

        $periodo =  new MyCampo();
        $periodo->nome        = 'periodo'; 
        $periodo->id          = 'periodo';
        $periodo->label       = 'Informe o Período';
        $periodo->valor       = trim(dataDbToBr($inicio).' - '.dataDbToBr($fim));
        $periodo->size        = 30;
        $periodo->tamanho     = 30;
        // $periodo->funcChan    = 'carrega_graficos()';
        $periodo->dispForm    = '3col';
        $this->dash_periodo    = $periodo->crDaterange();

        $dados_emp = $this->empresa->getEmpresa($empresa);
        $empres = array_column($dados_emp, 'emp_apelido', 'emp_id');
        // debug($empres);

        $emp                        = new MyCampo();
        // $emp->valor                 = $empresas;
        $emp->nome                  = 'empresa'; 
        $emp->id                    = 'empresa';
        $emp->label                 = 'Empresa(s)';
        $emp->selecionado           = $empresa;
        $emp->opcoes                = $empres;
        $emp->funcChan              = 'carrega_graficos()';
        $emp->dispForm              = '3col';
        // if(gettype($empresa[0]) == 'array'){
        //     $this->dash_empresa         = $emp->crMultiple();
        // } else {
            $this->dash_empresa         = $emp->crSelect();
        // }

        $campos[0] = $this->dash_periodo;
        $campos[1] = $this->dash_empresa;


        $this->data['campos']     	= $campos;  
        
        return view('vw_grafico', $this->data);
    }
}
