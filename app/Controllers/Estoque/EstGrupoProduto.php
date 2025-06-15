<?php namespace App\Controllers\Estoque;

use App\Controllers\BaseController;
use App\Libraries\Campos;
use App\Libraries\MyCampo;
use App\Models\Estoqu\EstoquGrupoProdutoModel;

class EstGrupoProduto extends BaseController {	
    public $data = [];
    public $permissao = '';
    public $GrupoProduto;
    public $conversao;
    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->GrupoProduto     = new EstoquGrupoProdutoModel();
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
        $this->data['colunas'] = montaColunasLista($this->data,'gru_id');
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
        $empresas = explode(',',session()->get('usu_empresa'));

            $dados_deposito = $this->GrupoProduto->getGrupoProduto();
            $depositos = [             
                'data' => montaListaColunas($this->data,'gru_id',$dados_deposito,'gru_nome'),
            ];
            cache()->save('depositos', $depositos, 60000);
        // }
        echo json_encode($depositos);
    }
    /**
     * Inclusão
     * add
     *
     * @return void
     */
    public function add()
    {
        $this->def_campos();

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->gru_id;
        $campos[0][1] = $this->gru_nome;
        $campos[0][2] = $this->gru_controlaestoque;

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
        $dados_GrupoProduto = $this->GrupoProduto->getGrupoProduto($id)[0];
        $this->def_campos($dados_GrupoProduto);
        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->gru_id;
        $campos[0][1] = $this->gru_nome;
        $campos[0][2] = $this->gru_controlaestoque;

        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';

        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('est_grupoproduto', $id);

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
        $this->GrupoProduto->delete($id);
        session()->setFlashdata('msg', 'Registro Excluído com Sucesso');
        return redirect()->to(site_url($this->data['controler']));
    }

    /**
     * Definição de Campos
     * def_campos
     *
     * @param bool $dados 
     * @return void
     */
    public function def_campos($dados = false, $pos = 0)
    {
        $id = new MyCampo('est_grupoproduto','gru_id');
        $id->valor = isset($dados['gru_id']) ? $dados['gru_id'] : '';
        $this->gru_id = $id->crOculto();

        $nome = new MyCampo('est_grupoproduto','gru_nome');
        $nome->obrigatorio = true;
        $nome->valor = isset($dados['gru_nome'])? $dados['gru_nome']: '';
        $this->gru_nome = $nome->crInput();

        $simnao['S'] = 'Sim';
        $simnao['N'] = 'Não';
        $cont = new MyCampo('est_grupoproduto','gru_controlaestoque');
        $cont->obrigatorio = true;
        $cont->valor = $cont->selecionado = isset($dados['gru_controlaestoque'])? $dados['gru_controlaestoque']: 'S';
        $cont->opcoes = $simnao;
        $this->gru_controlaestoque = $cont->cr2opcoes();

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
        $dados_dep = [
            'gru_id'           => $dados['gru_id'],
            'gru_nome'         => $dados['gru_nome'],
            'gru_controlaestoque'         => $dados['gru_controlaestoque'],
        ];
        if ($this->GrupoProduto->save($dados_dep)) {
            $ret['erro'] = false;
            $ret['msg'] = 'Grupo de Produto gravado com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url'] = site_url($this->data['controler']);
        } else {
            $ret['erro'] = true;
            $ret['msg'] = 'Não foi possível gravar o Grupo de Produto, Verifique!';
        }
        echo json_encode($ret);
    }
}
