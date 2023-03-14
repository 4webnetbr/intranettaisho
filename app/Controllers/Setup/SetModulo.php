<?php 
namespace App\Controllers\Setup;
use App\Controllers\BaseController;
use App\Libraries\Campos;
use App\Models\Setup\SetupModuloModel;

Class SetModulo extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $modulo;

	public function __construct(){
		$this->data         = session()->getFlashdata('dados_classe');
        $this->permissao    = $this->data['permissao'];
		$this->modulo 		= new SetupModuloModel();
        if($this->data['erromsg'] != ''){
            $this->__erro();
        }
	}

    function __erro(){
        echo view('vw_semacesso', $this->data);
    }

	public function index() 
	{
        $this->data['desc_metodo'] = 'Listagem de ';
        $this->data['colunas'] = array('ID','Módulo','Ordem','Ações');
        $this->data['url_lista']  = base_url($this->data['controler'].'/lista');

        echo view('vw_lista', $this->data);
	}

    public function lista(){
        $result = [];
		$dados_modulo = $this->modulo->getModulo();
        // debug($dados_modulo,false);
        for($p=0;$p<sizeof($dados_modulo);$p++){
            $modu = $dados_modulo[$p];
            $edit   = '';
            $exclui = '';
            if (strpbrk($this->permissao, 'E')) {
                $edit   = anchor($this->data['controler'].'/edit/'.$modu['mod_id'], '<i class="far fa-edit"></i>', ['class' =>'btn btn-outline-warning btn-sm mx-1','data-mdb-toggle'=>'tooltip','data-mdb-placement'=>'top','title'=>'Alterar este Registro' ]);
            }
            if (strpbrk($this->permissao, 'X')) {
                $url_del = $this->data['controler'].'/delete/'.$modu['mod_id'];
                $exclui = "<button class='btn btn-outline-danger btn-sm' data-mdb-toggle='tooltip' data-mdb-placement='top' title='Excluir este Registro' onclick='excluir(\"".$url_del."\",\"".$modu['mod_nome']."\")'><i class='far fa-trash-alt'></i></button>";
            }
            
            $dados_modulo[$p]['mod_nome'] = "<i class='".$modu['mod_icone']."'></i> ".$modu['mod_nome'];
            $dados_modulo[$p]['acao'] = $edit.' '.$exclui;
            $modu = $dados_modulo[$p];
            $result[] = [
                $modu['mod_id'],
                $modu['mod_nome'],
                $modu['mod_order'],
                $modu['acao']
            ];
        }
        $modus = [
            'data' => $result
        ];

        echo json_encode($modus); 
    }

    public function add(){
        $this->def_campos();

        $secao[0] = 'Dados Gerais'; 
        $campos[0][0][0] = $this->mod_id; 
        $campos[0][0][1] = $this->mod_nome;
        $campos[0][0][2] = $this->mod_orde;
        $campos[0][0][3] = $this->mod_icon;

		$this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
		$this->data['destino']    = 'store';

        $this->data['desc_metodo'] = 'Cadastro de ';
		echo view('vw_edicao', $this->data);
    }

    public function edit($id){
		$dados_modulo = $this->modulo->find($id);
		$this->def_campos($dados_modulo);

        $secao[0] = 'Dados Gerais'; 
        $campos[0][0][0] = $this->mod_id; 
        $campos[0][0][1] = $this->mod_nome;
        $campos[0][0][2] = $this->mod_orde;
        $campos[0][0][3] = $this->mod_icon;

		$this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
		$this->data['destino']    = 'store';

        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('setup_modulo', $id);

        echo view('vw_edicao', $this->data);
    }

    public function delete($id){
        $this->modulo->delete($id);
        session()->setFlashdata('msg', 'Registro Excluído com Sucesso' );
        return redirect()->to(site_url($this->data['controler'])); 
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
		$orde->valor	= (isset($dados['mod_order']))?$dados['mod_order']:'';
        $this->mod_orde = $orde->create();
    }

	public function store(){
        $ret = [];
		if($this->modulo->save($this->request->getPost())){
            $ret['erro'] = false;
            $ret['msg']  = 'Módulo gravado com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url']  = site_url($this->data['controler']);
		} else {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível gravar o Módulo, Verifique!';
        }
        echo json_encode($ret);
	}

}
