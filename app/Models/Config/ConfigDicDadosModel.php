<?php

namespace App\Models\Config;

use CodeIgniter\Model;

class ConfigDicDadosModel extends Model
{
    protected $DBGroup          = 'dbConfig';

    protected $table            = 'information_schema.tables';
    protected $primaryKey       = 'table_name';
    protected $returnType       = 'array';

    protected $allowedFields    = ['table_name',
                                    'table_rows',
                                    'table_comment',
                                ];

    public function getTabelas($nome = false, $grupo = 'default')
    {
        $DBGroup          = 'default';

        $this->builder()
                ->select(['table_schema','table_name','table_rows','table_comment']);

        if ($nome) {
            $this->builder()->where('table_name', $nome);
        }
        $this->builder()->where('table_schema', 'estoque_db');
        $this->builder()->orderBy('table_name', 'ASC');
        $ret = $this->builder()->get()->getResultArray();

        $DBGroup          = 'dbConfig';
        $this->builder()
                ->select(['table_schema','table_name','table_rows','table_comment']);

        if ($nome) {
            $this->builder()->where('table_name', $nome);
        }
        $this->builder()->where('table_schema', 'config_ceqweb_db');
        $this->builder()->orderBy('table_name', 'ASC');
        $ret2 = $this->builder()->get()->getResultArray();
        foreach ($ret2 as $reg) {
            array_push($ret, $reg);
        }
        return $ret;
    }

    public function getTabelaSearch($termo)
    {
        $DBGroup          = 'default';

        $array = ['table_name' => $termo . '%'];
        $this->builder()
                ->select(['table_name','table_rows','table_comment'])
                ->like($array);

        $this->builder()->where('table_schema', 'estoque_db');
        $this->builder()->orderBy('table_name', 'ASC');
        $ret = $this->builder()->get()->getResultArray();
        if (count($ret) == 0) {
            $DBGroup          = 'dbConfig';

            $array = ['table_name' => $termo . '%'];
            $this->builder()
                    ->select(['table_name','table_rows','table_comment'])
                    ->like($array);

            $this->builder()->where('table_schema', 'config_ceqweb_db');
            $this->builder()->orderBy('table_name', 'ASC');
            $ret = $this->builder()->get()->getResultArray();
        }
        return $ret;
    }

    public function getRelacionamentos($nome_tabela)
    {
        $DBGroup          = 'default';

        $array = ['kc.table_name' => $nome_tabela];
        // $db = db_connect();
        $builder = $this->builder('information_schema.KEY_COLUMN_USAGE kc');
        $builder->select('CONSTRAINT_NAME, 
                            kc.TABLE_NAME, 
                            kc.COLUMN_NAME, 
                            kc.REFERENCED_TABLE_NAME, 
                            kc.REFERENCED_COLUMN_NAME,
                            tb.TABLE_COMMENT');
        $builder->join('information_schema.TABLES tb', 'tb.TABLE_NAME = kc.REFERENCED_TABLE_NAME', 'inner');
        $builder->where($array);
        $builder->where('kc.table_schema', 'estoque_db');
        $builder->where('REFERENCED_TABLE_SCHEMA IS NOT NULL');
        $ret = $builder->get()->getResultArray();
        if (count($ret) == 0) {
            $DBGroup          = 'dbConfig';

            $array = ['kc.table_name' => $nome_tabela];
            // $db = db_connect();
            // $builder = $db->table('information_schema.KEY_COLUMN_USAGE kc');
            $this->builder('information_schema.KEY_COLUMN_USAGE kc');
            $builder->select('CONSTRAINT_NAME, 
                                kc.TABLE_NAME, 
                                kc.COLUMN_NAME, 
                                kc.REFERENCED_TABLE_NAME, 
                                kc.REFERENCED_COLUMN_NAME,
                                tb.TABLE_COMMENT');
            $builder->join('information_schema.TABLES tb', 'tb.TABLE_NAME = kc.REFERENCED_TABLE_NAME', 'inner');
            $builder->where($array);
            $builder->where('kc.table_schema', 'config_ceqweb_db');
            $builder->where('REFERENCED_TABLE_SCHEMA IS NOT NULL');
            $ret = $builder->get()->getResultArray();
        }
        return $ret;
    }

    public function getCampos($nome_tabela)
    {
        $DBGroup          = 'default';

        $array = ['table_name' => $nome_tabela];
        $builder = $this->builder('information_schema.columns');
        $builder->select('TABLE_NAME, 
                                COLUMN_NAME, 
                                IS_NULLABLE, DATA_TYPE, 
                                COALESCE(`CHARACTER_MAXIMUM_LENGTH`,NUMERIC_PRECISION) AS COLUMN_SIZE, 
                                COLUMN_COMMENT, 
                                COLUMN_KEY, 
                                CONCAT(COLUMN_COMMENT," - ",COLUMN_NAME) AS NOME_COMPLETO');
        $builder->where($array);
        $builder->where('table_schema', 'estoque_db');
        // $sql = $builder->getCompiledSelect();
        $ret = $builder->get()->getResultArray();
        if (count($ret) == 0) {
            $DBGroup          = 'dbConfig';

            $array = ['table_name' => $nome_tabela];
            $builder = $this->builder('information_schema.columns');
            $builder->select('TABLE_NAME, 
                                    COLUMN_NAME, 
                                    IS_NULLABLE, 
                                    DATA_TYPE, 
                                    COALESCE(`CHARACTER_MAXIMUM_LENGTH`,NUMERIC_PRECISION) AS COLUMN_SIZE, 
                                    COLUMN_COMMENT, 
                                    COLUMN_KEY, 
                                    CONCAT(COLUMN_COMMENT," - ",COLUMN_NAME) AS NOME_COMPLETO');
            $builder->where($array);
            $builder->where('table_schema', 'config_ceqweb_db');
            $ret = $builder->get()->getResultArray();
        }
        return $ret;
    }

    public function getDetalhesCampo($nome_tabela, $nome_campo)
    {
        $DBGroup          = 'default';
        $array = ['table_name' => $nome_tabela];
        $builder = $this->builder('information_schema.columns');
        $builder->select('TABLE_NAME, 
                                COLUMN_NAME, 
                                IS_NULLABLE, 
                                DATA_TYPE, 
                                COALESCE(`CHARACTER_MAXIMUM_LENGTH`,NUMERIC_PRECISION) AS COLUMN_SIZE, 
                                NUMERIC_SCALE, 
                                COLUMN_COMMENT, 
                                COLUMN_KEY');
        $builder->where($array);
        if (gettype($nome_campo) == 'array') {
            $builder->whereIn('column_name', $nome_campo);
        } else {
            $builder->where('column_name', $nome_campo);
        }
        $builder->where('table_schema', 'estoque_db');
        $ret = $builder->get()->getResultArray();
        if (count($ret) == 0) {
            $DBGroup          = 'dbConfig';
            $array = ['table_name' => $nome_tabela];
            // $array2 = "'column_name' in [$nome_campo]";
            // $db = db_connect();
            $builder = $this->builder('information_schema.columns');
            $builder->select('TABLE_NAME, 
                                    COLUMN_NAME, 
                                    IS_NULLABLE, 
                                    DATA_TYPE, 
                                    COALESCE(`CHARACTER_MAXIMUM_LENGTH`,NUMERIC_PRECISION) AS COLUMN_SIZE, 
                                    NUMERIC_SCALE, 
                                    COLUMN_COMMENT, 
                                    COLUMN_KEY');
            $builder->where($array);
            if (gettype($nome_campo) == 'array') {
                $builder->whereIn('column_name', $nome_campo);
            } else {
                $builder->where('column_name', $nome_campo);
            }
            $builder->where('table_schema', 'config_ceqweb_db');
            $ret = $builder->get()->getResultArray();
        }
        return $ret;
    }

    public function getCampoChave($nome_tabela)
    {
        $DBGroup          = 'default';

        $array = ['table_name' => $nome_tabela];
        // $db = db_connect();
        $builder = $this->builder('information_schema.columns');
        $builder->select('TABLE_NAME, COLUMN_NAME, 
                                IS_NULLABLE, 
                                DATA_TYPE, 
                                COALESCE(`CHARACTER_MAXIMUM_LENGTH`, NUMERIC_PRECISION) AS COLUMN_SIZE, 
                                COLUMN_COMMENT, 
                                COLUMN_KEY');
        $builder->where($array);
        $builder->where('COLUMN_KEY', 'PRI');
        $builder->where('table_schema', 'estoque_db');
        $ret = $builder->get()->getResultArray();
        if (count($ret) == 0) {
            $DBGroup          = 'dbConfig';
            $array = ['table_name' => $nome_tabela];
            $builder = $this->builder('information_schema.columns');
            $builder->select('TABLE_NAME, COLUMN_NAME, 
                                    IS_NULLABLE, 
                                    DATA_TYPE, 
                                    COALESCE(`CHARACTER_MAXIMUM_LENGTH`, NUMERIC_PRECISION) AS COLUMN_SIZE, 
                                    COLUMN_COMMENT, 
                                    COLUMN_KEY');
            $builder->where($array);
            $builder->where('COLUMN_KEY', 'PRI');
            $builder->where('table_schema', 'config_ceqweb_db');
            $ret = $builder->get()->getResultArray();
        }

        return $ret;
    }
}
