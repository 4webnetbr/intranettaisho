<?php namespace App\Controllers;

use App\Controllers\BaseController;
use App\Controllers\IntegraOpinae;
use App\Libraries\MyCampo;
use App\Models\Config\ConfigApiModel;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\DelivModel;
use App\Models\GerenteModel;


class DashDiretor extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $empresa;
    public $deliv;
    public $gerente;
    public $apis;


	public function __construct(){
		$this->data  = session()->getFlashdata('dados_tela');
        $this->permissao  = $this->data['permissao'];
        $this->empresa    = new  ConfigEmpresaModel();
        $this->deliv      = new  DelivModel();
        $this->gerente    = new  GerenteModel();
        $this->apis       = new  ConfigApiModel();
        if($this->data['erromsg'] != ''){
            $this->__erro();
        }
	}

    function __erro(){
        echo view('vw_semacesso', $this->data);
    }
 
    public function index()
    {
        $integ_nps = new IntegraOpinae();
        $integ_nps->integrar();
        
        $this->def_campos();
        $campos[0] = $this->dash_periodo;
        $campos[1] = $this->dash_empresa;
        $campos[2] = $this->dash_indicadores;

        $this->data['campos']     	= $campos;  
        return view('vw_dashdiretor', $this->data);
    }
    
    public function def_campos(){
        $periodo =  new MyCampo();
        $periodo->nome        = 'periodo'; 
        $periodo->id          = 'periodo';
        $periodo->label       = 'Informe o Período';
        $periodo->valor       = '';
        $periodo->size        = 30;
        $periodo->tamanho     = 30;
        $periodo->funcChan    = 'carrega_graficos_diretor()';
        $periodo->dispForm    = '3col';
        $this->dash_periodo    = $periodo->crDaterange();
        
        $empresas = explode(',',session()->get('usu_empresa'));
        // debug($empresas);
        $dados_emp = $this->empresa->getEmpresa($empresas);
        $empres = array_column($dados_emp, 'emp_apelido', 'emp_id');

        $emp                        = new MyCampo();
        // $emp->valor                 = $empresas;
        $emp->nome                  = 'empresa'; 
        $emp->id                    = 'empresa';
        $emp->label                 = 'Empresa(s)';
        $emp->selecionado           = $empresas;
        $emp->opcoes                = $empres;
        $emp->funcChan              = 'carrega_graficos_diretor()';
        $emp->dispForm              = '3col';
        $emp->largura               = 40;
        if(count($empresas) == 1){
            $emp->leitura           = true;
        }
        $this->dash_empresa         = $emp->crMultiple();

        $indica['']        = 'Escolha um Indicador';
        $indica['fat_dia'] = 'Faturamento Diário';
        $indica['fat_sem'] = 'Faturamento Semanal';
        $indica['fat_mes'] = 'Faturamento Mensal';
        $indica['nps_dia'] = 'NPS Diário';
        $indica['nps_sem'] = 'NPS Semanal';
        $indica['nps_mes'] = 'NPS Mensal';
        $indica['atrasos_deliv'] = '% de atrasos com mais de 60 minutos';
        $ind                        = new MyCampo();
        $ind->nome                  = 'indicadores'; 
        $ind->id                    = 'indicadores';
        $ind->label                 = 'Indicadores';
        $ind->selecionado           = '';
        $ind->opcoes                = $indica;
        $ind->funcChan              = 'carrega_graficos_diretor()';
        $ind->dispForm              = '3col';
        $ind->largura               = 40;
        $this->dash_indicadores         = $ind->crSelect();
        
    }

	public function secao(){
		$vars  		= $this->request->getPost();
		session()->set($vars);
	}
    
	public function busca_dados()
	{
        // debug($dados_emp);
		$filtro  		= $this->request->getPost();
		// debug($filtro, true);
		$busca          = $filtro['busca'];
		$periodo        = $filtro['periodo'];
		$inicio         = $filtro['inicio'];
		$fim            = $filtro['fim'];
		$empresa        = [$filtro['empresa']];
        $inicio         = dataBrToDb($inicio);
        $fim            = dataBrToDb($fim);

        $fimbu = strrpos($busca,'_');
        $tipoemp = substr($busca,$fimbu + 1);
        $busca = substr($busca, 0, $fimbu);
        // debug($busca);
        $semana = array(
            'Sun' => 'Dom', 
            'Mon' => 'Seg',
            'Tue' => 'Ter',
            'Wed' => 'Qua',
            'Thu' => 'Qui',
            'Fri' => 'Sex',
            'Sat' => 'Sáb'
        );
        $diasemana = array(['Dom','Sun'], ['Seg','Mon'],['Ter','Tue'],['Qua','Wed'],['Qui','Thu'],['Sex','Fri'],['Sáb','Sat']);
        
        $meses = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];

        $cormes = array(
            '1'  => 'rgb(255,0,0)',
            '2'  => 'rgb(0,0,255)',
            '3'  => 'rgb(255,255,0)',
            '4'  => 'rgb(0,128,0)',
            '5'  => 'rgb(255,165,0)',           
            '6'  => 'rgb(139,0,139)',
            '7'  => 'rgb(0,255,255)',            
            '8'  => 'rgb(139,69,19)',            
            '9'  => 'rgb(70,130,180)',           
            '10' => 'rgb(238,130,238)',            
            '11' => 'rgb(240,230,140)',
            '12' => 'rgb(75,0,130)',            
        );
        
        $cordia = array(
            'Sun' => 'rgb(255,0,0)',
            'Mon' => 'rgb(0,0,255)',
            'Tue' => 'rgb(255,255,0)',
            'Wed' => 'rgb(0,128,0)',
            'Thu' => 'rgb(255,165,0)',
            'Fri' => 'rgb(139,0,139)',
            'Sat' => 'rgb(0,255,255)',
        );
        $cornota = array(
            '10' =>'rgb(0,128,0)',
            '9' => 'rgb(0,128,0)',
            '8' => 'rgb(255,255,0)',
            '7' => 'rgb(255,255,0)',
            '6' => 'rgb(255,0,0)',
            '5' => 'rgb(255,0,0)',
            '4' => 'rgb(255,0,0)',           
            '3' => 'rgb(255,0,0)',            
            '2' => 'rgb(255,0,0)',            
            '1' => 'rgb(255,0,0)',            
            '0' => 'rgb(255,0,0)',            
        );

        $ret = [];
        $cores =  [];

        if(gettype($empresa[0]) == 'array'){
            $emp = $this->empresa->getEmpresa($empresa);
        } else{
            $emp = $this->empresa->getEmpresa($empresa)[0];
        }
        if($tipoemp == 'all'){
            $empid = [];
            for($e=0;$e<count($emp);$e++){
                array_push($empid, $emp[$e]['emp_id']);
            }
            if($busca == 'fat_dia'){
                // debug($empresa);
                $faturall = [];
                $faturall = $this->gerente->getFaturamentoEmpresas($empid, false, $inicio, $fim);
                // debug($faturall, true);
                $res_fatur =  [];
                $dataant = "";
                $cont = -1;
                $fatu =[];
                for ($f = 0; $f < count($faturall); $f++) {
                    $fat = $faturall[$f];
                    if($dataant != $fat['DataMovimento']){
                        if(count($fatu) > 0){
                            $cont++;
                            $cores[$cont] = $cordia[$data];
                            $res_fatur[$cont] = $fatu;        
                        }
                        $fatu =[];
                        $data = date('D',strtotime($fat['DataMovimento']));
                        $fatu['Data'] = $semana[$data].' - '.substr(dataDbToBr($fat['DataMovimento']),0,5);
                        $dataant = $fat['DataMovimento'];
                    }
                    $FatPct = floatval($fat['FatTotal']);
                    $fatu[$fat['emp_abrev']] = number_format($FatPct,2,'.','');
                }
                $cont++;
                $cores[$cont] = $cordia[$data];
                $res_fatur[$cont] = $fatu;        
                $ret['dados'] = $res_fatur;
            } else if($busca == 'fat_sem'){
                $fatur = $this->gerente->getFatDiaSemEmpresas($empid, false, $inicio, $fim);
                $res_fatur =  [];
                $cont = -1;
                $fatu =[];
                $dia_sem = '';
                for ($f = 0; $f < count($fatur); $f++) {
                    $fat = $fatur[$f];
                    if($fatur[$f]['dia_sem'] != $dia_sem){
                        if(count($fatu) > 0){
                            $cont++;
                            $cores[$cont] = $cordia[$dia_sem];
                            $res_fatur[$cont] = $fatu;        
                        }
                        $fatu =[];
                        $fatu['Dia da Semana'] = $semana[$fatur[$f]['dia_sem']];
                        $dia_sem = $fatur[$f]['dia_sem'];
                    }
                    $FatPct = floatval($fat['fat_dia_sem']);
                    $fatu[$fat['emp_abrev']] = number_format($FatPct,2,'.','');
                }
                $cont++;
                $cores[$cont] = $cordia[$dia_sem];
                $res_fatur[$cont] = $fatu;
                $ret['dados'] = $res_fatur;
            } else if($busca == 'fat_mes'){
                $fim = date('Y-m-d');
                $inicio = date('Y-m-d', strtotime("-14 months"));
                $fatur = $this->gerente->getFatMesEmpresas($empid, false, $inicio, $fim);
                $res_fatur =  [];
                $fatu = [];
                $cont = -1;
                $mesano = '';
                for ($f = 0; $f < count($fatur); $f++) {
                    $fat = $fatur[$f];
                    if($fatur[$f]['mes_num'].$fatur[$f]['ano'] != $mesano){
                        if(count($fatu) > 0){
                            $cont++;
                            // debug($mes);
                            $cores[$cont] = $cormes[$mes];
                            $res_fatur[$cont] = $fatu;        
                        }
                        $fatu =[];
                        $fatu['Mes'] = $meses[$fatur[$f]['mes_num'] -1].'/'.$fatur[$f]['ano'];
                        $mesano = $fatur[$f]['mes_num'].$fatur[$f]['ano'];
                        $mes    = ($fatur[$f]['mes_num']);
                    }
                    // $fatu =[];
                    $FatPct = floatval($fat['fat_mes']);
                    $fatu[$fat['emp_abrev']] = number_format($FatPct,2,'.','');
                    // $emp = $fat['emp_id'];
                }
                $cont++;
                $cores[$cont] = $cormes[$mes];
                $res_fatur[$cont] = $fatu;
                $ret['dados'] = $res_fatur;
                // debug($ret);
            }
        } else {
            if($busca == 'fat_dia'){
                $fatur = $this->gerente->getFaturamento($emp['emp_codempresa'], $emp['emp_codfilial'], $inicio, $fim);
                $TotPeriodo = 0;
                $TaxPeriodo = 0;
                for ($f = 0; $f < count($fatur); $f++) {
                    $fat = $fatur[$f];
                    $TotPeriodo += floatval($fat['FatTotal']);
                    $TaxPeriodo += floatval($fat['TotTaxaServico']);
                }
                if($TaxPeriodo == 0){
                    $TaxPeriodo = 1;
                }
                $res_fatur =  [];
                for ($f = 0; $f < count($fatur); $f++) {
                    $fat = $fatur[$f];
                    $fatu =[];
                    $data = date('D',strtotime($fat['DataMovimento']));
                    $fatu['Data'] = $semana[$data].' - '.substr(dataDbToBr($fat['DataMovimento']),0,5);
                    $FatPct = floatval($fat['FatTotal']);
                    $TaxPct = floatval($fat['TotTaxaServico']);
                    $fatu['FatTotal'] = (float)number_format($FatPct,2,'.','');
                    $fatu['TotTaxaServico'] = (float)number_format($TaxPct,2,'.','');
                    $cores[$f] = $cordia[$data];
                    $fatu['TotDescontos'] = (float)number_format($fat['TotDescontos'],2,'.','');
                    $fatu['TotAcrescimos'] = (float)number_format($fat['TotAcrescimos'],2,'.','');
                    $res_fatur[$f] = $fatu;
                }
                $ret['dados'] = $res_fatur;
                // debug($ret);
            } else if($busca == 'fat_sem'){
                $fatur = $this->gerente->getFatDiaSem($emp['emp_codempresa'], $emp['emp_codfilial'], $inicio, $fim);
                // debug($fatur, true);
                $TotPeriodo = 0;
                $TaxPeriodo = 0;
                for ($f = 0; $f < count($fatur); $f++) {
                    $fat = $fatur[$f];
                    $TotPeriodo += floatval($fat['fat_medio_dia']);
                }
                $res_fatur =  [];
                // debug($res_fatur);
                $cont = -1;
                $dia_sem = '';
                for ($f = 0; $f < count($fatur); $f++) {
                    $fat = $fatur[$f];
                    if($fatur[$f]['dia_sem'] != $dia_sem){
                        $cont++;
                        $fatu['Dia da Semana'] = $semana[$fatur[$f]['dia_sem']];
                        $fatu['% Faturamento'] = 0;
                        $dia_sem = $fatur[$f]['dia_sem'];
                    }
                    // $fatu =[];
                    $FatPct = floatval($fat['fat_medio_dia']);
                    $fatu['% Faturamento'] += (float)number_format($FatPct,2,'.','');
                    $cores[$cont] = $cordia[$fatur[$f]['dia_sem']];
                    $res_fatur[$cont] = $fatu;
                }
                $ret['dados'] = $res_fatur;
            } else if($busca == 'fat_mes'){
                $fim = date('Y-m-d');
                $inicio = date('Y-m-d', strtotime("-14 months"));
                $fatur = $this->gerente->getFatMes($emp['emp_codempresa'], $emp['emp_codfilial'], $inicio, $fim);
                $TotPeriodo = 0;
                for ($f = 0; $f < count($fatur); $f++) {
                    $fat = $fatur[$f];
                    $TotPeriodo += floatval($fat['fat_mes']);
                }
                $res_fatur =  [];
                // debug($res_fatur);
                $cont = -1;
                $mesano = '';
                for ($f = 0; $f < count($fatur); $f++) {
                    $fat = $fatur[$f];
                    if($fatur[$f]['mes_num'].$fatur[$f]['ano'] != $mesano){
                        $cont++;
                        $fatu['Mes'] = $meses[$fatur[$f]['mes_num'] -1].'/'.$fatur[$f]['ano'];
                        $fatu['% Faturamento'] = 0;
                        $mes = $fatur[$f]['mes_num'].$fatur[$f]['ano'];
                    }
                    // $fatu =[];
                    $FatPct = floatval($fat['fat_mes']);
                    $fatu['% Faturamento'] += (float)number_format($FatPct,2,'.','');
                    $cores[$cont] = $cormes[$fatur[$f]['mes_num']];
                    $res_fatur[$cont] = $fatu;
                }
                $ret['dados'] = $res_fatur;
                // debug($ret);
            } else if(substr($busca,0,3) == 'nps'){
                $ret['dados'] = [];
                if($busca == 'nps_dia'){
                    $notas = [];
                    $somas = [];
                    $nps_res = $this->gerente->getNotasNpsDiario($emp['emp_codempresa'], $emp['emp_codfilial'], $inicio, $fim);
                    $respostas = 0;
                    for($n=0;$n<count($nps_res); $n++){
                        $res = $nps_res[$n];
                        $dia = $res['opi_dia'];
                        $respostas += $res['respostas'];
                        $notas[$dia][10] = isset($notas[$dia][10])?$notas[$dia][10] + $res['nota10']:$res['nota10'];
                        $somas[$dia][10] = isset($somas[$dia][10])?$somas[$dia][10] + $res['soma10']:$res['soma10'];
                        $notas[$dia][9]  = isset($notas[$dia][9] )?$notas[$dia][9]  + $res['nota9']:$res['nota9'];
                        $somas[$dia][9]  = isset($somas[$dia][9] )?$somas[$dia][9]  + $res['soma9']:$res['soma9'];
                        $notas[$dia][8]  = isset($notas[$dia][8] )?$notas[$dia][8]  + $res['nota8']:$res['nota8'];
                        $somas[$dia][8]  = isset($somas[$dia][8] )?$somas[$dia][8]  + $res['soma8']:$res['soma8'];
                        $notas[$dia][7]  = isset($notas[$dia][7] )?$notas[$dia][7]  + $res['nota7']:$res['nota7'];
                        $somas[$dia][7]  = isset($somas[$dia][7] )?$somas[$dia][7]  + $res['soma7']:$res['soma7'];
                        $notas[$dia][6]  = isset($notas[$dia][6] )?$notas[$dia][6]  + $res['nota6']:$res['nota6'];
                        $somas[$dia][6]  = isset($somas[$dia][6] )?$somas[$dia][6]  + $res['soma6']:$res['soma6'];
                        $notas[$dia][5]  = isset($notas[$dia][5] )?$notas[$dia][5]  + $res['nota5']:$res['nota5'];
                        $somas[$dia][5]  = isset($somas[$dia][5] )?$somas[$dia][5]  + $res['soma5']:$res['soma5'];
                        $notas[$dia][4]  = isset($notas[$dia][4] )?$notas[$dia][4]  + $res['nota4']:$res['nota4'];
                        $somas[$dia][4]  = isset($somas[$dia][4] )?$somas[$dia][4]  + $res['soma4']:$res['soma4'];
                        $notas[$dia][3]  = isset($notas[$dia][3] )?$notas[$dia][3]  + $res['nota3']:$res['nota3'];
                        $somas[$dia][3]  = isset($somas[$dia][3] )?$somas[$dia][3]  + $res['soma3']:$res['soma3'];
                        $notas[$dia][2]  = isset($notas[$dia][2] )?$notas[$dia][2]  + $res['nota2']:$res['nota2'];
                        $somas[$dia][2]  = isset($somas[$dia][2] )?$somas[$dia][2]  + $res['soma2']:$res['soma2'];
                        $notas[$dia][1]  = isset($notas[$dia][1] )?$notas[$dia][1]  + $res['nota1']:$res['nota1'];
                        $somas[$dia][1]  = isset($somas[$dia][1] )?$somas[$dia][1]  + $res['soma1']:$res['soma1'];
                        $notas[$dia][0]  = isset($notas[$dia][0] )?$notas[$dia][0]  + $res['nota0']:$res['nota0'];
                        $somas[$dia][0]  = isset($somas[$dia][0] )?$somas[$dia][0]  + $res['soma0']:$res['soma0'];
                    }
                    // krsort($notas);
                    // krsort($somas);
                    // debug($notas);
                    foreach($notas as $dia => $val){
                        ksort($notas[$dia]);
                        $total[$dia]      = 0;
                        $promotores[$dia] = 0;
                        $neutros[$dia]    = 0;
                        $detratores[$dia] = 0;
                        foreach($notas[$dia] as $key => $valor){
                            $total[$dia] += $valor;
                            if($key == 10 || $key == 9){ // promotores
                                $promotores[$dia] += $valor;
                            } else if($key == 8 || $key == 7){ // neutros
                                $neutros[$dia] += $valor;
                            } else { // detratores
                                $detratores[$dia] += $valor;
                            }
                        }
                        $promotopct[$dia] = 0;
                        $promotopct[$dia] = number_format(($promotores[$dia] * 100) / $total[$dia],2);

                        $neutrospct[$dia]    = 0;
                        $neutrospct[$dia] = number_format(($neutros[$dia] * 100) / $total[$dia],2);

                        $detratopct[$dia] = 0;
                        $detratopct[$dia] = number_format(($detratores[$dia] * 100) / $total[$dia],2);

                        $npsmedio[$dia] = number_format((($promotores[$dia] - $detratores[$dia]) * 100) / $total[$dia],2);
                    }
                    if(isset($npsmedio)){
                        $ct = 0;
                        ksort($npsmedio);
                        foreach($npsmedio as $key => $valor){
                            $data = date('D',strtotime($key));
                            $ret['dados'][$ct]['dia']= $semana[$data].' - '.substr(dataDbToBr($key),0,5);
                            $ret['dados'][$ct]['npsmedio']= number_format($valor,0) ;
                            $diasem = substr(getdate(strtotime($key))['wday'],0,3);
                            $cores[$ct] = $cordia[$diasemana[$diasem][1]];                            
                            $ct++;
                        }
                    }
                    $ret['cores'] = $cores;
                    $ret['respostas'] = $respostas;
                } else if($busca == 'nps_sem'){
                    $notas = [];
                    $somas = [];
                    $nps_res = $this->gerente->getNotasNpsDiario($emp['emp_codempresa'], $emp['emp_codfilial'], $inicio, $fim);
                    $respostas = 0;
                    for($n=0;$n<count($nps_res); $n++){
                        $res = $nps_res[$n];
                        $dia = $res['opi_dia'];
                        $respostas += $res['respostas'];
                        $notas[$dia][10] = isset($notas[$dia][10])?$notas[$dia][10] + $res['nota10']:$res['nota10'];
                        $somas[$dia][10] = isset($somas[$dia][10])?$somas[$dia][10] + $res['soma10']:$res['soma10'];
                        $notas[$dia][9]  = isset($notas[$dia][9] )?$notas[$dia][9]  + $res['nota9']:$res['nota9'];
                        $somas[$dia][9]  = isset($somas[$dia][9] )?$somas[$dia][9]  + $res['soma9']:$res['soma9'];
                        $notas[$dia][8]  = isset($notas[$dia][8] )?$notas[$dia][8]  + $res['nota8']:$res['nota8'];
                        $somas[$dia][8]  = isset($somas[$dia][8] )?$somas[$dia][8]  + $res['soma8']:$res['soma8'];
                        $notas[$dia][7]  = isset($notas[$dia][7] )?$notas[$dia][7]  + $res['nota7']:$res['nota7'];
                        $somas[$dia][7]  = isset($somas[$dia][7] )?$somas[$dia][7]  + $res['soma7']:$res['soma7'];
                        $notas[$dia][6]  = isset($notas[$dia][6] )?$notas[$dia][6]  + $res['nota6']:$res['nota6'];
                        $somas[$dia][6]  = isset($somas[$dia][6] )?$somas[$dia][6]  + $res['soma6']:$res['soma6'];
                        $notas[$dia][5]  = isset($notas[$dia][5] )?$notas[$dia][5]  + $res['nota5']:$res['nota5'];
                        $somas[$dia][5]  = isset($somas[$dia][5] )?$somas[$dia][5]  + $res['soma5']:$res['soma5'];
                        $notas[$dia][4]  = isset($notas[$dia][4] )?$notas[$dia][4]  + $res['nota4']:$res['nota4'];
                        $somas[$dia][4]  = isset($somas[$dia][4] )?$somas[$dia][4]  + $res['soma4']:$res['soma4'];
                        $notas[$dia][3]  = isset($notas[$dia][3] )?$notas[$dia][3]  + $res['nota3']:$res['nota3'];
                        $somas[$dia][3]  = isset($somas[$dia][3] )?$somas[$dia][3]  + $res['soma3']:$res['soma3'];
                        $notas[$dia][2]  = isset($notas[$dia][2] )?$notas[$dia][2]  + $res['nota2']:$res['nota2'];
                        $somas[$dia][2]  = isset($somas[$dia][2] )?$somas[$dia][2]  + $res['soma2']:$res['soma2'];
                        $notas[$dia][1]  = isset($notas[$dia][1] )?$notas[$dia][1]  + $res['nota1']:$res['nota1'];
                        $somas[$dia][1]  = isset($somas[$dia][1] )?$somas[$dia][1]  + $res['soma1']:$res['soma1'];
                        $notas[$dia][0]  = isset($notas[$dia][0] )?$notas[$dia][0]  + $res['nota0']:$res['nota0'];
                        $somas[$dia][0]  = isset($somas[$dia][0] )?$somas[$dia][0]  + $res['soma0']:$res['soma0'];
                    }
                    foreach($notas as $dia => $val){
                        ksort($notas[$dia]);
                        $total[$dia]      = 0;
                        $promotores[$dia] = 0;
                        $neutros[$dia]    = 0;
                        $detratores[$dia] = 0;
                        foreach($notas[$dia] as $key => $valor){
                            $total[$dia] += $valor;
                            if($key == 10 || $key == 9){ // promotores
                                $promotores[$dia] += $valor;
                            } else if($key == 8 || $key == 7){ // neutros
                                $neutros[$dia] += $valor;
                            } else { // detratores
                                $detratores[$dia] += $valor;
                            }
                        }
                        $promotopct[$dia] = 0;
                        $promotopct[$dia] = number_format(($promotores[$dia] * 100) / $total[$dia],2);
    
                        $neutrospct[$dia]    = 0;
                        $neutrospct[$dia] = number_format(($neutros[$dia] * 100) / $total[$dia],2);
    
                        $detratopct[$dia] = 0;
                        $detratopct[$dia] = number_format(($detratores[$dia] * 100) / $total[$dia],2);
    
                        $npsmedio[$dia] = number_format((($promotores[$dia] - $detratores[$dia]) * 100) / $total[$dia],2);
                    }
                    foreach($npsmedio as $dia => $valor){
                        $diasem = substr(getdate(strtotime($dia))['wday'],0,3);
                        if(isset($ctadiasem[$diasem])){
                            $ctadiasem[$diasem]++;
                        } else {
                            $ctadiasem[$diasem] = 1;
                        }
                        if(isset($npsmediosom[$diasem])){
                            $npsmediosom[$diasem] += $valor;
                        } else {
                            $npsmediosom[$diasem] = $valor;                        
                        }
                    }
                    $ct =  0;
                    ksort($ctadiasem);
                    foreach($ctadiasem as $key  => $valor){
                        $npsmediosem[$key] = $npsmediosom[$key] / $ctadiasem[$key];
                        $ret['dados'][$ct]['sem']= $diasemana[$key][0];
                        $ret['dados'][$ct]['npsmedio']= number_format($npsmediosem[$key],2) ;
                        $cores[$ct] = $cordia[$diasemana[$key][1]];                            
                        $ct++;
                    }
                    $ret['respostas'] = $respostas;
                } else if($busca == 'nps_mes'){
                    $notas = [];
                    $somas = [];
                    $fim = date('Y-m-d');
                    $inicio = date('Y-m-d', strtotime("-14 months"));
                    $nps_res = $this->gerente->getNotasNpsMes($emp['emp_codempresa'], $emp['emp_codfilial'], $inicio, $fim);
                    $respostas = 0;
                    for($n=0;$n<count($nps_res); $n++){
                        $res = $nps_res[$n];
                        $mes = substr('00'.$res['mes'],-2);
                        $ano = $res['ano'];
                        $respostas += $res['respostas'];
                        $notas[$mes.$ano][10] = isset($notas[$mes.$ano][10])?$notas[$mes.$ano][10] + $res['nota10']:$res['nota10'];
                        $somas[$mes.$ano][10] = isset($somas[$mes.$ano][10])?$somas[$mes.$ano][10] + $res['soma10']:$res['soma10'];
                        $notas[$mes.$ano][9]  = isset($notas[$mes.$ano][9] )?$notas[$mes.$ano][9]  + $res['nota9']:$res['nota9'];
                        $somas[$mes.$ano][9]  = isset($somas[$mes.$ano][9] )?$somas[$mes.$ano][9]  + $res['soma9']:$res['soma9'];
                        $notas[$mes.$ano][8]  = isset($notas[$mes.$ano][8] )?$notas[$mes.$ano][8]  + $res['nota8']:$res['nota8'];
                        $somas[$mes.$ano][8]  = isset($somas[$mes.$ano][8] )?$somas[$mes.$ano][8]  + $res['soma8']:$res['soma8'];
                        $notas[$mes.$ano][7]  = isset($notas[$mes.$ano][7] )?$notas[$mes.$ano][7]  + $res['nota7']:$res['nota7'];
                        $somas[$mes.$ano][7]  = isset($somas[$mes.$ano][7] )?$somas[$mes.$ano][7]  + $res['soma7']:$res['soma7'];
                        $notas[$mes.$ano][6]  = isset($notas[$mes.$ano][6] )?$notas[$mes.$ano][6]  + $res['nota6']:$res['nota6'];
                        $somas[$mes.$ano][6]  = isset($somas[$mes.$ano][6] )?$somas[$mes.$ano][6]  + $res['soma6']:$res['soma6'];
                        $notas[$mes.$ano][5]  = isset($notas[$mes.$ano][5] )?$notas[$mes.$ano][5]  + $res['nota5']:$res['nota5'];
                        $somas[$mes.$ano][5]  = isset($somas[$mes.$ano][5] )?$somas[$mes.$ano][5]  + $res['soma5']:$res['soma5'];
                        $notas[$mes.$ano][4]  = isset($notas[$mes.$ano][4] )?$notas[$mes.$ano][4]  + $res['nota4']:$res['nota4'];
                        $somas[$mes.$ano][4]  = isset($somas[$mes.$ano][4] )?$somas[$mes.$ano][4]  + $res['soma4']:$res['soma4'];
                        $notas[$mes.$ano][3]  = isset($notas[$mes.$ano][3] )?$notas[$mes.$ano][3]  + $res['nota3']:$res['nota3'];
                        $somas[$mes.$ano][3]  = isset($somas[$mes.$ano][3] )?$somas[$mes.$ano][3]  + $res['soma3']:$res['soma3'];
                        $notas[$mes.$ano][2]  = isset($notas[$mes.$ano][2] )?$notas[$mes.$ano][2]  + $res['nota2']:$res['nota2'];
                        $somas[$mes.$ano][2]  = isset($somas[$mes.$ano][2] )?$somas[$mes.$ano][2]  + $res['soma2']:$res['soma2'];
                        $notas[$mes.$ano][1]  = isset($notas[$mes.$ano][1] )?$notas[$mes.$ano][1]  + $res['nota1']:$res['nota1'];
                        $somas[$mes.$ano][1]  = isset($somas[$mes.$ano][1] )?$somas[$mes.$ano][1]  + $res['soma1']:$res['soma1'];
                        $notas[$mes.$ano][0]  = isset($notas[$mes.$ano][0] )?$notas[$mes.$ano][0]  + $res['nota0']:$res['nota0'];
                        $somas[$mes.$ano][0]  = isset($somas[$mes.$ano][0] )?$somas[$mes.$ano][0]  + $res['soma0']:$res['soma0'];
                    }
                    // krsort($notas);
                    // krsort($somas);
                    // debug($notas);
                    foreach($notas as $mesano => $val){
                        // debug($sem);
                        // ksort($notas[$mesano]);
                        $total[$mesano]      = 0;
                        $promotores[$mesano] = 0;
                        $neutros[$mesano]    = 0;
                        $detratores[$mesano] = 0;
                        foreach($notas[$mesano] as $key => $valor){
                            $total[$mesano] += $valor;
                            if($key == 10 || $key == 9){ // promotores
                                $promotores[$mesano] += $valor;
                            } else if($key == 8 || $key == 7){ // neutros
                                $neutros[$mesano] += $valor;
                            } else { // detratores
                                $detratores[$mesano] += $valor;
                            }
                        }
                        $promotopct[$mesano] = 0;
                        $promotopct[$mesano] = number_format(($promotores[$mesano] * 100) / $total[$mesano],2);
                        $neutrospct[$mesano]    = 0;
                        $neutrospct[$mesano] = number_format(($neutros[$mesano] * 100) / $total[$mesano],2);
                        $detratopct[$mesano] = 0;
                        $detratopct[$mesano] = number_format(($detratores[$mesano] * 100) / $total[$mesano],2);

                        $npsmedio[$mesano] = number_format((($promotores[$mesano] - $detratores[$mesano]) * 100) / $total[$mesano],2);
                    }
                    if(isset($npsmedio)){
                        $ct = 0;
                        foreach($npsmedio as $p => $value){
                            $nummes = intval(substr($p,0,2));
                            $ret['dados'][$ct]['mes']= $meses[$nummes-1].'/'.substr($p,-4);
                            $ret['dados'][$ct]['npsmedio']= number_format($npsmedio[$p],0) ;
                            $cores[$ct] = $cormes[$nummes];                            
                            $ct++;
                        }
                        $ret['cores'] = $cores;
                        $ret['respostas'] = $respostas;
                    }
                } else if($busca == 'nps_clientes'){
                    $notas = [];
                    $somas = [];
                    $nps_res = $this->gerente->getNotasNpsDiario($emp['emp_codempresa'], $emp['emp_codfilial'], $inicio, $fim);
                    $respostas = 0;
                    $notas[10] = 0;
                    $somas[10] = 0;
                    $notas[9] = 0;
                    $somas[9] = 0;
                    $notas[8] = 0;
                    $somas[8] = 0;
                    $notas[7] = 0;
                    $somas[7] = 0;
                    $notas[6] = 0;
                    $somas[6] = 0;
                    $notas[5] = 0;
                    $somas[5] = 0;
                    $notas[4] = 0;
                    $somas[4] = 0;
                    $notas[3] = 0;
                    $somas[3] = 0;
                    $notas[2] = 0;
                    $somas[2] = 0;
                    $notas[1] = 0;
                    $somas[1] = 0;
                    $notas[0] = 0;
                    $somas[0] = 0;                    
                    // debug($nps_res);
                    for($n=0;$n<count($nps_res); $n++){
                        $res = $nps_res[$n];
                        // debug($res);
                        $respostas += $res['respostas'];
                        $notas[10] += $res['nota10'];
                        $somas[10] += $res['soma10'];
                        $notas[9]  += $res['nota9'];
                        $somas[9]  += $res['soma9'];
                        $notas[8]  += $res['nota8'];
                        $somas[8]  += $res['soma8'];
                        $notas[7]  += $res['nota7'];
                        $somas[7]  += $res['soma7'];
                        $notas[6]  += $res['nota6'];
                        $somas[6]  += $res['soma6'];
                        $notas[5]  += $res['nota5'];
                        $somas[5]  += $res['soma5'];
                        $notas[4]  += $res['nota4'];
                        $somas[4]  += $res['soma4'];
                        $notas[3]  += $res['nota3'];
                        $somas[3]  += $res['soma3'];
                        $notas[2]  += $res['nota2'];
                        $somas[2]  += $res['soma2'];
                        $notas[1]  += $res['nota1'];
                        $somas[1]  += $res['soma1'];
                        $notas[0]  += $res['nota0'];
                        $somas[0]  += $res['soma0'];
                    }
                    $tot_notas = array_sum($notas);
                    $tot_somas = array_sum($somas);
                    $nota_media = $tot_somas / $tot_notas;
                    krsort($notas);
                    krsort($somas);
                    $promotores = (isset($notas[10])?$notas[10]:0) + (isset($notas[9])?$notas[9]:0);
                    $neutros = (isset($notas[8])?$notas[8]:0) + (isset($notas[7])?$notas[7]:0);
                    $detratores = (isset($notas[6])?$notas[6]:0) + 
                                (isset($notas[5])?$notas[5]:0) + 
                                (isset($notas[4])?$notas[4]:0) + 
                                (isset($notas[3])?$notas[3]:0) + 
                                (isset($notas[2])?$notas[2]:0) + 
                                (isset($notas[1])?$notas[1]:0) + 
                                (isset($notas[0])?$notas[0]:0);

                    $promotopct = ($promotores * 100) / $tot_notas;
                    $neutpct = ($neutros * 100) / $tot_notas;
                    $detratopct = ($detratores * 100) / $tot_notas;
        
                    $ret['dados'][0]['Nota'] = 'Promotores';
                    $ret['dados'][0]['Valor'] = number_format($promotopct,2);
                    $cores[0] = $cornota[10];

                    $ret['dados'][1]['Nota'] = 'Neutros';
                    $ret['dados'][1]['Valor'] = number_format($neutpct,2);
                    $cores[1] = $cornota[8];

                    $ret['dados'][2]['Nota'] = 'Detratores';
                    $ret['dados'][2]['Valor'] = number_format($detratopct,2);
                    $cores[2] = $cornota[0];

                    $ret['cores'] = $cores;
                    $ret['nps'] = number_format(($promotopct - $detratopct),2);
                    $ret['respostas'] = $respostas;
                }
            } else if($busca == 'atrasos_deliv'){
                // verifica Delivery
                $deliv = $this->deliv->getTempos60($emp['emp_codempresa'], $emp['emp_codfilial'], $inicio, $fim);
                // debug($deliv);
                $res_deliv =  [];
                for ($d = 0; $d < count($deliv); $d++) {
                    $ddel = $deliv[$d];
                    $pct_almoco = ($ddel['almocomaior60'] * 100) / ($ddel['tot_pedido_almoco']>0?$ddel['tot_pedido_almoco']:1);
                    $pct_jantar = ($ddel['jantarmaior60'] * 100) / ($ddel['tot_pedido_jantar']>0?$ddel['tot_pedido_jantar']:1);
                    $data = date('D',strtotime($ddel['pcfy_DataPedido']));
                    $res_deliv[$d]['Data'] = $semana[$data].' - '.substr(dataDbToBr($ddel['pcfy_DataPedido']),0,5);
                    $res_deliv[$d]['Almoco'] = number_format($pct_almoco,2);
                    $res_deliv[$d]['Jantar'] = number_format($pct_jantar,2);
                    $cores[$d] = $cordia[$data];
                }
                $ret['dados'] = $res_deliv;
            }
        }
        $ret['registros'] = count($ret['dados']);
        $ret['cores'] = $cores;
        $newdata = [
            'busca'        	    => $busca,
            'inicio'        	=> $inicio,
            'fim'        	    => $fim,
            'empresa'			=> $empresa,
        ];
        session()->set($newdata);
        
        echo json_encode($ret);
    }


}
