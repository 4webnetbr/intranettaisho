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
use App\Models\Rechum\RechumQuadroCargoModel;
use App\Models\Rechum\RechumQuadroModel;
use App\Models\Rechum\RechumSetorModel;

class RhSolver extends BaseController {	
    public $data = [];
    public $permissao = '';
    public $common;
    public $pagamento;
    public $colaborador;
    public $holerite;
    public $ponto;
    public $setor;
    public $quadro;
    public $quadrocargo;
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
        $this->setor            = new RechumSetorModel(); 
        $this->quadro       = new RechumQuadroModel(); 
        $this->quadrocargo = new RechumQuadroCargoModel(); 
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

        $secao[0] = 'Solver';
        $campos[0][0] = $this->emp_id;
        $campos[0][1] = $this->competencia;
        $campos[0][2] = $this->distribui;
        $campos[0][3] = $this->set_id;
        // $campos[0][4] = $this->botao;
        $campos[0][4] = "<div id='tbsolver' class='accordion col-12 px-3 overflow-y-auto' style='max-height:70vh'>";

        // $colunas = ['id','Função','Qt Ant','Qt Novo','% Ant','Indice','Qtd*Ind*x','Simul Nov%','Arred Nov%','%','Aux','Gorjeta Ant','Gorjeta Nov','<>'];

        // $this->data['cols']     	= $colunas;  
        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['script'] = "";
        $this->data['destino'] = '';
        echo view('vw_edicao', $this->data);
    }

	public function busca_dados()
	{
        // debug($dados_emp);
		$filtro  		= $this->request->getVar();
		// debug($filtro, false);
		$empresa        = $filtro['empresa'];
		$competencia    = $filtro['competencia'];
		$setor          = $filtro['setor'];
		$distribui      = $filtro['distribui'];
        $mesano         = explode('/',$competencia);
        $mes            = $mesano[0];
        $ano            = $mesano[1];
        
        $inimes          = new \DateTime($ano.'-'.($mes + 1).'-11');
        $fimmes          = new \DateTime($ano.'-'.($mes + 2).'-10');
        $diasmes         = $inimes->diff($fimmes)->days + 1;
        // debug($diasvt);
        $ret = [];
        $c_calculo = [];
        $c_funcoes = [];
        

        $quadro = $this->quadro->getQuadro(false, $empresa, $setor);
        // debug($quadro);
        $competant   = substr("00".($mes-1),-2).'/'.$ano;
        $setor_antes = $this->colaborador->getColaboradorSetor($empresa, $setor, $competant);
        $dados_setor = $this->colaborador->getColaboradorSetor($empresa, $setor, $competencia);
        // debug($dados_setor);
        $pctdistribui = 0;
        $variavel = 1.14477;

        $ct=0;
        $cabecalho = "<div class='row col-12 bg-primary'>";
        $cabecalho .= "<div class='col-2 text-center float-start bg-primary text-white align-self-center'><h6>Funçao</h6></div>";
        $cabecalho .= "<div class='col-1 text-center float-start bg-primary text-white align-self-center'><h6>Quadro</h6></div>";
        $cabecalho .= "<div class='col-1 text-center float-start bg-primary text-white align-self-center'><h6>Qt Ant</h6></div>";
        $cabecalho .= "<div class='col-1 text-center float-start bg-primary text-white align-self-center'><h6>Qt Novo</h6></div>";
        $cabecalho .= "<div class='col-1 text-center float-start bg-primary text-white align-self-center'><h6>% Ant</h6></div>";
        $cabecalho .= "<div class='col-1 text-center float-start bg-primary text-white align-self-center'><h6>Índice</h6></div>";
        $cabecalho .= "<div class='d-none col-1 text-center float-start bg-primary text-white align-self-center'><h6>Qtd*Ind*x</h6></div>";
        $cabecalho .= "<div class='col-1 text-center float-start bg-primary text-white align-self-center'><h6>% Nova</h6></div>";
        $cabecalho .= "<div class='d-none col-1 text-center float-start bg-primary text-white align-self-center'><h6>Arr. Nov%</h6></div>";
        $cabecalho .= "<div class='d-none col-1 text-center float-start bg-primary text-white align-self-center'><h6>%</h6></div>";
        $cabecalho .= "<div class='d-none col-1 text-center float-start bg-primary text-white align-self-center'><h6>Aux<h6></div>";
        $cabecalho .= "<div class='col-2 text-center float-start bg-primary text-white align-self-center'><h6>Gorjeta<h6>";
        $cabecalho .=   "<div class='col-6 text-center float-start bg-primary text-white align-self-center'><h6>Ant<h6></div>";
        $cabecalho .=   "<div class='col-6 text-center float-start bg-primary text-white align-self-center'><h6>Nov<h6></div>";
        $cabecalho .= "</div>";
        $cabecalho .= "<div class='col-1 text-center float-start bg-primary text-white align-self-center'><h6><><h5></div>";
        $cabecalho .= "</div>";
        $indice = 1;
        $indant = 1;
        $numcolx = [];
        $numconx = [];
        $qtdindx = [];
        $indicex = [];
        $pctantx = [];
        $pctnovx = [];
        $simnovx = [];
        $pctbase = 0;
        for($s=0;$s<count($dados_setor);$s++){
            $seto = $dados_setor[$s];
            if(isset($quadro[0]['qua_id'])){
                $cagquadro = $this->quadrocargo->getQuadroCargo($quadro[0]['qua_id'],$seto['cag_id']);
                // debug($cagquadro);
                if(isset($cagquadro[0]['cag_id'])){
                    if($pctbase == 0){
                        $pctbase = $cagquadro[0]['quf_participacao'];
                    }
                    $quadro['col_quad'] = $cagquadro[0]['quf_vagas'];
                    $quadro['pct_ante'] = $cagquadro[0]['quf_participacao'];
                } else {
                    $pctbase = 1;
                    $quadro['col_quad'] = 0;
                    $quadro['pct_ante'] = '1';
                }
            }
            $pctdistribui += $quadro['pct_ante']*$seto['num_colab'];
            // debug($cagquadro);
            $pctant = ($quadro['pct_ante']>0)?$quadro['pct_ante']:1;
            $pctantx[$s] = $pctant;
            $indice      = number_format($pctant / $pctbase,6);
            // $pctbase     = $pctant;
            // debug($setor_antes[$s]);
            // debug($seto);
            $numcolx[$s] = isset($setor_antes[$s]['num_colab'])?$setor_antes[$s]['num_colab']:$seto['num_colab'];
            // debug($numcolx[$s]);
            $numconx[$s] = $seto['num_colab'];
            // debug($numconx[$s]);
            $qtdindx[$s] = $seto['num_colab'] * floatval($indice);
            $indicex[$s] = $indice;
        }
        $resultado = solver($qtdindx, $pctdistribui);
        
        // debug($resultado);
        $variavel = $resultado['percentual'];
        
        for($sn=0;$sn<count($dados_setor);$sn++){
            $indice = $indicex[$sn];
            $simnov = $indice * $variavel;
            $pctnov = floatval(number_format($simnov,2));
            $pctnovx[$sn] = $pctnov;
            $simnovx[$sn] = $simnov;
        }
        
        for($s=0;$s<count($dados_setor);$s++){
            $seto = $dados_setor[$s];
            // debug($cagquadro);
            $pctant = $pctantx[$s];
            $pctnov = $pctnovx[$s];
            $indice = $indicex[$s];
            $numcol = $numcolx[$s];
            $numcon = $numconx[$s];
            $qtdind = $numcol * floatval($indice) * $variavel;
            
            $simnov = $simnovx[$s];
            // debug(moedaToFloat($distribui));
            $gojant = $pctant * (moedaToFloat($distribui) / 100);
            $gojnov = $simnov * (moedaToFloat($distribui) / 100);
            $difgoj = floatToMoeda(floatval($gojant) - floatval($gojnov));

            if($s==0){
                $pctdistribuicao    = somarProduto($numcolx,$pctantx);
                $pcttotal           = somarProduto($numcolx,$simnovx);
                $difpercentual      = $pctdistribuicao - $pcttotal;
                $c_calculo['pctdistribui']      = $pctdistribui;
                $c_calculo['pctdistribuicao']   = $pctdistribuicao;
                $c_calculo['variavel']          = $variavel;
                $c_calculo['pcttotal']          = $pcttotal;
                $c_calculo['difpercentual']     = $difpercentual;
                $this->def_campos_calculo($c_calculo);
                
                $camposca[0][0]                   = "<div class='row col-12 bg-info p-1 rounded-5'>";
                $camposca[0][count($camposca[0])] = "<div class='col-2 text-center float-start p-1 d-inline-flex align-items-center'>";
                $camposca[0][count($camposca[0])] = "<div class='col-6 text-center float-start'>";
                $camposca[0][count($camposca[0])] = "% Objetivo";
                $camposca[0][count($camposca[0])] = "</div>";
                $camposca[0][count($camposca[0])] = "<div class='col-6 text-center float-start fw-bold'>";
                $camposca[0][count($camposca[0])] = $this->pctdistribui;
                $camposca[0][count($camposca[0])] = "</div>";
                $camposca[0][count($camposca[0])] = "</div>";
                $camposca[0][count($camposca[0])] = "<div class='col-3 text-center float-start p-1 d-inline-flex align-items-center'>";
                $camposca[0][count($camposca[0])] = "<div class='col-6 text-center float-start'>";
                $camposca[0][count($camposca[0])] = "(=) % distribuiçao";
                $camposca[0][count($camposca[0])] = "</div>";
                $camposca[0][count($camposca[0])] = "<div class='col-6 text-center float-start fw-bold'>";
                $camposca[0][count($camposca[0])] = $this->pctdistribuicao;
                $camposca[0][count($camposca[0])] = "</div>";
                $camposca[0][count($camposca[0])] = "</div>";
                $camposca[0][count($camposca[0])] = "<div class='col-2 text-center float-start p-1p-1 d-inline-flex align-items-center'>";
                $camposca[0][count($camposca[0])] = "<div class='col-6 text-center float-start'>";
                $camposca[0][count($camposca[0])] = "Variável";
                $camposca[0][count($camposca[0])] = "</div>";
                $camposca[0][count($camposca[0])] = "<div class='col-6 text-center float-start fw-bold'>";
                $camposca[0][count($camposca[0])] = $this->variavel;
                $camposca[0][count($camposca[0])] = "</div>";
                $camposca[0][count($camposca[0])] = "</div>";
                $camposca[0][count($camposca[0])] = "<div class='col-2 text-center float-start p-1 d-inline-flex align-items-center'>";
                $camposca[0][count($camposca[0])] = "<div class='col-6 text-center float-start'>";
                $camposca[0][count($camposca[0])] = "Total %";
                $camposca[0][count($camposca[0])] = "</div>";
                $camposca[0][count($camposca[0])] = "<div class='col-6 text-center float-start fw-bold'>";
                $camposca[0][count($camposca[0])] = $this->pcttotal;
                $camposca[0][count($camposca[0])] = "</div>";
                $camposca[0][count($camposca[0])] = "</div>";
                $camposca[0][count($camposca[0])] = "<div class='col-3 text-center float-start p-1 d-inline-flex align-items-center'>";
                $camposca[0][count($camposca[0])] = "<div class='col-6 text-center float-start'>";
                $camposca[0][count($camposca[0])] = "<> % distribuiçao";
                $camposca[0][count($camposca[0])] = "</div>";
                $camposca[0][count($camposca[0])] = "<div class='col-6 text-center float-start fw-bold'>";
                $camposca[0][count($camposca[0])] = $this->difpercentual;
                $camposca[0][count($camposca[0])] = "</div>";
                $camposca[0][count($camposca[0])] = "</div>";
                $camposca[0][count($camposca[0])] = "</div>";
                $camposca[0][count($camposca[0])] = $cabecalho;
            }
            $c_funcoes['cag_nome']  = $seto['cag_nome'];
            $c_funcoes['col_quad']  = $quadro['col_quad'];
            $c_funcoes['num_colab'] = $numcol;
            $c_funcoes['num_colnv'] = $numcon;
            $c_funcoes['pct_ant']   = $pctant;
            $c_funcoes['indice']    = $indice;
            $c_funcoes['qtdind']    = $qtdind;
            $c_funcoes['pctnov']    = $pctnov;
            $c_funcoes['simnov']    = $simnov;
            $c_funcoes['gojant']    = $gojant;
            $c_funcoes['gojnov']    = $gojnov;
            $c_funcoes['difgoj']    = $difgoj;
            
            $this->def_campos_funcoes($c_funcoes, $s);
            $camposca[0][count($camposca[0])] = "<div class='row col-12'>";
            $camposca[0][count($camposca[0])] = "<div class='col-2 text-start float-start'>";
            $camposca[0][count($camposca[0])] = $this->cag_nome;
            $camposca[0][count($camposca[0])] = "</div>";
            $camposca[0][count($camposca[0])] = "<div class='col-1 text-end float-start'>";
            $camposca[0][count($camposca[0])] = $this->col_quad;
            $camposca[0][count($camposca[0])] = "</div>";
            $camposca[0][count($camposca[0])] = "<div class='col-1 text-end float-start'>";
            $camposca[0][count($camposca[0])] = $this->num_colab;
            $camposca[0][count($camposca[0])] = "</div>";
            $camposca[0][count($camposca[0])] = "<div class='col-1 text-end float-start'>";
            $camposca[0][count($camposca[0])] = $this->num_colnv;
            $camposca[0][count($camposca[0])] = "</div>";
            $camposca[0][count($camposca[0])] = "<div class='col-1 text-end float-start'>";
            $camposca[0][count($camposca[0])] = $this->pct_ant;
            $camposca[0][count($camposca[0])] = "</div>";
            $camposca[0][count($camposca[0])] = "<div class='col-1 text-end float-start'>";
            $camposca[0][count($camposca[0])] = $this->indice;
            $camposca[0][count($camposca[0])] = "</div>";
            $camposca[0][count($camposca[0])] = "<div class='d-none col-1 text-end float-start'>";
            $camposca[0][count($camposca[0])] = $this->qtdind;
            $camposca[0][count($camposca[0])] = "</div>";
            $camposca[0][count($camposca[0])] = "<div class='col-1 text-end float-start'>";
            $camposca[0][count($camposca[0])] = $this->pctnov;
            $camposca[0][count($camposca[0])] = "</div>";
            $camposca[0][count($camposca[0])] = "<div class='d-none col-1 text-end float-start'>";
            $camposca[0][count($camposca[0])] = $this->simnov;
            $camposca[0][count($camposca[0])] = "</div>";
            $camposca[0][count($camposca[0])] = "<div class='d-none col-1 text-end float-start'>";
            $camposca[0][count($camposca[0])] = '';
            $camposca[0][count($camposca[0])] = "</div>";
            $camposca[0][count($camposca[0])] = "<div class='col-2 text-end float-start'>";
            $camposca[0][count($camposca[0])] = "<div class='col-6 text-end float-start'>";
            $camposca[0][count($camposca[0])] = $this->gojant;
            $camposca[0][count($camposca[0])] = "</div>";
            $camposca[0][count($camposca[0])] = "<div class='col-6 text-end float-start'>";
            $camposca[0][count($camposca[0])] = $this->gojnov;
            $camposca[0][count($camposca[0])] = "</div>";
            $camposca[0][count($camposca[0])] = "</div>";
            $camposca[0][count($camposca[0])] = "<div class='col-1 text-end float-start'>";
            $camposca[0][count($camposca[0])] = $this->difgoj;
            $camposca[0][count($camposca[0])] = "</div>";
            $camposca[0][count($camposca[0])] = "</div>";
            $camposca[0][count($camposca[0])] = "</div>";

        }

        // debug($resultado);
        
        $ret['data'] = $camposca;
        
        echo json_encode($ret);
    }

    /**
     * Definição de Campos
     * def_campos
     *
     * @param bool $dados 
     * @return void
     */
    public function def_campos_funcoes($fdados, $pos=0)
    {
        $nome                        = new MyCampo();
        $nome->id = $nome->nome      = "cag_nome";
        $nome->ordem                 = $pos;
        $nome->tipo                  = 'text';
        $nome->valor = $nome->selecionado = $fdados['cag_nome'];
        $nome->label                 = '';
        $nome->place                 = '';
        $nome->largura               = 40;
        $nome->size                  = 40;
        $nome->leitura               = true; // recalcular Solver
        $this->cag_nome              = $nome->crInput();

        $quad                        = new MyCampo();
        $quad->id = $quad->nome      = "col_quad";
        $quad->ordem                 = $pos;
        $quad->tipo                  = 'quantia';
        $quad->decimal               = 0;
        $quad->valor = $quad->selecionado = $fdados['col_quad'];
        $quad->label                 = '';
        $quad->place                 = '';
        $quad->largura               = 10;
        $quad->size                  = 10;
        $quad->leitura               = true; // recalcular Solver
        $this->col_quad              = $quad->crInput();

        $cola                        = new MyCampo();
        $cola->id = $cola->nome      = "num_colab";
        $cola->ordem                 = $pos;
        $cola->tipo                  = 'quantia';
        $cola->decimal               = 0;
        $cola->valor = $cola->selecionado = $fdados['num_colab'];
        $cola->label                 = '';
        $cola->place                 = '';
        $cola->largura               = 10;
        $cola->size                  = 10;
        $cola->leitura               = true; // recalcular Solver
        $this->num_colab              = $cola->crInput();

        $coln                        = new MyCampo();
        $coln->id = $coln->nome      = "num_colnv";
        $coln->ordem                 = $pos;
        $coln->tipo                  = 'quantia';
        $coln->decimal               = 0;
        $coln->valor = $coln->selecionado = $fdados['num_colnv'];
        $coln->label                 = '';
        $coln->place                 = '';
        $coln->largura               = 10;
        $coln->size                  = 10;
        $coln->leitura               = false; // recalcular Solver
        $coln->funcChan              = 'recalcula_solver(2)'; // recalcular Solver
        $this->num_colnv              = $coln->crInput();

        $pcta                        = new MyCampo();
        $pcta->id = $pcta->nome      = "pct_ant";
        $pcta->ordem                 = $pos;
        $pcta->tipo                  = 'quantia';
        $pcta->decimal               = 2;
        $pcta->valor = $pcta->selecionado = $fdados['pct_ant'];
        $pcta->label                 = '';
        $pcta->place                 = '';
        $pcta->largura               = 10;
        $pcta->size                  = 10;
        $pcta->leitura               = true; // recalcular Solver
        $this->pct_ant              = $pcta->crInput();

        $indi                        = new MyCampo();
        $indi->id = $indi->nome      = "indice";
        $indi->ordem                 = $pos;
        $indi->tipo                  = 'quantia';
        $indi->decimal               = 5;
        $indi->valor = $indi->selecionado = $fdados['indice'];
        $indi->label                 = '';
        $indi->place                 = '';
        $indi->largura               = 12;
        $indi->size                  = 12;
        $indi->leitura               = true; // recalcular Solver
        $this->indice              = $indi->crInput();

        $qtdi                        = new MyCampo();
        $qtdi->id = $qtdi->nome      = "qtdind";
        $qtdi->ordem                 = $pos;
        $qtdi->tipo                  = 'quantia';
        $qtdi->decimal               = 6;
        $qtdi->valor = $qtdi->selecionado = $fdados['qtdind'];
        $qtdi->label                 = '';
        $qtdi->place                 = '';
        $qtdi->largura               = 10;
        $qtdi->size                  = 10;
        $qtdi->leitura               = true; // recalcular Solver
        $this->qtdind              = $qtdi->crInput();

        $pctn                        = new MyCampo();
        $pctn->id = $pctn->nome      = "pctnov";
        $pctn->ordem                 = $pos;
        $pctn->tipo                  = 'quantia';
        $pctn->decimal               = 2;
        $pctn->valor = $pctn->selecionado = $fdados['pctnov'];
        $pctn->label                 = '';
        $pctn->place                 = '';
        $pctn->largura               = 10;
        $pctn->size                  = 10;
        $pctn->funcBlur              = 'recalcula_solver(3, this)'; // recalcular Solver
        $pctn->leitura               = false; // recalcular Solver
        $this->pctnov              = $pctn->crInput();

        $simn                        = new MyCampo();
        $simn->id = $simn->nome      = "simnov";
        $simn->ordem                 = $pos;
        $simn->tipo                  = 'quantia';
        $simn->decimal               = 2;
        $simn->valor = $simn->selecionado = $fdados['simnov'];
        $simn->label                 = '';
        $simn->place                 = '';
        $simn->largura               = 10;
        $simn->size                  = 10;
        $simn->funcChan              = 'recalcula_solver(2)'; // recalcular Solver
        $simn->leitura               = false; // recalcular Solver
        $this->simnov              = $simn->crInput();

        $goja                        = new MyCampo();
        $goja->id = $goja->nome      = "gojant";
        $goja->ordem                 = $pos;
        $goja->tipo                  = 'moeda';
        $goja->decimal               = 2;
        $goja->valor = $goja->selecionado = $fdados['gojant'];
        $goja->label                 = '';
        $goja->place                 = '';
        $goja->largura               = 15;
        $goja->size                  = 15;
        $goja->leitura               = true; // recalcular Solver
        $this->gojant              = $goja->crInput();

        $gojn                        = new MyCampo();
        $gojn->id = $gojn->nome      = "gojnov";
        $gojn->ordem                 = $pos;
        $gojn->tipo                  = 'moeda';
        $gojn->decimal               = 2;
        $gojn->valor = $gojn->selecionado = $fdados['gojnov'];
        $gojn->label                 = '';
        $gojn->place                 = '';
        $gojn->largura               = 15;
        $gojn->size                  = 15;
        $gojn->leitura               = true; // recalcular Solver
        $this->gojnov              = $gojn->crInput();

        $difg                        = new MyCampo();
        $difg->id = $difg->nome      = "difgoj";
        $difg->ordem                 = $pos;
        $difg->tipo                  = 'moeda';
        $difg->decimal               = 2;
        $difg->valor = $difg->selecionado = $fdados['difgoj'];
        $difg->label                 = '';
        $difg->place                 = '';
        $difg->largura               = 15;
        $difg->size                  = 15;
        $difg->leitura               = true; // recalcular Solver
        $this->difgoj              = $difg->crInput();

    }
    /**
     * Definição de Campos
     * def_campos
     *
     * @param bool $dados 
     * @return void
     */
    public function def_campos_calculo($dados)
    {
        $objp                        = new MyCampo();
        $objp->id = $objp->nome      = "pctdistribui";
        $objp->tipo                  = 'porcent';
        $objp->decimal               = 2;
        $objp->valor = $objp->selecionado = $dados['pctdistribui'];
        $objp->label                 = '';
        $objp->place                 = '';
        $objp->largura               = 10;
        $objp->size                  = 10;
        $objp->funcBlur              = 'recalcula_solver(1)'; // recalcular Solver
        $this->pctdistribui          = $objp->crInput();

        $pdis                        = new MyCampo();
        $pdis->id = $pdis->nome      = "pctdistribuicao";
        $pdis->tipo                  = 'porcent';
        $pdis->decimal               = 2;
        $pdis->valor = $pdis->selecionado = $dados['pctdistribuicao'];
        $pdis->label                 = '';
        $pdis->place                 = '';
        $pdis->largura               = 10;
        $pdis->size                  = 10;
        $pdis->leitura               = true;
        $this->pctdistribuicao            = $pdis->crInput();

        $vari                        = new MyCampo();
        $vari->id = $vari->nome      = "variavel";
        $vari->tipo                  = 'porcent';
        $vari->decimal               = 4;
        $vari->valor = $vari->selecionado = $dados['variavel'];
        $vari->label                 = '';
        $vari->place                 = '';
        $vari->largura               = 10;
        $vari->size                  = 10;
        $vari->leitura               = true;
        $this->variavel            = $vari->crInput();

        $totp                        = new MyCampo();
        $totp->id = $totp->nome      = "pcttotal";
        $totp->tipo                  = 'porcent';
        $totp->decimal               = 2;
        $totp->valor = $totp->selecionado = $dados['pcttotal'];
        $totp->label                 = '';
        $totp->place                 = '';
        $totp->largura               = 10;
        $totp->size                  = 10;
        $totp->leitura               = true;
        $this->pcttotal            = $totp->crInput();

        $difp                        = new MyCampo();
        $difp->id = $difp->nome      = "difpercentual";
        $difp->tipo                  = 'porcent';
        $difp->decimal               = 2;
        $difp->valor = $difp->selecionado = $dados['difpercentual'];
        $difp->label                 = '';
        $difp->place                 = '';
        $difp->largura               = 10;
        $difp->size                  = 10;
        $difp->leitura               = true;
        $this->difpercentual         = $difp->crInput();
    }
    /**
     * Definição de Campos
     * def_campos
     *
     * @param bool $dados 
     * @return void
     */
    // public function def_campos_solver($numcol, $pctant, $pctnov, $pos = 0)
    // {
    //     $coln                        = new MyCampo();
    //     $coln->id = $coln->nome      = "pctant[$pos]";
    //     $coln->tipo                  = 'quantia';
    //     $coln->decimal               = 0;
    //     $coln->valor = $coln->selecionado = $numcol;
    //     $coln->label                 = '';
    //     $coln->place                 = '';
    //     $coln->largura               = 10;
    //     $coln->size                  = 10;
    //     $this->numcolnovo            = $coln->crInput();

    //     $pcta                        = new MyCampo();
    //     $pcta->id = $pcta->nome      = "pctant[$pos]";
    //     $pcta->tipo                  = 'porcent';
    //     $pcta->decimal               = 2;
    //     $pcta->valor = $pcta->selecionado = $pctant;
    //     $pcta->label                 = '';
    //     $pcta->place                 = '';
    //     $pcta->largura               = 10;
    //     $pcta->size                  = 10;
    //     $this->pctant                = $pcta->crInput();

    //     $pctn                        = new MyCampo();
    //     $pctn->id = $pctn->nome      = "pctnov[$pos]";
    //     $pctn->tipo                  = 'porcent';
    //     $pctn->decimal                  = 5;
    //     $pctn->valor = $pctn->selecionado = $pctnov;
    //     $pctn->label                 = '';
    //     $pctn->place                 = '';
    //     $pctn->largura               = 12;
    //     $pctn->size                  = 12;
    //     $this->pctnov                = $pctn->crInput();
    // }

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
        $emp->largura               = 35;
        $emp->leitura               = $show;
        $emp->dispForm              = 'col-3';
        $emp->funcChan              = 'carrega_solver(this)';
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
        $comp->dispForm             = 'col-2';
        $comp->funcChan              = 'carrega_solver(this)';
        $this->competencia          = $comp->crDepende();
        
        $setores            = new RechumSetorModel();
        $dados_set          = $setores->getSetor();
        $opc_set            = array_column($dados_set, 'set_nome', 'set_id');

        $set                        = new MyCampo('rh_colaborador','set_id');
        $set->valor = $set->selecionado = isset($dados['set_id'])? $dados['set_id']: '';
        $set->obrigatorio           = true;
        $set->opcoes                = $opc_set;
        $set->largura               = 20;
        $set->leitura               = $show;
        $set->dispForm              = 'col-2';
        $set->funcChan              = 'carrega_solver(this)';
        $this->set_id               = $set->crSelect();

        $dis                        = new MyCampo();
        $dis->id = $dis->nome       = 'distribui';
        $dis->label                 = 'Distribuição';
        $dis->tipo                  = 'moeda';
        $dis->valor = $set->selecionado = 0;
        $dis->obrigatorio           = true;
        $dis->largura               = 15;
        $dis->size                  = 15;
        $dis->decimal               = 2;
        $dis->dispForm              = 'col-2';
        $dis->funcChan              = 'carrega_solver(this)';
        $this->distribui            = $dis->crInput();
        
        $bot = new MyCampo();
        $bot->nome = 'Calcular';
        $bot->id    = 'Calcular';
        $bot->label = 'Calcular';
        $bot->place = 'Calcular';
        $bot->i_cone = "<i class='fa-solid fa-play'></i> Calcular Solver";
        $bot->classep = 'btn btn-warning';
        $bot->tipo = 'button';
        $bot->funcChan = 'calcula_solver()';
        $bot->dispForm  = 'col-3';
        $this->botao = $bot->crBotao();

    }

	public function recalcula_solver($tipo,$pctalt = null)
	{
        $ret = [];
		$campos  		= $this->request->getVar();
        $distribui      = $campos['distribui'];
        $pctdistribui   = $campos['pctdistribui'];
        $pctbase = 0;
        for($s=0;$s<count($campos['cag_nome']);$s++){
            $pctant      = str_replace(',','.',$campos['pct_ant'][$s]);
            // debug($pctant);
            $pctantx[$s] = $pctant;
            if($pctbase == 0){
                $pctbase = $pctant;
            }
            $indice      = number_format($pctant / $pctbase,6);
            // $pctbase     = $pctant;
            // debug($setor_antes[$s]);
            // debug($seto);
            $numcolx[$s] = $campos['num_colab'][$s];
            // debug($numcolx[$s]);
            $numconx[$s] = $campos['num_colnv'][$s];
            // debug($numconx[$s]);
            $qtdindx[$s] = $campos['num_colnv'][$s] * floatval($indice);
            $indicex[$s] = $indice;
        }
        // debug($indicex);
        if($tipo == 1){
            $resultado = solver($qtdindx, $pctdistribui);
            $variavel = floatval(number_format($resultado['percentual'],2));
        } else {
            $variavel = floatval(number_format($campos['variavel'],2));
        }
        // debug($variavel);
        // RECALCULA OS ÍNDICES PELAS NOVAS PCT
        $pctbase = 0;
        for($sn=0;$sn<count($campos['cag_nome']);$sn++){
            $indice = $indicex[$sn];
            if($pctalt != null && $pctalt == $sn){
                $simnov = str_replace(",",'.',$campos['pctnov'][$pctalt]);
                // debug($simnov);
            } else {
                $simnov = $indice * $variavel;
            }
            $pctnov = floatval(number_format($simnov,2));
            $pctnovx[$sn] = $pctnov;
            $simnovx[$sn] = $simnov;

            $pctant      = $simnov;
            // debug($pctant);
            // $pctantx[$s] = $pctant;
            if($pctbase == 0){
                $pctbase = $pctnov;
            }
            $indice       = number_format($pctnov / $pctbase,6);
            $qtdindx[$sn] = floatval(intval($numconx[$sn]) * floatval($indice) * $variavel);
            $indicex[$sn] = $indice;
        }
        // if($pctalt != null){
        //     debug($simnovx);
        // }
        // debug($indicex);
        $pctdistribui = number_format(floatval(soma($qtdindx)));
        // debug($pctdistribui);

        for($s=0;$s<count($campos['cag_nome']);$s++){
            // debug($cagquadro);
            $pctant = $pctantx[$s];
            $indice = $indicex[$s];
            $numcol = $numcolx[$s];
            $numcon = $numconx[$s];
            $simnov = $simnovx[$s];
            $pctnov = $pctnovx[$s];
            $qtdind = $qtdindx[$s];
            
            // debug(moedaToFloat($distribui));
            $gojant = $pctant * (moedaToFloat($distribui) / 100);
            $gojnov = $simnov * (moedaToFloat($distribui) / 100);
            // $pctdistribui += $indicex[$s];

            $difgoj = floatToMoeda(floatval($gojnov) - floatval($gojant));

            if($s==0){
                // debug(count($numcolx));
                // debug(count($pctantx), true);
                
                $pctdistribuicao    = somarProduto($pctantx,$numcolx);
                $pcttotal           = somarProduto($pctnovx,$numconx);
                $difpercentual      = $pctdistribuicao - $pcttotal;
                $c_calculo['pctdistribui']      = $pctdistribui;
                $c_calculo['pctdistribuicao']   = $pctdistribuicao;
                $c_calculo['variavel']          = $variavel;
                $c_calculo['pcttotal']          = $pcttotal;
                $c_calculo['difpercentual']     = $difpercentual;
                $ret['calculo'] = $c_calculo;
            }
            $c_funcoes['cag_nome']  = $campos['cag_nome'][$s];
            $c_funcoes['col_quad']  = $campos['col_quad'][$s];
            $c_funcoes['num_colab'] = $numcol;
            $c_funcoes['num_colnv'] = $numcon;
            $c_funcoes['pct_ant']   = number_format($pctant,2);
            $c_funcoes['indice']    = number_format($indice,5);
            $c_funcoes['qtdind']    = number_format($qtdind,8);
            $c_funcoes['pctnov']    = number_format($pctnov,2);
            $c_funcoes['simnov']    = number_format($simnov,8);
            $c_funcoes['gojant']    = floatToMoeda($gojant);
            $c_funcoes['gojnov']    = floatToMoeda($gojnov);
            $c_funcoes['difgoj']    = $difgoj;
            $ret['funcoes'][$s] = $c_funcoes;
        }
        
        echo json_encode($ret);
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
