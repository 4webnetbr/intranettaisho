<?php 
namespace App\Models\Estoqu;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class EstoquDepositoModel extends Model
{
    protected $DBGroup          = 'dbEstoque';

    protected $table            = 'est_deposito';
    protected $view             = 'vw_est_deposito_relac';
    protected $primaryKey       = 'dep_id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields    = ['dep_id',
                                    'emp_id',
                                    'dep_nome',
                                ];

                                
    protected $skipValidation   = true;  

    protected $deletedField  = 'dep_excluido';
   
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
     * getDeposito
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getDeposito($dep_id = false, $emp_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_deposito_relac');
        $builder->select('*'); 
        if($dep_id){
            if(gettype($dep_id) == 'array'){
                $builder->whereIn("dep_id", $dep_id);
            } else {
                $builder->where("dep_id", $dep_id);
            }
        }
        if($emp_id){
            if(gettype($emp_id) == 'array'){
                $builder->whereIn("emp_id", $emp_id);
            } else {
                $builder->where("emp_id", $emp_id);
            }
        }
        $builder->orderBy("dep_nome");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);
        
        return $ret;

    }                 

    /**
     * getDepositoSearch
     *
     * Retorna os dados da Linha, pelo termo (nome) informado
     * Utilizado nas Seleções de Linha
     *  
     * @param mixed $termo 
     * @return array
     */
    public function getDepositoSearch($termo)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_deposito_relac');
        $builder->select('*'); 
		$builder->groupStart();
			$builder->like('dep_nome', $termo);
		$builder->groupEnd();
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }                 
}
?>