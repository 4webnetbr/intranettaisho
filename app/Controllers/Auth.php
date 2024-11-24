<?php namespace App\Controllers;

use App\Models\Config\ConfigUsuarioModel;
use CodeIgniter\RESTful\ResourceController;
use Config\Services;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth extends ResourceController
{
	public $usuario;
	protected $format = 'json';

	public function login()
	{
		/**
		 * JWT claim types
		 * https://auth0.com/docs/tokens/concepts/jwt-claims#reserved-claims
		 */
		$this->usuario = new ConfigUsuarioModel();

        $login 		= trim($this->request->getVar('username'));
        $senha 		= trim($this->request->getVar('password'));
        // $data 		= array('lower(trim(ent_usuario))'=>$login,'trim(ent_senha)'=>md5($senha));  
		log_message('info', 'Login '.$login);
		log_message('info', 'Senha '.$senha);
		// log_message('info', 'senha '.$senha);
        $data = array('lower(trim(usu_login))' => $login);

        $log_config =  $this->usuario->usuLogonConfig($data);
        if (!$log_config) {
			log_message('info', 'Login Inválido');
			return $this->respond(['message' => 'Login Inválido', 'authenticated' => false,'token'=>''], 200);
        } else {
            $conf_senha = (md5($senha) == trim($log_config[0]['usu_senha']));
            if (!$conf_senha) {
					// $session->setFlashdata('msg', 'Senha não corresponde ao Usuário!');
					// return redirect()->to('/login');
					log_message('info', 'Login Inválido');
					return $this->respond(['message' => 'Login Inválido', 'authenticated' => false,'token'=>''], 200);
			} else {		
				$key = Services::getSecretKey(); 
				$time = time();
				$payload = [
					'iat' => $time,
					'data' => [
						'id'	=> $log_config[0]['usu_id'], 
						'nome' 	=> $log_config[0]['usu_nome'],
						'perfil'	=> $log_config[0]['prf_id']
					]
				];
				$conteudo = 'Login: '.$log_config[0]['usu_nome']."\nDataHora: ".date('Y-m-d H:i:s')."\n\n";
				log_message('info', $conteudo);

				$jwt = JWT::encode($payload, $key,'HS256');
				return $this->respond(['message' => 'Login Ok','perfil' => $log_config[0]['prf_id'], 'authenticated' => true,'token' => $jwt], 200);
			}
		}
	}

	public function validateToken($token){
		try {
			$key = Services::getSecretKey();
			return JWT::decode($token, new Key($key, 'HS256'));
		} catch (\Exception $e) {
			return false;
		}
	}

	public function verifyToken(){
		$key = Services::getSecretKey();
		$token = $this->request->getPost("token");

		if($this->validateToken($token) == false){
			return $this->respond(['message'=>'Token Inválido'],401);
		} else {
			$data = JWT::decode($token, $key);
			return $this->respond(['data'=>$data],200);
		}
	}
}