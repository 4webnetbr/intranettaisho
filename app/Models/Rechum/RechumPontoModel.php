<?php 
namespace App\Models\Rechum;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class RechumPontoModel extends Model
{
    protected $DBGroup          = 'dbRh';

    protected $table            = 'rh_ponto';
    protected $view             = 'vw_rh_ponto_lista_relac';
    protected $viewfolha        = 'vw_rh_ponto_folha_relac';
    protected $viewresumo       = 'vw_rh_ponto_resumo_relac';
    protected $primaryKey       = 'pon_id';
    protected $useAutoIncrement = true;

    protected $useSoftDeletes   = false;

    protected $allowedFields    = [ 'pon_id',
                                    'pon_competencia',
                                    'emp_id',
                                    'col_id',
                                    'pon_data',
                                    'pon_ent1',
                                    'pon_sai1',
                                    'pon_ent2',
                                    'pon_sai2',
                                    'pon_normais',
                                    'pon_faltas',
                                    'pon_noturnas',
                                    'pon_bancodeb',
                                    'pon_bancocre',
                                    'pon_bancototal',
                                    'pon_bancosaldo',
                                ];

                                
    protected $returnType = 'App\Entities\Ponto';
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
     * getPonto
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getPonto($pon_id = false, $emp_id = false)
    {
        $db = db_connect('dbRh');
        $builder = $db->table($this->view);
        $builder->select('*'); 
        if($pon_id){
            $builder->where("pon_id", $pon_id);
        }
        if($emp_id){
            $builder->where("emp_id", $emp_id);
        }
        $builder->orderBy("pon_competencia, emp_id, col_nome");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);
        
        return $ret;

    }                 

    /**
     * getListaPonto
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getListaPonto($colab = false, $compet = false, $pon_id = false)
    {
        $db = db_connect('dbRh');
        $builder = $db->table($this->viewfolha);
        $builder->select('*'); 
        if($colab){
            $builder->where("col_id", $colab);
        }
        if($compet){
            $builder->where("pon_mesanocompetencia", $compet);
        }
        if($pon_id){
            $builder->where("pon_id", $pon_id);
        }

        $builder->orderBy("pon_mesanocompetencia, emp_id, col_nome, pon_id");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);
        
        return $ret;

    }                 

    /**
     * getResumoPonto
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getResumoPonto($colab = false, $compet = false, $empresa = false)
    {
        $db = db_connect('dbRh');
        $builder = $db->table($this->viewresumo);
        $builder->select('*'); 
        if($colab){
            $builder->where("col_id", $colab);
        }
        if($compet){
            $builder->where("pon_mesanocompetencia", $compet);
        }
        if($empresa){
            $builder->where("emp_id", $empresa);
        }
        $builder->orderBy("pon_mesanocompetencia, emp_id, col_nome");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);
        
        return $ret;

    }                 

    /**
     * getPontoUnico
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getPontoUnico($emp_id, $col_id, $competencia, $data)
    {
        $db = db_connect('dbRh');
        $builder = $db->table($this->table);
        $builder->select('*'); 
        $builder->where("emp_id", $emp_id);
        $builder->where("col_id", $col_id);
        $builder->where("pon_competencia", $competencia);
        $builder->where("pon_data", $data);
        $builder->orderBy("pon_competencia, emp_id");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);
        
        return $ret;

    }                 


    /**
     * getPontoSearch
     *
     * Retorna os dados da Linha, pelo termo (nome) informado
     * Utilizado nas Seleções de Linha
     *  
     * @param mixed $termo 
     * @return array
     */
    public function getPontoSearch($termo)
    {
        $db = db_connect('dbRh');
        $builder = $db->table($this->table);
        $builder->select('*'); 
		$builder->groupStart();
			$builder->like('hol_competencia', $termo);
		$builder->groupEnd();
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }                 
}