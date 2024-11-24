<?php 
namespace App\Models\Rechum;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class RechumCargoModel extends Model
{
    protected $DBGroup          = 'dbRh';

    protected $table            = 'rh_cargo';
    protected $view             = 'vw_rh_cargos_relac';
    protected $primaryKey       = 'cag_id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'App\Entities\Cargo';
    protected $useSoftDeletes   = true;

    protected $allowedFields    = [ 'cag_id',
                                    'set_id',
                                    'cag_nome',
                                    'cag_cbo',
                                    'cag_participacao',
                                    'cag_descricao',
                                ];

                                
    protected $skipValidation   = false;  
    protected $validationRules = [
        'cag_nome'=>'required|is_unique[rh_cargo.cag_nome, cag_id,{cag_id}]',
    ];

    protected $validationMessages = [
        'cag_nome' => [
            'required' => 'O Nome da Função é Obrigatório.',
            'is_unique' =>  'Essa Função, já está cadastrado'
        ],

    ];

    protected $deletedField  = 'cag_excluido';
   
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
     * getCargo
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getCargo($cag_id = false, $set_id = false)
    {
        $db = db_connect('dbRh');
        $builder = $db->table($this->view);
        $builder->select('*'); 
        if($cag_id){
            $builder->where("cag_id", $cag_id);
        }
        if($set_id){
            $builder->where("set_id", $set_id);
        }
        $builder->orderBy("cag_nome");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);
        
        return $ret;

    }                 


    /**
     * getCargo
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getCargoSetor($cag_id = false, $set_id = false)
    {
        $db = db_connect('dbRh');
        $builder = $db->table($this->view);
        $builder->select('*'); 
        if($cag_id){
            $builder->where("cag_id", $cag_id);
        }
        if($set_id){
            $builder->where("set_id", $set_id);
        }
        // $builder->groupBy("set_id");
        $builder->orderBy("set_nome, cag_nome");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);
        
        return $ret;

    }                 
    
    /**
     * getCargoSearch
     *
     * Retorna os dados da Linha, pelo termo (nome) informado
     * Utilizado nas Seleções de Linha
     *  
     * @param mixed $termo 
     * @return array
     */
    public function getCargoSearch($termo)
    {
        $db = db_connect('dbRh');
        $builder = $db->table($this->table);
        $builder->select('*'); 
		$builder->groupStart();
			$builder->like('cag_nome', $termo);
		$builder->groupEnd();
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }                 
}