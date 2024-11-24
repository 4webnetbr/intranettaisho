<?php 
namespace App\Models\Rechum;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class RechumHoleriteItemModel extends Model
{
    protected $DBGroup          = 'dbRh';

    protected $table            = 'rh_holerite_item';
    protected $view             = 'rh_holerite_item';
    protected $primaryKey       = 'hoit_id';
    protected $useAutoIncrement = true;

    protected $useSoftDeletes   = false;

    protected $allowedFields    = [ 'hoit_id',
                                    'hol_id',
                                    'hoit_cod',
                                    'hoit_descricao',
                                    'hoit_valor',
                                    'hoit_valortotal',
                                    'hoit_tipo',
                                    ];

                                
    protected $returnType = 'App\Entities\HoleriteItem';
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
     * getHoleriteItem
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getHoleriteItem($hol_id = false)
    {
        $db = db_connect('dbRh');
        $builder = $db->table($this->view);
        $builder->select('*'); 
        if($hol_id){
            $builder->where("hol_id", $hol_id);
        }
        // $builder->orderBy("hol_competencia, emp_id, col_nome");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);
        
        return $ret;

    }                 

    /**
     * getHoleriteItemUnico
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getHoleriteItemUnico($holerite, $codigo)
    {
        $db = db_connect('dbRh');
        $builder = $db->table($this->view);
        $builder->select('*'); 
        $builder->where("hol_id", $holerite);
        $builder->where("hoit_cod", $codigo);
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);
        
        return $ret;

    }                 



}