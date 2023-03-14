<?php namespace App\Models\Setup;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class SetupMenuNovoModel extends Model
{
    protected $table            = "setup_menu_novo";
    protected $primaryKey       = "men_id";
    protected $useAutoIncrement = true;

    protected $returnType       = "array";
    protected $useSoftDeletes   = true;

    protected $allowedFields    = ["men_id",
                                    "men_tipo",
                                    "men_hierarquia", 
                                    "men_menu_id",
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
        $this->builder()->select("$this->table.*, modu.mod_id, modu.mod_icone, modu.mod_nome, modu.mod_order, clas.clas_id, clas.clas_titulo, clas.clas_controler");
        $this->builder()->join("setup_modulo modu", "modu.mod_id = $this->table.men_modulo_id", "left");
        $this->builder()->join("setup_classe clas", "clas.clas_id = $this->table.men_classe_id","left");
        if($men_id){
            $this->builder()->where("$this->table.men_id",$men_id);
        }
        $this->builder()->where("men_excluido",null);
        $this->builder()->orderBy("men_order, modu.mod_id, modu.mod_order");
        $ret = $this->builder()->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }

    public function getMenuPai($men_id = false){ 
        $this->builder()->select("$this->table.*, modu.mod_id, modu.mod_icone, modu.mod_nome, modu.mod_order, clas.clas_id, clas.clas_titulo, clas.clas_controler");
        $this->builder()->join("setup_modulo modu", "modu.mod_id = $this->table.men_modulo_id");
        $this->builder()->join("setup_classe clas", "clas.clas_id = $this->table.men_classe_id","left");
        if($men_id){
            $this->builder()->where("$this->table.men_id",$men_id);
        }
        $this->builder()->where("$this->table.men_hierarquia",1);
        $this->builder()->where("$this->table.men_excluido",null);
        $this->builder()->orderBy("$this->table.men_order, modu.mod_id, modu.mod_order");
        $ret = $this->builder()->get()->getResultArray();

        return $ret;
    }

    public function getSubMenu($men_pai){ 
        $this->builder()->select("$this->table.*, modu.mod_id, modu.mod_icone, modu.mod_nome, modu.mod_order, clas.clas_id, clas.clas_titulo, clas.clas_controler");
        $this->builder()->join("setup_modulo modu", "modu.mod_id = $this->table.men_modulo_id");
        $this->builder()->join("setup_classe clas", "clas.clas_id = $this->table.men_classe_id","left");
        $this->builder()->where("$this->table.men_menu_id",$men_pai);
        $this->builder()->where("$this->table.men_hierarquia",2);
        $this->builder()->where("$this->table.men_excluido",null);
        $this->builder()->orderBy("$this->table.men_order");
        $ret = $this->builder()->get()->getResultArray();
        return $ret;
    }

    public function getMenuModulo($mod_id = false){ 
        $db = db_connect();

        // $builder = $db->table($this->table);
        $this->builder->select("modu.mod_id, modu.mod_icone, modu.mod_nome, modu.mod_order, menu.men_id, menu.men_nome, menu.men_controler, menu.men_icone, menu.men_metodo, menu.men_order");
        $this->builder->join("setup_modulo modu", "modu.mod_id = menu.men_modulo_id");
        if($mod_id){
            $this->builder->where("menu.men_modulo_id",$mod_id);
        }
        $this->builder->where("men_excluido",null);
        $this->builder->orderBy("modu.mod_order, menu.men_order");
        return $this->builder->get()->getResultArray();
    }

    public function getMenuPerfil($perfil_id, $tipo_usu = 1){  
        $this->builder()->select("modu.mod_id, modu.mod_icone, modu.mod_nome, modu.mod_order, clas.clas_id, clas.clas_titulo, clas.clas_controler, setup_menu.men_id, setup_menu.men_nome, setup_menu.men_icone, setup_menu.men_metodo, setup_menu.men_order");
        $this->builder()->join("setup_modulo modu", "modu.mod_id = setup_menu.men_modulo_id");
        $this->builder()->join("setup_classe clas", "clas.clas_id = setup_menu.men_classe_id");
        $this->builder()->join("setup_perfil_item pit", "pit.pit_menu_id = setup_menu.men_id");
        if($tipo_usu < 3){
            $this->builder()->where("setup_menu.men_tipo", $tipo_usu);
        }
        $this->builder()->where("pit.pit_perfil_id", $perfil_id);
        $this->builder()->where("men_excluido",null);
        $this->builder()->orderBy("modu.mod_order, setup_menu.men_order");

        $ret = $this->builder()->get()->getResultArray();

        // d($this->db->getLastQuery());  

        return $ret;
    }
}
