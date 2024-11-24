<<<<<<< HEAD
<?php 
namespace App\Models\Estoqu;

=======
<?php

namespace App\Models\Estoqu;

use App\Libraries\MyCampo;
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7
use App\Models\LogMonModel;
use CodeIgniter\Model;

class EstoquDepositoModel extends Model
{
    protected $DBGroup          = 'dbEstoque';
<<<<<<< HEAD

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
=======
    protected $table            = 'est_sap_deposito';
    protected $view             = 'est_sap_deposito';
    protected $primaryKey       = 'dep_codDep';
    // protected $useAutoIncremodt = false;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [ 'dep_codDep',
                                    'dep_desDep',
                                    'dep_aceNeg',
                                    'dep_codDescricao',
    
    
                                ];

    // Callbacks
    protected $allowCallbacks = true;

>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7
    protected $afterInsert   = ['depoisInsert'];
    protected $afterUpdate   = ['depoisUpdate'];
    protected $afterDelete   = ['depoisDelete'];

    protected $logdb;

<<<<<<< HEAD
/**
     * This method saves the session "usu_id" value to "created_by" and "updated_by" array 
     * elements before the row is inserted into the database.
     *
     */
    protected function depoisInsert(array $data) {
        $logdb = new LogMonModel(); 
        $registro = $data['id'];
        $log = $logdb->insertLog($this->table,'Incluído',$registro, $data['data']);
=======
    /**
     * This method saves the session "usu_id" value to "created_by" and "updated_by" array
     * elements before the row is inserted into the database.
     *
     */
    protected function depoisInsert(array $data)
    {
        $logdb = new LogMonModel();
        $registro = $data['id'];
        $log = $logdb->insertLog($this->table, 'Incluído', $registro, $data['data']);
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7
        return $data;
    }

    /**
<<<<<<< HEAD
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
            $builder->where("dep_id", $dep_id);
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
=======
     * This method saves the session "usu_id" value to "updated_by" array element before
     * the row is inserted into the database.
     *
     */
    protected function depoisUpdate(array $data)
    {
        $logdb = new LogMonModel();
        $registro = $data['id'][0];
        $log = $logdb->insertLog($this->table, 'Alteração', $registro, $data['data']);
        return $data;
    }

    /**
     * This method saves the session "usu_id" value to "deletede_by" array element before
     * the row is inserted into the database.
     *
     */
    protected function depoisDelete(array $data)
    {
        $logdb = new LogMonModel();
        $registro = $data['id'][0];
        $log = $logdb->insertLog($this->table, 'Excluído', $registro, $data['data']);
        return $data;
    }

    public function getDeposito($dep_id = false)
    {
        $this->builder()->select('*');
        if ($dep_id) {
            $this->builder()->where('dep_codDep', $dep_id);
        }
        return $this->builder()->get()->getResultArray();
    }

    public function getTransacaoSearch($termo)
    {
        $array = ['dep_desDep' => $termo . '%'];
        $this->builder()->select('*');
        $this->builder()->like($array);

        return $this->builder()->get()->getResultArray();
    }

    public function defCampos($dados = false, $show = false)
    {
        $cdep           =  new MyCampo('est_sap_deposito', 'dep_codDep', true);
        $cdep->valor    = (isset($dados['dep_codDep'])) ? $dados['dep_codDep'] : '';
        $cdep->obrigatorio = true;
        $cdep->leitura  = $show;
        $ret['dep_codDep'] = $cdep->crInput();

        $ddep           =  new MyCampo('est_sap_deposito', 'dep_desDep');
        $ddep->valor    = (isset($dados['dep_desDep'])) ? $dados['dep_desDep'] : '';
        $ddep->obrigatorio = true;
        $ddep->leitura  = $show;
        $ret['dep_desDep'] = $ddep->crInput();

        $acng           =  new MyCampo('est_sap_deposito', 'dep_aceNeg');
        $acng->valor    = (isset($dados['dep_aceNeg'])) ? $dados['dep_aceNeg'] : '';
        $acng->obrigatorio = true;
        $acng->leitura  = $show;
        $ret['dep_aceNeg'] = $acng->crInput();
        
        $cdes           =  new MyCampo('est_sap_deposito', 'dep_codDescricao');
        $cdes->valor    = (isset($dados['dep_codDescricao'])) ? $dados['dep_codDescricao'] : '';
        $cdes->obrigatorio = true;
        $cdes->leitura  = $show;
        $ret['dep_codDescricao'] = $cdes->crInput();
        
        return $ret;
    }    

}
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7
