<?php

namespace App\Controllers\Config;

use App\Controllers\BaseController;
use App\Libraries\Campos;
use App\Libraries\MyCampo;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\Config\ConfigPerfilModel;
use App\Models\Config\ConfigTelaModel;
use App\Models\Config\ConfigUsuarioModel;
use App\Models\Estoqu\EstoquDepositoModel;

class CfgUsuario extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $usuario;
    public $perfil;
    public $tela;
    public $empresa;
    public $deposito;
    public $usu_id;
    public $usu_nome;
    public $usu_email;
    public $usu_login;
    public $usu_nova_senha;
    public $usu_perfil;
    public $usu_dashboard;
    public $usu_empresa;
    public $usu_deposito;
    public $usu_contra_senha;
    
    public function __construct()
    {
        $this->data = session()->getFlashdata('dados_tela');
        $this->permissao    = $this->data['permissao'];
        $this->usuario      = new ConfigUsuarioModel();
        $this->perfil       = new ConfigPerfilModel();
        $this->tela       = new ConfigTelaModel();
        $this->empresa       = new ConfigEmpresaModel();
        $this->deposito       = new EstoquDepositoModel();

        if ($this->data['erromsg'] != '') {
            $this->__erro();
        }
    }

    public function __erro()
    {
        echo view('vw_semacesso', $this->data);
    }

    /**
     * Tela de Abertura
     * index
     */
    public function index()
    {
        $this->data['colunas'] = montaColunasLista($this->data, 'usu_id,');
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
        if (!$usuarios = cache('usuarios')) {
            $dados_usuario = $this->usuario->getUsuarioId();
            $usuarios = [
                'data' => montaListaColunas($this->data, 'usu_id', $dados_usuario, 'usu_nome'),
            ];
            cache()->save('usuarios', $usuarios, 30);
        }

        echo json_encode($usuarios);
    }

    public function add()
    {
        $this->def_campos();

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->usu_id;
        $campos[0][1] = $this->usu_nome;
        $campos[0][2] = $this->usu_email;
        $campos[0][3] = $this->usu_login;
        $campos[0][4] = $this->usu_nova_senha;
        $campos[0][5] = $this->usu_perfil;
        $campos[0][6] = $this->usu_dashboard;
        $campos[0][7] = $this->usu_empresa;
        $campos[0][8] = $this->usu_deposito;

        // $secao[1] = 'Avatar';
        // $campos[1][0] = $this->usu_avatar;

        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';

        echo view('vw_edicao', $this->data);
    }

    public function edit($id)
    {
        // busca a usuario
        $dados_usuario = $this->usuario->getUsuarioId($id)[0];
        // debug($dados_usuario);
        $this->def_campos($dados_usuario);

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->usu_id;
        $campos[0][1] = $this->usu_nome;
        $campos[0][2] = $this->usu_email;
        $campos[0][3] = $this->usu_login;
        $campos[0][4] = $this->usu_nova_senha;
        $campos[0][5] = $this->usu_perfil;
        $campos[0][6] = $this->usu_dashboard;
        $campos[0][7] = $this->usu_empresa;
        $campos[0][8] = $this->usu_deposito;

        // $secao[1] = 'Avatar';
        // $campos[1][0] = $this->usu_avatar;

        $this->data['desc_edicao'] = $dados_usuario['usu_nome'];
        $this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
        $this->data['destino']    = 'store';

        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('cfg_usuario', $id);

        echo view('vw_edicao', $this->data);
    }

    public function edit_senha($id)
    {
        // busca a usuario
        $anterior['anterior'] = $_SERVER["HTTP_REFERER"];
        session()->set($anterior);

        // $id = session()->get('usu_id');
        $dados_usuario = $this->usuario->getUsuarioId($id)[0];
        $this->def_campos($dados_usuario, true);

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->usu_id;
        $campos[0][1] = $this->usu_nome;
        $campos[0][2] = $this->usu_email;
        $campos[0][3] = $this->usu_login;
        $campos[0][4] = $this->usu_nova_senha;
        $campos[0][5] = $this->usu_contra_senha;
        $campos[0][6] = "<span id='msg_senha' class='text-danger bg-warning'></span>";
        $campos[0][7] = $this->usu_perfil;


        // $secao[1] = 'Avatar';
        // $campos[1][0] = $this->usu_avatar;

        $this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
        $this->data['destino']    = 'store';
        $this->data['desc_metodo'] = 'Alteração de Senha de';

        echo view('vw_edicao', $this->data);
    }

    public function delete($id)
    {
        $this->usuario->delete($id);
        session()->setFlashdata('msg', 'Registro Excluído com Sucesso!');
        return redirect()->to(site_url($this->data['controler'])); 
    }

    public function def_campos($dados = false, $leitura = false){
		$id				= new MyCampo('cfg_usuario','usu_id');
		$id->objeto		= 'oculto';
		$id->valor		= (isset($dados['usu_id']))?$dados['usu_id']:'';
		$this->usu_id	= $id->crOculto();

		$nome           =  new MyCampo('cfg_usuario','usu_nome');
        $nome->obrigatorio = true;
        $nome->hint    	= 'Informe o Nome do Usuário';
		$nome->valor	= (isset($dados['usu_nome']))?$dados['usu_nome']:'';
        $nome->dispForm = '1col';
        $this->usu_nome = $nome->crInput();


		$email              =  new MyCampo('cfg_usuario','usu_email');
        $email->tipo    	= 'email';
		$email->valor	    = (isset($dados['usu_email']))?$dados['usu_email']:'';
        $email->dispForm = '1col';
        $this->usu_email    = $email->crInput();

		$login                  =  new MyCampo('cfg_usuario','usu_login');
        $login->obrigatorio     = true;
        $login->classep       = 'text-lowercase';
		$login->valor	    = (isset($dados['usu_login']))?$dados['usu_login']:'';
        $login->dispForm = '1col';
        $this->usu_login    = $login->crInput();

        $perfis             = array_column($this->perfil->getPerfil(),'prf_nome','prf_id');
		$perfil             =  new MyCampo('cfg_usuario','prf_id');
        $perfil->leitura    = $leitura;
        $perfil->obrigatorio = true;
        $perfil->opcoes     = $perfis;
		$perfil->valor	    = (isset($dados['prf_id']))?$dados['prf_id']:'';
        $perfil->selecionado = $perfil->valor;
        $perfil->dispForm = '1col';
        if($leitura){
            $perfil->infotop   = 'Para alterar o Perfil, solicite ao Gestor do Sistema';
        }
        $this->usu_perfil   = $perfil->crSelect();

        $lst_telas = $this->tela->getTelaId();
        $telas = array_column($lst_telas, 'tel_nome', 'tel_id');
        $dash                  =  new MyCampo('cfg_usuario','usu_dashboard');
        $dash->obrigatorio     = false;
		$dash->valor	       = (isset($dados['usu_dashboard'])) ? $dados['usu_dashboard'] : '';
        $dash->selecionado     = $dash->valor;
        $dash->opcoes          = $telas;
        $dash->dispForm = '1col';
        $this->usu_dashboard   = $dash->crSelect();

        $nova_senha             =  new MyCampo('cfg_usuario','usu_senha');
        $nova_senha->tipo    	= 'password';
        $nova_senha->size   		= 20;
        $nova_senha->maxLength   	= 12;
		$nova_senha->largura   	= 25;
		$nova_senha->valor	    = '';
        $nova_senha->dispForm = '1col';
        if($leitura){
            $nova_senha->infotop   = 'Para manter a mesma senha, deixe-a em branco';
        }
        $this->usu_nova_senha = $nova_senha->crInput();

        $contra_senha           =  new MyCampo();
        $contra_senha->tipo    	= 'password';
        $contra_senha->nome    	= 'contra_senha';
        $contra_senha->id      	= 'contra_senha';
        $contra_senha->label   	= 'Confirme a Senha';
        $contra_senha->place   	= 'Confirme a Senha';
        $contra_senha->obrigatorio = false;
        $contra_senha->size   		= 20;
        $contra_senha->maxLength   	= 12;
		$contra_senha->largura   	= 25;
		$contra_senha->valor	    = '';
        $contra_senha->dispForm = '1col';
        $contra_senha->funcBlur	    = "compara_senha('contra_senha','usu_senha')";
        $this->usu_contra_senha = $contra_senha->crInput();


        $empresas           = array_column($this->empresa->getEmpresa(),'emp_apelido','emp_id');
		$empres             =  new MyCampo('cfg_usuario','usu_empresa');
        $empres->obrigatorio = true;
        $empres->classep    = 'selectpicker';
        $empres->opcoes     = $empresas;
        $empres->selecionado = (isset($dados['usu_empresa']))?explode(",",$dados['usu_empresa']):[];
        $empres->dispForm = '1col';
        if($leitura){
            $empres->infotop   = 'Para alterar a Empresa, solicite ao Gestor do Sistema';
        }
        $this->usu_empresa   = $empres->crMultiple();

        $depositos           = array_column($this->deposito->getDeposito(false, (isset($dados['usu_empresa']))?explode(",",$dados['usu_empresa']):false),'dep_completo','dep_id');
		$depos             =  new MyCampo('cfg_usuario','usu_deposito');
        $depos->obrigatorio = true;
        $depos->classep    = 'selectpicker';
        $depos->urlbusca   = 'busca/busca_deposito_empresa';
        $depos->pai        = 'usu_empresa';
        $depos->opcoes     = $depositos;
        $depos->selecionado = (isset($dados['usu_deposito']))?explode(",",$dados['usu_deposito']):[];
        $depos->dispForm = '1col';
        if($leitura){
            $depos->infotop   = 'Para alterar o Depósito, solicite ao Gestor do Sistema';
        }
        $this->usu_deposito   = $depos->crDependeMultiple();
        
    }

	public function store() {
        $dados = $this->request->getPost();
        $empresas = implode(",",$dados['usu_empresa']);
        $dados['usu_empresa'] = $empresas;
        $depositos = implode(",",$dados['usu_deposito']);
        $dados['usu_deposito'] = $depositos;
        if (isset($dados['usu_senha'])) {
            if ($dados['usu_senha'] == '') {
                unset($dados['usu_senha']);
            } else {
                $dados['usu_senha'] = md5($dados['usu_senha']);
            }
        }
        if ($this->usuario->save($dados)) {
            if ($dados['usu_id'] != '') {
                $usu_id = $dados['usu_id'];
            } else {
                $usu_id = $this->usuario->getInsertID();
            }
            $avatar = $this->request->getFile('usu_avatar');
            if (isset($avatar) && $avatar->getFilename() != '') {
                $path_avat = 'assets/uploads/usuario/usu_' . $usu_id . '.jpg';
                @unlink($path_avat);

                $avatar->move('assets/uploads/usuario', 'usu_' . $usu_id . '.jpg');
            }
            $ret['erro'] = false;
            $ret['msg']  = 'Usuario gravado com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url']  = session()->get('anterior');
            if ($ret['url'] == '') {
                $ret['url']  = site_url($this->data['controler']);
            }
        } else {
            $error = $this->usuario->getErrors();
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível gravar o Usuario, Verifique!' . $error;
            session()->setFlashdata('msg', $ret['msg']);
        }
        echo json_encode($ret);
	}

}