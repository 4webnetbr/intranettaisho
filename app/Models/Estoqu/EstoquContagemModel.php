<?php

namespace App\Models\Estoqu;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class EstoquContagemModel extends Model
{
    protected $DBGroup          = 'dbEstoque';

    protected $table            = 'est_contagem';
    protected $view             = 'vw_est_contagem_lista_relac';
    protected $primaryKey       = 'cta_id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields    = [
        'cta_id',
        'cta_data',
        'emp_id',
        'dep_id',
    ];


    protected $skipValidation   = true;

    protected $deletedField  = 'cta_excluido';

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
     * getContagem
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getContagem($cta_id = false, $pro_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_contagem_relac');
        $builder->select('*');
        if ($cta_id) {
            $builder->where("cta_id", $cta_id);
        }
        if ($pro_id) {
            $builder->where("pro_id", $pro_id);
        }
        $builder->orderBy("ctp_id");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);

        return $ret;
    }

    /**
     * getContagem
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getContagemDeposito($dep_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_contagem_lista_relac');
        $builder->select('*');
        if ($dep_id) {
            $builder->where("dep_id", $dep_id);
        }
        $builder->orderBy("cta_data");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);

        return $ret;
    }


    /**
     * getContagemLista
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getContagemLista($cta_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_contagem_lista_relac');
        $builder->select('*');
        if ($cta_id) {
            $builder->where("cta_id", $cta_id);
        }
        $empresas           = explode(',', session()->get('usu_empresa'));
        $builder->whereIn("emp_id", $empresas);

        // $builder->where("cta_excluido IS NULL");
        $builder->orderBy("cta_data DESC");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);

        return $ret;
    }

    /**
     * getCompraProd
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getContagemProd($cta_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_contagem_produto_relac');
        $builder->select('*');
        if ($cta_id) {
            $builder->where("cta_id", $cta_id);
        }
        $builder->orderBy("cta_datahora DESC");
        $empresas           = explode(',', session()->get('usu_empresa'));
        $builder->whereIn("emp_id", $empresas);
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);

        return $ret;
    }


    /**
     * getContagem
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getTotalContagem($pro_id = false, $dep_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_total_contagem');
        $builder->select('*');
        if ($pro_id) {
            $builder->where("pro_contagem", $pro_id);
        }
        if ($dep_id) {
            $builder->where("dep_contagem", $dep_id);
        }
        $builder->orderBy("data_contagem DESC");
        $builder->limit(1);
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);

        return $ret;
    }

    /**
     * getContagem
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getUltimaContagem($dep_id)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('est_controle_contagem');
        $builder->select('*');
        if ($dep_id) {
            $builder->where("dep_id", $dep_id);
        }
        $builder->orderBy('data_ultima_contagem DESC');
        $builder->limit(1);
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);

        return $ret;
    }

    /**
     * getConsumo
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getProdutosConsumo($empresa = false, $deposito = false)
    {
        if (!$empresa || !$deposito) {
            return false;
        }
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_produtos_relac pro');
        $builder->select('pro.pro_id, pro.pro_nome, pro.gru_nome,pro.und_nome, pro.und_id, sld.saldo');
        $builder->join('vw_saldo_produtos sld', "sld.pro_id = pro.pro_id AND sld.emp_id = $empresa AND sld.dep_id = $deposito", 'left');
        $builder->join('est_consumo con', "con.pro_id = pro.pro_id AND con.emp_id = $empresa", 'left');
        $builder->join('est_minmax mmx', "mmx.pro_id = pro.pro_id AND mmx.emp_id = $empresa", 'left');
        $builder->groupStart();
        $builder->where("con.con_consumo > 0");
        $builder->orWhere("con.con_duracao > 0");
        $builder->orWhere("mmx.mmi_maximo > 0");
        $builder->groupEnd();
        $builder->orderBy("pro.gru_nome");
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);

        return $ret;
    }
    /**
     * getConsumo
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getProdutosMarcas($empresa = false, $deposito = false)
    {
        if (!$empresa || !$deposito) {
            return false;
        }
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_produtos_marca_relac pro');
        $builder->select('pro.pro_id, pro.pro_nome, pro.gru_nome, pro.und_nome, pro.und_id, sld.saldo, mar_nome, mar_apresenta, mar_conversao, mar_codigo');
        $builder->join('vw_saldo_produtos sld', "sld.pro_id = pro.pro_id AND sld.emp_id = $empresa AND sld.dep_id = $deposito", 'left');
        $builder->join('est_consumo con', "con.pro_id = pro.pro_id AND con.emp_id = $empresa", 'left');
        $builder->join('est_minmax mmx', "mmx.pro_id = pro.pro_id AND mmx.emp_id = $empresa", 'left');
        $builder->groupStart();
        $builder->where("con.con_consumo > 0");
        $builder->orWhere("con.con_duracao > 0");
        $builder->orWhere("mmx.mmi_maximo > 0");
        $builder->groupEnd();
        $builder->orderBy("pro.gru_nome, pro.pro_nome, pro.mar_nome");
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);

        return $ret;
    }
}
