<?php namespace App\Models;
use CodeIgniter\Model;

class CommonModel extends Model
{
    protected $table            = 'setup_log';
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
}
