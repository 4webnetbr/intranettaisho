<?php namespace App\Models\Setup;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class SetupModuloModel extends Model
{
    protected $table            = 'setup_modulo';
    protected $primaryKey       = 'mod_id';
    protected $useAutoIncremodt = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields    = ['mod_id',
                                    'mod_nome',
                                    'mod_order',
                                    'mod_icone'
                                ];

    protected $skipValidation   = true;  

    protected $deletedField  = 'mod_excluido';
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
   
    public function getModulo($mod_id = false){ 
        $this->builder()->select('mod_id, mod_icone, mod_nome, mod_icone, mod_order');
        if($mod_id){
            $this->builder()->where('mod_id',$mod_id);
        }
        $this->builder()->where('mod_excluido',null);
        $this->builder()->orderBy('mod_order');
        return $this->builder()->get()->getResultArray();
    }

    public function getModulosSearch($termo)
    {
        $array = ['mod_nome' => $termo.'%'];
        $this->builder()->select(['mod_id','mod_nome','mod_icone']);
        $this->builder()->where('mod_excluido',null);
        $this->builder()->like($array);
    
        return $this->builder()->get()->getResultArray();
    }                                

}
