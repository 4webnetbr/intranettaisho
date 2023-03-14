<?php namespace App\Models\Setup;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class SetupPerfilModel extends Model {

    protected $table      = 'setup_perfil';
    protected $primaryKey = 'per_id';

    protected $returnType     = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = ['per_id',
                                'per_nome',
                                'per_descricao',
                                'per_empresa_id',
                                ];
                                
    protected $useTimestamps = true;
    protected $createdField  = 'per_criado';
    protected $updatedField  = 'per_alterado';
    protected $deletedField  = 'per_excluido';

    protected $skipValidation     = false;    

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

    public function getPerfilId($id = false)
    {
        $this->builder()->select();
        if ($id) {
            $this->builder()->where('per_id', $id);
        }
        $this->builder()->where('per_excluido',null);
        $this->builder()->orderBy('per_id');
        return $this->find();
    }                 

    public function getPerfilIdSel()
    {
        $this->builder()->select();
        $this->builder()->where('per_id >', 1);
        $this->builder()->where('per_excluido',null);
        $this->builder()->orderBy('per_id');
        return $this->find();
    }                 

}