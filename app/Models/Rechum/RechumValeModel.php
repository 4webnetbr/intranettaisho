<?php 
namespace App\Models\Rechum;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class RechumValeModel extends Model
{
    protected $DBGroup          = 'dbRh';

    protected $table            = 'rh_vale';
    protected $view             = 'vw_rh_vale_relac';
    protected $primaryKey       = 'val_id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';

    protected $allowedFields    = [ 'val_id',
                                    'col_id',
                                    'val_data',
                                    'val_valor'
                                ];

                                
    protected $skipValidation   = false;  

    protected $deletedField  = 'val_excluido';
   
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
     * getVale
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getVale($val_id = false)
    {
        $db = db_connect('dbRh');
        $builder = $db->table($this->view);
        $builder->select('*'); 
        if($val_id){
            $builder->where("val_id", $val_id);
        }
        $builder->orderBy("col_nome");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);
        
        return $ret;

    }                 

    /**
     * getValeCol
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getValeCol($col_id = false)
    {
        $db = db_connect('dbRh');
        $builder = $db->table($this->view);
        $builder->select('*'); 
        $builder->whereIn("col_id", $col_id);
        $builder->orderBy("col_nome");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);
        
        return $ret;

    }                 

    /**
     * getValeColComp
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getValeColComp($col_id = false, $compete = false)
    {
        $db = db_connect('dbRh');
        $builder = $db->table($this->view);
        $builder->select('*'); 
        $builder->where("col_id", $col_id);
        $builder->where("val_mesanocompetencia", $compete);
        $builder->orderBy("col_nome");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);
        
        return $ret;

    }                 


    /**
     * getValeSearch
     *
     * Retorna os dados da Linha, pelo termo (nome) informado
     * Utilizado nas Seleções de Linha
     *  
     * @param mixed $termo 
     * @return array
     */
    public function getValeSearch($termo)
    {
        $db = db_connect('dbRh');
        $builder = $db->table($this->table);
        $builder->select('*'); 
		$builder->groupStart();
			$builder->like('col_nome', $termo);
		$builder->groupEnd();
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }                 
}