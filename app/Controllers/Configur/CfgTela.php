<?php

namespace App\Controllers\Configur;

use App\Controllers\BaseController;
use App\Libraries\Campos;
use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\Config\ConfigDicDadosModel;
use App\Models\Config\ConfigModuloModel;
use App\Models\Config\ConfigTelaListaModel;
use App\Models\Config\ConfigTelaModel;
use App\Models\Config\ConfigUsuarioModel;
use PHPUnit\Util\Json;
use ReflectionClass;
use ReflectionMethod;

class CfgTela extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $Tela;
    public $TelaLista;
    public $dicionario;
    public $logs;
    public $usuario;
    public $modulo;
    public $common;

    private $tel_id;
    private $tel_modulo;
    private $tel_nome;
    private $tel_icon;
    private $tel_cont;
    private $tel_txtb;
    private $tel_desc;
    private $tel_tabela;
    private $tel_regg;
    private $tel_regc;
    private $tel_camp;
    private $tel_trel;
    private $tel_meto;
    private $tel_codi;

    /**
     * Construtor da Tela
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->Tela    = new ConfigTelaModel();
        $this->TelaLista    = new ConfigTelaListaModel();
        $this->dicionario = new ConfigDicDadosModel();
        $this->usuario   = new ConfigUsuarioModel();
        $this->modulo    = new ConfigModuloModel();
        $this->common    = new CommonModel();
        $this->dicionario     = new ConfigDicDadosModel();
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
        $tabela = $this->data['tabela'];
        $campos = montaColunasCampos($this->data, 'tel_id');
        // debug($_POST);

        // $draw  = $_POST['draw'];
        // $limit = $_POST['length'];
        // $start = $_POST['start'];
        // $ord   = isset($_POST['order']) ? $_POST['order'][0]['column'] : 1;
        // $order = $campos[$ord];
        // $dir   = isset($_POST['order']) ? $_POST['order'][0]['dir'] : 'ASC';
        // $busca = $_POST['search']['value'];
        // debug($limit);
        // debug($start);
        // debug($order);
        // debug($dir);
        $banco = 'dbConfig';
        // $dados_tela = $this->common->getPostsSearch($banco, $tabela, $campos, $limit, $start, $busca, $order, $dir);
        // debug($dados, true);        
        $dados_tela = $this->Tela->getTelaId();
        $classs = [
            'data' => montaListaColunas($this->data, 'tel_id', $dados_tela, $campos[3]),
        ];

        $total = $this->common->getPostsTotal($banco, $tabela, $campos);

        // $output = array(
        //     "draw"              => $draw,
        //     "recordsTotal"      => $total,
        //     "recordsFiltered"   => count($dados_tela),
        //     "data"     => $classs['data'],
        // );
        echo json_encode($classs);
    }

    /**
     * Inclusão
     * add
     *
     * @return void
     */
    public function add()
    {
        $this->defCampos([]);

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->tel_id;
        $campos[0][1] = $this->tel_modulo;
        $campos[0][2] = $this->tel_nome;
        $campos[0][3] = $this->tel_icon;
        $campos[0][4] = $this->tel_cont;
        $campos[0][5] = $this->tel_txtb;
        $campos[0][6] = $this->tel_desc;
        $campos[0][7] = $this->tel_tabela;

        $secao[1] = 'Regras Gerais';
        $campos[1][0] = $this->tel_regg;

        $secao[2] = 'Regras do Cadastro';
        $campos[2][0] = $this->tel_regc;


        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';

        echo view('vw_edicao', $this->data);
    }

    /**
     * Edição
     * edit
     *
     * @param mixed $id
     * @return void
     */
    public function edit($id)
    {
        $dados_tela = $this->Tela->getTelaId($id)[0];
        session()->set('tabela', $dados_tela['tel_tabela']);
        // $displ = $dados_tela['tel_nome'];
        $this->defCampos($dados_tela);

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->tel_id;
        $campos[0][1] = $this->tel_modulo;
        $campos[0][2] = $this->tel_nome;
        $campos[0][3] = $this->tel_icon;
        $campos[0][4] = $this->tel_cont;
        $campos[0][5] = $this->tel_txtb;
        $campos[0][6] = $this->tel_desc;

        $secao[1] = 'Regras Gerais';
        $campos[1][0] = $this->tel_regg;

        $secao[2] = 'Regras do Cadastro';
        $campos[2][0] = $this->tel_regc;

        $secao[3] = 'Base de Dados';
        $campos[3][0] = $this->tel_tabela;
        $campos[3][1] = $this->tel_camp;
        $campos[3][2] = $this->tel_trel;

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
            $this->defCamposLista();
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
    public function addCampoLista($ind)
    {
        $this->defCamposLista(false, $ind);

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
            $this->Tela->delete($id);
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

        $cont               = new MyCampo('cfg_tela', 'tel_controler');
        $cont->obrigatorio = true;
        $cont->valor = array_key_exists('tel_controler', $dados) ? $dados['tel_controler'] : '';
        $cont->dispForm     = '2col';
        $this->tel_cont = $cont->crInput();

        $txtb               = new MyCampo('cfg_tela', 'tel_texto_botao');
        $txtb->obrigatorio = true;
        $txtb->valor = array_key_exists('tel_texto_botao', $dados) ? $dados['tel_texto_botao'] : '';
        $this->tel_txtb = $txtb->crInput();

        $opc_tab = [];
        if (array_key_exists('tel_tabela', $dados)) {
            $opc_tabs         = $this->dicionario->getTabelaSearch($dados['tel_tabela']);
            $opc_tab          = array_column($opc_tabs, 'table_name', 'table_name');
        }
        $tabe               =  new MyCampo('cfg_tela', 'tel_tabela');
        $tabe->largura      = 50;
        $tabe->obrigatorio  = true;
        $tabe->urlbusca        = base_url('buscas/busca_tabela');
        $tabe->opcoes       = $opc_tab;
        $tabe->valor        = array_key_exists('tel_tabela', $dados) ? $dados['tel_tabela'] : '';
        $tabe->selecionado  = $tabe->valor;
        $this->tel_tabela   = $tabe->crSelbusca();

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

        $tabela = session()->get('tabela');
        // debug($tabela);
        if ($tabela != '') {
            $campos_tab = $this->dicionario->getCampos($tabela);
            $relac = $this->dicionario->getRelacionamentos($tabela);
        }
        $camp           = new MyCampo();
        $camp->label    = 'Campos da Tabela';
        if ($tabela != '') {
            $camp->valor    = campos_tabela($campos_tab);
        }
        $this->tel_camp = $camp->crShow();

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
                $path = 'App\\Controllers\\Configur\\';
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
        $tabela = session()->get('tabela');
        $campos_tab = $this->dicionario->getCampos($tabela);
        // debug($campos_tab, false);
        $campos_lis = array_column($campos_tab, 'NOME_COMPLETO', 'COLUMN_NAME');
        // debug($campos_lis, false);

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
        $this->lis_campo      = $camp->crSelect();

        $rotu           = new MyCampo('cfg_tela_lista', 'lis_rotulo');
        $rotu->nome     = $rotu->nome . "[$pos]";
        $rotu->id       = $rotu->id . "[$pos]";
        $rotu->valor          = (isset($lista['lis_rotulo'])) ? $lista['lis_rotulo'] : '';
        $rotu->dispForm        = '2col';
        $rotu->classep        = 'semmb';
        $this->lis_rotulo       = $rotu->crInput();

        $atrib['data-index'] = $pos;
        $add            = new MyCampo();
        $add->attrdata  = $atrib;
        $add->nome      = "bt_add[$pos]";
        $add->id        = "bt_add[$pos]";
        $add->i_cone    = "<i class='fas fa-plus'></i>";
        $add->place     = "Adicionar Campo";
        $add->classep   = "btn-outline-success btn-sm bt-repete listagem";
        $add->funcChan  = "addCampo('" . base_url("CfgTela/addCampoLista") . "','listagem',this)";
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
            'tel_tabela'       => $dados['tel_tabela'],
            'tel_descricao'    => $dados['tel_descricao'],
            // 'tel_lista'        => $tel_lista,
            // 'tel_filtros'       => $tel_filtros,
            'tel_regras_gerais'       => $dados['tel_regras_gerais'],
            'tel_regras_cadastro'       => $dados['tel_regras_cadastro'],
        ];
        // debug($dados_clas);
        if ($this->Tela->save($dados_clas)) {
            $tel_id = $this->Tela->getInsertID();
            if ($dados['tel_id'] != '') {
                $tel_id = $dados['tel_id'];
            }
            if (isset($dados['lis_id'])) {
                $data_atu = date('Y-m-d H:i');
                $lis_exc = $this->TelaLista->excluiCampoLista($tel_id);
                foreach ($dados['lis_id'] as $key => $value) {
                    $dados_lis = [
                        // 'lis_id'             => $dados['lis_id'][$lis],
                        'tel_id'             => $tel_id,
                        'lis_campo'          => $dados['lis_campo'][$key],
                        'lis_rotulo'         => $dados['lis_rotulo'][$key],
                        'lis_atualizado'     => $data_atu,
                    ];
                    $lis_id = $this->TelaLista->save($dados_lis);
                    if (!$lis_id) {
                        $ret['erro'] = true;
                        $ret['msg'] = 'Não foi possível gravar os Campos da Listagem, Verifique!';
                        break;
                    }
                }
            }
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
