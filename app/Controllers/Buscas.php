<?php namespace App\Controllers;
use App\Controllers\BaseController;
use App\Models\Config\ConfigClasseModel;
use App\Models\Config\ConfigDicDadosModel;
use App\Models\Config\ConfigMenuModel;
use App\Models\Config\ConfigModuloModel;
use App\Models\Config\ConfigUsuarioModel;

class Buscas extends BaseController
{
    public $data = [];
    public $menu;
    public $modulo; 
    public $classe;
    public $usuario; 
    public $admDados;

	public function __construct(){
		$this->menu 		        = new ConfigMenuModel();
		$this->modulo 		        = new ConfigModuloModel();
		$this->classe 		        = new ConfigClasseModel();
        $this->usuario              = new ConfigUsuarioModel();
        $this->admDados             = new ConfigDicDadosModel();
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
        if ($_REQUEST['busca']) {
            $termo              = $_REQUEST['busca'];
            $submenus = $this->menu->getSubMenu($termo);
            if(sizeof($submenus) <= 0){
                $ret[0]['id'] = '-1';
                $ret[0]['text'] = 'SubMenu não encontrada...';
            } else {
                for ($c = 0;$c<sizeof($submenus);$c++) {
                    $ret[$c]['id']      = $submenus[$c]['men_id'];
                    $ret[$c]['text']    = $submenus[$c]['men_etiqueta'];
                }
            }
        }
        echo json_encode($ret);
        exit;
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
                    $ret[$c]['id']      = $class[$c]['cls_id'];
                    $ret[$c]['text']    = $class[$c]['cls_nome'];
                    $ret[$c]['icone']    = $class[$c]['cls_icone'];
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
                    $ret[$c]['id']      = $class[$c]['cls_id'];
                    $ret[$c]['text']    = $class[$c]['cls_nome'];
                    $ret[$c]['icone']    = $class[$c]['cls_icone'];
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
        // debug($_REQUEST,false);
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