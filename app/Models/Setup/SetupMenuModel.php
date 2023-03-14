<?php namespace App\Models\Setup;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class SetupMenuModel extends Model
{
    protected $table            = 'setup_menu';
    protected $primaryKey       = 'men_id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields    = ['men_id',
                                    'men_modulo_id',
                                    'men_nome',
                                    'men_classe_id',
                                    'men_metodo', 
                                    'men_icone',
                                    'men_order'
                                ];

    protected $skipValidation   = true;  

    // protected $createdField  = 'men_criado';
    // protected $updatedField  = 'men_alterado';
    protected $deletedField  = 'men_excluido';
    // Callbacks
    protected $allowCallbacks = true;
    protected $afterInsert   = ['depoisInsert'];
    protected $afterUpdate   = ['depoisUpdate'];
    protected $afterDelete   = ['depoisDelete'];

    protected $logdb;

/**
     * This method saves the session "usu_id" value to "created_by" and "updated_by" array 
     * elements before the row is inserted into the database.
     *
     */
    protected function depoisInsert(array $data) {
        $logdb = new LogMonModel(); 
        $registro = $data['id'];
        $log = $logdb->insertLog($this->table,'Incluído',$registro, $data['data']);
        return $data;
    }

    /**
     * This method saves the session "usu_id" value to "updated_by" array element before 
     * the row is inserted into the database.
     *
     */
    protected function depoisUpdate(array $data) {
        $logdb = new LogMonModel(); 
        $registro = $data['id'][0];
        $log = $logdb->insertLog($this->table,'Alterado',$registro, $data['data']);
        return $data;
    } 

    /**
     * This method saves the session "usu_id" value to "deletede_by" array element before 
     * the row is inserted into the database.
     *
     */
    protected function depoisDelete(array $data) {
        $logdb = new LogMonModel(); 
        $registro = $data['id'][0];
        $log = $logdb->insertLog($this->table,'Excluído',$registro, $data['data']);
        return $data;
    } 
   
    public function getMenu($men_id = false){ 
        $db = db_connect();

        $builder = $db->table('setup_menu menu');
        $builder->select('modu.mod_id, modu.mod_icone, modu.mod_nome, modu.mod_order, clas.clas_id, clas.clas_titulo, clas.clas_controler, menu.men_id, menu.men_nome, menu.men_icone, menu.men_metodo, menu.men_order');
        $builder->join('setup_modulo modu', 'modu.mod_id = menu.men_modulo_id');
        $builder->join('setup_classe clas', 'clas.clas_id = menu.men_classe_id','left');
        if($men_id){
            $builder->where('menu.men_id',$men_id);
        }
        $builder->where('men_excluido',null);
        $builder->orderBy('modu.mod_id, modu.mod_order, menu.men_order');
        return $builder->get()->getResultArray();
    }

    public function getMenuPai($men_id = false){ 
        $db = db_connect();

        $builder = $db->table('setup_menu menu');
        $builder->select('modu.mod_id, modu.mod_icone, modu.mod_nome, modu.mod_order, clas.clas_id, clas.clas_titulo, clas.clas_controler, menu.men_id, menu.men_nome, menu.men_icone, menu.men_metodo, menu.men_order');
        $builder->join('setup_modulo modu', 'modu.mod_id = menu.men_modulo_id');
        $builder->join('setup_classe clas', 'clas.clas_id = menu.men_classe_id','left');
        if($men_id){
            $builder->where('menu.men_id',$men_id);
        }
        $builder->where('men_hierarquia',1);
        $builder->where('men_excluido',null);
        $builder->orderBy('modu.mod_id, modu.mod_order, menu.men_order');
        return $builder->get()->getResultArray();
    }

    public function getSubMenu($men_pai){ 
        $db = db_connect();

        $builder = $db->table('setup_menu menu');
        $builder->select('modu.mod_id, modu.mod_icone, modu.mod_nome, modu.mod_order, clas.clas_id, clas.clas_titulo, clas.clas_controler, menu.men_id, menu.men_nome, menu.men_icone, menu.men_metodo, menu.men_order');
        $builder->join('setup_modulo modu', 'modu.mod_id = menu.men_modulo_id');
        $builder->join('setup_classe clas', 'clas.clas_id = menu.men_classe_id','left');
        $builder->where('menu.men_menu_id',$men_pai);
        $builder->where('men_hierarquia',2);
        $builder->where('men_excluido',null);
        $builder->orderBy('modu.mod_id, modu.mod_order, menu.men_order');
        return $builder->get()->getResultArray();
    }

    public function getMenuModulo($mod_id = false){ 
        $db = db_connect();

        $builder = $db->table('setup_menu menu');
        $builder->select('modu.mod_id, modu.mod_icone, modu.mod_nome, modu.mod_order, menu.men_id, menu.men_nome, menu.men_controler, menu.men_icone, menu.men_metodo, menu.men_order');
        $builder->join('setup_modulo modu', 'modu.mod_id = menu.men_modulo_id');
        if($mod_id){
            $builder->where('menu.men_modulo_id',$mod_id);
        }
        $builder->where('men_excluido',null);
        $builder->orderBy('modu.mod_order, menu.men_order');
        return $builder->get()->getResultArray();
    }

    public function getMenuPerfil($perfil_id, $tipo_usu = 1){  
        $this->builder()->select('modu.mod_id, modu.mod_icone, modu.mod_nome, modu.mod_order, clas.clas_id, clas.clas_titulo, clas.clas_controler, setup_menu.men_id, setup_menu.men_nome, setup_menu.men_icone, setup_menu.men_metodo, setup_menu.men_order');
        $this->builder()->join('setup_modulo modu', 'modu.mod_id = setup_menu.men_modulo_id');
        $this->builder()->join('setup_classe clas', 'clas.clas_id = setup_menu.men_classe_id');
        $this->builder()->join('setup_perfil_item pit', 'pit.pit_menu_id = setup_menu.men_id');
        if($tipo_usu < 3){
            $this->builder()->where('setup_menu.men_tipo', $tipo_usu);
        }
        $this->builder()->where('pit.pit_perfil_id', $perfil_id);
        $this->builder()->where('men_excluido',null);
        $this->builder()->orderBy('modu.mod_order, setup_menu.men_order');

        $ret = $this->builder()->get()->getResultArray();

        // d($this->db->getLastQuery());  

        return $ret;
    }
}
