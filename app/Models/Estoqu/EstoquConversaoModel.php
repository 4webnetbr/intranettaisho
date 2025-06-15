<?php namespace App\Models\Estoqu;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class EstoquConversaoModel extends Model
{
    protected $DBGroup          = 'dbEstoque';
    protected $table            = 'est_conversao';
    protected $primaryKey       = 'cvs_id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = ['cvs_id',
                                    'cvs_id_origem',
                                    'cvs_id_destino',
                                    'cvs_operador',
                                    'cvs_fator',
                                    'cvs_atualizado'
                                ];

                                
    protected $skipValidation   = true;  

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
     * getConversao
     *
     * Retorna a tabela de conversoes da medida
     * 
     * @param bool $id 
     * @return array
     */
    public function getConversao($cvs_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('est_conversao');
        $builder->select('*'); 
        if($cvs_id){
            $builder->where("cvs_id_origem", $cvs_id);
        }
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);
        
        return $ret;

    }                 

    /**
     * getFatorConversao
     *
     * Retorna o Fator de conversoes das medidas informadas
     * 
     * @param bool $id 
     * @return array
     */
    public function getFatorConversao($origem, $destino)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('est_conversao');
        $builder->select('*'); 
        $builder->where("cvs_id_origem", $origem);
        $builder->where("cvs_id_destino", $destino);
        $ret = $builder->get()->getResultArray();

        return $ret;

    }                 

    /**
     * exclui
     *
     * Retorna a tabela de conversoes da medida
     * 
     * @param string $data 
     * @return string
     */
    public function exclui($origem, $data_atu)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('est_conversao');
        $builder->where("cvs_id_origem",$origem);
        $builder->groupStart();
        $builder->where("cvs_atualizado != '$data_atu'");
        $builder->orWhere("cvs_atualizado IS NULL");
        $builder->groupEnd();
        $ret = $builder->delete();
        // debug($this->db->getLastQuery(), false);
        return $ret;

    }                 
}
?>