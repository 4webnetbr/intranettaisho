<?php namespace App\Models;
use CodeIgniter\Model;

class CommonModel extends Model
{
    protected $table            = 'cfg_log';
    protected $primaryKey       = 'log_id';
    protected $useAutoIncremodt = true;

    protected $returnType       = 'array';
    protected $allowedFields    = ['log_id',
                                    'log_tabela',
                                    'log_operacao',
                                    'log_id_registro',
                                    'log_id_usuario',
                                    'log_data'
                                ];
                                
    /**
     * insertReg
     *
     * Insere o Registro na Tabela informada
     *  
     * @param string $table 
     * @param mixed $data 
     * @return array
     */
    public function insertReg($table, $data){
        $insert_id = $this->db->insert($table, $data);
        
        return $insert_id;
    }

    /**
     * getFieldsTable
     *
     * Retorna os Campos da Tabela informada
     *  
     * @param mixed $table 
     * @return array
     */
    public function getFieldsTable($table){
        $fields = $this->db->getFieldData($table);     
        
        return $fields;
    }

    /**
     * insertLog
     *
     * Insere o Registro na Tabela de Log
     *  
     * @param string $tabela
     * @param string $operacao
     * @param int    $registro
     * @return int
     */
    public function insertLog($tabela, $operacao, $registro){
        $sql_data = [
            'log_tabela'        => $tabela,
            'log_operacao'      => $operacao,
            'log_id_registro'   => $registro,
            'log_id_usuario'    => session()->get('usu_id'),
            'log_data'          => date('Y-m-d H:i:s')
        ];
        $ins_id = $this->builder()->insert($sql_data);
        
        return $ins_id;
    }

    public function getPostsSearch($banco, $table, $fields, $limit, $start, $search, $col, $dir)
    {
        $db = db_connect($banco);
        $builder = $db->table($table);
        $builder->select($fields);
        if ($search != '') {
            $builder->like($fields[0], $search);
            for ($f = 1; $f < count($fields); $f++) {
                $builder->orLike($fields[$f], $search);
            }
        }
        $builder->limit($limit, $start);
        $builder->orderBy($col, $dir);

        $ret = $builder->get()->getResultArray();
        // $sql = $this->db->getLastQuery();
        // debug($sql, false);
        return $ret;
    }

    public function getPostsTotal($banco, $table, $fields)
    {
        $db = db_connect($banco);
        $builder = $db->table($table);
        $ret = $builder->countAll();

        // $ret = $builder->get()->getResultArray();
        // $sql = $this->db->getLastQuery();
        // debug($sql, true);

        return $ret;
    }

}
