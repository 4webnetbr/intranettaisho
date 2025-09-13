<?php

namespace App\Models\Estoqu;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class EstoquSaidaModel extends Model
{
    protected $DBGroup          = 'dbEstoque';

    protected $table            = 'est_saida';
    protected $viewcompl        = 'vw_est_saidas_relac';
    protected $view             = 'vw_est_saida_produtos_relac';
    protected $primaryKey       = 'sai_id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields    = [
        'sai_id',
        'emp_id',
        'dep_id',
        'sai_data',
        'sai_excluido',
    ];


    protected $skipValidation   = true;

    protected $deletedField  = 'sai_excluido';

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
    protected function depoisInsert(array $data)
    {
        $logdb = new LogMonModel();
        $registro = $data['id'];
        $log = $logdb->insertLog($this->table, 'Incluído', $registro, $data['data']);
        return $data;
    }

    /**
     * This method saves the session "usu_id" value to "updated_by" array element before 
     * the row is inserted into the database.
     *
     */
    protected function depoisUpdate(array $data)
    {
        $logdb = new LogMonModel();
        $registro = $data['id'][0];
        $log = $logdb->insertLog($this->table, 'Alterado', $registro, $data['data']);
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

    /**
     * getSaida
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getSaida($pro_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table($this->viewcompl);
        $builder->select('*');
        if ($pro_id) {
            $builder->where("sai_id", $pro_id);
        }
        $empresas           = explode(',', session()->get('usu_empresa'));
        $builder->whereIn("emp_id", $empresas);
        $builder->orderBy("sai_data");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);

        return $ret;
    }

    /**
     * getSaidaLista
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getSaidaLista($sai_id = false, $emp_id = false)
    {
        $dia7 = new \DateTime("-7 days");
        $dia7 = $dia7->format('Y-m-d');
        $db = db_connect('dbEstoque');
        $builder = $db->table($this->view);
        $builder->select('*');
        if ($sai_id) {
            $builder->where("sai_id", $sai_id);
        }
        if ($emp_id) {
            $builder->whereIn("emp_id", $emp_id);
        }
        $builder->where("sai_data >=", $dia7);

        $builder->orderBy("sai_data DESC");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);

        return $ret;
    }

    /**
     * getSaidaProd
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getSaidaProd($pro_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_saida_produtos_relac');
        $builder->select('*');
        if ($pro_id) {
            $builder->where("sai_id", $pro_id);
        }
        $builder->orderBy("sai_data");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);

        return $ret;
    }

    /**
     * getProdutoSaida
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getProdutoSaida($sai_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_saida_produtos_relac');
        $builder->select('*');
        if ($sai_id) {
            $builder->where("sai_id", $sai_id);
        }
        $builder->orderBy('sap_id');
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);        
        return $ret;
    }

    /**
     * getProdutoSaidaMarca
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getProdutoSaidaMarca($codigo = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_saidas_relac');
        $builder->select('*');
        if ($codigo) {
            $builder->where("TRIM(mar_codigo)", trim($codigo));
        }
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);        
        return $ret;
    }

    /**
     * getSaidaSearch
     *
     * Retorna os dados da Linha, pelo termo (nome) informado
     * Utilizado nas Seleções de Linha
     *  
     * @param mixed $termo 
     * @return array
     */
    public function getSaidaSearch($termo)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_saidas_relac');
        $builder->select('*');
        $builder->groupStart();
        $builder->like('pro_nome', $termo);
        $builder->groupEnd();
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }

    /**
     * getUltimoId
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @return array
     */
    public function getUltimoId()
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table($this->table);
        $builder->selectMax('sai_id');
        $ret = $builder->get()->getResultArray();

        return $ret;
    }

    /**
     * getTotalSaida
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getTotalSaida($pro_id = false, $dep_id = false, $data = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_total_saida');
        $builder->select('SUM(qtia_saida) as tot_saida');
        if ($pro_id) {
            $builder->where("pro_saida", $pro_id);
        }
        if ($dep_id) {
            $builder->where("dep_saida", $dep_id);
        }
        if ($data) {
            $builder->where("data_saida >=", $data);
        }
        $builder->orderBy("data_saida");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);

        return $ret;
    }

    /**
     * getTotalSaida
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getRelSaida($dep_id = false, $emp_id = false, $inicio = false, $fim = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_saida_produtos_relac');
        $builder->select('*');
        if ($dep_id) {
            $builder->where("dep_id", $dep_id);
        }
        if ($emp_id) {
            $builder->where("emp_id", $emp_id);
        }
        if ($inicio) {
            $builder->where("sai_data >=", $inicio);
        }
        if ($fim) {
            $builder->where("sai_data <=", $fim);
        }
        $builder->orderBy("pro_nome, sai_data");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), true);

        return $ret;
    }
}
