<?php namespace App\Controllers\Estoque;

use App\Controllers\BaseController;
use App\Libraries\Campos;
use App\Libraries\MyCampo;
use App\Models\Estoqu\EstoquGrupoCompraModel;

class EstGrupoCompra extends BaseController {	
    public $data = [];
    public $permissao = '';
    public $GrupoCompra;
    public $conversao;
    public $grc_id;
    public $grc_nome;
    public $grc_validade;
    public $grc_prazo;
    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->GrupoCompra     = new EstoquGrupoCompraModel();
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
        $this->data['colunas'] = montaColunasLista($this->data,'grc_id');
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

            $dados_deposito = $this->GrupoCompra->getGrupoCompra();
            $depositos = [             
                'data' => montaListaColunas($this->data,'grc_id',$dados_deposito,'grc_nome'),
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
        $campos[0][0] = $this->grc_id;
        $campos[0][1] = $this->grc_nome;
        $campos[0][2] = $this->grc_validade;
        $campos[0][3] = $this->grc_prazo;

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
        $dados_GrupoCompra = $this->GrupoCompra->getGrupoCompra($id)[0];
        $this->def_campos($dados_GrupoCompra);
        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->grc_id;
        $campos[0][1] = $this->grc_nome;
        $campos[0][2] = $this->grc_validade;
        $campos[0][3] = $this->grc_prazo;

        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';

        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('est_grupocompra', $id);

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
        $this->GrupoCompra->delete($id);
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
        $id = new MyCampo('est_grupocompra','grc_id');
        $id->valor = isset($dados['grc_id']) ? $dados['grc_id'] : '';
        $this->grc_id = $id->crOculto();

        $nome = new MyCampo('est_grupocompra','grc_nome');
        $nome->obrigatorio = true;
        $nome->valor = isset($dados['grc_nome'])? $dados['grc_nome']: '';
        $this->grc_nome = $nome->crInput();

        $simnao['S'] = 'Sim';
        $simnao['N'] = 'Não';
        $vali = new MyCampo('est_grupocompra','grc_validade');
        $vali->obrigatorio  = true;
        $vali->valor = $vali->selecionado        = isset($dados['grc_validade'])? $dados['grc_validade']: 'S';
        $vali->opcoes       = $simnao;
        $this->grc_validade = $vali->crRadio();

        $praz = new MyCampo('est_grupocompra','grc_prazo');
        $praz->obrigatorio = true;
        $praz->valor = $praz->selecionado = isset($dados['grc_prazo'])? $dados['grc_prazo']: 'S';
        $praz->opcoes = $simnao;
        $this->grc_prazo = $praz->crRadio();

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
            'grc_id'           => $dados['grc_id'],
            'grc_nome'         => $dados['grc_nome'],
            'grc_validade'     => $dados['grc_validade'],
            'grc_prazo'        => $dados['grc_prazo'],
        ];
        if ($this->GrupoCompra->save($dados_dep)) {
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
