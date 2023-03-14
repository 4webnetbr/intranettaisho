<?php namespace App\Controllers\Setup;
use App\Controllers\BaseController;
use App\Models\Setup\SetupPerfilItemModel;


class Home_setup extends BaseController
{
	public $data = [];
    public $perfil_ite;

	public function __construct(){
		$this->data 			= session()->getFlashdata('dados_classe');
		$this->perfil_ite 		= new SetupPerfilItemModel();
        if ($this->data['erromsg'] != '') {
            $this->__erro();
        }
    }

    function __erro()
    {
        echo view('vw_semacesso', $this->data);
    }

	public function index(){
		// $busca_permissoes       = $this->perfil_ite->getItemPerfilClasse(session()->get('usu_perfil_id'), 'Pedidos');
		echo view('setup/vw_home', $this->data);
	}
}
