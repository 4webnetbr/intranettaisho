<?php namespace App\Models\Setup;
use CodeIgniter\Model;

class SetupDicDadosModel extends Model
{
    protected $table            = 'information_schema.tables';
    protected $primaryKey       = 'table_name';
    protected $returnType       = 'array';

    protected $allowedFields    = ['table_name',
                                    'table_rows',
                                    'table_comment',
                                ];


                                
    public function getTabelas($nome = false)
    {
        $this->builder()
                ->select(['table_name','table_rows','table_comment']);
    
        if ($nome) {
            $this->builder()->where('table_name', $nome);
        }
        $this->builder()->where('table_schema', 'estoque_db');
        $this->builder()->orderBy('table_name', 'ASC');
        $ret = $this->builder()->get()->getResultArray();
        
        // $sql = $this->builder()->getCompiledSelect();
        // echo $sql;

        return $ret;
    }                 

    public function getTabelaSearch($termo)
    {
        $array = ['table_name' => $termo.'%'];
        $this->builder()
                ->select(['table_name','table_rows','table_comment'])
                ->like($array);
    
        $this->builder()->where('table_schema', 'estoque_db');
        $this->builder()->orderBy('table_name', 'ASC');
        // $sql = $this->builder()->getCompiledSelect();
        // echo $sql;

        return $this->builder()->get()->getResultArray();
    }                 

    public function getRelacionamentos($nome_tabela){
        $array = ['kc.table_name' => $nome_tabela];
        $db = db_connect();
        $builder = $db->table('information_schema.KEY_COLUMN_USAGE kc');
        $builder->select('CONSTRAINT_NAME, 
        kc.TABLE_NAME, 
        kc.COLUMN_NAME, 
        kc.REFERENCED_TABLE_NAME, 
        kc.REFERENCED_COLUMN_NAME,
        tb.TABLE_COMMENT');
        $builder->join('information_schema.TABLES tb', 'tb.TABLE_NAME = kc.REFERENCED_TABLE_NAME','inner');
        // $builder->join('information_schema.columns col', 'col.table_name = kc.TABLE_NAME AND col.COLUMN_NAME = kc.COLUMN_NAME','inner');
        $builder->where($array);
        $builder->where('kc.table_schema', 'estoque_db');
        $builder->where('REFERENCED_TABLE_SCHEMA IS NOT NULL');
        $ret = $builder->get()->getResultArray();
        // $sql = $this->db->getLastQuery();
        // echo $sql;

        return $ret;
    }

    public function getCampos($nome_tabela)
    {
        $array = ['table_name' => $nome_tabela];
        $db = db_connect();
        $builder = $db->table('information_schema.columns');
        $builder->select('TABLE_NAME, COLUMN_NAME, IS_NULLABLE, DATA_TYPE, COALESCE(`CHARACTER_MAXIMUM_LENGTH`,NUMERIC_PRECISION) AS COLUMN_SIZE, COLUMN_COMMENT, COLUMN_KEY');
        $builder->where($array);
        $builder->where('table_schema', 'estoque_db');
        $ret = $builder->get()->getResultArray();
        // $sql = $this->db->getLastQuery();
        // echo $sql;

        return $ret;
    }
    
}
