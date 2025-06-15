<?php namespace App\Controllers\Rh;

use App\Controllers\BaseController;
use App\Libraries\Campos;
use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\Rechum\RechumCargoModel;
use App\Models\Rechum\RechumSetorModel;

class RhCargo extends BaseController {	
    public $data = [];
    public $permissao = '';
    public $common;
    public $cargo;
    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->common       = new CommonModel();
        $this->cargo       = new RechumCargoModel(); 
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
        $this->data['colunas'] = montaColunasLista($this->data,'cag_id');
        $this->data['url_lista'] = base_url($this->data['controler'].'/lista');
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
        // $empresas = explode(',',session()->get('usu_empresa'));

            $dados_cargos = $this->cargo->getCargo();
            $cargos = [             
                'data' => montaListaColunas($this->data,'cag_id',$dados_cargos,'cag_nome'),
            ];
        // }
        echo json_encode($cargos);
    }
    /**
     * Inclusão
     * add
     *
     * @return void
     */
    public function add($modal = false)
    {
        $this->def_campos();

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->cag_id;
        $campos[0][1] = $this->set_id;
        $campos[0][2] = $this->cag_nome;
        $campos[0][3] = $this->cag_cbo;
        $campos[0][4] = $this->cag_participacao;
        $campos[0][5] = $this->cag_descricao;

        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';

        if(!$modal){
            echo view('vw_edicao', $this->data);
        } else {
            echo view('vw_edicao_modal', $this->data);
        }
    }

    /**
     * Consulta
     * show
     *
     * @param mixed $id 
     * @return void
     */
    public function show($id)
    {
        $this->edit($id, true);
    }
    /**
     * Edição
     * edit
     *
     * @param mixed $id 
     * @return void
     */
    public function edit($id, $show = false)
    {
        $dados_cargo = $this->cargo->getCargo($id)[0];
        // debug($dados_cargo);
        $this->def_campos($dados_cargo, 0, $show);
        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->cag_id;
        $campos[0][1] = $this->set_id;
        $campos[0][2] = $this->cag_nome;
        $campos[0][3] = $this->cag_cbo;
        $campos[0][4] = $this->cag_participacao;
        $campos[0][5] = $this->cag_descricao;

        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';

        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('rh_cargo', $id);

        echo view('vw_edicao', $this->data);
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
            $this->cargo->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Cargo Excluída com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir a Cargo, Verifique!';
        }
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
        $id = new MyCampo('rh_cargo','cag_id');
        $id->valor = isset($dados['cag_id']) ? $dados['cag_id'] : '';
        $this->cag_id = $id->crOculto();

        $nome                       = new MyCampo('rh_cargo','cag_nome');
        $nome->obrigatorio          = true;
        $nome->valor                = isset($dados['cag_nome'])? $dados['cag_nome']: '';
        $nome->largura               = 50;
        $nome->tamanho               = 50;
        $nome->leitura               = $show;
        $this->cag_nome             = $nome->crInput();

        $desc                       = new MyCampo('rh_cargo','cag_descricao');
        $desc->valor                = isset($dados['cag_descricao'])? $dados['cag_descricao']: '';
        $desc->leitura               = $show;
        $this->cag_descricao             = $desc->crTexto();
        
        $empresas           = explode(',',session()->get('usu_empresa'));
        $setores            = new RechumSetorModel();
        $dados_set          = $setores->getSetor();
        $opc_set            = array_column($dados_set, 'set_nome', 'set_id');

        $set                        = new MyCampo('rh_cargo','set_id');
        $set->valor = $set->selecionado = isset($dados['set_id'])? $dados['set_id']: '';
        $set->obrigatorio           = true;
        $set->opcoes                = $opc_set;
        $set->largura               = 30;
        $set->leitura               = $show;
        $this->set_id               = $set->crSelect();

        $cbo                       = new MyCampo('rh_cargo','cag_cbo');
        $cbo->obrigatorio          = true;
        $cbo->valor                = isset($dados['cag_cbo'])? $dados['cag_cbo']: '';
        $cbo->largura               = 20;
        $cbo->tamanho               = 20;
        $cbo->leitura               = $show;
        $this->cag_cbo             = $cbo->crInput();

        $part                       = new MyCampo('rh_cargo','cag_participacao');
        $part->obrigatorio          = true;
        $part->valor                = isset($dados['cag_participacao'])? $dados['cag_participacao']: '';
        $part->largura               = 50;
        $part->tamanho               = 50;
        $part->leitura               = $show;
        $this->cag_participacao     = $part->crInput();


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
            'set_id'           => $dados['set_id'],
            'cag_nome'         => $dados['cag_nome'],
            'cag_cbo'          => $dados['cag_cbo'],
            'cag_participacao' => $dados['cag_participacao'],
            'cag_descricao'    => $dados['cag_descricao'],
        ];
        // debug($dados_fun,true);
        $salvar = $this->cargo->save($dados_fun);
        if ($salvar) {
            $ret['erro'] = false;
            $ret['msg'] = 'Função gravada com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url'] = site_url($this->data['controler']);
        } else {
            $ret['erro'] = true;
            $erros = $this->cargo->errors();
            $ret['msg'] = 'Não foi possível gravar a Função, Verifique!<br><br>';
            foreach ($erros as $erro) {
                $ret['msg'] .= $erro;
            }
        }
        echo json_encode($ret);
    }
}
