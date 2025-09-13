<?php

namespace App\Models\Estoqu;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class EstoquConsumoModel extends Model
{
    protected $DBGroup          = 'dbEstoque';

    protected $table            = 'est_consumo';
    protected $view             = 'vw_est_consumos_relac';
    protected $primaryKey       = 'con_id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'con_id',
        'emp_id',
        'pro_id',
        'con_duracao',
        'con_consumo',
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
     * getConsumo
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getConsumo($con_id = false, $empresa = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_produto_consumo');
        $builder->select("*");
        if ($con_id) {
            $builder->where("con_id", $con_id);
        }
        if ($empresa) {
            $builder->groupStart();
            $builder->where("emp_id", $empresa);
            $builder->orWhere("emp_id", NULL);
            $builder->groupEnd();
        }
        $builder->orderBy("con_consumo DESC, con_duracao DESC, pro_nome");
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);


        return $ret;
    }

    /**
     * getConsumoProd
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getConsumoProdAnt($con_id = false, $pro_id = false, $emp_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_produto_consumo');
        $builder->select('*');
        if ($con_id) {
            $builder->where("con_id", $con_id);
        }
        if ($pro_id) {
            if (is_array($pro_id)) {
                $builder->whereIn("pro_id", $pro_id);
            } else {
                $builder->where("pro_id", $pro_id);
            }
        }
        if ($emp_id) {
            $builder->where("emp_id", $emp_id);
        }
        $builder->where("pro_status", 'A');
        $builder->orderBy("con_id");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);

        return $ret;
    }

    public function getConsumoProd($con_id = false, $pro_id = false, $emp_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('est_produto p');
        $builder->select('p.pro_id,
                g.gru_nome,
                p.pro_nome,
                u.und_completa,
                c.con_duracao,
                c.con_consumo,
                c.con_id');
        $builder->join('est_consumo c', "p.pro_id = c.pro_id AND c.emp_id = $emp_id", 'left');
        $builder->join('est_grupoproduto g', "p.gru_id = g.gru_id", 'left');
        $builder->join('est_unidades u', "p.und_id = u.und_id", 'left');
        if ($con_id) {
            $builder->where("con_id", $con_id);
        }
        if ($pro_id) {
            if (is_array($pro_id)) {
                $builder->whereIn("p,pro_id", $pro_id);
            } else {
                $builder->where("p.pro_id", $pro_id);
            }
        }
        $builder->where("p.pro_status", 'A');
        $builder->where("p.pro_excluido", NULL);
        // if ($emp_id) {
        //     $builder->where("emp_id", $emp_id);
        // }
        $builder->orderBy("p.pro_nome");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);

        return $ret;
    }

    /**
     * getConsumoCod
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getConsumoCod($codigo = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_consumos_relac');
        $builder->select('*');
        if ($codigo) {
            $builder->where("con_codigo", $codigo);
        }
        $builder->orderBy("pro_nome");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);        
        return $ret;
    }

    /**
     * getConsumoSearch
     *
     * Retorna os dados da Linha, pelo termo (nome) informado
     * Utilizado nas Seleções de Linha
     *  
     * @param mixed $termo 
     * @return array
     */
    public function getConsumoSearch($termo)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_consumos_relac');
        $builder->select('*');
        $builder->groupStart();
        $builder->like('pro_nome', $termo);
        $builder->groupEnd();
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }

    /**
     * getRelConsumo
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getRelConsumo($emp_id = false, $inicio = false, $fim = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_consumos_relac');
        $builder->select('*');
        if ($emp_id) {
            $builder->where("emp_id", $emp_id);
        }
        if ($inicio) {
            $builder->where("con_data >=", $inicio);
        }
        if ($fim) {
            $builder->where("con_data <=", $fim);
        }
        $builder->orderBy("emp_apelido, con_data, pro_nome ");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);

        return $ret;
    }
}
