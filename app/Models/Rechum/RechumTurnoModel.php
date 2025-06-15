<?php 
namespace App\Models\Rechum;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class RechumTurnoModel extends Model
{
    protected $DBGroup          = 'dbRh';

    protected $table            = 'rh_turno';
    protected $view             = 'vw_rh_turno_relac';
    protected $primaryKey       = 'tur_id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields    = [ 'tur_id',
                                    'emp_id',
                                    'tur_nome',
                                    'tur_inicio',
                                    'tur_final',
                                ];

                                
    protected $skipValidation   = false;  
    protected $validationRules = [
        'tur_nome'=>'required|is_unique[rh_turno.tur_nome, tur_id,{tur_id}]',
    ];

    protected $validationMessages = [
        'tur_nome' => [
            'required' => 'O Nome do Turno é Obrigatório.',
            'is_unique' =>  'Esse Turno, já está cadastrado'
        ],

    ];

    protected $deletedField  = 'tur_excluido';
   
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
     * getTurno
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getTurno($tur_id = false)
    {
        $db = db_connect('dbRh');
        $builder = $db->table($this->view);
        $builder->select('*'); 
        if($tur_id){
            $builder->where("tur_id", $tur_id);
        }
        $builder->orderBy("tur_nome");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);
        
        return $ret;

    }                 

    /**
     * getTurnoEmp
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getTurnoEmp($emp_id = false)
    {
        $db = db_connect('dbRh');
        $builder = $db->table($this->view);
        $builder->select('*'); 
        $builder->where("emp_id", $emp_id);
        $builder->orderBy("tur_nome");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);
        
        return $ret;

    }                 

    /**
     * getTurnoSearch
     *
     * Retorna os dados da Linha, pelo termo (nome) informado
     * Utilizado nas Seleções de Linha
     *  
     * @param mixed $termo 
     * @return array
     */
    public function getTurnoSearch($termo)
    {
        $db = db_connect('dbRh');
        $builder = $db->table($this->view);
        $builder->select('*'); 
		$builder->groupStart();
			$builder->like('tur_nome', $termo);
		$builder->groupEnd();
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }                 
}