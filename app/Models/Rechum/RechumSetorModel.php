<?php 
namespace App\Models\Rechum;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class RechumSetorModel extends Model
{
    protected $DBGroup          = 'dbRh';

    protected $table            = 'rh_setor';
    protected $view             = 'vw_rh_setor_relac';
    protected $primaryKey       = 'set_id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields    = [ 'set_id',
                                    // 'emp_id',
                                    'set_nome',
                                    'set_pctdistribui'
                                ];

                                
    protected $skipValidation   = false;  
    protected $validationRules = [
        'set_nome'=>'required|is_unique[rh_setor.set_nome, set_id,{set_id}]',
    ];

    protected $validationMessages = [
        'set_nome' => [
            'required' => 'O Nome do Setor é Obrigatório.',
            'is_unique' =>  'Esse Setor, já está cadastrado'
        ],

    ];

    protected $deletedField  = 'set_excluido';
   
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

    /**
     * getSetor
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getSetor($set_id = false)
    {
        $db = db_connect('dbRh');
        $builder = $db->table($this->view);
        $builder->select('*'); 
        if($set_id){
            $builder->where("set_id", $set_id);
        }
        $builder->orderBy("set_nome");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);
        
        return $ret;

    }                 

        /**
     * getSetorEmp
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    // public function getSetorEmp($emp_id = false)
    // {
    //     $db = db_connect('dbRh');
    //     $builder = $db->table($this->view);
    //     $builder->select('*'); 
    //     // $builder->whereIn("emp_id", $emp_id);
    //     $builder->orderBy("set_nome");
    //     $ret = $builder->get()->getResultArray();

    //     // debug($this->db->getLastQuery(), false);
        
    //     return $ret;

    // }                 


    /**
     * getSetorSearch
     *
     * Retorna os dados da Linha, pelo termo (nome) informado
     * Utilizado nas Seleções de Linha
     *  
     * @param mixed $termo 
     * @return array
     */
    public function getSetorSearch($termo)
    {
        $db = db_connect('dbRh');
        $builder = $db->table($this->table);
        $builder->select('*'); 
		$builder->groupStart();
			$builder->like('set_nome', $termo);
		$builder->groupEnd();
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }                 
}