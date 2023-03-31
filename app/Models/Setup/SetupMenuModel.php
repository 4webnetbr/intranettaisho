<?php namespace App\Models\Setup;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class SetupMenuModel extends Model
{
    protected $table            = "setup_menu";
    protected $primaryKey       = "men_id";
    protected $useAutoIncrement = true;

    protected $returnType       = "array";
    protected $useSoftDeletes   = true;

    protected $allowedFields    = ["men_id",
                                    "men_hierarquia", 
                                    "men_menu_id",
                                    "men_submenu_id",
                                    "men_modulo_id",
                                    "men_classe_id",
                                    "men_etiqueta",
                                    "men_icone",
                                    "men_order"
                                ];
                                
    protected $skipValidation   = true;  

    protected $deletedField  = "men_excluido";
    // Callbacks
    protected $allowCallbacks = true;
    protected $afterInsert   = ["depoisInsert"];
    protected $afterUpdate   = ["depoisUpdate"];
    protected $afterDelete   = ["depoisDelete"];

    protected $logdb;

/**
     * This method saves the session "usu_id" value to "created_by" and "updated_by" array 
     * elements before the row is inserted into the database.
     *
     */
    protected function depoisInsert(array $data) {
        $logdb = new LogMonModel(); 
        $registro = $data["id"];
        $log = $logdb->insertLog($this->table,"Incluído",$registro, $data["data"]);
        return $data;
    }

    /**
     * This method saves the session "usu_id" value to "updated_by" array element before 
     * the row is inserted into the database.
     *
     */
    protected function depoisUpdate(array $data) {
        $logdb = new LogMonModel(); 
        $registro = $data["id"][0];
        $log = $logdb->insertLog($this->table,"Alterado",$registro, $data["data"]);
        return $data;
    } 

    /**
     * This method saves the session "usu_id" value to "deletede_by" array element before 
     * the row is inserted into the database.
     *
     */
    protected function depoisDelete(array $data) {
        $logdb = new LogMonModel(); 
        $registro = $data["id"][0];
        $log = $logdb->insertLog($this->table,"Excluído",$registro, $data["data"]);
        return $data;
    } 
   
    public function getMenu($men_id = false){ 
        $db = db_connect();
        $builder = $db->table('vw_setup_menu_relac');
        $builder->select('*'); 
        if($men_id){
            $builder->where("men_id",$men_id);
        }
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }

    public function getMenuPai($men_id = false){ 
        $db = db_connect();
        $builder = $db->table('vw_setup_menu_relac menu');
        $builder->select('*'); 
        if($men_id){
            $builder->where("menu.men_id",$men_id);
        }
        $builder->where("men_hierarquia",'2');
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);

        return $ret;
    }

    public function getSubMenu($men_pai){ 
        $db = db_connect();
        $builder = $db->table('vw_setup_menu_relac menu');
        $builder->select('*'); 
        if($men_pai){
            $builder->where("menu.men_menu_id",$men_pai);
        }
        $builder->where("men_hierarquia",'3');
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);

        return $ret;
    }

    public function getRaizMenuPerfil($perfil_id = false, $tipo_usu = 1){  
        $db = db_connect();
        $builder = $db->table('vw_setup_menu_relac');
        $builder->select('*'); 
        if($tipo_usu && $tipo_usu < 3){
            $builder->where("men_tipo", $tipo_usu);
        }
        $builder->where("men_excluido",null);
        $builder->wherein("men_hierarquia",[1,2]);
        $ret = $builder->get()->getResultArray();
        // d($this->db->getLastQuery(), false);
        return $ret;
    }

    public function getPrimeiroNivel($menu_id, $perfil_id = false){  
        $db = db_connect();
        $builder = $db->table('vw_setup_menu_relac menu');
        $builder->select('*'); 
        if($perfil_id){
            $builder->join('setup_perfil_item pit','(pit.pit_classe_id = menu.men_classe_id OR menu.clas_id IS NULL) AND pit.pit_perfil_id = '.$perfil_id,'inner');            
        }
        $builder->where("men_menu_id", $menu_id);
        $builder->where("men_excluido",null);
        $builder->groupStart();
        $builder->where("men_hierarquia",3);
        $builder->orWhere("men_hierarquia",4);
        $builder->where("men_submenu_id <",1);
        $builder->groupEnd();
        $builder->groupBy('men_id');
        $ret = $builder->get()->getResultArray();
        // d($this->db->getLastQuery(), false);
        // d($this->db->getLastQuery());  
        return $ret;

    }

    public function getSegundoNivel($menu_id, $submenu, $perfil_id = false){  
        $db = db_connect();
        $builder = $db->table('vw_setup_menu_relac menu');
        if($perfil_id){
            $builder->join('setup_perfil_item pit','(pit.pit_classe_id = menu.men_classe_id OR menu.clas_id IS NULL) AND pit.pit_perfil_id = '.$perfil_id,'inner');            
        }
        $builder->select('*'); 
        $builder->where("men_menu_id", $menu_id);
        $builder->where("men_submenu_id", $submenu);
        $builder->wherein("men_hierarquia",[4]);
        $builder->groupBy('men_id');
        $ret = $builder->get()->getResultArray();
        // d($this->db->getLastQuery(), false);
        
        return $ret;
    }

}
