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

    public function getTabelas($nome_tabela = false, $grupo = 'default')
    {
        $this->DBGroup          = 'dbEstoque';
        $this->builder()
                ->select(['table_schema','table_name','table_rows','table_comment']);

        if ($nome_tabela) {
            $this->builder()->where('table_name', $nome_tabela);
        }
        $this->builder()->where('table_schema', 'taisho_estoquedb');
        $this->builder()->orderBy('table_name', 'ASC');
        $ret = $this->builder()->get()->getResultArray();

        $this->DBGroup          = 'dbRh';
        $this->builder()
                ->select(['table_schema','table_name','table_rows','table_comment']);

        if ($nome_tabela) {
            $this->builder()->where('table_name', $nome_tabela);
        }
        $this->builder()->where('table_schema', 'taisho_rhdb');
        $this->builder()->orderBy('table_name', 'ASC');

        $retRh = $this->builder()->get()->getResultArray();
        foreach ($retRh as $reg) {
            array_push($ret, $reg);
        }

        $this->DBGroup          = 'default';

        $this->builder()
                ->select(['table_schema','table_name','table_rows','table_comment']);

        if ($nome_tabela) {
            $this->builder()->where('table_name', $nome_tabela);
        }
        $this->builder()->where('table_schema', 'taisho_gerentedb');
        $this->builder()->orderBy('table_name', 'ASC');

        $retDef = $this->builder()->get()->getResultArray();
        foreach ($retDef as $reg) {
            array_push($ret, $reg);
        }

        $this->DBGroup          = 'dbConfig';
        $this->builder()
                ->select(['table_schema','table_name','table_rows','table_comment']);

        if ($nome_tabela) {
            $this->builder()->where('table_name', $nome_tabela);
        }
        $this->builder()->where('table_schema', 'taisho_configdb');
        $this->builder()->orderBy('table_name', 'ASC');
        $retCfg = $this->builder()->get()->getResultArray();
        foreach ($retCfg as $reg) {
            array_push($ret, $reg);
        }
        return $ret;
    }

    public function getTabelaSearch($nome_tabela)
    {
        if(substr($nome_tabela,0,3) == 'est' || 
            substr($nome_tabela,0,6) == 'vw_est') {
            $this->DBGroup          = 'dbEstoque';
            $schema                 = 'taisho_estoquedb';
        } else if(substr($nome_tabela,0,2) == 'rh' || 
            substr($nome_tabela,0,5) == 'vw_rh') {
            $this->DBGroup          = 'dbRh';
            $schema                 = 'taisho_rhdb';
        } else if(substr($nome_tabela,0,3) == 'ger' || 
            substr($nome_tabela,0,3) == 'cfy' ||
            substr($nome_tabela,0,6) == 'vw_tax' ||
            substr($nome_tabela,0,6) == 'vw_tot' ||
            substr($nome_tabela,0,6) == 'vw_ger') {
            $this->DBGroup          = 'default';
            $schema                 = 'taisho_gerentedb';
        } else if(substr($nome_tabela,0,3) == 'cfg' || 
            substr($nome_tabela,0,6) == 'vw_cfg') {
            $this->DBGroup          = 'dbConfig';
            $schema                 = 'taisho_configdb';
        }
        $array = ['table_name' => $nome_tabela . '%'];
        $this->builder()
                ->select(['table_name','table_rows','table_comment'])
                ->like($array);

        $this->builder()->where('table_schema', $schema);
        $this->builder()->orderBy('table_name', 'ASC');

        $ret = $this->builder()->get()->getResultArray();
        return $ret;
    }

    public function getRelacionamentos($nome_tabela)
    {
        if(substr($nome_tabela,0,3) == 'est' || 
            substr($nome_tabela,0,6) == 'vw_est') {
            $this->DBGroup          = 'dbEstoque';
            $schema                 = 'taisho_estoquedb';
        } else if(substr($nome_tabela,0,2) == 'rh' || 
            substr($nome_tabela,0,5) == 'vw_rh') {
            $this->DBGroup          = 'dbRh';
            $schema                 = 'taisho_rhdb';
        } else if(substr($nome_tabela,0,3) == 'ger' || 
            substr($nome_tabela,0,3) == 'cfy' ||
            substr($nome_tabela,0,6) == 'vw_tax' ||
            substr($nome_tabela,0,6) == 'vw_tot' ||
            substr($nome_tabela,0,6) == 'vw_ger') {
            $this->DBGroup          = 'default';
            $schema                 = 'taisho_gerentedb';
        } else if(substr($nome_tabela,0,3) == 'cfg' || 
            substr($nome_tabela,0,6) == 'vw_cfg') {
            $this->DBGroup          = 'dbConfig';
            $schema                 = 'taisho_configdb';
        }
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
        $builder->where('kc.table_schema', $schema);
        $builder->where('REFERENCED_TABLE_SCHEMA IS NOT NULL');

        $ret = $builder->get()->getResultArray();
        return $ret;
    }

    public function getCampos($nome_tabela)
    {
        // debug($nome_tabela);
        if(substr($nome_tabela,0,3) == 'est' || 
            substr($nome_tabela,0,6) == 'vw_est') {
            $this->DBGroup          = 'dbEstoque';
            $schema                 = 'taisho_estoquedb';
        } else if(substr($nome_tabela,0,2) == 'rh' || 
            substr($nome_tabela,0,5) == 'vw_rh') {
            $this->DBGroup          = 'dbRh';
            $schema                 = 'taisho_rhdb';
        } else if(substr($nome_tabela,0,3) == 'ger' || 
            substr($nome_tabela,0,3) == 'cfy' ||
            substr($nome_tabela,0,6) == 'vw_tax' ||
            substr($nome_tabela,0,6) == 'vw_tot' ||
            substr($nome_tabela,0,6) == 'vw_ger') {
            $this->DBGroup          = 'default';
            $schema                 = 'taisho_gerentedb';
        } else if(substr($nome_tabela,0,3) == 'cfg' || 
            substr($nome_tabela,0,6) == 'vw_cfg') {
            $this->DBGroup          = 'dbConfig';
            $schema                 = 'taisho_configdb';
        }
        $db = db_connect($this->DBGroup);
        $query = $db->query("SELECT TABLE_NAME, 
                            COLUMN_NAME, 
                            IS_NULLABLE, 
                            DATA_TYPE, 
                            COALESCE(`CHARACTER_MAXIMUM_LENGTH`,NUMERIC_PRECISION) AS COLUMN_SIZE, 
                            NUMERIC_SCALE, 
                            COLUMN_COMMENT, 
                            COLUMN_KEY,
                            CONCAT(COLUMN_COMMENT,' - ',COLUMN_NAME) AS NOME_COMPLETO
                            FROM information_schema.columns
                            WHERE TABLE_NAME = '".$nome_tabela."'
                            AND TABLE_SCHEMA = '".$schema."'");
        $ret = $query->getResultArray();
        $lq = $query = $db->getLastQuery();
        // debug($lq);
        // debug($ret);
        return $ret;
    }

    public function getDetalhesCampo($nome_tabela, $nome_campo)
    {
        // debug($nome_tabela, $nome_campo);
        if(substr($nome_tabela,0,3) == 'est' || 
            substr($nome_tabela,0,6) == 'vw_est') {
            $this->DBGroup          = 'dbEstoque';
            $schema                 = 'taisho_estoquedb';
        } else if(substr($nome_tabela,0,2) == 'rh' || 
            substr($nome_tabela,0,5) == 'vw_rh') {
            $this->DBGroup          = 'dbRh';
            $schema                 = 'taisho_rhdb';
        } else if(substr($nome_tabela,0,3) == 'ger' || 
            substr($nome_tabela,0,3) == 'cfy' ||
            substr($nome_tabela,0,6) == 'vw_tax' ||
            substr($nome_tabela,0,6) == 'vw_tot' ||
            substr($nome_tabela,0,6) == 'vw_ger') {
            $this->DBGroup          = 'default';
            $schema                 = 'taisho_gerentedb';
        } else if(substr($nome_tabela,0,3) == 'cfg' || 
            substr($nome_tabela,0,6) == 'vw_cfg') {
            $this->DBGroup          = 'dbConfig';
            $schema                 = 'taisho_configdb';
        }
        $db = db_connect($this->DBGroup);
        $consulta = "SELECT TABLE_NAME, 
                            COLUMN_NAME, 
                            IS_NULLABLE, 
                            DATA_TYPE, 
                            COALESCE(`CHARACTER_MAXIMUM_LENGTH`,NUMERIC_PRECISION) AS COLUMN_SIZE, 
                            NUMERIC_SCALE, 
                            COLUMN_COMMENT, 
                            COLUMN_KEY,
                            CONCAT(COLUMN_COMMENT,' - ',COLUMN_NAME) AS NOME_COMPLETO
                            FROM information_schema.columns
                            WHERE TABLE_NAME = '".$nome_tabela."'
                            AND TABLE_SCHEMA = '".$schema."' ";
        if (gettype($nome_campo) == 'array') {
            for($c=0;$c<count($nome_campo);$c++){
                $nome_campo[$c] = "'".$nome_campo[$c]."'";
            }
            $campos = implode(",", $nome_campo);
            $consulta .= "AND column_name IN (".$campos.") ";
        } else {
            $consulta .= "AND column_name = '".$nome_campo."' ";
        }
                        
        $query = $db->query($consulta);
        // $lq = $db->getLastQuery();
        // debug($lq, true);
        $ret = $query->getResultArray();
        // debug($ret);
        return $ret;
    }

    public function getCampoChave($nome_tabela)
    {
        if(substr($nome_tabela,0,3) == 'est' || 
            substr($nome_tabela,0,6) == 'vw_est') {
            $this->DBGroup          = 'dbEstoque';
            $schema                 = 'taisho_estoquedb';
        } else if(substr($nome_tabela,0,2) == 'rh' || 
            substr($nome_tabela,0,5) == 'vw_rh') {
            $this->DBGroup          = 'dbRh';
            $schema                 = 'taisho_rhdb';
        } else if(substr($nome_tabela,0,3) == 'ger' || 
            substr($nome_tabela,0,3) == 'cfy' ||
            substr($nome_tabela,0,6) == 'vw_tax' ||
            substr($nome_tabela,0,6) == 'vw_tot' ||
            substr($nome_tabela,0,6) == 'vw_ger') {
            $this->DBGroup          = 'default';
            $schema                 = 'taisho_gerentedb';
        } else if(substr($nome_tabela,0,3) == 'cfg' || 
            substr($nome_tabela,0,6) == 'vw_cfg') {
            $this->DBGroup          = 'dbConfig';
            $schema                 = 'taisho_configdb';
        }
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
        $builder->where('table_schema', $schema);

        $ret = $builder->get()->getResultArray();

        return $ret;
    }
}
