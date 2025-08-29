<?php

namespace App\Models\Estoqu;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class EstoquCompraModel extends Model
{
    protected $DBGroup          = 'dbEstoque';

    protected $table            = 'est_compra';
    protected $view             = 'vw_est_compras_produto_relac';
    protected $primaryKey       = 'com_id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields    = [
        'com_id',
        'emp_id',
        'for_id',
        'ped_id',
        'com_data',
        'com_previsao',
        'com_entrega',
        'com_valor',
        'com_status',
        'com_excluido',

    ];


    protected $skipValidation   = true;

    protected $deletedField  = 'com_excluido';

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
     * getCompra
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getCompra($pro_id = false, $empresa = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_compras_relac');
        $builder->select("*");
        if ($pro_id) {
            $builder->where("com_id", $pro_id);
        }
        if ($empresa) {
            $builder->where("emp_id", $empresa);
        }
        $builder->where("com_status", 'P');
        $builder->orderBy("com_id desc, com_previsao, for_razao");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);

        return $ret;
    }

    /**
     * getCompra
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getCompraLista($pro_id = false, $empresa = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table($this->view);
        $builder->select("*");
        if ($pro_id) {
            $builder->where("com_id", $pro_id);
        }
        if ($empresa) {
            $builder->where("emp_id", $empresa);
        }
        $builder->where("com_status", 'P');
        $builder->orderBy("com_data DESC");
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
    public function getCompraProd($com_id = false, $pro_id = false, $stat = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_compras_produto_relac');
        $builder->select('*');
        if ($com_id) {
            $builder->where("com_id", $com_id);
        }
        if ($pro_id) {
            $builder->where("pro_id", $pro_id);
        }
        if ($stat) {
            $builder->where("cop_status", $stat);
        }
        $builder->orderBy("com_data");
        $ret = $builder->get()->getResultArray();

        // log_message('info', 'Não Chegou: ' . $this->db->getLastQuery() . ' Função: gravanaochegou');
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
    public function getCompraCop($cop_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_compras_produto_relac');
        $builder->select('*');
        if ($cop_id) {
            $builder->where("cop_id", $cop_id);
        }
        $builder->orderBy("com_data");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);

        return $ret;
    }

    /**
     * getCompraCod
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getCompraCod($codigo = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_compras_relac');
        $builder->select('*');
        if ($codigo) {
            $builder->where("com_codigo", $codigo);
        }
        $builder->orderBy("pro_nome");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);        
        return $ret;
    }

    /**
     * getCompraSearch
     *
     * Retorna os dados da Linha, pelo termo (nome) informado
     * Utilizado nas Seleções de Linha
     *  
     * @param mixed $termo 
     * @return array
     */
    public function getCompraSearch($termo)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_compras_relac');
        $builder->select('*');
        $builder->groupStart();
        $builder->like('pro_nome', $termo);
        $builder->groupEnd();
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }

    /**
     * getRelCompra
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getRelCompra($for_id = false, $emp_id = false, $inicio = false, $fim = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_compras_produto_relac');
        $builder->select('*');
        if ($for_id) {
            $builder->where("for_id", $for_id);
        }
        if ($emp_id) {
            $builder->where("emp_id", $emp_id);
        }
        if ($inicio) {
            $builder->where("com_data >=", $inicio);
        }
        if ($fim) {
            $builder->where("com_data <=", $fim);
        }
        $builder->orderBy("emp_apelido, for_razao DESC, com_data, pro_nome ");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);

        return $ret;
    }

    /**
     * getCompra
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getCompraVsEntrada($com_id)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_compras_vs_entradas');
        $builder->select("*");
        $builder->where("com_id", $com_id);
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);

        return $ret;
    }

    /**
     * getCompra
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getCompraProdPendente($pro_id = false, $empresa = false, $fornecedor = false, $compra = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_compras_produtos_pendentes');
        $builder->select("*");
        if ($pro_id) {
            $builder->where("com_id", $pro_id);
        }
        if ($empresa) {
            $builder->where("emp_id", $empresa);
        }
        if ($fornecedor) {
            $builder->where("for_id", $fornecedor);
        }
        if ($compra) {
            $builder->where("com_id", $compra);
        }
        $builder->where("com_status", 'P');
        $builder->orderBy("com_data DESC");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);

        return $ret;
    }
}
