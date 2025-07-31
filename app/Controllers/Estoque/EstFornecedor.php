<?php

namespace App\Controllers\Estoque;

use App\Controllers\BaseController;
use App\Libraries\Campos;
use App\Libraries\MyCampo;
use App\Models\Estoqu\EstoquFornecedorModel;
use App\Models\Estoqu\EstoquGrupoCompraModel;

class EstFornecedor extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $fornecedor;
    public $grupo;
    public $for_id;
    public $for_pessoa;
    public $for_cpf;
    public $for_cnpj;
    public $for_razao;
    public $for_fantasia;
    public $for_contato;
    public $for_minimo;
    public $for_fone;
    public $for_cep;
    public $for_rua;
    public $for_complemento;
    public $for_numero;
    public $for_bairro;
    public $for_cidade;
    public $for_estado;
    public $for_grupo;
    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->fornecedor = new EstoquFornecedorModel();
        $this->grupo      = new EstoquGrupoCompraModel();

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
        $this->data['colunas'] = montaColunasLista($this->data, 'for_id');
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
        // if (!$fornec = cache('fornec')) {
        $campos = montaColunasCampos($this->data, 'for_id');
        $dados_fornec = $this->fornecedor->getFornecedor();
        $fornec = [
            'data' => montaListaColunas($this->data, 'for_id', $dados_fornec, $campos[1]),
        ];
        cache()->save('fornec', $fornec, 60000);
        // }

        echo json_encode($fornec);
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
        $campos[0] = [];
        $campos[0][count($campos[0])] = $this->for_id;
        $campos[0][count($campos[0])] = $this->for_pessoa;
        $campos[0][count($campos[0])] = $this->for_cpf;
        $campos[0][count($campos[0])] = $this->for_cnpj;
        $campos[0][count($campos[0])] = $this->for_razao;
        $campos[0][count($campos[0])] = $this->for_fantasia;
        $campos[0][count($campos[0])] = $this->for_contato;
        $campos[0][count($campos[0])] = $this->for_fone;
        $campos[0][count($campos[0])] = $this->for_cep;
        $campos[0][count($campos[0])] = $this->for_rua;
        $campos[0][count($campos[0])] = $this->for_complemento;
        $campos[0][count($campos[0])] = $this->for_numero;
        $campos[0][count($campos[0])] = $this->for_bairro;
        $campos[0][count($campos[0])] = $this->for_cidade;
        $campos[0][count($campos[0])] = $this->for_estado;
        $campos[0][count($campos[0])] = $this->for_grupo;
        $campos[0][count($campos[0])] = $this->for_minimo;


        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';
        $this->data['script'] = "<script>muda_pessoa('for_pessoa'); </script>";

        if (!$modal) {
            echo view('vw_edicao', $this->data);
        } else {
            echo view('vw_edicao_modal', $this->data);
        }
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
        $dados_for = $this->fornecedor->getFornecedor($id)[0];
        $this->def_campos($dados_for);

        $secao[0] = 'Dados Gerais';
        $campos[0] = [];
        $campos[0][count($campos[0])] = $this->for_id;
        $campos[0][count($campos[0])] = $this->for_pessoa;
        $campos[0][count($campos[0])] = $this->for_cpf;
        $campos[0][count($campos[0])] = $this->for_cnpj;
        $campos[0][count($campos[0])] = $this->for_razao;
        $campos[0][count($campos[0])] = $this->for_fantasia;
        $campos[0][count($campos[0])] = $this->for_contato;
        $campos[0][count($campos[0])] = $this->for_fone;
        $campos[0][count($campos[0])] = $this->for_cep;
        $campos[0][count($campos[0])] = $this->for_rua;
        $campos[0][count($campos[0])] = $this->for_complemento;
        $campos[0][count($campos[0])] = $this->for_numero;
        $campos[0][count($campos[0])] = $this->for_bairro;
        $campos[0][count($campos[0])] = $this->for_cidade;
        $campos[0][count($campos[0])] = $this->for_estado;
        $campos[0][count($campos[0])] = $this->for_grupo;
        $campos[0][count($campos[0])] = $this->for_minimo;


        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';
        $this->data['script'] = "<script>muda_pessoa('for_pessoa'); </script>";

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
        // TODO implementar

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
        $id = new MyCampo('est_fornecedor', 'for_id');
        $id->valor        = isset($dados['for_id']) ? $dados['for_id'] : '';
        $this->for_id     = $id->crOculto();

        $tipos = [
            'F' => 'Física',
            'J' => 'Jurídica'
        ];

        $tipo =  new MyCampo('est_fornecedor', 'for_pessoa');
        $tipo->tabela         = 'est_fornecedor';
        $tipo->objeto         = 'radio';
        $tipo->obrigatorio    = true;
        $tipo->opcoes         = $tipos;
        $tipo->valor          = (isset($dados['for_pessoa'])) ? $dados['for_pessoa'] : 'J';
        $tipo->selecionado    = $tipo->valor;
        $tipo->funcChan       = 'muda_pessoa(this.id)';
        $tipo->dispForm       = '2col';
        $this->for_pessoa         = $tipo->crRadio();

        $cpf =  new MyCampo('est_fornecedor', 'for_cnpj');
        $cpf->tipo           = 'cpf';
        $cpf->id = $cpf->nome = 'for_cpf';
        $cpf->label          = 'CPF';
        $cpf->place          = 'CPF';
        $cpf->obrigatorio    = false;
        $cpf->largura        = 20;
        $cpf->valor          = (isset($dados['for_cnpj'])) ? $dados['for_cnpj'] : '';
        $cpf->dispForm      = '2col';
        $cpf->funcBlur    = 'pesquisaCPF(this.value, 2)';
        $this->for_cpf         = $cpf->crInput();

        $cnpj =  new MyCampo('est_fornecedor', 'for_cnpj');
        $cnpj->id = $cnpj->nome = 'for_cnpj';
        $cnpj->tipo           = 'cnpj';
        $cnpj->label          = 'CNPJ';
        $cnpj->place          = 'CNPJ';
        $cnpj->obrigatorio    = false;
        $cnpj->size           = 18;
        $cnpj->maximo         = 18;
        $cnpj->largura        = 24;
        $cnpj->funcBlur    = 'pesquisaCNPJ(this.value, 2)';
        $cnpj->valor          = (isset($dados['for_cnpj'])) ? $dados['for_cnpj'] : '';
        $cnpj->dispForm   = '2col';
        $this->for_cnpj         = $cnpj->crInput();

        $nome = new MyCampo('est_fornecedor', 'for_razao');
        $nome->obrigatorio = true;
        $nome->dispForm   = '2col';
        $nome->valor       = isset($dados['for_razao']) ? $dados['for_razao'] : '';
        $this->for_razao    = $nome->crInput();

        $apel = new MyCampo('est_fornecedor', 'for_fantasia');
        $apel->dispForm   = '2col';
        $apel->valor       = isset($dados['for_fantasia']) ? $dados['for_fantasia'] : '';
        $this->for_fantasia    = $apel->crInput();


        $ac = new MyCampo('est_fornecedor', 'for_contato');
        $ac->obrigatorio = false;
        $ac->dispForm   = '2col';
        $ac->valor       = isset($dados['for_contato']) ? $dados['for_contato'] : '';
        $this->for_contato    = $ac->crInput();

        $min = new MyCampo('est_fornecedor', 'for_minimo');
        $min->obrigatorio = false;
        $min->dispForm   = '2col';
        $min->minimo    = 0;
        $min->maximo    = 100000;
        $min->classep  = 'float-start';
        $min->valor       = isset($dados['for_minimo']) ? $dados['for_minimo'] : '0.00';
        $this->for_minimo    = $min->crInput();

        $ecep = new MyCampo('est_fornecedor', 'for_cep');
        $ecep->tipo         = 'cep';
        $ecep->obrigatorio = true;
        $ecep->funcBlur = 'pesquisacep(this, this.value)';
        $ecep->dispForm   = '2col';
        $ecep->valor       = isset($dados['for_cep']) ? $dados['for_cep'] : '';
        $ecep->largura        = 11;
        $this->for_cep    = $ecep->crInput();

        $esta =  new MyCampo('est_fornecedor', 'for_estado');
        $esta->obrigatorio    = true;
        $esta->valor          = (isset($dados['for_estado'])) ? $dados['for_estado'] : '';
        $esta->selecionado    = (isset($dados['for_estado'])) ? $dados['for_estado'] : '';
        $esta->dispForm      = '2col';
        $esta->largura        = 10;
        $this->for_estado     = $esta->crInput();

        $cida =  new MyCampo('est_fornecedor', 'for_cidade');
        $cida->obrigatorio  = true;
        $cida->valor        = (isset($dados['for_cidade'])) ? $dados['for_cidade'] : '';
        $cida->selecionado  = (isset($dados['for_cidade'])) ? $dados['for_cidade'] : '';
        $cida->largura      = 50;
        $cida->dispForm     = '2col';
        $this->for_cidade = $cida->crInput();

        $erua = new MyCampo('est_fornecedor', 'for_rua');
        $erua->valor       = isset($dados['for_rua']) ? $dados['for_rua'] : '';
        $erua->largura        = 50;
        $erua->dispForm      = '2col';
        $this->for_rua    = $erua->crInput();

        $enum = new MyCampo('est_fornecedor', 'for_numero');
        $enum->valor       = isset($dados['for_numero']) ? $dados['for_numero'] : '';
        $enum->largura        = 10;
        $enum->dispForm      = '2col';
        $this->for_numero    = $enum->crInput();

        $ebai = new MyCampo('est_fornecedor', 'for_bairro');
        $ebai->dispForm   = '2col';
        $ebai->largura     = 50;
        $ebai->valor       = isset($dados['for_bairro']) ? $dados['for_bairro'] : '';
        $this->for_bairro    = $ebai->crInput();

        $ecom = new MyCampo('est_fornecedor', 'for_complemento');
        $ecom->dispForm   = '2col';
        $ecom->valor       = isset($dados['for_complemento']) ? $dados['for_complemento'] : '';
        $ecom->largura        = 50;
        $this->for_complemento    = $ecom->crInput();

        $cfon = new MyCampo('est_fornecedor', 'for_fone');
        $cfon->tipo         = 'whatsapp';
        $cfon->obrigatorio  = false;
        $cfon->valor        = isset($dados['for_fone']) ? $dados['for_fone'] : '';
        $cfon->largura        = 30;
        $cfon->dispForm      = '2col';
        $this->for_fone    = $cfon->crInput();

        $grupos           = array_column($this->grupo->getGrupocompra(), 'grc_nome', 'grc_id');
        $grup             =  new MyCampo('est_fornecedor', 'for_grupo');
        $grup->obrigatorio = true;
        $grup->classep    = 'selectpicker';
        $grup->opcoes     = $grupos;
        $grup->selecionado = (isset($dados['for_grupo'])) ? explode(",", $dados['for_grupo']) : [];
        $grup->dispForm = '1col';
        $this->for_grupo   = $grup->crMultiple();
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
        if ($dados['for_pessoa'] == 'F') {
            $cnpj = $dados['for_cpf'];
        } else {
            $cnpj = $dados['for_cnpj'];
        }
        $grupos = implode(',', $dados['for_grupo']);
        $dados_dep = [
            'for_id'        => $dados['for_id'],
            'for_razao'     => $dados['for_razao'],
            'for_fantasia'  => $dados['for_fantasia'],
            'for_cnpj'      => $cnpj,
            'for_pessoa'    => $dados['for_pessoa'],
            'for_cep'       => $dados['for_cep'],
            'for_rua'       => $dados['for_rua'],
            'for_bairro'    => $dados['for_bairro'],
            'for_numero'    => $dados['for_numero'],
            'for_complemento'   => $dados['for_complemento'],
            'for_cidade'    => $dados['for_cidade'],
            'for_estado'    => $dados['for_estado'],
            'for_fone'      => $dados['for_fone'],
            'for_contato'   => $dados['for_contato'],
            'for_minimo'   => $dados['for_minimo'],
            'for_grupo'     => $grupos,
        ];
        if ($this->fornecedor->save($dados_dep)) {
            $ret['erro'] = false;
            $ret['msg'] = 'Fornecedor gravado com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url'] = site_url($this->data['controler']);
        } else {
            $ret['erro'] = true;
            $ret['msg'] = 'Não foi possível gravar o Fornecedor, Verifique!';
        }
        cache()->clean();
        echo json_encode($ret);
    }
}
