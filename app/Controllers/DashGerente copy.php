<?php namespace App\Controllers;

use App\Controllers\BaseController;
use App\Controllers\IntegraOpinae;
use App\Libraries\MyCampo;
use App\Models\Config\ConfigApiModel;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\DelivModel;
use App\Models\GerenteModel;


class DashGerente extends BaseController
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

        $this->data['campos']     	= $campos;  
        return view('vw_dashgerente', $this->data);
    }
    
    public function def_campos(){
        $periodo =  new MyCampo();
        $periodo->nome        = 'periodo'; 
        $periodo->id          = 'periodo';
        $periodo->label       = 'Informe o Período';
        $periodo->valor       = '';
        $periodo->size        = 30;
        $periodo->tamanho     = 30;
        $periodo->funcChan    = 'carrega_graficos()';
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
        $emp->selecionado           = $empresas[0];
        $emp->opcoes                = $empres;
        $emp->funcChan              = 'carrega_graficos()';
        $emp->dispForm              = '3col';
        if(count($empresas) == 1){
            $emp->leitura           = true;
        }
        $this->dash_empresa         = $emp->crSelect();
        
    }

	public function secao(){
		$vars  		= $this->request->getPost();
		session()->set($vars);
	}
    
	public function busca_dados()
	{
        // debug($dados_emp);
		$filtro  		= $this->request->getPost();
		// debug($filtro, false);
		$busca          = $filtro['busca'];
		$periodo        = $filtro['periodo'];
		$inicio         = $filtro['inicio'];
		$fim            = $filtro['fim'];
		$empresa        = [$filtro['empresa']];
        $inicio         = dataBrToDb($inicio);
        $fim            = dataBrToDb($fim);

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
            '1' => 'rgba(0, 0, 255, 0.5)',
            '2' => 'rgba(0, 255, 255, 0.5)',
            '3' => 'rgba(255, 127, 127, 0.5)',
            '4' => 'rgba(255, 0, 255, 0.5)',
            '5' => 'rgba(200, 200, 200, 0.5)',           
            '6' => 'rgba(0, 255, 0, 0.5)',
            '7' => 'rgba(200, 255, 10, 0.5)',            
            '8' => 'rgba(255, 100, 0, 0.5)',            
            '9' => 'rgba(100, 100, 100, 0.5)',           
            '10' => 'rgba(255, 0, 200, 0.5)',            
            '11' => 'rgba(255, 255, 0, 0.5)',
            '12' => 'rgba(100, 200, 200, 0.5)',            
        );
        
        $cordia = array(
            'Sun' => 'rgba(0, 0, 255, 0.5)',
            'Mon' => 'rgba(255, 0, 255, 0.5)',
            'Tue' => 'rgba(255, 255, 0, 0.5)',
            'Wed' => 'rgba(0, 255, 255, 0.5)',
            'Thu' => 'rgba(0, 255, 0, 0.5)',
            'Fri' => 'rgba(255, 127, 127, 0.5)',
            'Sat' => 'rgba(255, 0, 0, 0.5)'            
        );
        $cornota = array(
            '10' => 'rgba(0, 255, 0, 0.5)',
            '9' => 'rgba(0, 255, 0, 0.5)',
            '8' => 'rgba(255, 255, 0, 0.5)',
            '7' => 'rgba(255, 255, 0, 0.5)',
            '6' => 'rgba(255, 0, 0, 0.5)',
            '5' => 'rgba(255, 0, 0, 0.5)',
            '4' => 'rgba(255, 0, 0, 0.5)',           
            '3' => 'rgba(255, 0, 0, 0.5)',            
            '2' => 'rgba(255, 0, 0, 0.5)',            
            '1' => 'rgba(255, 0, 0, 0.5)',            
            '0' => 'rgba(255, 0, 0, 0.5)',            
        );

        $ret = [];
        $cores =  [];

        $emp = $this->empresa->getEmpresa($empresa)[0];

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
                $FatPct = floatval((floatval($fat['FatTotal']) * 100) / $TotPeriodo);
                $TaxPct = floatval((floatval($fat['TotTaxaServico']) * 100) / $TotPeriodo);
                $fatu['FatTotal'] = (float)number_format($FatPct,2,'.','');
                $fatu['TotTaxaServico'] = (float)number_format($TaxPct,2,'.','');
                $cores[$f] = $cordia[$data];
                $fatu['TotDescontos'] = (float)number_format($fat['TotDescontos'],2,'.','');
                $fatu['TotAcrescimos'] = (float)number_format($fat['TotAcrescimos'],2,'.','');
                $res_fatur[$f] = $fatu;
            }
            $ret['dados'] = $res_fatur;
        } else if($busca == 'fat_sem'){
            $fatur = $this->gerente->getFatDiaSem($emp['emp_codempresa'], $emp['emp_codfilial'], $inicio, $fim);
            // debug($fatur, true);
            $TotPeriodo = 0;
            $TaxPeriodo = 0;
            for ($f = 0; $f < count($fatur); $f++) {
                $fat = $fatur[$f];
                $TotPeriodo += floatval($fat['fat_dia_sem']);
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
                $FatPct = floatval((floatval($fat['fat_dia_sem']) * 100) / $TotPeriodo);
                $fatu['% Faturamento'] += (float)number_format($FatPct,2,'.','');
                $cores[$cont] = $cordia[$fatur[$f]['dia_sem']];
                $res_fatur[$cont] = $fatu;
            }
            $ret['dados'] = $res_fatur;
        } else if($busca == 'fat_mes'){
            $fim = date('Y-m-d');
            $inicio = date('Y-m-d', strtotime("-5 months"));
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
                $FatPct = floatval((floatval($fat['fat_mes']) * 100) / $TotPeriodo);
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
                for($n=0;$n<count($nps_res); $n++){
                    $res = $nps_res[$n];
                    $dia = dataDbToBr($res['opi_dia']);
                    $notas[$dia][10] = $res['nota10'];
                    $somas[$dia][10] = $res['soma10'];
                    $notas[$dia][9]  = $res['nota9'];
                    $somas[$dia][9]  = $res['soma9'];
                    $notas[$dia][8]  = $res['nota8'];
                    $somas[$dia][8]  = $res['soma8'];
                    $notas[$dia][7]  = $res['nota7'];
                    $somas[$dia][7]  = $res['soma7'];
                    $notas[$dia][6]  = $res['nota6'];
                    $somas[$dia][6]  = $res['soma6'];
                    $notas[$dia][5]  = $res['nota5'];
                    $somas[$dia][5]  = $res['soma5'];
                    $notas[$dia][4]  = $res['nota4'];
                    $somas[$dia][4]  = $res['soma4'];
                    $notas[$dia][3]  = $res['nota3'];
                    $somas[$dia][3]  = $res['soma3'];
                    $notas[$dia][2]  = $res['nota2'];
                    $somas[$dia][2]  = $res['soma2'];
                    $notas[$dia][1]  = $res['nota1'];
                    $somas[$dia][1]  = $res['soma1'];
                    $notas[$dia][0]  = $res['nota0'];
                    $somas[$dia][0]  = $res['soma0'];
                }
                krsort($notas);
                krsort($somas);
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
                $ret['cores'] = $cores;
                $ret['respostas'] = count($pesq['tableData']);
            } else if($busca == 'nps_sem'){
                $notas = [];
                $somas = [];
                $nps_res = $this->gerente->getNotasNpsSemana($emp['emp_codempresa'], $emp['emp_codfilial'], $inicio, $fim);
                for($n=0;$n<count($nps_res); $n++){
                    $res = $nps_res[$n];
                    $dia_sem = $res['dia_sem'];
                    $notas[$dia_sem][10] = $res['nota10'];
                    $somas[$dia_sem][10] = $res['soma10'];
                    $notas[$dia_sem][9]  = $res['nota9'];
                    $somas[$dia_sem][9]  = $res['soma9'];
                    $notas[$dia_sem][8]  = $res['nota8'];
                    $somas[$dia_sem][8]  = $res['soma8'];
                    $notas[$dia_sem][7]  = $res['nota7'];
                    $somas[$dia_sem][7]  = $res['soma7'];
                    $notas[$dia_sem][6]  = $res['nota6'];
                    $somas[$dia_sem][6]  = $res['soma6'];
                    $notas[$dia_sem][5]  = $res['nota5'];
                    $somas[$dia_sem][5]  = $res['soma5'];
                    $notas[$dia_sem][4]  = $res['nota4'];
                    $somas[$dia_sem][4]  = $res['soma4'];
                    $notas[$dia_sem][3]  = $res['nota3'];
                    $somas[$dia_sem][3]  = $res['soma3'];
                    $notas[$dia_sem][2]  = $res['nota2'];
                    $somas[$dia_sem][2]  = $res['soma2'];
                    $notas[$dia_sem][1]  = $res['nota1'];
                    $somas[$dia_sem][1]  = $res['soma1'];
                    $notas[$dia_sem][0]  = $res['nota0'];
                    $somas[$dia_sem][0]  = $res['soma0'];
                }
                krsort($notas);
                krsort($somas);
                // debug($notas);
                foreach($notas as $sem => $val){
                    // debug($sem);
                    ksort($notas[$sem]);
                    $total[$sem]      = 0;
                    $promotores[$sem] = 0;
                    $neutros[$sem]    = 0;
                    $detratores[$sem] = 0;
                    foreach($notas[$sem] as $key => $valor){
                        $total[$sem] += $valor;
                        if($key == 10 || $key == 9){ // promotores
                            $promotores[$sem] += $valor;
                        } else if($key == 8 || $key == 7){ // neutros
                            $neutros[$sem] += $valor;
                        } else { // detratores
                            $detratores[$sem] += $valor;
                        }
                    }
                    $promotopct[$sem] = 0;
                    $promotopct[$sem] = number_format(($promotores[$sem] * 100) / $total[$sem],2);

                    $neutrospct[$sem]    = 0;
                    $neutrospct[$sem] = number_format(($neutros[$sem] * 100) / $total[$sem],2);

                    $detratopct[$sem] = 0;
                    $detratopct[$sem] = number_format(($detratores[$sem] * 100) / $total[$sem],2);

                    $npsmedio[$sem] = number_format((($promotores[$sem] - $detratores[$sem]) * 100) / $total[$sem],2);
                }
                for($p=0;$p<count($npsmedio);$p++){
                    $ret['dados'][$p]['dia']= $diasemana[$p][0];
                    $ret['dados'][$p]['npsmedio']= number_format($npsmedio[$p],0) ;
                    $cores[$p] = $cordia[$diasemana[$p][1]];                            
                }
                $ret['cores'] = $cores;
            } else if($busca == 'nps_mes'){
                $notas = [];
                $somas = [];
                $nps_res = $this->gerente->getNotasNpsMes($emp['emp_codempresa'], $emp['emp_codfilial'], $inicio, $fim);
                for($n=0;$n<count($nps_res); $n++){
                    $res = $nps_res[$n];
                    $mes = substr('00'.$res['mes'],-2);
                    $ano = $res['ano'];
                    $notas[$mes.$ano][10] = $res['nota10'];
                    $somas[$mes.$ano][10] = $res['soma10'];
                    $notas[$mes.$ano][9]  = $res['nota9'];
                    $somas[$mes.$ano][9]  = $res['soma9'];
                    $notas[$mes.$ano][8]  = $res['nota8'];
                    $somas[$mes.$ano][8]  = $res['soma8'];
                    $notas[$mes.$ano][7]  = $res['nota7'];
                    $somas[$mes.$ano][7]  = $res['soma7'];
                    $notas[$mes.$ano][6]  = $res['nota6'];
                    $somas[$mes.$ano][6]  = $res['soma6'];
                    $notas[$mes.$ano][5]  = $res['nota5'];
                    $somas[$mes.$ano][5]  = $res['soma5'];
                    $notas[$mes.$ano][4]  = $res['nota4'];
                    $somas[$mes.$ano][4]  = $res['soma4'];
                    $notas[$mes.$ano][3]  = $res['nota3'];
                    $somas[$mes.$ano][3]  = $res['soma3'];
                    $notas[$mes.$ano][2]  = $res['nota2'];
                    $somas[$mes.$ano][2]  = $res['soma2'];
                    $notas[$mes.$ano][1]  = $res['nota1'];
                    $somas[$mes.$ano][1]  = $res['soma1'];
                    $notas[$mes.$ano][0]  = $res['nota0'];
                    $somas[$mes.$ano][0]  = $res['soma0'];
                }
                krsort($notas);
                krsort($somas);
                // debug($notas);
                foreach($notas as $mesano => $val){
                    // debug($sem);
                    ksort($notas[$mesano]);
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
                    $neutrospct[$mesano] = number_format(($neutros[$mesano] * 100) / $total[$mes],2);
                    $detratopct[$mesano] = 0;
                    $detratopct[$mesano] = number_format(($detratores[$mesano] * 100) / $total[$mesano],2);

                    $npsmedio[$mesano] = number_format((($promotores[$mesano] - $detratores[$mesano]) * 100) / $total[$mesano],2);
                }
                ksort($npsmedio);
                $ct = 0;
                foreach($npsmedio as $p => $value){
                    $nummes = intval(substr($p,0,2));
                    $ret['dados'][$ct]['mes']= $meses[$nummes-1].'/'.substr($p,-4);
                    $ret['dados'][$ct]['npsmedio']= number_format($npsmedio[$p],0) ;
                    $cores[$ct] = $cormes[$p];                            
                    $ct++;
                }
                $ret['cores'] = $cores;
            } else if($busca == 'nps_clientes'){
                $notas = [];
                $somas = [];
                $nps_res = $this->gerente->getNotasNpsDiario($emp['emp_codempresa'], $emp['emp_codfilial'], $inicio, $fim);
                for($n=0;$n<count($nps_res); $n++){
                    $res = $nps_res[$n];
                    // $dia = dataDbToBr($res['opi_dia']);
                    $notas[10] = $res['nota10'];
                    $somas[10] = $res['soma10'];
                    $notas[9]  = $res['nota9'];
                    $somas[9]  = $res['soma9'];
                    $notas[8]  = $res['nota8'];
                    $somas[8]  = $res['soma8'];
                    $notas[7]  = $res['nota7'];
                    $somas[7]  = $res['soma7'];
                    $notas[6]  = $res['nota6'];
                    $somas[6]  = $res['soma6'];
                    $notas[5]  = $res['nota5'];
                    $somas[5]  = $res['soma5'];
                    $notas[4]  = $res['nota4'];
                    $somas[4]  = $res['soma4'];
                    $notas[3]  = $res['nota3'];
                    $somas[3]  = $res['soma3'];
                    $notas[2]  = $res['nota2'];
                    $somas[2]  = $res['soma2'];
                    $notas[1]  = $res['nota1'];
                    $somas[1]  = $res['soma1'];
                    $notas[0]  = $res['nota0'];
                    $somas[0]  = $res['soma0'];
                }
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
                $ret['respostas'] = count($pesq['tableData']);
            }
            // $nome_api = '';
            // if($empresa[0] == '1'){
            //     $nome_api = 'Opinae 039';
            // } else if($empresa[0] == '3'){
            //     $nome_api = 'Opinae 040';
            // } else if($empresa[0] == '2'){
            //     $nome_api = 'Opinae 041';
            // } else if($empresa[0] == '7'){
            //     $nome_api = 'Opinae 201';
            // }
            // if($nome_api != ''){
            //     $api = $this->apis->getApisSearch($nome_api);
            //     if($api){
            //         $api = $api[0];
            //         if($busca == 'nps_mes'){
            //             $fim = date('Y-m-d');
            //             $inicio = date('Y-m-d', strtotime("-5 months"));                                    
            //         }
            //         $pesquisas = get_opinae_curl($api, $inicio, $fim);
            //         $pesq = json_decode(json_encode($pesquisas), true);
            //         // debug($pesq,true);
            //         $notas = [];
            //         $somas = [];
            //         foreach($pesq['tableData'] as $key => $value){
            //             if(is_array($value)){
            //                 $ctkey = 0;
            //                 foreach($value as $chave => $valor){
            //                     $ctkey++;
            //                     if($ctkey == 4){
            //                         $notas[$valor] = isset($notas[$valor]) ? $notas[$valor] + 1:1;
            //                         $somas[$valor] = isset($somas[$valor]) ? $somas[$valor] + $valor:$valor;
            //                          break;
            //                     }
            //                 }
            //             }
            //         }
            //         $tot_notas = array_sum($notas);
            //         $tot_somas = array_sum($somas);
            //         $nota_media = $tot_somas / $tot_notas;
            //         krsort($notas);
            //         krsort($somas);
                    if($busca == 'nps_notas'){
                        $ct = 0;
                        foreach($notas as $key => $value){
                            $ret['dados'][$ct]['Nota']= 'Nota '.$key ;
                            $pct_nota = ($value * 100) / $tot_notas;
                            $ret['dados'][$ct]['valor']= number_format($pct_nota,2) ;
                            $cores[$ct] = $cornota[$key];
                            $ct++;
                        }
                        $ret['cores'] = $cores;
                        $ret['nota_media'] = number_format($nota_media,2);
                    } else if($busca == 'nps_clientes'){
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
                        $ret['respostas'] = count($pesq['tableData']);
                    } else if($busca == 'nps_sem'){
                        $notas = [];
                        $somas = [];
                        foreach($pesq['tableData'] as $key => $value){
                            if(is_array($value)){
                                $ctkey = 0;
                                foreach($value as $chave => $valor){
                                    if($chave == 'date'){
                                        $dia_sem = substr(getdate(strtotime(dataBrToDb(substr($valor,0,10))))['wday'],0,3);
                                    }
                                    $ctkey++;
                                    if($ctkey == 4){
                                        $notas[$dia_sem][$valor] = isset($notas[$dia_sem][$valor]) ? $notas[$dia_sem][$valor] + 1:1;
                                        $somas[$dia_sem][$valor] = isset($somas[$dia_sem][$valor]) ? $somas[$dia_sem][$valor] + $valor:$valor;
                                         break;
                                    }
                                }
                            }
                        }
                        krsort($notas);
                        krsort($somas);
                        // debug($notas);
                        foreach($notas as $sem => $val){
                            // debug($sem);
                            ksort($notas[$sem]);
                            $total[$sem]      = 0;
                            $promotores[$sem] = 0;
                            $neutros[$sem]    = 0;
                            $detratores[$sem] = 0;
                            foreach($notas[$sem] as $key => $valor){
                                $total[$sem] += $valor;
                                if($key == 10 || $key == 9){ // promotores
                                    $promotores[$sem] += $valor;
                                } else if($key == 8 || $key == 7){ // neutros
                                    $neutros[$sem] += $valor;
                                } else { // detratores
                                    $detratores[$sem] += $valor;
                                }
                            }
                            $promotopct[$sem] = 0;
                            $promotopct[$sem] = number_format(($promotores[$sem] * 100) / $total[$sem],2);

                            $neutrospct[$sem]    = 0;
                            $neutrospct[$sem] = number_format(($neutros[$sem] * 100) / $total[$sem],2);

                            $detratopct[$sem] = 0;
                            $detratopct[$sem] = number_format(($detratores[$sem] * 100) / $total[$sem],2);

                            $npsmedio[$sem] = number_format((($promotores[$sem] - $detratores[$sem]) * 100) / $total[$sem],2);
                        }
                        for($p=0;$p<count($npsmedio);$p++){
                            $ret['dados'][$p]['dia']= $diasemana[$p][0];
                            $ret['dados'][$p]['npsmedio']= number_format($npsmedio[$p],0) ;
                            // $ret['dados'][$p]['neutros']= number_format($neutrospct[$p],0) ;
                            // $ret['dados'][$p]['detratores']= number_format($detratopct[$p],0) ;
                            $cores[$p] = $cordia[$diasemana[$p][1]];                            
                        }
                        $ret['cores'] = $cores;
                    }  else if($busca == 'nps_mes'){
                        $notas = [];
                        $somas = [];
                        foreach($pesq['tableData'] as $key => $value){
                            if(is_array($value)){
                                $ctkey = 0;
                                foreach($value as $chave => $valor){
                                    if($chave == 'date'){
                                        $mes = getdate(strtotime(dataBrToDb(substr($valor,0,10))))['mon'];
                                        $ano = getdate(strtotime(dataBrToDb(substr($valor,0,10))))['year'];
                                    }
                                    $ctkey++;
                                    if($ctkey == 4){
                                        $notas[$mes][$valor] = isset($notas[$mes][$valor]) ? $notas[$mes][$valor] + 1:1;
                                        $somas[$mes][$valor] = isset($somas[$mes][$valor]) ? $somas[$mes][$valor] + $valor:$valor;
                                         break;
                                    }
                                }
                            }
                        }
                        krsort($notas);
                        krsort($somas);
                        // debug($notas);
                        foreach($notas as $mes => $val){
                            // debug($sem);
                            ksort($notas[$mes]);
                            $total[$mes]      = 0;
                            $promotores[$mes] = 0;
                            $neutros[$mes]    = 0;
                            $detratores[$mes] = 0;
                            foreach($notas[$mes] as $key => $valor){
                                $total[$mes] += $valor;
                                if($key == 10 || $key == 9){ // promotores
                                    $promotores[$mes] += $valor;
                                } else if($key == 8 || $key == 7){ // neutros
                                    $neutros[$mes] += $valor;
                                } else { // detratores
                                    $detratores[$mes] += $valor;
                                }
                            }
                            $promotopct[$mes] = 0;
                            $promotopct[$mes] = number_format(($promotores[$mes] * 100) / $total[$mes],2);
                            $neutrospct[$mes]    = 0;
                            $neutrospct[$mes] = number_format(($neutros[$mes] * 100) / $total[$mes],2);
                            $detratopct[$mes] = 0;
                            $detratopct[$mes] = number_format(($detratores[$mes] * 100) / $total[$mes],2);

                            $npsmedio[$mes] = number_format((($promotores[$mes] - $detratores[$mes]) * 100) / $total[$mes],2);
                        }
                        ksort($npsmedio);
                        $ct = 0;
                        foreach($npsmedio as $p => $value){
                            $ret['dados'][$ct]['mes']= $meses[$p-1];
                            $ret['dados'][$ct]['npsmedio']= number_format($npsmedio[$p],0) ;
                            $cores[$ct] = $cormes[$p];                            
                            $ct++;
                        }
                        $ret['cores'] = $cores;
                    } else if($busca == 'nps_dia'){
                        $notas = [];
                        $somas = [];
                        foreach($pesq['tableData'] as $key => $value){
                            if(is_array($value)){
                                $ctkey = 0;
                                foreach($value as $chave => $valor){
                                    if($chave == 'date'){
                                        $dia = dataBrToDb(substr($valor,0,10));
                                    }
                                    $ctkey++;
                                    if($ctkey == 4){
                                        $notas[$dia][$valor] = isset($notas[$dia][$valor]) ? $notas[$dia][$valor] + 1:1;
                                        $somas[$dia][$valor] = isset($somas[$dia][$valor]) ? $somas[$dia][$valor] + $valor:$valor;
                                         break;
                                    }
                                }
                            }
                        }
                        krsort($notas);
                        krsort($somas);
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
                        $ret['cores'] = $cores;
                        $ret['respostas'] = count($pesq['tableData']);
                        // debug($ret, true);
                    }
                }
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
