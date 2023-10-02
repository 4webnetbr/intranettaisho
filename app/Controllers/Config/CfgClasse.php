<?php

namespace App\Controllers\Config;

use App\Controllers\BaseController;
use App\Libraries\Campos;
use App\Models\CommonModel;
use App\Models\Config\ConfigClasseListaModel;
use App\Models\Config\ConfigClasseModel;
use App\Models\Config\ConfigDicDadosModel;
use App\Models\Config\ConfigModuloModel;
use App\Models\Config\ConfigUsuarioModel;
use PHPUnit\Util\Json;

class CfgClasse extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $classe;
    public $classeLista;
    public $dicionario;
    public $logs;
    public $usuario;
    public $modulo;
    public $common;

    private $cls_id;
    private $cls_modulo;
    private $cls_nome;
    private $cls_icon;
    private $cls_cont;
    private $cls_txtb;
    private $cls_desc;
    private $cls_tabela;
    private $cls_regg;
    private $cls_regc;
    private $cls_lista;
    private $cls_camp;
    private $cls_trel;
    private $cls_meto;
    private $cls_codi;
    private $cls_deslis;
    private $bt_addl;
    private $bt_dell;

    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_classe');
        $this->permissao = $this->data['permissao'];
        $this->classe    = new ConfigClasseModel();
        $this->classeLista    = new ConfigClasseListaModel();
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
        $this->data['colunas'] = montaColunasLista($this->data, 'cls_id,');
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
        $dados_classe = $this->classe->getClasseId();
        $classs = [
            'data' => montaListaColunas($this->data, 'cls_id', $dados_classe, 'cls_nome'),
        ];

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
        $campos[0][0] = $this->cls_id;
        $campos[0][1] = $this->cls_modulo;
        $campos[0][2] = $this->cls_nome;
        $campos[0][3] = $this->cls_icon;
        $campos[0][4] = $this->cls_cont;
        $campos[0][5] = $this->cls_txtb;
        $campos[0][6] = $this->cls_desc;
        $campos[0][7] = $this->cls_tabela;

        $secao[1] = 'Regras Gerais';
        $campos[1][0] = $this->cls_regg;

        $secao[2] = 'Regras do Cadastro';
        $campos[2][0] = $this->cls_regc;


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
        $dados_classe = $this->classe->getClasseId($id)[0];
        session()->set('tabela', $dados_classe['cls_tabela']);
        // $displ = $dados_classe['cls_nome'];
        $this->defCampos($dados_classe);

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->cls_id;
        $campos[0][1] = $this->cls_modulo;
        $campos[0][2] = $this->cls_nome;
        $campos[0][3] = $this->cls_icon;
        $campos[0][4] = $this->cls_cont;
        $campos[0][5] = $this->cls_txtb;
        $campos[0][6] = $this->cls_desc;

        $secao[1] = 'Regras Gerais';
        $campos[1][0] = $this->cls_regg;

        $secao[2] = 'Regras do Cadastro';
        $campos[2][0] = $this->cls_regc;

        $secao[3] = 'Base de Dados';
        $campos[3][0] = $this->cls_tabela;
        $campos[3][1] = $this->cls_camp;
        $campos[3][2] = $this->cls_trel;

        $secao[4] = 'Listagem';
        $displ[4] = 'tabela';
        $dados_lista = $this->classeLista->getListagem($id);
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
        $campos[5][0] = $this->cls_meto;

        $secao[6] = 'Código Fonte';
        $campos[6][0] = $this->cls_codi;

        $this->data['desc_edicao'] = $dados_classe['cls_nome'];
        $this->data['displ'] = $displ;
        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';
        $this->data['script'] = "<script>acerta_botoes_rep('listagem')</script>";

        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('cfg_classe', $id);

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
        $this->classe->delete($id);
        session()->setFlashdata('msg', 'Registro Excluído com Sucesso');
        redirect()->to(site_url($this->data['controler']));
        return;
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
        $id = new Campos();
        $id->objeto = 'oculto';
        $id->nome = 'cls_id';
        $id->id = 'cls_id';
        $id->valor = array_key_exists('cls_id', $dados) ? $dados['cls_id'] : '';
        $this->cls_id = $id->create();

        $opc_mod = [];
        if (isset($dados['mod_id'])) {
            $opc_mods = $this->modulo->getModulo($dados['mod_id']);
            $opc_mod  = array_column($opc_mods, 'mod_nome', 'mod_id');
        }
        $modu =  new Campos();
        $modu->objeto       = 'selbusca';
        $modu->nome         = 'mod_id';
        $modu->id           = 'mod_id';
        $modu->label        = 'Módulo';
        $modu->place        = 'Módulo';
        $modu->obrigatorio  = true;
        $modu->hint         = 'Escolha o Módulo';
        $modu->valor        = array_key_exists('mod_id', $dados) ? $dados['mod_id'] : '';
        $modu->selecionado  = $modu->valor;
        $modu->busca        = base_url('buscas/busca_modulo');
        $modu->opcoes       = $opc_mod;
        $modu->tamanho      = 35;
        $modu->novo_cadastro = base_url('CfgModulo/add/modal=true');
        $this->cls_modulo = $modu->create();

        $nome = new Campos();
        $nome->objeto = 'input';
        $nome->tipo   = 'text';
        $nome->nome   = 'cls_nome';
        $nome->id     = 'cls_nome';
        $nome->label  = 'Nome';
        $nome->place  = 'Nome';
        $nome->obrigatorio = true;
        $nome->hint = 'Informe o Nome';
        $nome->size = 30;
        $nome->tamanho = 40;
        $nome->valor = array_key_exists('cls_nome', $dados) ? $dados['cls_nome'] : '';
        $this->cls_nome = $nome->create();

        $icon = new Campos();
        $icon->objeto = 'input';
        $icon->tipo = 'icone';
        $icon->nome = 'cls_icone';
        $icon->id = 'cls_icone';
        $icon->label = 'Ícone';
        $icon->place = 'Ícone';
        $icon->obrigatorio = true;
        $icon->hint = 'Informe o Ícone';
        $icon->size = 100;
        $icon->tamanho = 50;
        $icon->max_size = 50;
        $icon->valor = array_key_exists('cls_icone', $dados) ? $dados['cls_icone'] : '';
        $this->cls_icon = $icon->create();

        $cont = new Campos();
        $cont->objeto = 'input';
        $cont->tipo = 'text';
        $cont->nome = 'cls_controler';
        $cont->id = 'cls_controler';
        $cont->label = 'Controler';
        $cont->place = 'Controler';
        $cont->obrigatorio = true;
        $cont->hint = 'Informe o Controler';
        $cont->size = 20;
        $cont->tamanho = 25;
        $cont->valor = array_key_exists('cls_controler', $dados)
            ? $dados['cls_controler']
            : '';
        $this->cls_cont = $cont->create();

        $meto = new Campos();
        $meto->objeto = 'show';
        $meto->id = 'cls_metodos';
        $meto->label = 'Métodos';
        $meto->size = 40;
        $meto->tamanho = 'auto';
        $meto->valor = '';
        if (array_key_exists('cls_controler', $dados)) {
            $metoddos = get_class_methods($this);
            $meto->valor = metodos_classe($metoddos);
        }
        $this->cls_meto = $meto->create();

        $txtb = new Campos();
        $txtb->objeto = 'input';
        $txtb->tipo = 'text';
        $txtb->nome = 'cls_texto_botao';
        $txtb->id = 'cls_texto_botao';
        $txtb->label = 'Texto do Botão Add';
        $txtb->place = 'Texto do Botão Add';
        $txtb->obrigatorio = false;
        $txtb->hint = 'Informe o Texto do Botão Add';
        $txtb->size = 20;
        $txtb->tamanho = 25;
        $txtb->valor = array_key_exists('cls_texto_botao', $dados)
            ? $dados['cls_texto_botao']
            : '';
        $this->cls_txtb = $txtb->create();

        $opc_tab = [];
        if (array_key_exists('cls_tabela', $dados)) {
            $opc_tabs         = $this->dicionario->getTabelaSearch($dados['cls_tabela']);
            $opc_tab          = array_column($opc_tabs, 'table_name', 'table_name');
        }

        $tabe = new Campos();
        $tabe->objeto           = 'selbusca';
        $tabe->nome             = 'cls_tabela';
        $tabe->id               = 'cls_tabela';
        $tabe->label            = 'Tabela Principal';
        $tabe->place            = 'Escolha a Tabela Principal';
        $tabe->obrigatorio      = true;
        $tabe->hint             = 'Escolha a Tabela Principal';
        $tabe->valor            = array_key_exists('cls_tabela', $dados) ? $dados['cls_tabela'] : '';
        $tabe->selecionado      = $tabe->valor;
        $tabe->busca            = base_url('buscas/busca_tabela');
        $tabe->tamanho          = 35;
        $tabe->opcoes           = $opc_tab;
        $this->cls_tabela       = $tabe->create();

        $tabela = session()->get('tabela');
        // debug($tabela);
        if ($tabela != '') {
            $campos_tab = $this->dicionario->getCampos($tabela);
            $relac = $this->dicionario->getRelacionamentos($tabela);
        }
        // debug($campos_tab);
        $camp = new Campos();
        $camp->objeto       = 'show';
        $camp->id           = 'cls_campos';
        $camp->label        = 'Campos';
        $camp->size         = 'auto';
        $camp->tamanho      = 'auto';
        $camp->valor        = '';
        if ($tabela != '') {
            $camp->valor    = campos_tabela($campos_tab);
        }
        $this->cls_camp     = $camp->create();


        $trel = new Campos();
        $trel->objeto       = 'show';
        $trel->id           = 'cls_relac';
        $trel->label        = 'Tabelas Relacionadas';
        $trel->size         = 'auto';
        $trel->tamanho      = 'auto';
        $trel->valor        = '';
        if ($tabela != '') {
            $trel->valor    = relacion_tabela($relac);
        }
        $this->cls_trel     = $trel->create();

        if ($tabela != '' && array_key_exists('cls_lista', $dados)) {
            $campos_opc = [];
            for ($ct = 0; $ct < count($campos_tab); $ct++) {
                $camp = $campos_tab[$ct];
                if (
                    stristr($camp['COLUMN_NAME'], 'excluido') === false
                    && stristr($camp['COLUMN_NAME'], '_id') === false
                        && stristr(strtolower($camp['COLUMN_NAME']), 'id_') === false
                            && stristr($camp['COLUMN_COMMENT'], 'campo') === false
                ) {
                    if ($camp['DATA_TYPE'] != 'mediumtext' && $camp['DATA_TYPE'] != 'text') {
                            array_push($campos_opc, $camp);
                    }
                }
            }
            $campos_opc         = array_column($campos_opc, 'COLUMN_COMMENT', 'COLUMN_NAME');
            $listacamp          = ordenaSelecionados($campos_opc, $dados['cls_lista']);
            $selec              = explode(",", $dados['cls_lista']);
            $liscamp            = new Campos();
            $liscamp->objeto            = 'checkbutton';
            $liscamp->nome              = "cls_lista[]";
            $liscamp->id                = "cls_lista[]";
            $liscamp->label             = 'Campos na Listagem:';
            $liscamp->size              = 60;
            $liscamp->tamanho           = 70;
            $liscamp->obrigatorio       = true;
            $liscamp->classe            = 'btn btn-outline-primary ';
            $liscamp->infotop           = 'Escolha os Campos que aparecerão na Listagem';
            $liscamp->opcoes            = $listacamp;
            $liscamp->valor             = array_key_exists('cls_lista', $dados) ? $selec : '';
            $liscamp->selecionado        = $liscamp->valor;
            // debug($liscamp);
            $this->cls_lista           = $liscamp->create();
            $campos_opc = $campos_tab;
            $semf = [
                'COLUMN_NAME' => '',
                'COLUMN_COMMENT' => 'Sem Filtro',
            ];
            array_push($campos_opc, $semf);
            $campos_opc = array_column($campos_opc, 'COLUMN_COMMENT', 'COLUMN_NAME');
        } else {
            $this->cls_lista = '';
        }

        $desc = new Campos();
        $desc->objeto   = 'texto';
        $desc->nome     = 'cls_descricao';
        $desc->id       = 'cls_descricao';
        $desc->label    = 'Descrição';
        $desc->place    = 'Descrição';
        $desc->obrigatorio = false;
        $desc->hint     = 'Informe a Descrição';
        $desc->size     = 70;
        $desc->max_size = 3;
        $desc->tamanho  = 80;
        $desc->valor    = array_key_exists('cls_descricao', $dados)
            ? $dados['cls_descricao']
            : '';
        $this->cls_desc = $desc->create();

        $regg = new Campos();
        $regg->objeto   = 'texto';
        $regg->tipo     = 'textarea';
        $regg->nome     = 'cls_regras_gerais';
        $regg->id       = 'cls_regras_gerais';
        $regg->label    = 'Regras Gerais';
        $regg->place    = 'Regras Gerais';
        $regg->obrigatorio = false;
        $regg->hint     = 'Informe as Regras Gerais';
        $regg->classe   = 'editor';
        $regg->size     = 80;
        $regg->max_size = 5;
        $regg->tamanho  = 100;
        $regg->valor    = array_key_exists('cls_regras_gerais', $dados)
            ? $dados['cls_regras_gerais']
            : '';
        $this->cls_regg = $regg->create();

        $regc = new Campos();
        $regc->objeto = 'texto';
        $regc->tipo = 'textarea';
        $regc->nome = 'cls_regras_cadastro';
        $regc->id = 'cls_regras_cadastro';
        $regc->label = 'Regras do Cadastro';
        $regc->place = 'Regras do Cadastro';
        $regc->obrigatorio = false;
        $regc->hint = 'Informe as Regras  do Cadastro';
        $regc->classe   = 'editor';
        $regc->size     = 80;
        $regc->max_size = 5;
        $regc->tamanho  = 100;
        $regc->valor = array_key_exists('cls_regras_cadastro', $dados)
            ? $dados['cls_regras_cadastro']
            : '';
        $this->cls_regc = $regc->create();

        if ($dados) {
            if (substr($dados['cls_controler'], 0, 3) == 'Cfg') {
                $fonte = verCodigo('Controllers/Config/' . $dados['cls_controler']);
            } else {
                $fonte = verCodigo('Controllers/' . $dados['cls_controler']);
            }
            $codi = new Campos();
            $codi->objeto   = 'show';
            $codi->nome     = 'codigo';
            $codi->id       = 'codigo';
            $codi->label    = 'Código Fonte';
            $codi->size     = '100%';
            $codi->tamanho  = 'auto';
            $codi->valor    = '<pre>' . $fonte . '</pre>';
            $this->cls_codi = $codi->create();
        }
    }

    public function defCamposLista($lista = '', $pos = 0)
    {
        $tabela = session()->get('tabela');
        $campos_tab = $this->dicionario->getCampos($tabela);
        // debug($campos_tab, false);
        $campos_lis = array_column($campos_tab, 'NOME_COMPLETO', 'COLUMN_NAME');
        // debug($campos_lis, false);

        $id = new Campos();
        $id->tabela       = 'cfg_classe_lista';
        $id->campo        = 'lis_id';
        $id->objeto       = 'oculto';
        $id->nome           = "lis_id[$pos]";
        $id->id             = "lis_id[$pos]";
        $id->valor        = isset($lista['lis_id']) ? $lista['lis_id'] : '';
        $this->lis_id     = $id->create();

        $camp =  new Campos();
        $camp->tabela         = 'cfg_classe_lista';
        $camp->campo          = "lis_campo";
        $camp->objeto         = 'select';
        $camp->nome           = "lis_campo[$pos]";
        $camp->id             = "lis_campo[$pos]";
        $camp->obrigatorio    = true;
        $camp->opcoes         = $campos_lis;
        $camp->valor          = (isset($lista['lis_campo'])) ? $lista['lis_campo'] : '';
        $camp->selecionado    = $camp->valor;
        $camp->tipo_form      = 'inline';
        $camp->repete         = true;
        $this->lis_campo      = $camp->create();

        $rotu =  new Campos();
        $rotu->tabela         = 'cfg_classe_lista';
        $rotu->campo          = "lis_rotulo";
        $rotu->nome           = "lis_rotulo[$pos]";
        $rotu->id             = "lis_rotulo[$pos]";
        $rotu->obrigatorio    = true;
        $rotu->valor          = (isset($lista['lis_rotulo'])) ? $lista['lis_rotulo'] : '';
        $rotu->size           = 18;
        $rotu->tamanho        = 23;
        $rotu->repete         = true;
        $rotu->tipo_form      = 'inline';
        $this->lis_rotulo       = $rotu->create();

        $atrib['data-index'] = $pos;
        $add = new Campos();
        $add->objeto    = 'botao';
        $add->tipo      = 'button';
        $add->attrdata  = $atrib;
        $add->nome      = "bt_add[$pos]";
        $add->id        = "bt_add[$pos]";
        $add->i_cone     = "<i class='fas fa-plus'></i>";
        $add->place     = "Adicionar Campo";
        $add->classe    = "btn-outline-success btn-sm bt-repete listagem mt-4";
        $add->funcao_chan = "addCampo('" . base_url("CfgClasse/addCampoLista") . "','listagem',this)";
        $this->bt_add   = $add->create();

        $del = new Campos();
        $del->objeto    = 'botao';
        $del->tipo      = 'button';
        $del->attrdata  = $atrib;
        $del->nome      = "bt_del[$pos]";
        $del->id        = "bt_del[$pos]";
        $del->i_cone     = "<i class='fas fa-trash'></i>";
        $del->place     = "Excluir Campo";
        $del->classe    = "btn-outline-danger btn-sm bt-exclui listagem mt-4";
        $del->funcao_chan = "exclui_campo('listagem',this)";
        $this->bt_del   = $del->create();
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
        $cls_lista = '';
        if (isset($dados['cls_lista'])) {
            $cls_lista = implode(',', $dados['cls_lista']);
        }

        $dados_clas = [
            'cls_id'           => $dados['cls_id'],
            'mod_id'           => $dados['mod_id'],
            'cls_nome'         => $dados['cls_nome'],
            'cls_icone'        => $dados['cls_icone'],
            'cls_controler'    => $dados['cls_controler'],
            'cls_texto_botao'  => $dados['cls_texto_botao'],
            'cls_tabela'       => $dados['cls_tabela'],
            'cls_descricao'    => $dados['cls_descricao'],
            'cls_lista'        => $cls_lista,
            // 'cls_filtros'       => $cls_filtros,
            'cls_regras_gerais'       => $dados['cls_regras_gerais'],
            'cls_regras_cadastro'       => $dados['cls_regras_cadastro'],
        ];
        // debug($dados_clas);
        if ($this->classe->save($dados_clas)) {
            $cls_id = $this->classe->getInsertID();
            if ($dados['cls_id'] != '') {
                $cls_id = $dados['cls_id'];
            }
            if (isset($dados['lis_id'])) {
                $data_atu = date('Y-m-d H:i');
                $lis_exc = $this->classeLista->excluiCampoLista($cls_id);
                foreach ($dados['lis_id'] as $key => $value) {
                    $dados_lis = [
                        // 'lis_id'             => $dados['lis_id'][$lis],
                        'cls_id'             => $cls_id,
                        'lis_campo'          => $dados['lis_campo'][$key],
                        'lis_rotulo'         => $dados['lis_rotulo'][$key],
                        'lis_atualizado'     => $data_atu,
                    ];
                    $lis_id = $this->classeLista->save($dados_lis);
                    if (!$lis_id) {
                        $ret['erro'] = true;
                        $ret['msg'] = 'Não foi possível gravar os Campos da Listagem, Verifique!';
                        break;
                    }
                }
            }
            $ret['erro'] = false;
            $ret['msg'] = 'Classe gravada com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url'] = site_url($this->data['controler']);
        } else {
            $ret['erro'] = true;
            $ret['msg'] = 'Não foi possível gravar a Classe, Verifique!';
        }
        echo json_encode($ret);
    }
}
/* End of file CfgClasse.php */
