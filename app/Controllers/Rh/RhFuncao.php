<?php namespace App\Controllers\Rh;

use App\Controllers\BaseController;
use App\Libraries\Campos;
use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\Rechum\RechumFuncaoModel;

class RhFuncao extends BaseController {	
    public $data = [];
    public $permissao = '';
    public $common;
    public $funcao;
    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->common       = new CommonModel();
        $this->funcao       = new RechumFuncaoModel(); 
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
        $this->data['colunas'] = montaColunasLista($this->data,'fun_id');
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

            $dados_funcaos = $this->funcao->getFuncao();
            $funcaos = [             
                'data' => montaListaColunas($this->data,'fun_id',$dados_funcaos,'fun_nome'),
            ];
        // }
        echo json_encode($funcaos);
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
        $campos[0][0] = $this->fun_id;
        $campos[0][1] = $this->fun_nome;

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
        $dados_funcao = $this->funcao->getFuncao($id)[0];
        // debug($dados_funcao);
        $this->def_campos($dados_funcao, 0, $show);
        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->fun_id;
        $campos[0][1] = $this->fun_nome;

        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';

        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('rh_funcao', $id);

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
            $this->funcao->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Funcao Excluída com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir a Funcao, Verifique!';
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
        $id = new MyCampo('rh_funcao','fun_id');
        $id->valor = isset($dados['fun_id']) ? $dados['fun_id'] : '';
        $this->fun_id = $id->crOculto();

        $nome                       = new MyCampo('rh_funcao','fun_nome');
        $nome->obrigatorio          = true;
        $nome->valor                = isset($dados['fun_nome'])? $dados['fun_nome']: '';
        $nome->largura               = 50;
        $nome->tamanho               = 50;
        $nome->leitura               = $show;
        $this->fun_nome             = $nome->crInput();

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
            'fun_id'           => $dados['fun_id'],
            'fun_nome'         => $dados['fun_nome'],
        ];
        // debug($dados_fun,true);
        $salvar = $this->funcao->save($dados_fun);
        if ($salvar) {
            $ret['erro'] = false;
            $ret['msg'] = 'Função gravada com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url'] = site_url($this->data['controler']);
        } else {
            $ret['erro'] = true;
            $erros = $this->funcao->errors();
            $ret['msg'] = 'Não foi possível gravar a Função, Verifique!<br><br>';
            foreach ($erros as $erro) {
                $ret['msg'] .= $erro;
            }
        }
        echo json_encode($ret);
    }
}
