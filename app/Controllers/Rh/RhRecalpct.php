<?php namespace App\Controllers\Rh;

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

class RhRecalcpct extends BaseController {	
    public $data = [];
    public $permissao = '';
    public $common;
    public $pagamento;
    public $colaborador;
    public $holerite;
    public $ponto;
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

        $campos[0] = $this->emp_id;
        $campos[1] = $this->competencia;
        $campos[2] = $this->setor;
        $campos[3] = $this->botao;

        $colunas = ['id','Colaborador','Função','Setores','Partic','Dias Trab','Dias Mês','Gorj Inicial','Gorj c/ Falta','Valor Falta','Falta Total/Setor','Dist Falta','Gorjeta'];

        $this->data['cols']     	= $colunas;  
        $this->data['nome']     	= 'recalcperc';  
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'index';

        echo view('vw_gorjeta', $this->data);
    }

	public function busca_dados()
	{
        // debug($dados_emp);
		$filtro  		= $this->request->getVar();
		// debug($filtro, false);
		$empresa        = $filtro['empresa'];
		$competencia    = $filtro['competencia'];
		$valordist      = moedaToFloat($filtro['valordistribuido']);
        $distribui      = $valordist * 0.8;
        $mesano         = explode('/',$competencia);
        $mes            = $mesano[0];
        $ano            = $mesano[1];
        
        $inimes          = new \DateTime($ano.'-'.($mes + 1).'-11');
        $fimmes          = new \DateTime($ano.'-'.($mes + 2).'-10');
        $diasmes         = $inimes->diff($fimmes)->days + 1;
        // debug($diasvt);
        $ret = [];

        $dados_holerite = $this->holerite->getHolerite(false, $empresa, $competencia);
        // debug($dados_holerite);
        $gorjeta  = [];
        $setores  = [];
        $vlcomfa  = [];
        $diastra  = [];
        $ct=0;
        for($h=0;$h<count($dados_holerite);$h++){
            $hole = $dados_holerite[$h];
            $colabor = $hole['col_id'];
            $compete = $hole['hol_mesanocompetencia']; 
            $dados_resumo = $this->ponto->getResumoPonto($colabor, $compete);
            $diasgorj = $diasmes;
            if(count($dados_resumo) > 0){
                $dados_resumo = $dados_resumo[0];
                $diasgorj = $diasmes - ($dados_resumo['pon_falta'] + $dados_resumo['pon_atestado'] + $dados_resumo['pon_vazio'] + $dados_resumo['pon_inss']);
            }
            $dados_colab = $this->colaborador->getColaborador($hole['col_id'])[0];
            $pctpart = ($dados_colab['col_pctparticipacao']>0)?$dados_colab['col_pctparticipacao']:2;
            $vlgorjini = $distribui * ($pctpart / 100);
            $vlgorjfal = $vlgorjini * ($diasgorj / $diasmes);
            $vlcomfalt = $vlgorjini - $vlgorjfal;
            $setores[$ct] = ($dados_colab['set_nome']!='')?$dados_colab['set_nome']:'Sem Setor';
            $vlcomfa[$ct] = $vlcomfalt;
            $diastra[$ct] = $diasgorj;
            $gorjeta[$ct][0] = $dados_colab['col_id'];
            $gorjeta[$ct][1] = $dados_colab['col_nome'];
            $gorjeta[$ct][2] = $dados_colab['cag_nome'];
            $gorjeta[$ct][3] = ($dados_colab['set_nome']!='')?$dados_colab['set_nome']:'Sem Setor';
            $gorjeta[$ct][4] = $pctpart.'%';
            $gorjeta[$ct][5] = $diasgorj;
            $gorjeta[$ct][6] = $diasmes;
            $gorjeta[$ct][7] = floatToMoeda($vlgorjini);
            $gorjeta[$ct][8] = floatToMoeda($vlgorjfal);
            $gorjeta[$ct][9] = floatToMoeda($vlcomfalt);
            $ct++;
        }
        // debug($setores);
        // debug($vlcomfa);
        // debug($diastra);
        for ($g=0; $g < count($gorjeta); $g++) { 
            $gorj = $gorjeta[$g];
            // debug($gorj[8]);
            // debug(moedaToFloat($gorj[8]));
            $faltaseto = somase($setores,$gorj[3],$vlcomfa);
            $distdiast = somase($setores,$gorj[3],$diastra);
            $distfalta = $faltaseto * ($gorj[5] / $distdiast);
            // debug($distfalta);
            $vlgorjeta = moedaToFloat($gorj[8]) + $distfalta;
            $gorjeta[$g][10] = floatToMoeda($faltaseto);
            $gorjeta[$g][11] = floatToMoeda($distfalta);
            $gorjeta[$g][12] = floatToMoeda($vlgorjeta);
        }

        $ret['data'] = $gorjeta;
        
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
        $empresas           = explode(',',session()->get('usu_empresa'));
        $empresa            = new ConfigEmpresaModel();
        $dados_emp          = $empresa->getEmpresa($empresas);
        $opc_emp            = array_column($dados_emp, 'emp_apelido', 'emp_id');

        $emp                        = new MyCampo('rh_colaborador','emp_id');
        $emp->valor = $emp->selecionado = isset($dados['emp_id'])? $dados['emp_id']: '';
        $emp->obrigatorio           = true;
        $emp->opcoes                = $opc_emp;
        $emp->largura               = 50;
        $emp->leitura               = $show;
        $emp->dispForm  = 'col-3';
        $this->emp_id               = $emp->crSelect();

        $comp                        = new MyCampo();
        $comp->id = $comp->nome      = 'competencia';
        $comp->valor = $comp->selecionado = '';
        $comp->label                 = 'Competência';
        $comp->place                 = '';
        $comp->obrigatorio           = true;
        $comp->opcoes                = [];
        $comp->largura               = 20;
        $comp->leitura               = $show;
        $comp->pai                  = 'emp_id';
        $comp->urlbusca             = 'buscas/buscaCompetencia';
        $comp->dispForm  = 'col-2';
        $this->competencia          = $comp->crDepende();
        
        $vldi                        = new MyCampo();
        $vldi->id = $vldi->nome      = 'valordistribuido';
        $vldi->tipo                  = 'moeda';
        $vldi->valor = $vldi->selecionado = '0.00';
        $vldi->label                 = 'Valor Distribuído';
        $vldi->place                 = '';
        $vldi->obrigatorio           = true;
        $vldi->size                  = 12;
        $vldi->decimal               = 2;
        $vldi->largura               = 20;
        $vldi->leitura               = $show;
        $vldi->dispForm             = 'col-3';
        $this->valordistribuido          = $vldi->crInput();

        
        $bot = new MyCampo();
        $bot->nome = 'Calcular';
        $bot->id    = 'Calcular';
        $bot->label = 'Calcular';
        $bot->place = 'Calcular';
        $bot->i_cone = "<i class='fa-solid fa-play'></i> Calcular Gorjeta";
        $bot->classep = 'btn btn-warning';
        $bot->tipo = 'button';
        $bot->funcChan = 'calcula_gorgeta()';
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
