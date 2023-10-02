<?php 
namespace App\Controllers\Config;
use App\Controllers\BaseController;
use App\Libraries\Campos;
use App\Models\Config\ConfigModuloModel;
use DatabaseException;

Class CfgModulo extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $modulo;

	public function __construct(){
		$this->data         = session()->getFlashdata('dados_classe');
        $this->permissao    = $this->data['permissao'];
		$this->modulo 		= new ConfigModuloModel();
        if ($this->data['erromsg'] != '') {
            $this->__erro();
        }
	}

    function __erro(){
        echo view('vw_semacesso', $this->data);
    }

    /**
     * Tela de Abertura
     * index
     */
    public function index()
    {
        $this->data['colunas'] = montaColunasLista($this->data, 'mod_id,');
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
        $dados_modulo = $this->modulo->getModulo();
        $modulos = [
            'data' => montaListaColunas($this->data, 'mod_id', $dados_modulo, 'mod_nome'),
        ];

        echo json_encode($modulos);
    }

    public function add($modal = false){
        $this->def_campos();

        $secao[0] = 'Dados Gerais'; 
        $campos[0][0] = $this->mod_id; 
        $campos[0][1] = $this->mod_nome;
        // $campos[0][2] = $this->mod_orde;
        $campos[0][2] = $this->mod_icon;

		$this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
		$this->data['destino']    = 'store';

        $this->data['desc_metodo'] = 'Cadastro de ';
        if(!$modal){
            echo view('vw_edicao', $this->data);
        } else {
            // $this->data['destino']    = 'store/modal';
            echo view('vw_edicao_modal', $this->data);
        }
    }

    public function edit($id){
		$dados_modulo = $this->modulo->find($id);
		$this->def_campos($dados_modulo);

        $secao[0] = 'Dados Gerais'; 
        $campos[0][0] = $this->mod_id; 
        $campos[0][1] = $this->mod_nome;
        // $campos[0][2] = $this->mod_orde;
        $campos[0][2] = $this->mod_icon;

		$this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
		$this->data['destino']    = 'store';

        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('config_modulo', $id);

        echo view('vw_edicao', $this->data);
    }

    public function delete($id)
    {
        $ret = [];
        try {
            $this->modulo->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Módulo Excluído com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir o Módulo, Verifique!<br><br>';
            $ret['msg'] .= 'Este Módulo possui relacionamentos em outros cadastros!';
        }
        echo json_encode($ret);
    }

    public function def_campos($dados = false){
		$mid			= new Campos();
		$mid->objeto	= 'oculto';
		$mid->nome		= 'mod_id';
		$mid->id		= 'mod_id';
		$mid->valor		= (isset($dados['mod_id']))?$dados['mod_id']:'';
		$this->mod_id	= $mid->create();

		$nome =  new Campos();
		$nome->objeto  	= 'input';
        $nome->tipo    	= 'text';
        $nome->nome    	= 'mod_nome';
        $nome->id      	= 'mod_nome';
        $nome->label   	= 'Nome';
        $nome->place   	= 'Nome';
        $nome->obrigatorio = true;
        $nome->hint    	= 'Informe o Nome';
        $nome->size   	= 50;
		$nome->tamanho  = 50;
		$nome->valor	= (isset($dados['mod_nome']))?$dados['mod_nome']:'';
        $this->mod_nome = $nome->create();

		$icon =  new Campos(); 
		$icon->objeto  	= 'input';
        $icon->tipo    	= 'icone';
        $icon->nome    	= 'mod_icone';
        $icon->id      	= 'mod_icone';
        $icon->label   	= 'Ícone';
        $icon->place   	= 'Ícone';
        $icon->obrigatorio = false;
        $icon->hint    	= 'Informe o Ícone';
        $icon->size   	= 100;
		$icon->tamanho  = 50;
		$icon->valor	= (isset($dados['mod_icone']))?$dados['mod_icone']:'';
        $this->mod_icon = $icon->create();

		$orde =  new Campos(); 
		$orde->objeto  	= 'input';
        $orde->tipo    	= 'number';
        $orde->nome    	= 'mod_order';
        $orde->id      	= 'mod_order';
        $orde->label   	= 'Ordem';
        $orde->place   	= 'Ordem';
        $orde->obrigatorio = false;
        $orde->hint    	= 'Informe a Ordem';
        $orde->size   	= 10;
		$orde->tamanho  = 15;
        $orde->minimo   = 0;
		$orde->maximo   = 50;
        $orde->step     = 1;
        $orde->valor    = isset($dados['mod_order']) ? $dados['mod_order'] : '';
        $this->mod_orde = $orde->create();
    }

    public function store()
    {
        $ret = [];
        $postado = $this->request->getPost();
        $erros = [];
        if ($postado['mod_id'] == '') {
            if ($this->modulo->insert($postado)) {
                $ret['erro'] = false;
            } else {
                $erros = $this->modulo->errors();
                $ret['erro'] = true;
            }
        } else {
            if ($this->modulo->update($postado['mod_id'], $postado)) {
                $ret['erro'] = false;
            } else {
                $erros = $this->modulo->errors();
                $ret['erro'] = true;
            }
        }

        if ($ret['erro']) {
            $ret['msg']  = 'Não foi possível gravar o Módulo, Verifique!<br><br>';
            foreach ($erros as $erro) {
                $ret['msg'] .= $erro . '<br>';
            }
        } else {
            $ret['msg']  = 'Módulo gravado com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url']  = site_url($this->data['controler']);
        }
        echo json_encode($ret);
    }

}
