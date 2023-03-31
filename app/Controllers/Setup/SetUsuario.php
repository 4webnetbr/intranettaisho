<?php namespace App\Controllers\Setup;
use App\Controllers\BaseController;
use App\Libraries\Campos;
use App\Models\Setup\SetupPerfilModel;
use App\Models\Setup\SetupUsuarioModel;

class SetUsuario extends BaseController
{   
    public $data = []; 
    public $permissao = '';

	public function __construct(){
		$this->data = session()->getFlashdata('dados_classe');
        $this->permissao = $this->data['permissao'];
		$this->usuario 	    = new SetupUsuarioModel();
        $this->perfil       = new SetupPerfilModel();

        if($this->data['erromsg'] != ''){
            $this->__erro();
        }
	}

    function __erro(){
        echo view('vw_semacesso', $this->data);
    }
 

    public function index()
	{
        $this->data['colunas'] = array('ID','Nome','Login','E-mail','Perfil','Ações');
        $this->data['url_lista']  = base_url($this->data['controler'].'/lista');

        echo view('vw_lista', $this->data);
	}

    public function lista(){
        $result = [];
		$dados_usuario = $this->usuario->getUsuarioId();
        for($p=0;$p<sizeof($dados_usuario);$p++){
            $users = $dados_usuario[$p];
            $edit   = '';
            $editsenha   = '';
            $exclui = '';
            if(session()->usu_perfil_id <= $users['usu_perfil_id']){
                if (strpbrk($this->permissao, 'E')) {
                    $edit   = anchor($this->data['controler'].'/edit/'.$users['usu_id'], '<i class="far fa-edit"></i>', ['class' =>'btn btn-outline-warning btn-sm mx-1','data-mdb-toggle'=>'tooltip','data-mdb-placement'=>'top','title'=>'Alterar este Registro' ]);
                    $editsenha   = anchor($this->data['controler'].'/edit_senha/'.$users['usu_id'], '<i class="fas fa-key"></i>', ['class' =>'btn btn-outline-primary btn-sm mx-1','data-mdb-toggle'=>'tooltip','data-mdb-placement'=>'top','title'=>'Alterar Senha' ]);
                }
                if (strpbrk($this->permissao, 'X')) {
                    $url_del = $this->data['controler'].'/delete/'.$users['usu_id'];
                    $exclui = "<button class='btn btn-outline-danger btn-sm' data-mdb-toggle='tooltip' data-mdb-placement='top' title='Excluir este Registro' onclick='excluir(\"".$url_del."\",\"".$users['usu_nome']."\")'><i class='far fa-trash-alt'></i></button>";
                }
            }
            $img_name       = 'usu_'.$users['usu_id'].'.jpg';
            $sem_avat       = base_url('assets/images/sem_avatar.png');
            $path_ser       = FCPATH.'assets/uploads/usuario/';
            $img_path       = site_url('assets/uploads/usuario/');
            if(file_exists($path_ser.$img_name)){
                $avatar = $img_path.$img_name.'?nocache='.time();
            } else {
                $avatar = $sem_avat;
            }
            $dados_usuario[$p]['usu_nome'] = anchor($this->data['controler'].'/edit/'.$users['usu_id'],"<img src='$avatar' class='img-user rounded-circle me-3 float-start'> ".$users['usu_nome']);
            $dados_usuario[$p]['acao'] = $edit.' '.$editsenha.' '.$exclui;
            $users = $dados_usuario[$p];
            $result[] = [
                $users['usu_id'],
                $users['usu_nome'],
                $users['usu_login'],
                $users['usu_email'],
                $users['per_nome'],
                $users['acao']
            ];
        }
        $users_all = [
            'data' => $result
        ];

        echo json_encode($users_all); 
    }

    public function add(){
        $this->def_campos();

        $secao[0] = 'Dados Gerais'; 
        $campos[0][0] = $this->usu_id;
        $campos[0][1] = $this->usu_nome;
        $campos[0][2] = $this->usu_email;
        $campos[0][3] = $this->usu_login;
        $campos[0][4] = $this->usu_nova_senha;
        $campos[0][5] = $this->usu_perfil;

        $secao[1] = 'Avatar'; 
        $campos[1][0] = $this->usu_avatar;
        
		$this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
		$this->data['destino'] = 'store';

		echo view('vw_edicao', $this->data);
	}

    public function edit($id){
		// busca a usuario
		$dados_usuario = $this->usuario->find($id);
		$this->def_campos($dados_usuario);

        $secao[0] = 'Dados Gerais';  
        $campos[0][0] = $this->usu_id;
        $campos[0][1] = $this->usu_nome;
        $campos[0][2] = $this->usu_email;
        $campos[0][3] = $this->usu_login;
        $campos[0][4] = $this->usu_nova_senha;
        $campos[0][5] = $this->usu_perfil;

        $secao[1] = 'Avatar'; 
        $campos[1][0] = $this->usu_avatar;
        
		$this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
		$this->data['destino']    = 'store';

		echo view('vw_edicao', $this->data);
	}

    public function edit_senha(){
		// busca a usuario
        $anterior['anterior'] = $_SERVER["HTTP_REFERER"];
        session()->set($anterior);

        $id = session()->get('usu_id');
		$dados_usuario = $this->usuario->find($id);
		$this->def_campos($dados_usuario, true);

        $secao[0] = 'Dados Gerais';  
        $campos[0][0] = $this->usu_id;
        $campos[0][1] = $this->usu_nome;
        $campos[0][2] = $this->usu_email;
        $campos[0][3] = $this->usu_login;
        $campos[0][4] = '<b><i>Para manter a mesma senha, deixe-a em branco</i></b>';
        $campos[0][5] = $this->usu_nova_senha;
        $campos[0][6] = $this->usu_contra_senha;
        $campos[0][7] = "<span id='msg_senha' class='text-danger bg-warning'></span>";
        $campos[0][8] = '<b><i>Para alterar o Perfil, solicite ao Gestor do Sistema</i></b>';
        $campos[0][9] = $this->usu_perfil;


        $secao[1] = 'Avatar'; 
        $campos[1][0] = $this->usu_avatar;
        
		$this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
		$this->data['destino']    = 'store';
        $this->data['desc_metodo'] = 'Alteração de Senha de';

		echo view('vw_edicao', $this->data);
	}

    public function delete($id){
        $this->usuario->delete($id);
        session()->setFlashdata('msg', 'Registro Excluído com Sucesso!');
        return redirect()->to(site_url($this->data['controler'])); 
    }

    public function def_campos($dados = false, $leitura = false){
		$id				= new Campos();
		$id->objeto		= 'oculto';
		$id->nome		= 'usu_id';
		$id->valor		= (isset($dados['usu_id']))?$dados['usu_id']:'';
		$this->usu_id	= $id->create();

		$nome =  new Campos();
		$nome->objeto  	= 'input';
        $nome->tipo    	= 'text';
        $nome->nome    	= 'usu_nome';
        $nome->id      	= 'usu_nome';
        $nome->label   	= 'Nome';
        $nome->place   	= 'Nome';
        $nome->obrigatorio = true;
        $nome->hint    	= 'Informe o Nome do Usuário';
        $nome->leitura  = false;
        $nome->size   	= 30;
		$nome->tamanho  = 30;
		$nome->valor	= (isset($dados['usu_nome']))?$dados['usu_nome']:'';
        $this->usu_nome = $nome->create();


		$email =  new Campos();
		$email->objeto  	= 'input';
        $email->tipo    	= 'email';
        $email->nome    	= 'usu_email';
        $email->id      	= 'usu_email';
        $email->label   	= 'E-mail';
        $email->place   	= 'E-mail';
        $email->obrigatorio = false;
        $email->leitura     = false;
        $email->hint    	= 'Informe o E-mail';
        $email->size   		= 100;
		$email->tamanho   	= 75;
		$email->valor	    = (isset($dados['usu_email']))?$dados['usu_email']:'';
        $this->usu_email = $email->create();

		$login =  new Campos();
		$login->objeto  	    = 'input';
        $login->tipo    	    = 'text';
        $login->nome    	    = 'usu_login';
        $login->id      	    = 'usu_login';
        $login->label   	    = 'Login';
        $login->place   	    = 'Login';
        $login->leitura         = false;
        $login->obrigatorio     = true;
        $login->hint    	    = 'Informe o Login';
        $login->classe       = 'text-lowercase';
        $login->size   	    = 35;
		$login->tamanho      = 40;
		$login->valor	    = (isset($dados['usu_login']))?$dados['usu_login']:'';
        $this->usu_login    = $login->create();

        $perfis             = array_column($this->perfil->getPerfil(),'per_nome','per_id');
		$perfil             =  new Campos();
		$perfil->objeto  	= 'select';
        $perfil->nome    	= 'usu_perfil_id';
        $perfil->id      	= 'usu_perfil_id';
        $perfil->label   	= 'Perfil de Acesso';
        $perfil->leitura  = $leitura;
        $perfil->obrigatorio = true;
        $perfil->hint    	= 'Escolha o Perfil de Acesso';
        $perfil->size   	= 50;
		$perfil->tamanho   	= 55;
        $perfil->opcoes     = $perfis;
        $perfil->selecionado = (isset($dados['usu_perfil_id']))?$dados['usu_perfil_id']:''; 
		$perfil->valor	    = (isset($dados['usu_perfil_id']))?$dados['usu_perfil_id']:'';
        $this->usu_perfil   = $perfil->create();

        $nova_senha =  new Campos();
		$nova_senha->objeto  	= 'input';
        $nova_senha->tipo    	= 'password';
        $nova_senha->nome    	= 'usu_senha';
        $nova_senha->id      	= 'usu_senha';
        $nova_senha->label   	= 'Senha';
        $nova_senha->place   	= 'Senha';
        $nova_senha->obrigatorio = false;
        $nova_senha->hint    	= 'Informe a Senha';
        $nova_senha->size       = 20;
        $nova_senha->max_size   	= 12;
		$nova_senha->tamanho   	= 25;
		$nova_senha->valor	    = '';
        $this->usu_nova_senha = $nova_senha->create();

        $contra_senha =  new Campos();
		$contra_senha->objeto  	= 'input';
        $contra_senha->tipo    	= 'password';
        $contra_senha->nome    	= 'contra_senha';
        $contra_senha->id      	= 'contra_senha';
        $contra_senha->label   	= 'Confirme a Senha';
        $contra_senha->place   	= 'Confirme a Senha';
        $contra_senha->obrigatorio = false;
        $contra_senha->size   		= 20;
        $contra_senha->max_size   	= 12;
		$contra_senha->tamanho   	= 25;
		$contra_senha->valor	    = '';
        $contra_senha->funcao_chan	    = "compara_senha('contra_senha','usu_senha')";
        $contra_senha->funcao_blur	    = "compara_senha('contra_senha','usu_senha')";
        $this->usu_contra_senha = $contra_senha->create();

        $tipo_us[1] = 'Setup';
        $tipo_us[2] = 'Sistema';
        $tipo_us[3] = 'Ambos';

        $tipo_u =  new Campos();
        $tipo_u->objeto         = 'radio';
        $tipo_u->nome           = "usu_tipo";
        $tipo_u->id             = "usu_tipo";
        $tipo_u->label          = 'Tipo de Acesso';
        $tipo_u->obrigatorio    = false;
        $tipo_u->valor          = $tipo_us;
        $tipo_u->selecionado    = (isset($dados['usu_tipo']))?$dados['usu_tipo']:'0'; 
        $tipo_u->size           = 20;
        $tipo_u->tamanho        = 1;
        $this->usu_tipo_u       = $tipo_u->create();

        $avatar				    = new Campos();
		$avatar->objeto		    = 'imagem';
		$avatar->nome		    = 'usu_avatar';
		$avatar->id		        = 'usu_avatar';
        $avatar->label   	    = 'Avatar';
        $avatar->place   	    = 'Avatar';
        $avatar->obrigatorio  = false;
        $avatar->hint    	    = 'Informe o Avatar';
        $avatar->leitura   	    = false;
        $avatar->size   	    = 200;
		$avatar->tamanho        = 200;
        $avatar->accept         = '.png, .jpg, .jpeg';
        $avatar->pasta          = 'usuario';
        $avatar->img_name       = '';
        $avat                   = '';
        if (isset($dados['usu_id'])) {
            $img_name       = 'usu_'.$dados['usu_id'].'.jpg';
            $sem_avat       = base_url('assets/images/sem_avatar.png');
            $path_ser       = FCPATH.'assets/uploads/usuario/';
            $img_path       = site_url('assets/uploads/usuario/');
            if(file_exists($path_ser.$img_name)){
                $avatar->img_name = $img_path.$img_name.'?nocache='.time();
            } else {
                $avatar->img_name = $sem_avat;
            }
        } else {
            $avatar->img_name     = base_url('assets/images/sem_avatar.png');
        }
        $avat                   = $avatar->img_name.'?noc='.time();
        $avatar->funcao_chan    = "readURL(this, '#img_$avatar->id', $avatar->size, $avatar->tamanho)";
        $avatar->valor		    = $avat;
		$this->usu_avatar	    = $avatar->create();
    }

	public function store(){
        $dados = $this->request->getPost();
        if (isset($dados['usu_senha'])) {
            if($dados['usu_senha'] == ''){
                unset($dados['usu_senha']);
            } else {
                $dados['usu_senha'] = md5($dados['usu_senha']);
            }
        }
		if($this->usuario->save($dados)){
            if($dados['usu_id'] != ''){
                $usu_id = $dados['usu_id'];
            } else {
                $usu_id = $this->usuario->getInsertID();
            }
            $avatar = $this->request->getFile('usu_avatar');
            if ($avatar->getFilename() != '') {
                $path_avat = 'assets/uploads/usuario/usu_'.$usu_id.'.jpg';
                @unlink($path_avat);

                $avatar->move('assets/uploads/usuario', 'usu_'.$usu_id.'.jpg');
            }
            $ret['erro'] = false;
            $ret['msg']  = 'Usuario gravado com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url']  = session()->get('anterior');
            if($ret['url'] == ''){
                $ret['url']  = site_url($this->data['controler']);
            }
        } else {
            $error = $this->usuario->getErrors();
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível gravar o Usuario, Verifique!'.$error;
            session()->setFlashdata('msg', $ret['msg']);
        }
        echo json_encode($ret);
	}

}