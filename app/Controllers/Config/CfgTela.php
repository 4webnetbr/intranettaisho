<?php

namespace App\Controllers\Config;

use App\Controllers\BaseController;
use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\Config\ConfigDicDadosModel;
use App\Models\Config\ConfigModuloModel;
use App\Models\Config\ConfigTelaListaModel;
use App\Models\Config\ConfigTelaModel;
use App\Models\Config\ConfigUsuarioModel;
use ReflectionClass;
use ReflectionMethod;

class CfgTela extends BaseController
{
    public $data = [];
    public $permissao = '';
<<<<<<< HEAD
    public $tela;
    public $telalista;
=======
    public $Tela;
    public $TelaLista;
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7
    public $dicionario;
    public $logs;
    public $usuario;
    public $modulo;
    public $common;
    public $model_atual;

    private $tel_id;
    private $tel_modulo;
    private $tel_nome;
    private $tel_icon;
    private $tel_cont;
    private $tel_txtb;
    private $tel_desc;
    private $tel_tabela;
    private $tel_view;
    private $tel_regg;
    private $tel_regc;
    private $tel_camp;
    private $tel_camp_view;
    private $tel_trel;
    private $tel_meto;
    private $tel_codi;
    private $tel_mode;

    /**
     * Construtor da Tela
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
<<<<<<< HEAD
        $this->tela    = new ConfigTelaModel();
        $this->telalista    = new ConfigTelaListaModel();
=======
        $this->Tela    = new ConfigTelaModel();
        $this->TelaLista    = new ConfigTelaListaModel();
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7
        $this->dicionario = new ConfigDicDadosModel();
        $this->usuario   = new ConfigUsuarioModel();
        $this->modulo    = new ConfigModuloModel();
        $this->common    = new CommonModel();
<<<<<<< HEAD
        $this->model_atual = $this->tela;
=======
        $this->dicionario     = new ConfigDicDadosModel();
        $this->model_atual = $this->Tela;
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7

        if ($this->data['erromsg'] != '') {
            $this->__erro();
        }
    }

    /**
     * Erro de Acesso
     * erro
     */
    public function __erro()
    {
        echo view('vw_semacesso', $this->data);
    }

    /**
     * Tela de Abertura
     * index
     */
    public function index()
    {
        $this->data['colunas'] = montaColunasLista($this->data, 'tel_id');
        $this->data['url_lista'] = base_url($this->data['controler'] . '/lista');
        echo view('vw_lista', $this->data);
    }

    /**
     * Listagem
     * lista
     *
     * @return void
     */
    public function lista()
    {
        if (!$telas = cache('telas')) {
            $campos = montaColunasCampos($this->data, 'tel_id');
<<<<<<< HEAD
            $dados_tela = $this->tela->getTelaId();
=======
            $dados_tela = $this->Tela->getTelaId();
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7
            $telas = [
                'data' => montaListaColunas($this->data, 'tel_id', $dados_tela, $campos[3]),
            ];
            cache()->save('telas', $telas, 60000);
        }

        echo json_encode($telas);
    }

    /**
     * Inclusão
     * add
     *
     * @return void
     */
    public function add()
    {
<<<<<<< HEAD
        $this->model_atual = $this->tela;

        $fields = $this->tela->defCampos([], false, '', '');

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $fields['tel_id'];
        $campos[0][1] = $fields['tel_modulo'];
        $campos[0][2] = $fields['tel_nome'];
        $campos[0][3] = $fields['tel_icon'];
        $campos[0][4] = $fields['tel_txtb'];
        $campos[0][5] = $fields['tel_cont'];
        $campos[0][6] = $fields['tel_mode'];
        $campos[0][7] = $fields['tel_desc'];
=======
        $this->model_atual = $this->Tela;

        $this->defCampos([]);


        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->tel_id;
        $campos[0][1] = $this->tel_modulo;
        $campos[0][2] = $this->tel_nome;
        $campos[0][3] = $this->tel_icon;
        $campos[0][4] = $this->tel_txtb;
        $campos[0][5] = $this->tel_cont;
        $campos[0][6] = $this->tel_mode;
        $campos[0][7] = $this->tel_desc;
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7
        // $campos[0][8] = $this->tel_tabela;
        // $campos[0][9] = $this->tel_view;

        $secao[1] = 'Regras Gerais';
<<<<<<< HEAD
        $campos[1][0] = $fields['tel_regg'];

        $secao[2] = 'Regras do Cadastro';
        $campos[2][0] = $fields['tel_regc'];
=======
        $campos[1][0] = $this->tel_regg;

        $secao[2] = 'Regras do Cadastro';
        $campos[2][0] = $this->tel_regc;

>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7

        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';

        echo view('vw_edicao', $this->data);
    }

<<<<<<< HEAD
    public function show($id){
        $this->edit($id, true);
    }
=======
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7
    /**
     * Edição
     * edit
     *
     * @param mixed $id
     * @return void
     */
<<<<<<< HEAD
    public function edit($id, $show = false)
    {
        $dados_tela = $this->tela->getTelaId($id)[0];
        $this->model_atual = $this->tela;
=======
    public function edit($id)
    {
        $dados_tela = $this->Tela->getTelaId($id)[0];
        $this->model_atual = $this->Tela;
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7
        // debug($dados_tela['tel_model']);
        if (isset($dados_tela['tel_model']) && $dados_tela['tel_model'] != null) {
            $model = $dados_tela['tel_model'];
            $compl_model = substr($model, 0, 6);
            $pasta = "App\\Models\\".$compl_model."\\";
            $this->model_atual = model($pasta . $model);
        }
<<<<<<< HEAD
        $tabela = $this->model_atual->table;
        $view   = $this->model_atual->view;
        $fields = $this->tela->defCampos($dados_tela, $show, $tabela, $view);

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $fields['tel_id'];
        $campos[0][1] = $fields['tel_modulo'];
        $campos[0][2] = $fields['tel_nome'];
        $campos[0][3] = $fields['tel_icon'];
        $campos[0][4] = $fields['tel_txtb'];
        $campos[0][5] = $fields['tel_cont'];
        $campos[0][6] = $fields['tel_mode'];
        $campos[0][7] = $fields['tel_desc'];

        $secao[1] = 'Regras Gerais';
        $campos[1][0] = $fields['tel_regg'];

        $secao[2] = 'Regras do Cadastro';
        $campos[2][0] = $fields['tel_regc'];

        $secao[3] = 'Base de Dados';
        $campos[3][0] = $fields['tel_tabela'];
        $campos[3][1] = $fields['tel_view'];
        $campos[3][2] = $fields['tel_camp'];
        $campos[3][3] = $fields['tel_trel'];
        $campos[3][4] = $fields['tel_camp_view'];

        $secao[4] = 'Listagem';
        $displ[4] = 'tabela';
        $dados_lista = $this->telalista->getListagem($id);
        if (count($dados_lista) > 0) {
            for ($c = 0; $c < count($dados_lista); $c++) {
                $this->defCamposLista($dados_lista[$c], $c, $show, $tabela, $view);
                $campos[4][$c][0] = $this->lis_id;
                $campos[4][$c][1] = $this->lis_campo;
                $campos[4][$c][2] = $this->lis_rotulo;
                if(!$show){
                    $campos[4][$c][3] = $this->bt_add;
                    $campos[4][$c][4] = $this->bt_del;
                } else {
                    $campos[4][$c][3] = '';
                    $campos[4][$c][4] = '';
                }
            }
        } else {
            $this->defCamposLista($dados_tela, 0, $show, $tabela, $view);
            $campos[4][0][0] = $this->lis_id;
            $campos[4][0][1] = $this->lis_campo;
            $campos[4][0][2] = $this->lis_rotulo;
            if(!$show){
                $campos[4][0][3] = $this->bt_add;
                $campos[4][0][4] = $this->bt_del;
            } else {
                $campos[4][0][3] = '';
                $campos[4][0][4] = '';
            }
        }

        $secao[5] = 'Funções';
        $campos[5][0] = $fields['tel_meto'];

        $secao[6] = 'Código Fonte';
        $campos[6][0] = $fields['tel_codi'];
=======
        // debug($this->model_atual);

        $this->defCampos($dados_tela);

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->tel_id;
        $campos[0][1] = $this->tel_modulo;
        $campos[0][2] = $this->tel_nome;
        $campos[0][3] = $this->tel_icon;
        $campos[0][4] = $this->tel_txtb;
        $campos[0][5] = $this->tel_cont;
        $campos[0][6] = $this->tel_mode;
        $campos[0][7] = $this->tel_desc;

        $secao[1] = 'Regras Gerais';
        $campos[1][0] = $this->tel_regg;

        $secao[2] = 'Regras do Cadastro';
        $campos[2][0] = $this->tel_regc;

        $secao[3] = 'Base de Dados';
        $campos[3][0] = $this->tel_tabela;
        $campos[3][1] = $this->tel_view;
        $campos[3][2] = $this->tel_camp;
        $campos[3][3] = $this->tel_trel;
        $campos[3][4] = $this->tel_camp_view;

        $secao[4] = 'Listagem';
        $displ[4] = 'tabela';
        $dados_lista = $this->TelaLista->getListagem($id);
        if (count($dados_lista) > 0) {
            for ($c = 0; $c < count($dados_lista); $c++) {
                $this->defCamposLista($dados_lista[$c], $c);
                $campos[4][$c][0] = $this->lis_id;
                $campos[4][$c][1] = $this->lis_campo;
                $campos[4][$c][2] = $this->lis_rotulo;
                $campos[4][$c][3] = $this->bt_add;
                $campos[4][$c][4] = $this->bt_del;
            }
        } else {
            $this->defCamposLista($dados_tela, 0);
            $campos[4][0][0] = $this->lis_id;
            $campos[4][0][1] = $this->lis_campo;
            $campos[4][0][2] = $this->lis_rotulo;
            $campos[4][0][3] = $this->bt_add;
            $campos[4][0][4] = $this->bt_del;
        }

        $secao[5] = 'Funções';
        $campos[5][0] = $this->tel_meto;

        $secao[6] = 'Código Fonte';
        $campos[6][0] = $this->tel_codi;
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7

        $this->data['desc_edicao'] = $dados_tela['tel_nome'];

        $this->data['displ'] = $displ;
        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';
        $this->data['script'] = "<script>acerta_botoes_rep('listagem')</script>";

        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('cfg_tela', $id);

        echo view('vw_edicao', $this->data);
    }

    /**
     * Summary of addCampoLis
     * @param mixed $ind
     * @return never
     */
    public function addCampoLista($id, $ind)
    {
<<<<<<< HEAD
        $dados_tela = $this->tela->getTelaId($id)[0];
        // debug($dados_tela);
        $this->model_atual = $this->tela;
=======
        $dados_tela = $this->Tela->getTelaId($id)[0];
        // debug($dados_tela);
        $this->model_atual = $this->Tela;
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7
        // debug($dados_tela['tel_model']);
        if (isset($dados_tela['tel_model']) && $dados_tela['tel_model'] != null) {
            $model = $dados_tela['tel_model'];
            $compl_model = substr($model, 0, 6);
            $pasta = "App\\Models\\".$compl_model."\\";
            $this->model_atual = model($pasta . $model);
        }
<<<<<<< HEAD
        $tabela = $this->model_atual->table;
        $view   = $this->model_atual->view;
        
        $lista['tel_id'] = $dados_tela['tel_id'];

        $this->defCamposLista($lista, $ind, false, $tabela, $view);
=======
        $lista['tel_id'] = $dados_tela['tel_id'];

        $this->defCamposLista($lista, $ind);
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7

        $campo[0] = $this->lis_id;
        $campo[1] = $this->lis_campo;
        $campo[2] = $this->lis_rotulo;
        $campo[3] = $this->bt_add;
        $campo[4] = $this->bt_del;

        echo json_encode($campo);
        exit;
    }

    /**
     * Exclusão
     * delete
     *
     * @param mixed $id
     * @return void
     */
    public function delete($id)
    {
        $ret = [];
        try {
<<<<<<< HEAD
            $this->tela->delete($id);
=======
            $this->Tela->delete($id);
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Tela Excluída com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir a Tela, Verifique!<br><br>';
            $ret['msg'] .= 'Esta Tela possui relacionamentos em outros cadastros!';
        }
        echo json_encode($ret);
    }

    /**
     * Definição de Campos
     * def_campos
     *
     * @param array $dados
     * @return void
     */
<<<<<<< HEAD
    
    public function defCamposLista($lista = '', $pos = 0, $show = false, $tabela = '', $view ='')
    {
        $campos_tab = $this->dicionario->getCampos($view);
        $campos_lis = array_column($campos_tab, 'NOME_COMPLETO', 'COLUMN_NAME');
        
=======
    public function defCampos($dados, $pos = 0)
    {
        $id         = new MyCampo('cfg_tela', 'tel_id');
        $id->valor = array_key_exists('tel_id', $dados) ? $dados['tel_id'] : '';
        $this->tel_id = $id->crOculto();

        $opc_mod = [];
        if (isset($dados['mod_id'])) {
            $opc_mods = $this->modulo->getModulo($dados['mod_id']);
            $opc_mod  = array_column($opc_mods, 'mod_nome', 'mod_id');
        }
        $modu               =  new MyCampo('cfg_tela', 'mod_id');
        $modu->label        = 'Módulo';
        $modu->largura      = 50;
        $modu->obrigatorio  = true;
        $modu->urlbusca        = base_url('buscas/busca_modulo');
        $modu->opcoes       = $opc_mod;
        $modu->cadModal     = base_url('CfgModulo/add/modal=true');
        $modu->valor        = array_key_exists('mod_id', $dados) ? $dados['mod_id'] : '';
        $modu->selecionado  = $modu->valor;
        $modu->dispForm     = '2col';
        $this->tel_modulo   = $modu->crSelbusca();

        $nome               = new MyCampo('cfg_tela', 'tel_nome');
        $nome->obrigatorio = true;
        $nome->valor = array_key_exists('tel_nome', $dados) ? $dados['tel_nome'] : '';
        $nome->dispForm     = '2col';
        $this->tel_nome = $nome->crInput();

        $icon           =  new MyCampo('cfg_tela', 'tel_icone');
        $icon->tipo     = 'icone';
        $icon->valor    = (isset($dados['tel_icone'])) ? $dados['tel_icone'] : '';
        $icon->dispForm     = '2col';
        $this->tel_icon = $icon->crInput();

        $pasta = APPPATH . '/Controllers';
        $arquivos = buscaArquivos($pasta, false, ['BaseController.php','Buscas.php','Logger.php']);
        sort($arquivos);

        $arqs = array_combine(array_values($arquivos), array_values($arquivos));
        $cont               = new MyCampo('cfg_tela', 'tel_controler');
        $cont->obrigatorio = true;
        $cont->valor = array_key_exists('tel_controler', $dados) ? $dados['tel_controler'] : '';
        $cont->dispForm     = '2col';
        $cont->opcoes       = $arqs;
        $cont->selecionado  = $cont->valor;
        $this->tel_cont     = $cont->crSelect();

        $pasta = APPPATH . '/Models';
        $models = buscaArquivos($pasta, false, []);
        sort($models);
        $mods = array_combine(array_values($models), array_values($models));
        $mode               = new MyCampo('cfg_tela', 'tel_model');
        $mode->obrigatorio = false;
        $mode->valor = array_key_exists('tel_model', $dados) ? $dados['tel_model'] : '';
        $mode->dispForm     = '2col';
        $mode->opcoes       = $mods;
        $mode->selecionado  = $mode->valor;
        $this->tel_mode     = $mode->crSelect();

        $txtb               = new MyCampo('cfg_tela', 'tel_texto_botao');
        $txtb->obrigatorio = false;
        $txtb->dispForm     = '2col';
        $txtb->valor = array_key_exists('tel_texto_botao', $dados) ? $dados['tel_texto_botao'] : '';
        $this->tel_txtb = $txtb->crInput();

        // $opc_tab = [];
        // if (array_key_exists('tel_tabela', $dados)) {
        //     $opc_tabs         = $this->dicionario->getTabelaSearch($dados['tel_tabela']);
        //     $opc_tab          = array_column($opc_tabs, 'table_name', 'table_name');
        // }
        // debug($this->model_atual,true);
        $tabela = $this->model_atual->table;
        // debug($tabela, true);
        $tabe               =  new MyCampo();
        $tabe->largura      = 40;
        $tabe->dispForm     = '2col';
        // $tabe->obrigatorio  = true;
        // $tabe->urlbusca        = base_url('buscas/busca_tabela');
        // $tabe->opcoes       = $opc_tab;
        $tabe->label        = 'Tabela Principal';
        $tabe->valor        = $tabela;
        // $tabe->selecionado  = $tabe->valor;
        $this->tel_tabela   = $tabe->crShow();

        $view = $this->model_atual->view;
        $cvie               =  new MyCampo();
        $cvie->largura      = 40;
        $cvie->dispForm     = '2col';
        // $cvie->obrigatorio  = true;
        // $tabe->urlbusca        = base_url('buscas/busca_tabela');
        // $tabe->opcoes       = $opc_tab;
        $cvie->label        = 'Visão Principal';
        $cvie->valor        = $view;
        // $tabe->selecionado  = $tabe->valor;
        $this->tel_view   = $cvie->crShow();

        $desc           = new MyCampo('cfg_tela', 'tel_descricao');
        $desc->colunas  = 80;
        $desc->linhas   = 3;
        $desc->maximo   = 255;
        $desc->valor    = array_key_exists('tel_descricao', $dados)
            ? $dados['tel_descricao']
            : '';
        $this->tel_desc = $desc->crTexto();

        $regg           = new MyCampo('cfg_tela', 'tel_regras_gerais');
        $regg->valor    = array_key_exists('tel_regras_gerais', $dados)
            ? $dados['tel_regras_gerais']
            : '';
        $this->tel_regg = $regg->crEditor();

        $regc           = new MyCampo('cfg_tela', 'tel_regras_cadastro');
        $regc->valor    = array_key_exists('tel_regras_cadastro', $dados)
            ? $dados['tel_regras_cadastro']
            : '';
        $this->tel_regc = $regc->crEditor();

        // debug($tabela);
        // if ($tabela != '') {
        $campos_tab = $this->dicionario->getCampos($tabela);
        // }
        // debug($campos_tab);
        $camp           = new MyCampo();
        $camp->label    = 'Campos da Tabela';
        if ($tabela != '') {
            $camp->valor    = campos_tabela($campos_tab);
        }
        $this->tel_camp = $camp->crShow();

        $campos_view = $this->dicionario->getCampos($view);
        $camp           = new MyCampo();
        $camp->label    = 'Campos da Visão Principal';
        $camp->valor    = campos_tabela($campos_view);
        $this->tel_camp_view = $camp->crShow();

        $relac = $this->dicionario->getRelacionamentos($tabela);
        $trel           = new MyCampo();
        $trel->label    = 'Tabelas Relacionadas';
        if ($tabela != '') {
            $trel->valor    = relacion_tabela($relac);
        }
        $this->tel_trel = $trel->crShow();

        $meto           = new MyCampo();
        $meto->label    = 'Métodos';
        $meto->valor    = '';
        if (isset($dados['tel_controler'])) {
            $path = 'App\\Controllers\\';
            if (substr($dados['tel_controler'], 0, 3) == 'Cfg') {
                $path = 'App\\Controllers\\Config\\';
            }
            if (class_exists($path . $dados['tel_controler'])) {
                $class      = new ReflectionClass($path . $dados['tel_controler']);
                $methods    = $class->getMethods(ReflectionMethod::IS_PUBLIC);

                $meto->valor = metodosTela($methods, $class->name);
            } else {
                $meto->valor = 'Tela ainda não foi codificada!';
            }
        }
        $this->tel_meto = $meto->crShow();

        if ($dados) {
            if (substr($dados['tel_controler'], 0, 3) == 'Cfg') {
                $fonte = verCodigo('Controllers/Config/' . $dados['tel_controler']);
            } else {
                $fonte = verCodigo('Controllers/' . $dados['tel_controler']);
            }

            $codi           = new MyCampo();
            $codi->label    = 'Código Fonte';
            $codi->valor    = '<pre>' . $fonte . '</pre>';
            $this->tel_codi = $codi->crShow();
        }
    }

    public function defCamposLista($lista = '', $pos = 0)
    {
        // debug($lista, true);
        $view = $this->model_atual->view;
        
        $campos_tab = $this->dicionario->getCampos($view);
        // debug($campos_tab, false);
        $campos_lis = array_column($campos_tab, 'NOME_COMPLETO', 'COLUMN_NAME');
        // debug($campos_lis, false);

>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7
        $id             = new MyCampo('cfg_tela_lista', 'lis_id');
        $id->nome       = $id->nome . "[$pos]";
        $id->id         = $id->id . "[$pos]";
        $id->valor      = isset($lista['lis_id']) ? $lista['lis_id'] : '';
        $this->lis_id   = $id->crOculto();

        $camp           = new MyCampo('cfg_tela_lista', 'lis_campo');
        $camp->nome     = $camp->nome . "[$pos]";
        $camp->id       = $camp->id . "[$pos]";
        $camp->opcoes         = $campos_lis;
        $camp->valor          = (isset($lista['lis_campo'])) ? $lista['lis_campo'] : '';
        $camp->selecionado    = $camp->valor;
        $camp->dispForm        = '2col';
        $camp->classep        = 'semmb';
<<<<<<< HEAD
        $camp->leitura        = $show;
=======
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7
        $this->lis_campo      = $camp->crSelect();

        $rotu           = new MyCampo('cfg_tela_lista', 'lis_rotulo');
        $rotu->nome     = $rotu->nome . "[$pos]";
        $rotu->id       = $rotu->id . "[$pos]";
        $rotu->valor          = (isset($lista['lis_rotulo'])) ? $lista['lis_rotulo'] : '';
        $rotu->dispForm        = '2col';
        $rotu->classep        = 'semmb';
<<<<<<< HEAD
        $rotu->leitura      = $show;
=======
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7
        $this->lis_rotulo       = $rotu->crInput();

        $atrib['data-index'] = $pos;
        $add            = new MyCampo();
        $add->attrdata  = $atrib;
        $add->nome      = "bt_add[$pos]";
        $add->id        = "bt_add[$pos]";
        $add->i_cone    = "<i class='fas fa-plus'></i>";
        $add->place     = "Adicionar Campo";
        $add->classep   = "btn-outline-success btn-sm bt-repete listagem";
        $add->funcChan  = "addCampo('".base_url("CfgTela/addCampoLista/".$lista['tel_id']) . "','listagem',this)";
        $this->bt_add   = $add->crBotao();

        $del            = new MyCampo();
        $del->attrdata  = $atrib;
        $del->nome      = "bt_del[$pos]";
        $del->id        = "bt_del[$pos]";
        $del->i_cone    = "<i class='fas fa-trash'></i>";
        $del->classep   = "btn-outline-danger btn-sm bt-exclui listagem";
        $del->funcChan  = "exclui_campo('listagem',this)";
        $del->place     = "Excluir Campo";
        $this->bt_del   = $del->crBotao();
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
        // debug($dados);
        $tel_lista = '';
        if (isset($dados['tel_lista'])) {
            $tel_lista = implode(',', $dados['tel_lista']);
        }

        $dados_clas = [
            'tel_id'           => $dados['tel_id'],
            'mod_id'           => $dados['mod_id'],
            'tel_nome'         => $dados['tel_nome'],
            'tel_icone'        => $dados['tel_icone'],
            'tel_controler'    => $dados['tel_controler'],
            'tel_texto_botao'  => $dados['tel_texto_botao'],
            'tel_model'        => $dados['tel_model'],
            'tel_descricao'    => $dados['tel_descricao'],
            // 'tel_lista'        => $tel_lista,
            // 'tel_filtros'       => $tel_filtros,
            'tel_regras_gerais'       => $dados['tel_regras_gerais'],
            'tel_regras_cadastro'       => $dados['tel_regras_cadastro'],
        ];
        // debug($dados_clas);
<<<<<<< HEAD
        if ($this->tela->save($dados_clas)) {
            $tel_id = $this->tela->getInsertID();
=======
        if ($this->Tela->save($dados_clas)) {
            $tel_id = $this->Tela->getInsertID();
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7
            if ($dados['tel_id'] != '') {
                $tel_id = $dados['tel_id'];
            }
            if (isset($dados['lis_id'])) {
                $data_atu = date('Y-m-d H:i');
<<<<<<< HEAD
                $lis_exc = $this->telalista->excluiCampoLista($tel_id);
=======
                $lis_exc = $this->TelaLista->excluiCampoLista($tel_id);
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7
                foreach ($dados['lis_id'] as $key => $value) {
                    $dados_lis = [
                        // 'lis_id'             => $dados['lis_id'][$lis],
                        'tel_id'             => $tel_id,
                        'lis_campo'          => $dados['lis_campo'][$key],
                        'lis_rotulo'         => $dados['lis_rotulo'][$key],
                        'lis_atualizado'     => $data_atu,
                    ];
<<<<<<< HEAD
                    $lis_id = $this->telalista->save($dados_lis);
=======
                    $lis_id = $this->TelaLista->save($dados_lis);
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7
                    if (!$lis_id) {
                        $ret['erro'] = true;
                        $ret['msg'] = 'Não foi possível gravar os Campos da Listagem, Verifique!';
                        break;
                    }
                }
            }
            cache()->clean();
            $ret['erro'] = false;
            $ret['msg'] = 'Tela gravada com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url'] = site_url($this->data['controler']);
        } else {
            $ret['erro'] = true;
            $ret['msg'] = 'Não foi possível gravar a Tela, Verifique!';
        }
        echo json_encode($ret);
    }
}
