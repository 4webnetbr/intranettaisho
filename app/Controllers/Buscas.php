<?php namespace App\Controllers;
use App\Controllers\BaseController;
use App\Models\Setup\SetupClasseModel;
use App\Models\Setup\SetupDicDadosModel;
use App\Models\Setup\SetupMenuModel;
use App\Models\Setup\SetupModuloModel;
use App\Models\Setup\SetupUsuarioModel;

class Buscas extends BaseController
{
    public $data = [];
    public $menu;
    public $modulo; 
    public $classe;
    public $usuario; 
    public $admDados;

	public function __construct(){
		$this->menu 		        = new SetupMenuModel();
		$this->modulo 		        = new SetupModuloModel();
		$this->classe 		        = new SetupClasseModel();
        $this->usuario              = new SetupUsuarioModel();
        $this->admDados             = new SetupDicDadosModel();
	}

    public function busca_hierarquia(){

        $ret    = [];
        if ($_REQUEST['campo']) {
            $data = $_REQUEST;
            $termo              = $data['campo'][0]['id_dep'];
            if($termo == 1){
                $hierarquia[2] = 'Pai';
                $hierarquia[3] = 'Filho';
            } else {
                $hierarquia[1] = 'Órfão';
                $hierarquia[3] = 'Filho';
                $hierarquia[4] = 'Neto';        
            }
        }
        echo json_encode($hierarquia);
    }
    public function busca_menu_pai(){
        $menus = $this->menu->getMenuPai();
        $menu_pai = array_column($menus,'men_etiqueta','men_id');
        echo json_encode($menu_pai);
    }

    public function busca_submenu(){
        $data = $_REQUEST;
        $termo              = $data['campo'][0]['id_dep'];
        $submenus = $this->menu->getSubMenu($termo);
        $sub_menu = array_column($submenus,'men_etiqueta','men_id');
        echo json_encode($sub_menu);
    }


    public function busca_modulo(){
        $ret    = [];
        if ($_REQUEST['busca']) {
            $termo              = $_REQUEST['busca'];
            $modulos            = $this->modulo->getModulosSearch($termo);
            if(sizeof($modulos) <= 0){
                $ret[0]['id'] = '-1';
                $ret[0]['text'] = 'Módulo não encontrado...';
            } else {
                for ($c = 0;$c<sizeof($modulos);$c++) {
                    $ret[$c]['id']      = $modulos[$c]['mod_id'];
                    $ret[$c]['text']    = $modulos[$c]['mod_nome'];
                    $ret[$c]['icone']    = $modulos[$c]['mod_icone'];
                }
            }
        }
        echo json_encode($ret);
        exit;
    }

    public function busca_modulo_id(){
        $ret    = [];
        if ($_REQUEST['busca']) {
            $termo              = $_REQUEST['busca'];
            $modulos            = $this->modulo->getModulo($termo);
            if(sizeof($modulos) <= 0){
                $ret[0]['id'] = '-1';
                $ret[0]['text'] = 'Módulo não encontrada...';
            } else {
                for ($c = 0;$c<sizeof($modulos);$c++) {
                    $ret[$c]['id']      = $modulos[$c]['mod_id'];
                    $ret[$c]['text']    = $modulos[$c]['mod_nome'];
                    $ret[$c]['icone']    = $modulos[$c]['mod_icone'];
                }
            }
        }
        echo json_encode($ret);
        exit;
    }

    public function busca_menu() {
        $data = $_REQUEST;
        $tipo = $data['campo'][0]['id_dep'];
        $menus = $this->menu->getMenuModulo($tipo);
        // echo $this->db->last_query();
        if (count($menus) > 0) {
            $menu = array_column($menus, 'men_nome', 'men_id');
            $menu_ret = json_encode($menu);
            echo $menu_ret;
        }
        exit;
    }

    public function busca_classe() {
        $ret    = [];
        if ($_REQUEST['busca']) {
            $termo              = $_REQUEST['busca'];
            $class = $this->classe->getClasseSearch($termo);
            if(sizeof($class) <= 0){
                $ret[0]['id'] = '-1';
                $ret[0]['text'] = 'Classe não encontrada...';
            } else {
                for ($c = 0;$c<sizeof($class);$c++) {
                    $ret[$c]['id']      = $class[$c]['clas_id'];
                    $ret[$c]['text']    = $class[$c]['clas_titulo'];
                    $ret[$c]['icone']    = $class[$c]['clas_icone'];
                }
            }
        }
        echo json_encode($ret);
        exit;
    }

    public function busca_classe_id() {
        $ret    = [];
        if ($_REQUEST['busca']) {
            $termo              = $_REQUEST['busca'];
            $class = $this->classe->getClasseId($termo);
            if(sizeof($class) <= 0){
                $ret[0]['id'] = '-1';
                $ret[0]['text'] = 'Classe não encontrada...';
            } else {
                for ($c = 0;$c<sizeof($class);$c++) {
                    $ret[$c]['id']      = $class[$c]['clas_id'];
                    $ret[$c]['text']    = $class[$c]['clas_titulo'];
                    $ret[$c]['icone']    = $class[$c]['clas_icone'];
                }
            }
        }
        echo json_encode($ret);
        exit;
    }

    public function busca_menupai() {
        $data = $_REQUEST;
        $tipo = $data['campo'][0]['id_dep'];
        $menus = $this->menu->getMenuModulo($tipo);
        // echo $this->db->last_query();
        if (count($menus) > 0) {
            $menu = array_column($menus, 'men_nome', 'men_id');
            $menu_ret = json_encode($menu);
            echo $menu_ret;
        }
        exit;
    }

    public function busca_tabela(){
        $ret    = [];
        if ($_REQUEST['busca']) {
            $termo            = $_REQUEST['busca'];
            $tabelas         = $this->admDados->getTabelaSearch($termo);
            if(sizeof($tabelas) <= 0){
                $ret[0]['id'] = '-1';
                $ret[0]['text'] = 'Tabela não encontrada...';
            } else {
                for ($c = 0;$c<sizeof($tabelas);$c++) {
                    $ret[$c]['id'] = $tabelas[$c]['table_name'];
                    $ret[$c]['text'] = $tabelas[$c]['table_name'];
                }
            }
        }
        echo json_encode($ret);
    }

}