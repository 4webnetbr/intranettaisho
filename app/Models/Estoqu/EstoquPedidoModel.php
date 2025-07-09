<?php

namespace App\Models\Estoqu;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class EstoquPedidoModel extends Model
{
    protected $DBGroup          = 'dbEstoque';

    protected $table            = 'est_pedido';
    protected $view             = 'vw_est_pedidos_relac';
    protected $primaryKey       = 'ped_id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'ped_id',
        'emp_id',
        'ped_data',
        'ped_datains',
        'pro_id',
        'und_id',
        'ped_qtia',
        'ped_status',
        'ped_justifica',
        'ped_sugestao',

    ];


    protected $skipValidation   = true;

    protected $deletedField  = 'ped_excluido';

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
     * getPedido
     *
     * Retorna os dados da Linha, pelo ID informado
     *
     * @param bool $id
     * @return array
     */
    public function getPedido($ped_id = false, $empresa = false)
    {
        if (!$empresa) {
            $empresa           = explode(',', session()->get('usu_empresa'));
        }
        $db = db_connect('dbEstoque');
        $builder = $db->table("vw_est_pedidos_pendentes_relac as pro");
        $builder->select("pro.pro_id,
                        pro.grc_nome,
                        pro.pro_nome,
                        pro.ped_id,
                        pro.ped_data,
                        pro.ped_datains,
                        pro.ped_qtia,
                        pro.ped_justifica,
                        pro.ped_sugestao,
                        pro.und_sigla_compra,
                        pro.und_id_compra,
                        sld.saldo,
                        sld.und_id as und_id_saldo, 
                        sld.und_sigla as und_sigla_saldo");
        $builder->join('vw_saldo_produtos sld', "sld.pro_id = pro.pro_id AND sld.emp_id = $empresa", 'left');
        if ($ped_id) {
            $builder->where("pro.ped_id", $ped_id);
        }
        if ($empresa) {
            if (is_array($empresa)) {
                $builder->whereIn("pro.emp_id", $empresa);
            } else {
                $builder->where("pro.emp_id", $empresa);
            }
        }
        // $builder->orderBy("pro.ped_data DESC");
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);


        return $ret;
    }

    /**
     * getPedidoProd
     *
     * Retorna os dados da Linha, pelo ID informado
     *
     * @param bool $id
     * @return array
     */
    public function getPedidoProd($ped_id = false, $pro_id = false, $empresa = false)
    {
        if (!$empresa) {
            $empresa           = explode(',', session()->get('usu_empresa'));
        }
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_pedidos_relac');
        $builder->select('*');
        if ($ped_id) {
            $builder->where("ped_id", $ped_id);
        }
        if ($pro_id) {
            $builder->where("pro_id", $pro_id);
        }
        if ($empresa) {
            $builder->where("emp_id", $empresa);
            $builder->where("ped_status", 'P');
        }
        $builder->orderBy("ped_data");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);

        return $ret;
    }

    /**
     * getPedidoCod
     *
     * Retorna os dados da Linha, pelo ID informado
     *
     * @param bool $id
     * @return array
     */
    public function getPedidoCod($codigo = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_pedidos_relac');
        $builder->select('*');
        if ($codigo) {
            $builder->where("ped_codigo", $codigo);
        }
        $builder->orderBy("pro_nome");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);
        return $ret;
    }

    /**
     * getPedidoSearch
     *
     * Retorna os dados da Linha, pelo termo (nome) informado
     * Utilizado nas Seleções de Linha
     *
     * @param mixed $termo
     * @return array
     */
    public function getPedidoSearch($termo)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_pedidos_relac');
        $builder->select('*');
        $builder->groupStart();
        $builder->like('pro_nome', $termo);
        $builder->groupEnd();
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }

    /**
     * getRelPedido
     *
     * Retorna os dados da Linha, pelo ID informado
     *
     * @param bool $id
     * @return array
     */
    public function getRelPedido($emp_id = false, $inicio = false, $fim = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_pedidos_relac');
        $builder->select('*');
        if ($emp_id) {
            $builder->where("emp_id", $emp_id);
        }
        if ($inicio) {
            $builder->where("ped_data >=", $inicio);
        }
        if ($fim) {
            $builder->where("ped_data <=", $fim);
        }
        $builder->orderBy("emp_apelido, ped_data, pro_nome ");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);

        return $ret;
    }

    public function getCotacaoProdutos($empresa = false, $grc_id = false)
    {
        if (!$empresa) {
            $empresa           = explode(',', session()->get('usu_empresa'));
        }
        $db = db_connect('dbEstoque');
        // $builder = $db->table("vw_pedidos_com_cotacoes");
        // $builder = $db->table("vw_produtos_cotados");
        $builder = $db->table("vw_pedidos_com_cotacao_fornec");
        $builder->select("*");
        // if ($ped_id) {
        //     $builder->where("pro.ped_id", $ped_id);
        // }
        if ($empresa) {
            if (is_array($empresa)) {
                $builder->whereIn("emp_id", $empresa);
            } else {
                $builder->where("emp_id", $empresa);
            }
        }
        if ($grc_id) {
            $builder->where("grc_id", $grc_id);
        }
        // $start = microtime(true);
        $builder->orderBy("grc_nome, pro_nome, ped_datains");
        $ret = $builder->get()->getResultArray();
        // debug($db->getLastQuery(), true);

        // $end = microtime(true);
        
        // $executionTime = $end - $start;
        // Fim da medição
        // debug($executionTime);
        return $ret;
    }
}
