<?php

namespace App\Models\Estoqu;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class EstoquProdutoModel extends Model
{
    protected $DBGroup          = 'dbEstoque';

    protected $table            = 'est_produto';
    protected $view             = 'vw_est_produtos_relac';
    protected $primaryKey       = 'pro_id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields    = [
        'pro_id',
        'gru_id',
        'grc_id',
        'pro_nome',
        'und_id',
        'und_id_compra',
        'pro_fcc',
        'pro_link',
        'pro_status'

    ];


    protected $skipValidation   = true;

    protected $deletedField  = 'pro_excluido';

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
     * getProduto
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getProduto($pro_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_produtos_relac');
        $builder->select('*');
        if ($pro_id) {
            $builder->where("pro_id", $pro_id);
        }
        // $builder->where("pro_status", 'A');
        $builder->orderBy("gru_nome, pro_nome");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);

        return $ret;
    }

    /**
     * getProduto
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getProdutoPedido($empresa,$pro_id = false,  $fields = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_produtos_relac pro');
        // $builder->select('pro.*, ent.ent_id, COALESCE(ent.emp_id,ped.emp_id,null) as emp_id, mmx.mmi_minimo, mmx.mmi_maximo, ped.ped_id, ped.ped_data, COALESCE(ped.ped_qtia, 0) AS ped_qtia, ped.ped_justifica, sld.saldo, sld.und_id as und_id_saldo, sld.und_sigla as und_sigla_saldo'); 
        if (!$fields) {
            // $builder->select('pro.*, ent.ent_id, COALESCE(ent.emp_id,ped.emp_id,null) as emp_id, con.con_consumo, con_duracao, con_tporeposicao, ped.ped_id, ped.ped_data, COALESCE(ped.ped_qtia, 0) AS ped_qtia, ped.ped_justifica, sld.saldo, sld.und_id as und_id_saldo, sld.und_sigla as und_sigla_saldo, mmx.mmi_minimo,mmx.mmi_maximo, (sld.saldo / pro.pro_fcc) as saldo_compra');
            $builder->select('pro.*, ent.ent_id, COALESCE(ent.emp_id,ped.emp_id,null) as emp_id, con.con_consumo, con_duracao, con_tporeposicao, ped.ped_id, ped.ped_data, COALESCE(ped.ped_qtia, 0) AS ped_qtia, ped.ped_justifica, sld.saldo, sld.und_id as und_id_saldo, sld.und_sigla as und_sigla_saldo, mmx.mmi_minimo,mmx.mmi_maximo,   (sld.saldo / pro.pro_fcc) as saldo_compra');
        } else {
            $builder->select($fields);
        }
        $builder->join('vw_saldo_produtos sld', "sld.pro_id = pro.pro_id AND sld.emp_id = $empresa", 'left');
        $builder->join('est_entrada_produto enp', "enp.pro_id = pro.pro_id", 'left');
        $builder->join('est_entrada ent', "ent.ent_id = enp.ent_id AND ent.emp_id = $empresa", 'left');
        $builder->join('est_pedido ped', "ped.pro_id = pro.pro_id AND ped.emp_id = $empresa AND ped.ped_status = 'P'", 'left');
        $builder->join('est_consumo con', "con.pro_id = pro.pro_id AND con.emp_id = $empresa", 'left');
        $builder->join('est_minmax mmx', "mmx.pro_id = pro.pro_id AND mmx.emp_id = $empresa", 'left');
        if ($pro_id) {
            $builder->where("pro.pro_id", $pro_id);
        }
        $builder->groupStart();
        $builder->where("con.con_consumo > 0");
        $builder->orWhere("con.con_duracao > 0");
        $builder->orWhere("mmx.mmi_maximo > 0");
        $builder->groupEnd();
        $builder->groupStart();
        $builder->where("ent.emp_id", $empresa);
        $builder->orWhere("ent.ent_id IS NULL");
        $builder->groupEnd();
        $builder->where("pro.pro_status", 'A');
        // $builder->where("pro.pro_excluido", NULL);
        $builder->groupBy("pro.pro_id");
        $builder->orderBy("sld.total_entradas DESC, sld.total_saidas DESC, pro_nome");

        // debug($builder->getCompiledSelect(), true);
        $ret = $builder->get()->getResultArray();

        return $ret;
    }

    /**
     * getProduto
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getProdutoPedidoView( $empresa,$pro_id = NULL)
    {
        $db = db_connect('dbEstoque');
        $sql = "CALL GetProdutosPedidos(?, ?)";
        $query = $db->query($sql, [$empresa, $pro_id]);
        // debug($query);
        // Obtém o resultado
        $ret = $query->getResultArray();

        // Libera o resultado, se necessário
        $query->freeResult();

        // debug($this->db->getLastQuery(), false);

        return $ret;
    }

    /**
     * getProdutoSearch
     *
     * Retorna os dados da Linha, pelo termo (nome) informado
     * Utilizado nas Seleções de Linha
     *  
     * @param mixed $termo 
     * @return array
     */
    public function getProdutoSearch($termo)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_produtos_relac');
        $builder->select('*');
        $builder->groupStart();
        $builder->like('pro_nome', $termo);
        $builder->groupEnd();
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }

    /**
     * getRelHistorico
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getRelHistorico($empresa, $deposito, $produto, $inicio, $fim)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_historico_produto');
        $builder->select('*');
        $builder->where("emp_id", $empresa);
        $builder->where("dep_id", $deposito);
        $builder->where("pro_id", $produto);
        $builder->where("data_ref >=", $inicio);
        $builder->where("data_ref <=", $fim);
        $builder->orderBy("datahora");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);

        return $ret;
    }

    /**
     * getProduto
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getSaldos($dep_id = false, $pro_id = false, $emp_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_saldo_produtos');
        $builder->select('*');
        if ($dep_id) {
            $builder->where("dep_id", $dep_id);
        }
        if ($pro_id) {
            $builder->where("pro_id", $pro_id);
        }
        if ($emp_id) {
            $builder->where("emp_id", $emp_id);
        }
        // $builder->orderBy("pro_nome");
        $ret = $builder->get()->getResultArray();

        $sql = $this->db->getLastQuery();
        log_message('info', 'SQL: ' . $sql . ' Função: getSaldo');
        // debug($sql);


        return $ret;
    }

    /**
     * getRelMovimento
     *
     * Retorna os Movimentos de Pedido, Compra e Entrada de Produtos
     * 
     * @param bool $id 
     * @return array
     */
    public function getRelMovimento($emp_id = false, $inicio = false, $final = false)
    {
        if (!$emp_id || !$inicio || !$final) {
            return false;
        }
        $db = db_connect('dbEstoque');
        $sql = "CALL sp_flow_mov_produto(?, ?, ?)";
        $query = $db->query($sql, [$emp_id, $inicio, $final]);
        // debug($query);
        // Obtém o resultado
        $result = $query->getResultArray();

        // Libera o resultado, se necessário
        $query->freeResult();

        log_message('info', 'SQL: ' . $sql . ' Função: getMovimento');
        return $result;
    }
}
