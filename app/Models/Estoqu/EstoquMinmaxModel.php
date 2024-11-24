<?php 
namespace App\Models\Estoqu;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class EstoquMinmaxModel extends Model
{
    protected $DBGroup          = 'dbEstoque';

    protected $table            = 'est_minmax';
    protected $view             = 'vw_est_minmax_relac';
    protected $primaryKey       = 'mmi_id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [ 'mmi_id',
                                    'emp_id',
                                    'pro_id',
                                    'mmi_minimo',
                                    'mmi_maximo',
    
                                ];

                                
    protected $skipValidation   = false;  


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
     * getMinmax
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getMinmax($emp_id = false, $pro_id = false, $mmi_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_minmax_relac');
        $builder->select('*'); 
        if($emp_id){
            $builder->where("emp_id", $emp_id);
        }
        if($pro_id){
            $builder->where("pro_id", $pro_id);
        }
        if($mmi_id){
            $builder->where("mmi_id", $mmi_id);
        }
        $builder->orderBy("pro_nome");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);
        
        return $ret;

    }                 

}
?>