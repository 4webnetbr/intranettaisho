<?php 
namespace App\Models\Rechum;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class RechumFuncaoModel extends Model
{
    protected $DBGroup          = 'dbRh';

    protected $table            = 'rh_funcao';
    protected $view             = 'rh_funcao';
    protected $primaryKey       = 'fun_id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields    = [ 'fun_id',
                                    'fun_nome',
                                ];

                                
    protected $skipValidation   = false;  
    protected $validationRules = [
        'fun_nome'=>'required|is_unique[rh_funcao.fun_nome, fun_id,{fun_id}]',
    ];

    protected $validationMessages = [
        'fun_nome' => [
            'required' => 'O Nome da Função é Obrigatório.',
            'is_unique' =>  'Essa Função, já está cadastrado'
        ],

    ];

    protected $deletedField  = 'fun_excluido';
   
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
     * getFuncao
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getFuncao($fun_id = false)
    {
        $db = db_connect('dbRh');
        $builder = $db->table($this->table);
        $builder->select('*'); 
        if($fun_id){
            $builder->where("fun_id", $fun_id);
        }
        $builder->orderBy("fun_nome");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);
        
        return $ret;

    }                 


    /**
     * getFuncaoSearch
     *
     * Retorna os dados da Linha, pelo termo (nome) informado
     * Utilizado nas Seleções de Linha
     *  
     * @param mixed $termo 
     * @return array
     */
    public function getFuncaoSearch($termo)
    {
        $db = db_connect('dbRh');
        $builder = $db->table($this->table);
        $builder->select('*'); 
		$builder->groupStart();
			$builder->like('fun_nome', $termo);
		$builder->groupEnd();
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }                 
}