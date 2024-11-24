<?php namespace App\Controllers\Estoque;

use App\Controllers\BaseController;
use App\Libraries\Campos;
use App\Libraries\MyCampo;
use App\Models\Estoqu\EstoquGrupoProdutoModel;
use App\Models\Estoqu\EstoquProdutoModel;
use App\Models\Estoqu\EstoquUndMedidaModel;

class EstProduto extends BaseController {	
    public $data = [];
    public $permissao = '';
    public $Produto;
    public $grupoproduto;
    public $unidades;
    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->Produto     = new EstoquProdutoModel();
        $this->grupoproduto = new EstoquGrupoProdutoModel();
        $this->unidades     = new EstoquUndMedidaModel();
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
        $this->data['colunas'] = montaColunasLista($this->data,'pro_id');
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

            $dados_produto = $this->Produto->getProduto();
            $produtos = [             
                'data' => montaListaColunas($this->data,'pro_id',$dados_produto,'pro_nome'),
            ];
        // }
        echo json_encode($produtos);
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
        $campos[0][0] = $this->pro_id;
        $campos[0][1] = $this->gru_id;
        $campos[0][2] = $this->pro_nome;
        $campos[0][3] = $this->und_id;
        $campos[0][4] = $this->pro_minimo;
        $campos[0][5] = $this->und_compra;

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
        $dados_Produto = $this->Produto->getProduto($id)[0];
        $this->def_campos($dados_Produto, 0, $show);
        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->pro_id;
        $campos[0][1] = $this->gru_id;
        $campos[0][2] = $this->pro_nome;
        $campos[0][3] = $this->und_id;
        $campos[0][4] = $this->pro_minimo;
        $campos[0][5] = $this->und_compra;

        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';

        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('est_produto', $id);

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
            $this->Produto->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Produto Excluído com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir o Produto, Verifique!';
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
    public function def_campos($dados = false, $pos = 0, $show=false)
    {
        $id = new MyCampo('est_produto','pro_id');
        $id->valor = isset($dados['pro_id']) ? $dados['pro_id'] : '';
        $this->pro_id = $id->crOculto();

        $nome = new MyCampo('est_produto','pro_nome');
        $nome->obrigatorio = true;
        $nome->valor = isset($dados['pro_nome'])? $dados['pro_nome']: '';
        $nome->leitura       = $show;
        $this->pro_nome = $nome->crInput();

        $minimo = new MyCampo('est_produto','pro_minimo');
        $minimo->valor = isset($dados['pro_minimo'])? $dados['pro_minimo']: '';
        $minimo->largura               = 20;
        $minimo->leitura       = $show;
        $this->pro_minimo = $minimo->crInput();

        $dados_gru = $this->grupoproduto->getGrupoProduto();
        $grups = array_column($dados_gru, 'gru_nome', 'gru_id');

        $gru                        = new MyCampo('est_produto','gru_id');
        $gru->selecionado           = isset($dados['gru_id'])? $dados['gru_id']: '';
        $gru->opcoes                = $grups;
        $gru->largura               = 50;
        $gru->leitura       = $show;
        $this->gru_id         = $gru->crSelect();

        $dados_und = $this->unidades->getUndMedida();
        $unids = array_column($dados_und, 'und_completa', 'und_id');

        $und                        = new MyCampo('est_produto','und_id');
        $und->selecionado           = isset($dados['und_id'])? $dados['und_id']: '';
        $und->opcoes                = $unids;
        $und->largura               = 50;
        $und->leitura       = $show;
        $this->und_id         = $und->crSelect();

        $und                        = new MyCampo('est_produto','und_id_compra');
        $und->selecionado           = isset($dados['und_id_compra'])? $dados['und_id_compra']: '';
        $und->opcoes                = $unids;
        $und->largura               = 50;
        $und->leitura       = $show;
        $this->und_compra         = $und->crSelect();

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
            'pro_id'            => $dados['pro_id'],
            'gru_id'            => $dados['gru_id'],
            'pro_nome'          => $dados['pro_nome'],
            'und_id'            => $dados['und_id'],
            'pro_minimo'        => $dados['pro_minimo'],
            'und_id_compra'     => $dados['und_id_compra'],
        ];
        if ($this->Produto->save($dados_dep)) {
            $ret['erro'] = false;
            $ret['msg'] = 'Produto gravado com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url'] = site_url($this->data['controler']);
        } else {
            $ret['erro'] = true;
            $ret['msg'] = 'Não foi possível gravar o Produto, Verifique!';
        }
        echo json_encode($ret);
    }
}
