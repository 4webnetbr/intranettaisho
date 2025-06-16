<?php namespace App\Controllers\Rh;

use App\Controllers\BaseController;
use App\Libraries\Campos;
use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\Rechum\RechumSetorModel;

class RhSetor extends BaseController {	
    public $data = [];
    public $permissao = '';
    public $common;
    public $setor;
    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->common       = new CommonModel();
        $this->setor       = new RechumSetorModel(); 
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
        $this->data['colunas'] = montaColunasLista($this->data,'set_id');
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

            $dados_setors = $this->setor->getSetor();
            $setors = [             
                'data' => montaListaColunas($this->data,'set_id',$dados_setors,'set_nome'),
            ];
        // }
        echo json_encode($setors);
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
        $campos[0][0] = $this->set_id;
        // $campos[0][1] = $this->emp_id;
        $campos[0][1] = $this->set_nome;
        $campos[0][2] = $this->set_pctdistribui;

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
        $dados_setor = $this->setor->getSetor($id)[0];
        // debug($dados_setor);
        $this->def_campos($dados_setor, 0, $show);
        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->set_id;
        // $campos[0][1] = $this->emp_id;
        $campos[0][1] = $this->set_nome;
        $campos[0][2] = $this->set_pctdistribui;

        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';

        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('rh_setor', $id);

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
            $this->setor->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Setor Excluída com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir a Setor, Verifique!';
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
        $id = new MyCampo('rh_setor','set_id');
        $id->valor = isset($dados['set_id']) ? $dados['set_id'] : '';
        $this->set_id = $id->crOculto();

        $nome                       = new MyCampo('rh_setor','set_nome');
        $nome->obrigatorio          = true;
        $nome->valor                = isset($dados['set_nome'])? $dados['set_nome']: '';
        $nome->largura               = 50;
        $nome->tamanho               = 50;
        $nome->leitura               = $show;
        $this->set_nome             = $nome->crInput();

        $pdis                       = new MyCampo('rh_setor','set_pctdistribui');
        $pdis->obrigatorio          = true;
        $pdis->valor                = isset($dados['set_pctdistribui'])? $dados['set_pctdistribui']: '';
        $pdis->largura               = 50;
        $pdis->tamanho               = 50;
        $pdis->leitura               = $show;
        $this->set_pctdistribui     = $pdis->crInput();

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
            'set_id'           => $dados['set_id'],
            // 'emp_id'           => $dados['emp_id'],
            'set_nome'         => $dados['set_nome'],
            'set_pctdistribui'         => $dados['set_pctdistribui'],
        ];
        // debug($dados_fun,true);
        $salvar = $this->setor->save($dados_fun);
        if ($salvar) {
            $ret['erro'] = false;
            $ret['msg'] = 'Setor gravado com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url'] = site_url($this->data['controler']);
        } else {
            $ret['erro'] = true;
            $erros = $this->setor->errors();
            $ret['msg'] = 'Não foi possível gravar o Setor, Verifique!<br><br>';
            foreach ($erros as $erro) {
                $ret['msg'] .= $erro;
            }
        }
        echo json_encode($ret);
    }
}
