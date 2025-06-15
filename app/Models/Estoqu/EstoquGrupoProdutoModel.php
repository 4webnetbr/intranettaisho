<?php 
namespace App\Models\Estoqu;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class EstoquGrupoProdutoModel extends Model
{
    protected $DBGroup          = 'dbEstoque';

    protected $table            = 'est_grupoproduto';
    protected $view             = 'est_grupoproduto';
    protected $primaryKey       = 'gru_id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields    = ['gru_id',
                                    'gru_nome',
                                    'gru_controlaestoque'
                                ];

                                
    protected $skipValidation   = true;  

    protected $deletedField  = 'gru_excluido';
   
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
     * getGrupoProduto
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getGrupoProduto($gru_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('est_grupoproduto');
        $builder->select('*'); 
        if($gru_id){
            $builder->where("gru_id", $gru_id);
        }
        $builder->orderBy("gru_nome");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);
        
        return $ret;

    }                 

    /**
     * getGrupoProdutoSearch
     *
     * Retorna os dados da Linha, pelo termo (nome) informado
     * Utilizado nas Seleções de Linha
     *  
     * @param mixed $termo 
     * @return array
     */
    public function getGrupoProdutoSearch($termo)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('est_grupoproduto');
        $builder->select('*'); 
		$builder->groupStart();
			$builder->like('gru_nome', $termo);
		$builder->groupEnd();
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }                 
}
?>