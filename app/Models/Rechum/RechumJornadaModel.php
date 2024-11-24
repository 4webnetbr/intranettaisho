<?php 
namespace App\Models\Rechum;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class RechumJornadaModel extends Model
{
    protected $DBGroup          = 'dbRh';

    protected $table            = 'rh_jornada';
    protected $view             = 'vw_rh_jornada_relac';
    protected $primaryKey       = 'jor_id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'App\Entities\Jornada';
    protected $useSoftDeletes   = true;

    protected $allowedFields    = [ 'jor_id',
                                    'emp_id',
                                    'jor_competencia',
                                    'col_id',
                                    'jor_dia',
                                    'jor_ent1',
                                    'jor_sai1',
                                    'jor_ent2',
                                    'jor_sai2',
                                    'jor_ent3',
                                    'jor_sai3',
    
    
                                ];

                                
    protected $skipValidation   = false;  
    protected $validationRules = [
        'jor_nome'=>'required|is_unique[rh_jornada.jor_nome, jor_id,{jor_id}]',
    ];

    protected $validationMessages = [
        'jor_nome' => [
            'required' => 'O Nome do Jornada é Obrigatório.',
            'is_unique' =>  'Esse Jornada, já está cadastrado'
        ],

    ];

    protected $deletedField  = 'jor_excluido';
   
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
     * getJornada
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getJornada($jor_id = false)
    {
        $db = db_connect('dbRh');
        $builder = $db->table($this->view);
        $builder->select('*'); 
        if($jor_id){
            $builder->where("jor_id", $jor_id);
        }
        $builder->orderBy("jor_dia");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);
        
        return $ret;

    }                 

    /**
     * getJornadaEmp
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getJornadaEmp($emp_id = false)
    {
        $db = db_connect('dbRh');
        $builder = $db->table($this->view);
        $builder->select('*'); 
        $builder->where("emp_id", $emp_id);
        $builder->orderBy("jor_dia");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);
        
        return $ret;

    }                 

    /**
     * getJornadaUnica
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getJornadaUnica($empresa, $competencia, $colaborador)
    {
        $db = db_connect('dbRh');
        $builder = $db->table($this->view);
        $builder->select('*'); 
        $builder->where("emp_id", $empresa);
        $builder->where("jor_competencia", $competencia);
        $builder->where("col_id", $colaborador);
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);
        
        return $ret;

    }                 
    
    /**
     * getJornadaSearch
     *
     * Retorna os dados da Linha, pelo termo (nome) informado
     * Utilizado nas Seleções de Linha
     *  
     * @param mixed $termo 
     * @return array
     */
    public function getJornadaSearch($termo)
    {
        $db = db_connect('dbRh');
        $builder = $db->table($this->view);
        $builder->select('*'); 
		$builder->groupStart();
			$builder->like('jor_nome', $termo);
		$builder->groupEnd();
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }                 
}