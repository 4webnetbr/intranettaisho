<?php

namespace App\Controllers;
use App\Controllers\BaseController;
use App\Libraries\Campos; 
use App\Models\Config\ConfigApiModel;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\DelivModel;
use App\Models\GerenteModel;
use CodeIgniter\Controller;


class Table extends BaseController
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

	public function montaTable(){

		$empresa        = session()->empresa;
        $inicio         = dataBrToDb(session()->inicio); 
        $fim            = dataBrToDb(session()->fim); 
        $busca          = session()->busca;
        $coluns         = session()->colunas;
        $nome           = session()->nome;
        $tipo           = session()->tipo;
        $titulo           = session()->titulo;

        $fimbu = strrpos($busca,'_');
        $valor = false;
        if($fimbu > 4){
            $valor = true;
            $busca = substr($busca, 0, $fimbu);
        }

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
        // debug($empresa);
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
                if($valor){
                    $FatPct = floatval($fat['FatTotal']);
                    $TaxPct = floatval($fat['TotTaxaServico']);
                }
                $fatu['FatTotal'] = number_format($FatPct,2,'.','');
                $fatu['TotTaxaServico'] = number_format($TaxPct,2,'.','');
                $cores[$f] = $cordia[$data];
                $fatu['TotDescontos'] = number_format($fat['TotDescontos'],2,'.','');
                $fatu['TotAcrescimos'] = number_format($fat['TotAcrescimos'],2,'.','');
                $res_fatur[$f] = $fatu;
            }
            $ret['dados'] = $res_fatur;
            // $ret['dados'] = $fatur;
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
                $FatPct = floatval((floatval($fat['fat_medio_dia']) * 100) / $TotPeriodo);
                if($valor){
                    $FatPct = floatval($fat['fat_medio_dia']);
                }
                $fatu['% Faturamento'] += number_format($FatPct,2,'.','');
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
                $FatPct = floatval((floatval($fat['fat_mes']) * 100) / $TotPeriodo);
                if($valor){
                    $FatPct = floatval($fat['fat_mes']);
                }
                $fatu['% Faturamento'] += number_format($FatPct,2,'.','');
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
                // debug($nps_res, true);
                $respostas = 0;
                $diaatual = date('Y-m-d',strtotime($inicio));
                for($n=0;$n < count($nps_res); $n++){
                    $res = $nps_res[$n];
                    // debug($res);
                    $dia = $res['opi_dia'];
                    $respostas += $res['respostas'];
                    // debug('Dia '.$dia);
                    // debug('Dia Atual '.$diaatual);
                    if($dia > $diaatual){
                        while ($dia > $diaatual){
                            // debug('Dia Atual '.$diaatual);
                            $notas[$diaatual][10] = 0;
                            $somas[$diaatual][10] = 0;
                            $notas[$diaatual][9]  = 0;
                            $somas[$diaatual][9]  = 0;
                            $notas[$diaatual][8]  = 0;
                            $somas[$diaatual][8]  = 0;
                            $notas[$diaatual][7]  = 0;
                            $somas[$diaatual][7]  = 0;
                            $notas[$diaatual][6]  = 0;
                            $somas[$diaatual][6]  = 0;
                            $notas[$diaatual][5]  = 0;
                            $somas[$diaatual][5]  = 0;
                            $notas[$diaatual][4]  = 0;
                            $somas[$diaatual][4]  = 0;
                            $notas[$diaatual][3]  = 0;
                            $somas[$diaatual][3]  = 0;
                            $notas[$diaatual][2]  = 0;
                            $somas[$diaatual][2]  = 0;
                            $notas[$diaatual][1]  = 0;
                            $somas[$diaatual][1]  = 0;
                            $notas[$diaatual][0]  = 0;
                            $somas[$diaatual][0]  = 0;  
                            $diaatual = date('Y-m-d',strtotime("+1 days",strtotime($diaatual)));
                            // debug('Aqui N '.$n);
                            // $n--;
                        }
                    }
                    // } else {
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
                        $diaatual = date('Y-m-d',strtotime("+1 days",strtotime($diaatual)));
                    // }
                }
                // krsort($notas);
                // krsort($somas);
                // debug($notas, true);
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
                    $neutrospct[$dia]    = 0;
                    $detratopct[$dia] = 0;
                    $npsmedio[$dia] = 0;

                    if($total[$dia] >0){
                        $promotopct[$dia] = number_format(($promotores[$dia] * 100) / $total[$dia],2);
                        $neutrospct[$dia] = number_format(($neutros[$dia] * 100) / $total[$dia],2);
                        $detratopct[$dia] = number_format(($detratores[$dia] * 100) / $total[$dia],2);
                        $npsmedio[$dia] = number_format((($promotores[$dia] - $detratores[$dia]) * 100) / $total[$dia],2);
                    } 
                }
                $ct = 0;
                ksort($npsmedio);
                foreach($npsmedio as $key => $valor){
                    $data = date('D',strtotime($key));
                    $ret['dados'][$ct]['dia']= $semana[$data].' - '.substr(dataDbToBr($key),0,5);
                    $ret['dados'][$ct]['detratores']= number_format($detratores[$key],0);
                    $ret['dados'][$ct]['neutros']= number_format($neutros[$key],0);
                    $ret['dados'][$ct]['promotores']= number_format($promotores[$key],0);
                    $ret['dados'][$ct]['total']= number_format($promotores[$key] + $neutros[$key] + $detratores[$key],0);
                    $ret['dados'][$ct]['npsmedio']=$valor>0?number_format($valor,2):'SEM AVALIAÇÃO';
                    $ret['dados'][$ct]['promotopct']= number_format($promotopct[$key],2).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ' ;
                    $ret['dados'][$ct]['neutrospct']= number_format($neutrospct[$key],2).' ' ;
                    $ret['dados'][$ct]['detratopct']= number_format($detratopct[$key],2).' ' ;
                    $diasem = substr(getdate(strtotime($key))['wday'],0,3);
                    $cores[$ct] = $cordia[$diasemana[$diasem][1]];                            
                    $ct++;
                }
                $coluns = 'Dia, Detrator, Neutro, Promotor, Total, NPS,';
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
                    if(isset($promotores[$diasem])){
                        $promotores[$diasem] += $promotores[$dia];
                    } else {
                        $promotores[$diasem] = $promotores[$dia];
                    }
                    if(isset($neutros[$diasem])){
                        $neutros[$diasem] += $neutros[$dia];
                    } else {
                        $neutros[$diasem] = $neutros[$dia];
                    }
                    if(isset($detratores[$diasem])){
                        $detratores[$diasem] += $detratores[$dia];
                    } else {
                        $detratores[$diasem] = $detratores[$dia];
                    }
                    if(isset($total[$diasem])){
                        $total[$diasem] += $total[$dia];
                    } else {
                        $total[$diasem] = $total[$dia];
                    }
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
                    $ret['dados'][$ct]['promotores']= number_format($promotores[$key],2).' ' ;
                    $ret['dados'][$ct]['promotopct']= number_format($promotopct[$key],2).' ' ;
                    $ret['dados'][$ct]['neutros']= number_format($neutros[$key],2).' ' ;
                    $ret['dados'][$ct]['neutrospct']= number_format($neutrospct[$key],2).' ' ;
                    $ret['dados'][$ct]['detratores']= number_format($detratores[$key],2).' ' ;
                    $ret['dados'][$ct]['detratopct']= number_format($detratopct[$key],2).' ' ;
                    $ret['dados'][$ct]['npsmedio']= number_format($npsmediosem[$key],2) ;
                    $cores[$ct] = $cordia[$diasemana[$key][1]];                            
                    $ct++;
                }
                $coluns = 'Dia da Semana, Promotores, Neutros, Detratores, NPS Médio,';
                $ret['cores'] = $cores;
            } else if($busca == 'nps_mes'){
                $notas = [];
                $somas = [];
                $fim = date('Y-m-d');
                $inicio = date('Y-m-d', strtotime("-14 months"));
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
                $ct = 0;
                foreach($npsmedio as $p => $value){
                    $nummes = intval(substr($p,0,2));
                    $ret['dados'][$ct]['mes']= $meses[$nummes-1].'/'.substr($p,-4);
                    $ret['dados'][$ct]['promotores']= number_format($promotores[$key],2).' ' ;
                    $ret['dados'][$ct]['promotopct']= number_format($promotopct[$key],2).' ' ;
                    $ret['dados'][$ct]['neutros']= number_format($neutros[$key],2).' ' ;
                    $ret['dados'][$ct]['neutrospct']= number_format($neutrospct[$key],2).' ' ;
                    $ret['dados'][$ct]['detratores']= number_format($detratores[$key],2).' ' ;
                    $ret['dados'][$ct]['detratopct']= number_format($detratopct[$key],2).' ' ;
                    $ret['dados'][$ct]['npsmedio']= number_format($npsmedio[$p],2) ;
                    $cores[$ct] = $cormes[$nummes];                            
                    $ct++;
                }
                $coluns = 'Mes, Promotores, Neutros, Detratores, NPS Médio,';
                $ret['cores'] = $cores;
            } else if($busca == 'nps_clientes'){
                $notas = [];
                $somas = [];
                $nps_res = $this->gerente->getNotasNpsDiario($emp['emp_codempresa'], $emp['emp_codfilial'], $inicio, $fim);
                $respostas = 0;
                // debug($nps_res);
                for($n=0;$n<count($nps_res); $n++){
                    $res = $nps_res[$n];
                    $respostas += $res['respostas'];
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
        $ret['registros'] = count($ret['dados']);
        $ret['cores'] = $cores;
        // // debug($ret);
        $cols = rtrim($coluns,',');
        $acols = explode(',',$cols);
        // debug($ret['dados']);
        $this->data['dados']    = $ret['dados'];
        $this->data['cols']     = $acols;
        $this->data['nome']     = $nome;
        $this->data['titulo']     = $titulo;
        $this->data['periodo']    = 'de '.dataDbToBr($inicio).' até '.dataDbToBr($fim);
        $this->data['nomeempresa'] = $emp['emp_apelido'];

        return view('vw_tabela', $this->data);
	}
}
