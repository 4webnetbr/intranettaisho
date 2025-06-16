<?php namespace App\Controllers\Api;

use App\Controllers\Auth;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\Config\ConfigUsuarioModel;
use App\Models\Estoqu\EstoquDepositoModel;

class ApiConfig extends Auth
{
    public $empresa;
    public $usuario;
    public $deposito;

    public function __construct()
    {
        $this->empresa       = new ConfigEmpresaModel();        
        $this->usuario       = new ConfigUsuarioModel();        
        $this->deposito      = new EstoquDepositoModel();
    }

    /**
     * getEmpresas
     * Retorna a Lista de todas as entregas da Rota Informada
     * @return void
     */
    public function getEmpresas(){
        if($this->request->header('Authorization') != null){
            $token = $this->request->header('Authorization')->getValue();
            if($this->validateToken($token) == true){
                $token      = $this->request->header('Authorization')->getValue();
                $inform     = get_object_vars($this->validateToken($token));
                $dados      = get_object_vars($inform['data']);
                $usuario    = $dados['id'];
                log_message('info','Usuário: '.$usuario.' Função: getEmpresas');
                $user       = $this->usuario->getUsuarioId($usuario);
                $empuser = explode(",", $user[0]['usu_empresa']);
				$dados 		= $this->empresa->getEmpresa($empuser);
                log_message('info','Empresas: '.json_encode($dados));

                // echo $this->api->getLastQuery();
                $empres      = [];
				for($d=0;$d<sizeof($dados);$d++){
					$chave = $dados[$d]; 
                    $empres[$d]['emp_id']       = $chave['emp_id'];
                    $empres[$d]['emp_nome']     = $chave['emp_nome'];
                    $empres[$d]['emp_apelido']  = $chave['emp_apelido'];
                }
                return $this->respond($empres,200);
            } else {
                return $this->respond(['message'=>'Token Inválido'],401);
            }
        } else { 
            return $this->respond(['message'=>'Não Autorizado'],401);
        }
    }
    /**
     * getDepositos
     * Retorna a Lista dos Depositos da Empresa informada
     * @return void
     */
    public function getDepositos(){
        if($this->request->header('Authorization') != null){
            $token = $this->request->header('Authorization')->getValue();
            if($this->validateToken($token) == true){
                $token      = $this->request->header('Authorization')->getValue();
                $inform     = get_object_vars($this->validateToken($token));
                $dados      = get_object_vars($inform['data']);
                $usuario    = $dados['id'];
                log_message('info','Usuário: '.$usuario.' Função: getEmpresas');

                $empresa       = $this->request->getVar('empresa');
                $dados 		= $this->deposito->getDeposito(false, $empresa);
                log_message('info','Empresas: '.json_encode($dados));

                // echo $this->api->getLastQuery();
                $depos      = [];
				for($d=0;$d<sizeof($dados);$d++){
					$chave = $dados[$d]; 
                    $depos[$d]['dep_id']       = $chave['dep_id'];
                    $depos[$d]['dep_nome']     = $chave['dep_nome'];
                }
                return $this->respond($depos,200);
            } else {
                return $this->respond(['message'=>'Token Inválido'],401);
            }
        } else { 
            return $this->respond(['message'=>'Não Autorizado'],401);
        }
    }
}