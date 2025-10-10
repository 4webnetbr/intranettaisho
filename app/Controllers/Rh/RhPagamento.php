<?php

namespace App\Controllers\Rh;

use App\Controllers\BaseController;
use App\Libraries\Campos;
use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\Rechum\RechumColaboradorModel;
use App\Models\Rechum\RechumHoleriteModel;
use App\Models\Rechum\RechumPagamentoModel;
use App\Models\Rechum\RechumPontoModel;
use App\Models\Rechum\RechumSetorModel;
use App\Models\Rechum\RechumValeModel;

class RhPagamento extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $common;
    public $pagamento;
    public $colaborador;
    public $holerite;
    public $ponto;
    public $vale;
    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->common       = new CommonModel();
        $this->colaborador       = new RechumColaboradorModel();
        $this->holerite          = new RechumHoleriteModel();
        $this->ponto            = new RechumPontoModel();
        $this->vale            = new RechumValeModel();
        $this->data['scripts']   = 'my_calc_pagamento';
        if ($this->data['erromsg'] != '') {
            $this->__erro();
        }
    }

    /**
     * Erro de Acesso
     * erro
     */
    function __erro()
    {
        echo view('vw_semacesso', $this->data);
    }

    /**
     * Tela de Abertura
     * index
     */
    public function index()
    {
        $this->def_campos();

        // $campos[0] = $this->emp_id;
        $campos[0] = $this->emp_id;
        $campos[1] = $this->emp_id_registro;
        $campos[2] = $this->competencia;
        $campos[3] = $this->vtcuritiba;
        $campos[4] = $this->vtmetropolitana;
        $campos[5] = $this->botao;

        $colunas = ['id', 'Reg','Colaborador', 'Tipo', 'Combinado', 'Vencimentos', 'Descontos', 'Vale', 'VT', 'VT Falta', 'V Premio', 'Premio', 'V Mercado', 'Banco', 'p/fora', 'Total'];

        $desc_edicao = 
        "<div class='float-start bg-success py-1 px-3 me-1' >Férias</div> ".
        "<div class='float-start bg-warning py-1 px-3 me-1' >Demitido</div> ".
        "<div class='float-start bg-info py-1 px-3' >INSS</div> ";
        $this->data['cols']         = $colunas;
        $this->data['nome']         = 'pagamento';
        $this->data['desc_edicao']         = $desc_edicao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'index';

        echo view('vw_pagamento', $this->data);
    }

    public function busca_dados()
    {
        // debug($dados_emp);
        $filtro          = $this->request->getVar();
        // debug($filtro, false);
        $empresa        = $filtro['empresa'];
        $registro        = $filtro['registro'];
        if($registro == '-1' || $registro == ''){
            $registro = false;
        } else {
        // Se for string com vírgula, explode
            if (is_string($registro) && str_contains($registro, ',')) {
                $registro = explode(',', $registro);
            } else {
                $registro = (array)$registro;
            }
        }
        $competencia    = $filtro['competencia'];
        $vtcur          = moedaToFloat($filtro['vtcur']);
        $vtmet          = moedaToFloat($filtro['vtmet']);
        $mesano         = explode('/', $competencia);
        $mes            = $mesano[0];
        $ano            = $mesano[1];

        $inivt          = new \DateTime($ano . '-' . ($mes + 1) . '-11');
        $fimvt          = new \DateTime($ano . '-' . ($mes + 2) . '-10');
        $diasvt         = $inivt->diff($fimvt)->days + 1;
        // debug('Início '.$inivt->format('d/m/Y'));
        // debug('Fim '.$fimvt->format('d/m/Y'));
        // debug('Dias '.$diasvt);
        $ret = [];

        $dados_holerite = $this->holerite->getHolerite(false, $empresa, $registro, $competencia);
        // debug(count($dados_holerite));
        // debug($dados_holerite);
        $ct = 0;
        $totbanco = 0;
        $totpfora = 0;
        $tottotal = 0;
        $totvenci = 0;
        $totdesco = 0;
        $totvale  = 0;
        $totvt    = 0;
        $totvtfal = 0;
        $totpremi = 0;
        $totmerca = 0;
        for ($h = 0; $h < count($dados_holerite); $h++) {
            $hole = $dados_holerite[$h];
            // debug($hole);
            // if($hole['hol_situacao'] == 'Trabalhando'){
            // debug('Colab: '.$hole['col_nome']);
            if ($hole['col_vt'] == 'N') {
                $valorvt = 0;
            } else {
                $folgasem = ($hole['col_folgasemana'] == '') ? 1 : $hole['col_folgasemana'];
                // debug('Dia da Folga: '.$folgasem);
                $folgassemana = contarDiasDaSemana($inivt->format('Y-m-d'), $fimvt->format('Y-m-d'), $folgasem);
                // debug('Folgas Semana: '.$folgassemana);
                $folgasdomingo = 0;
                // debug('Domingo: '.$hole['col_folgadomingo']);
                if ($folgasem == 0  && $hole['col_folgadomingo'] == 0) {
                    $folgadommingo = 0;
                } else if ($hole['col_folgadomingo'] == 0 && $hole['col_folgadomingo'] != '' && $folgasem != 0) {
                    $folgasdomingo = contarDiasDaSemana($inivt->format('Y-m-d'), $fimvt->format('Y-m-d'), 0);
                } else {
                    $folgadom = ($hole['col_folgadomingo'] == '') ? 1 : $hole['col_folgadomingo'];
                    // debug('Domingo da Folga: '.$folgadom);
                    $achoudomingo = dataXnoMes($inivt->format('Y'), $inivt->format('m'), 7, $folgadom);
                    // debug($achoudomingo);
                    $acdomingo = new \DateTime($achoudomingo);
                    if ($acdomingo >= $inivt && $acdomingo <= $fimvt) {
                        $folgasdomingo++;
                    }
                    $achoudomingo = dataXnoMes($fimvt->format('Y'), $fimvt->format('m'), 7, $folgadom);
                    // debug($achoudomingo);
                    $acdomingo = new \DateTime($achoudomingo);
                    if ($acdomingo >= $inivt && $acdomingo <= $fimvt) {
                        $folgasdomingo++;
                    }
                }
                // debug('Folgas Domingo '.$folgasdomingo);
                // debug('Folgas Semana '.$folgassemana);
                $totalfolgas = $folgassemana + $folgasdomingo;
                $qtvt = $diasvt - $totalfolgas;
                // debug('Dias vt '.$qtvt);
                $colabor = $hole['col_id'];
                $compete = $hole['hol_mesanocompetencia'];
                $situac  = $hole['col_situacao'];
                // $demissao =  $hole['hol_demissao']; 
                $dados_resumo = $this->ponto->getResumoPonto($colabor, $compete);
                // debug($dados_resumo);
                $descontar = 0;
                $totfalta = 0;
                $totatest = 0;
                $ferias   = 0;
                $inss = 0;
                if (count($dados_resumo) > 0) {
                    $dados_resumo = $dados_resumo[0];
                    $descontar = $dados_resumo['pon_falta'] + $dados_resumo['pon_atestado'];
                    $totfalt = $dados_resumo['pon_falta'];
                    $totatest = $dados_resumo['pon_atestado'];
                    $inss = $dados_resumo['pon_inss'];
                    $ferias = $dados_resumo['pon_ferias'];
                }
                $qtvt = $qtvt - (($inss > $qtvt) ? $qtvt : $inss);
                // debug('Qt VT'.$qtvt);
                // $qtvt = $qtvt - $descontar;
                if ($hole['col_metropolitana'] == 'S') {
                    $valorvt = (2 * ($vtcur + $vtmet)) * $qtvt;
                } else {
                    $valorvt = (2 * $vtcur) * $qtvt;
                }
                if ($hole['col_metropolitana'] == 'S') {
                    $valorde = (2 * ($vtcur + $vtmet)) * $descontar;
                } else {
                    $valorde = (2 * $vtcur) * $descontar;
                }
                // debug("Valor VT ".$valorvt);
            }
            if ($situac == 'Demitido') {
                $valorvt = 0;
            }
            $mes = date('m');
            $ano = date('Y');
            $mesadm = date('m', strtotime($hole['col_admissao']));
            $anoadm = date('Y', strtotime($hole['col_admissao']));
            $mesano = $mes . '/' . $ano;
            $mesanoadm = $mesadm . '/' . $anoadm;
            if ($inss == 0 && $ferias < 5 && $mesanoadm != $mesano && $situac != 'Demitido') {
                if ($hole['col_vale'] > 40) {
                    $vale = $hole['col_salario'] + $hole['col_vale'];
                } else {
                    $vale = $hole['col_salario'] * ($hole['col_vale'] / 100);
                }
            } else {
                $vale = 0;
            }
            $busca_vale = $this->vale->getValeColComp($hole['col_id'], $hole['hol_mesanocompetencia']);
            $valeext = 0;
            if (count($busca_vale) > 0) {
                for ($v = 0; $v < count($busca_vale); $v++) {
                    $valeext += $busca_vale[$v]['val_valor'];
                }
            }
            $vale = $vale + $valeext;
            $tipo = $hole['col_tipo'];
            $banco = $hole['hol_proventos'] - $hole['hol_descontos'];
            if ($tipo == 9) {
                $banco = $hole['col_salario'];
            }
            // CALCULA O TOTAL
            if ($tipo == 0) {
                $total = $hole['hol_proventos'] - $hole['hol_descontos'];
            } else if ($tipo == 1) {
                if ($hole['hol_proventos'] - $valorvt - $hole['hol_descontos'] < $hole['col_salario']) {
                    $total = $hole['col_salario'] + $valorvt - $hole['hol_descontos'];
                } else {
                    $total = $hole['hol_proventos'] - $hole['hol_descontos'];
                }
            } else if ($tipo == 2) {
                if ($hole['hol_proventos'] - $valorvt < $hole['col_salario']) {
                    $total = $hole['col_salario'] + $valorvt - $vale - $valorde;
                } else {
                    $total = $hole['hol_proventos'] - $vale - $valorde;
                }
            } else if ($tipo == 10) {
                $total = $hole['hol_proventos'] - $hole['hol_descontos'];
            } else if ($tipo == 11) {
                $total = $hole['col_salario'] + $valorvt - $hole['hol_descontos'];
            } else if ($tipo == 12) {
                $total = $hole['col_salario'] + $valorvt - $vale - $valorde;
            } else if ($tipo == 9) {
                $total = $hole['col_salario'];
            }

            // CALCULA O VALE MERCADO
            if ($tipo == 9 || $inss > 0 || trim($situac) == 'Férias' || $ferias > 15) {
                $merca = 0;
            } else {
                if ($totatest > 1) {
                    $merca = 0;
                } else if ($totfalta > 0) {
                    $merca = 50;
                } else {
                    $merca = 100;
                }
            }

            // CALCULA O MERCADO
            $premio = $hole['col_premio'];
            if ($tipo == 9 || $inss > 0 || trim($situac) == 'Férias' || $ferias > 15) {
                $premio = 0;
            } else {
                $premio = $premio - ($totatest * 100);
                if($premio < 0){
                    $premio = 0;
                }
            }

            $pfora = $total - $banco;
            $totbanco += $banco;
            $totpfora += $pfora;
            $tottotal += $total;
            $totvenci += $hole['hol_proventos'];
            $totdesco += $hole['hol_descontos'];
            $totvale  += $vale;
            $totvt    += $valorvt;
            $totvtfal += $valorde;
            $totpremi += 0;
            $totmerca += $merca;
            $pagam[$ct][0] = 0;
            $pagam[$ct][count($pagam[$ct])] = $hole['emp_reg'];
            $pagam[$ct][count($pagam[$ct])] = $hole['col_nome'];
            $pagam[$ct][count($pagam[$ct])] = $hole['col_tipo'];
            $pagam[$ct][count($pagam[$ct])] = ($tipo != 0) ? floatToMoeda($hole['col_salario']) : '';
            $pagam[$ct][count($pagam[$ct])] = floatToMoeda($hole['hol_proventos']);
            $pagam[$ct][count($pagam[$ct])] = floatToMoeda($hole['hol_descontos']);
            $pagam[$ct][count($pagam[$ct])] = floatToMoeda($vale);
            $pagam[$ct][count($pagam[$ct])] = floatToMoeda($valorvt);
            $pagam[$ct][count($pagam[$ct])] = floatToMoeda($valorde);
            $pagam[$ct][count($pagam[$ct])] = floatToMoeda($hole['col_premio']);
            $pagam[$ct][count($pagam[$ct])] = floatToMoeda($premio);
            $pagam[$ct][count($pagam[$ct])] = floatToMoeda($merca);
            $pagam[$ct][count($pagam[$ct])] = floatToMoeda($banco);
            $pagam[$ct][count($pagam[$ct])] = floatToMoeda($pfora);
            $pagam[$ct][count($pagam[$ct])] = floatToMoeda($total);
            $pagam[$ct]['cor'] = (trim($situac) == 'Férias') ? 'bg-success' : (($situac == 'Demitido') ? 'bg-warning' : (($situac == 'Doença') ? 'bg-info' : ''));
            $ct++;
            // }
        }
        $pagam[$ct][0] = '';
        $pagam[$ct][count($pagam[$ct])] = '';
        $pagam[$ct][count($pagam[$ct])] = '';
        $pagam[$ct][count($pagam[$ct])] = '';
        $pagam[$ct][count($pagam[$ct])] = '';
        $pagam[$ct][count($pagam[$ct])] = floatToMoeda($totvenci);
        $pagam[$ct][count($pagam[$ct])] = floatToMoeda($totdesco);
        $pagam[$ct][count($pagam[$ct])] = floatToMoeda($totvale);
        $pagam[$ct][count($pagam[$ct])] = floatToMoeda($totvt);
        $pagam[$ct][count($pagam[$ct])] = floatToMoeda($totvtfal);
        $pagam[$ct][count($pagam[$ct])] = 0;
        $pagam[$ct][count($pagam[$ct])] = 0;
        $pagam[$ct][count($pagam[$ct])] = floatToMoeda($totmerca);
        $pagam[$ct][count($pagam[$ct])] = floatToMoeda($totbanco);
        $pagam[$ct][count($pagam[$ct])] = floatToMoeda($totpfora);
        $pagam[$ct][count($pagam[$ct])] = floatToMoeda($tottotal);
        $pagam[$ct]['cor'] = '';

        $ret['data'] = $pagam;
        // $ret['hideColumnIndex'] = 15;

        echo json_encode($ret);
    }

    /**
     * Definição de Campos
     * def_campos
     *
     * @param bool $dados 
     * @return void
     */
    public function def_campos($dados = false, $pos = 0, $show = false)
    {
        $empresas           = explode(',', session()->get('usu_empresa'));
        $empresa            = new ConfigEmpresaModel();
        $dados_emp          = $empresa->getEmpresa($empresas);
        $opc_emp            = array_column($dados_emp, 'emp_apelido', 'emp_id');

        $emp                        = new MyCampo('rh_colaborador', 'emp_id');
        $emp->valor = $emp->selecionado = isset($dados['emp_id']) ? $dados['emp_id'] : '';
        $emp->obrigatorio           = true;
        $emp->opcoes                = $opc_emp;
        $emp->largura               = 50;
        $emp->leitura               = $show;
        $emp->dispForm              = 'col-6';
        $this->emp_id               = $emp->crSelect();

        $reg                        = new MyCampo('rh_colaborador', 'emp_id_registro');
        $reg->valor                 = isset($dados['emp_id_registro']) ? $dados['emp_id_registro'] : '';
        $reg->selecionado           = isset($dados['emp_id_registro']) ? [$dados['emp_id_registro']] : [];
        $reg->obrigatorio           = true;
        $reg->opcoes                = $opc_emp;
        $reg->largura               = 50;
        $reg->leitura               = $show;
        $reg->dispForm              = 'col-6';
        $reg->pai                   = 'emp_id';
        $reg->urlbusca              = 'buscas/buscaEmpRegistro';
        $this->emp_id_registro               = $reg->crDependeMultiple();

        $meses = [];

        for ($i = 1; $i < 7; $i++) {
            $data = date('m/Y', strtotime("-$i months"));
            $meses[$data] = $data;
        }


        $comp                        = new MyCampo();
        $comp->id = $comp->nome      = 'competencia';
        $comp->valor = $comp->selecionado = '';
        $comp->label                 = 'Competência';
        $comp->place                 = '';
        $comp->obrigatorio           = true;
        $comp->opcoes                = $meses;
        $comp->largura               = 20;
        $comp->leitura               = $show;
        // $comp->pai                  = 'emp_id_registro[]';
        // $comp->urlbusca             = 'buscas/buscaCompetencia';
        $comp->dispForm             = 'col-2';
        $this->competencia          = $comp->crSelect();

        $vtcu                        = new MyCampo();
        $vtcu->id = $vtcu->nome      = 'vtcuritiba';
        $vtcu->tipo                  = 'moeda';
        $vtcu->valor = $vtcu->selecionado = '6.00';
        $vtcu->label                 = 'VT Curitiba';
        $vtcu->place                 = '';
        $vtcu->obrigatorio           = true;
        $vtcu->size                  = 12;
        $vtcu->decimal               = 2;
        $vtcu->largura               = 20;
        $vtcu->leitura               = $show;
        $vtcu->dispForm             = 'col-3';
        $this->vtcuritiba          = $vtcu->crInput();

        $vtme                        = new MyCampo();
        $vtme->id = $vtme->nome      = 'vtmetropolitana';
        $vtme->tipo                  = 'moeda';
        $vtme->valor = $vtme->selecionado = '6.00';
        $vtme->label                 = 'VT Região Met';
        $vtme->place                 = '';
        $vtme->obrigatorio           = true;
        $vtme->size                  = 12;
        $vtme->decimal               = 2;
        $vtme->largura               = 20;
        $vtme->leitura               = $show;
        $vtme->dispForm             = 'col-3';
        $this->vtmetropolitana          = $vtme->crInput();

        $bot = new MyCampo();
        $bot->nome = 'Calcular';
        $bot->id    = 'Calcular';
        $bot->label = 'Calcular';
        $bot->place = 'Calcular';
        $bot->i_cone = "<i class='fa-solid fa-play'></i> Calcular Pagamento";
        $bot->classep = 'btn btn-warning';
        $bot->tipo = 'button';
        $bot->funcChan = 'calcula_pagamento()';
        $bot->dispForm  = 'col-3';
        $this->botao = $bot->crBotao();
    }


    /**
     * Gravação
     * store
     *
     * @return void
     */
    public function store()
    {
        $ret = [];
        $dados = $this->request->getPost();
        $dados_fun = [
            'cag_id'           => $dados['cag_id'],
            'cag_nome'         => $dados['cag_nome'],
            'cag_descricao'    => $dados['cag_descricao'],
        ];
        // debug($dados_fun,true);
        $salvar = $this->pagamento->save($dados_fun);
        if ($salvar) {
            $ret['erro'] = false;
            $ret['msg'] = 'Função gravada com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url'] = site_url($this->data['controler']);
        } else {
            $ret['erro'] = true;
            $erros = $this->pagamento->errors();
            $ret['msg'] = 'Não foi possível gravar a Função, Verifique!<br><br>';
            foreach ($erros as $erro) {
                $ret['msg'] .= $erro;
            }
        }
        echo json_encode($ret);
    }
}
