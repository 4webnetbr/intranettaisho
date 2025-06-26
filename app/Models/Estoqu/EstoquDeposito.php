<?php 
namespace App\Models\Estoqu;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class EstoquDeposito extends Model
{
    protected $DBGroup          = 'default';

    protected $table            = 'est_deposito';
    protected $view             = 'est_deposito';
    protected $primaryKey       = 'und_id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields    = ['und_id',
                                    'und_sigla',
                                    'und_nome',
                                    'und_descricao',
                                ];

                                
    protected $skipValidation   = true;  

    protected $deletedField  = 'und_excluido';
   
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
     * @param mixed $emp_id 
     * @return array
     */
    public function getDeposito($emp_id = false)
    {
        debug($emp_id, true);
        // $db = db_connect();
        // $builder = $db->table('est_deposito');
        // $builder->select('*'); 
        // if($emp_id){
        //     $builder->wherein("emp_id", $emp_id);
        // }
        // $builder->where("dep_excluido", null);
        // $builder->orderBy("dep_nome");

        // $ret = $builder->get()->getResultArray();

        // // debug($this->db->getLastQuery(), false);
        
        // return $ret;

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
        $db = db_connect();
        $builder = $db->table('est_deposito');
        $builder->select('*'); 
		$builder->groupStart();
			$builder->like('und_sigla', $termo);
			$builder->orLike('und_nome', $termo); 
		$builder->groupEnd();
        $builder->where("und_excluido", null);
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }                 
}
?>