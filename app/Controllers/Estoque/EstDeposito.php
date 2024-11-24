<?php namespace App\Controllers\Estoque;

use App\Controllers\BaseController;
use App\Libraries\Campos;
use App\Libraries\MyCampo;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\Estoqu\EstoquDepositoModel;

class EstDeposito extends BaseController {	
    public $data = [];
    public $permissao = '';
    public $Deposito;
    public $conversao;
    public $empresa;
    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->Deposito     = new EstoquDepositoModel();
        $this->empresa      = new ConfigEmpresaModel();
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
        $this->data['colunas'] = montaColunasLista($this->data,'dep_id');
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

            $dados_deposito = $this->Deposito->getDeposito();
            $depositos = [             
                'data' => montaListaColunas($this->data,'dep_id',$dados_deposito,'dep_nome'),
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
        $campos[0][0] = $this->dep_id;
        $campos[0][1] = $this->emp_id;
        $campos[0][2] = $this->dep_nome;

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
        $dados_Deposito = $this->Deposito->getDeposito($id)[0];
        $this->def_campos($dados_Deposito);
        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->dep_id;
        $campos[0][1] = $this->emp_id;
        $campos[0][2] = $this->dep_nome;

        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';

        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('est_deposito', $id);

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
        $this->Deposito->delete($id);
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
        $id = new MyCampo('est_deposito','dep_id');
        $id->valor = isset($dados['dep_id']) ? $dados['dep_id'] : '';
        $this->dep_id = $id->crOculto();

        $nome = new MyCampo('est_deposito','dep_nome');
        $nome->obrigatorio = true;
        $nome->valor = isset($dados['dep_nome'])? $dados['dep_nome']: '';
        $this->dep_nome = $nome->crInput();

        $empresas = explode(',',session()->get('usu_empresa'));
        $dados_emp = $this->empresa->getEmpresa($empresas);
        $empres = array_column($dados_emp, 'emp_apelido', 'emp_id');

        $emp                        = new MyCampo('est_deposito','emp_id');
        $emp->selecionado           = $empresas[0];
        $emp->opcoes                = $empres;
        if(count($empresas) == 1){
            $emp->leitura           = true;
        }
        $this->emp_id         = $emp->crSelect();

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
            'dep_id'           => $dados['dep_id'],
            'emp_id'            => $dados['emp_id'],
            'dep_nome'         => $dados['dep_nome'],
        ];
        if ($this->Deposito->save($dados_dep)) {
            $ret['erro'] = false;
            $ret['msg'] = 'Depósito gravado com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url'] = site_url($this->data['controler']);
        } else {
            $ret['erro'] = true;
            $ret['msg'] = 'Não foi possível gravar o Depósito, Verifique!';
        }
        echo json_encode($ret);
    }
}
