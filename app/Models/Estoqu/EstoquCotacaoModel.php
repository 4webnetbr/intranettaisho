<?php 
namespace App\Models\Estoqu;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class EstoquCotacaoModel extends Model
{
    protected $DBGroup          = 'dbEstoque';

    protected $table            = 'est_cotacao';
    protected $view             = 'vw_est_cotacao_produto_relac';
    protected $primaryKey       = 'cot_id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields    = [ 'cot_id',
                                    'emp_id',
                                    'cot_data',
                                    'cot_prazo',
                                    'cot_status',
                                ];

                                
    protected $skipValidation   = true;  

    protected $deletedField  = 'cot_excluido';
   
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
    protected function depoisInsert(array $data) {
        $logdb = new LogMonModel(); 
        $registro = $data['id'];
        $log = $logdb->insertLog($this->table,'Incluído',$registro, $data['data']);
        return $data;
    }

    /**
     * This method saves the session "usu_id" value to "updated_by" array element before 
     * the row is inserted into the database.
     *
     */
    protected function depoisUpdate(array $data) {
        $logdb = new LogMonModel(); 
        $registro = $data['id'][0];
        $log = $logdb->insertLog($this->table,'Alterado',$registro, $data['data']);
        return $data;
    } 

    /**
     * This method saves the session "usu_id" value to "deletede_by" array element before 
     * the row is inserted into the database.
     *
     */
    protected function depoisDelete(array $data) {
        $logdb = new LogMonModel(); 
        $registro = $data['id'][0];
        $log = $logdb->insertLog($this->table,'Excluído',$registro, $data['data']);
        return $data;
    } 

    /**
     * getCotacao
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getCotacao($pro_id = false, $empresa = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table($this->view);
        $builder->select("*"); 
        if($pro_id){
            $builder->where("cot_id", $pro_id);
        }
        if($empresa){
            $builder->whereIn("emp_id", $empresa);
        }
        $builder->where("cot_status", 'P');
        $builder->orderBy("cot_data, for_razao");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);
        
        return $ret;

    }                 

    /**
     * getCotacao
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getCotacaoLista($pro_id = false, $empresa = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table($this->view);
        $builder->select("*"); 
        if($pro_id){
            $builder->where("cot_id", $pro_id);
        }
        if($empresa){
            $builder->whereIn("emp_id", $empresa);
        }
        $builder->whereIn("cot_status", 'S');
        $builder->orderBy("cot_data DESC");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);
        
        return $ret;

    }                 

    /**
     * getCotacaoProd
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getCotacaoProd($cot_id = false, $pro_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_cotacao_produto_relac');
        $builder->select('*'); 
        if($cot_id){
            $builder->where("cot_id", $cot_id);
        }
        if($pro_id){
            $builder->where("pro_id", $pro_id);
        }
        $builder->orderBy("cot_data");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);        
        return $ret;
    }                 

    /**
     * getCotacaoCod
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getCotacaoCod($codigo = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_cotacaos_relac');
        $builder->select('*'); 
        if($codigo){
            $builder->where("cot_codigo", $codigo);
        }
        $builder->orderBy("pro_nome");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);        
        return $ret;

    }                 

    /**
     * getCotacaoSearch
     *
     * Retorna os dados da Linha, pelo termo (nome) informado
     * Utilizado nas Seleções de Linha
     *  
     * @param mixed $termo 
     * @return array
     */
    public function getCotacaoSearch($termo)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_cotacaos_relac');
        $builder->select('*'); 
		$builder->groupStart();
			$builder->like('pro_nome', $termo);
		$builder->groupEnd();
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }          
    
    /**
     * getRelCotacao
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getRelCotacao($for_id = false, $emp_id = false, $inicio = false, $fim = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_cotacaos_produto_relac');
        $builder->select('*'); 
        if($for_id){
            $builder->where("for_id", $for_id);
        }
        if($emp_id){
            $builder->where("emp_id", $emp_id);
        }
        if($inicio){
            $builder->where("cot_data >=", $inicio);
        }
        if($fim){
            $builder->where("cot_data <=", $fim);
        }
        $builder->orderBy("emp_apelido, for_razao DESC, cot_data, pro_nome ");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);
        
        return $ret;

    }                 

    public function getPedidosPendentes($empresas){
        $columns = [];
        foreach ($empresas as $empresa) {
            $columns[] = "MAX(CASE WHEN cfg_empresa.emp_id = {$empresa['emp_id']} THEN vw_est_pedidos_relac.ped_qtia ELSE NULL END) AS `{$empresa['emp_abrev']}`";
        }
        $columns_sql = implode(", ", $columns);
        
        $query = "
            SELECT
                vw_est_produtos_relac.pro_id, 
                vw_est_produtos_relac.pro_nome, 
                vw_est_produtos_relac.und_id, 
                vw_est_produtos_relac.und_id_compra, 
                vw_est_produtos_relac.und_sigla_compra, 
                vw_est_produtos_relac.und_sigla,
                $columns_sql
            FROM
                taisho_estoquedb.vw_est_produtos_relac
            LEFT JOIN
                taisho_estoquedb.vw_est_pedidos_relac
            ON 
                vw_est_produtos_relac.pro_id = vw_est_pedidos_relac.pro_id AND vw_est_pedidos_relac.ped_status = 'P'
            INNER JOIN
                taisho_configdb.cfg_empresa
            ON 
                vw_est_pedidos_relac.emp_id = cfg_empresa.emp_id
            GROUP BY
                vw_est_produtos_relac.pro_id, 
                vw_est_produtos_relac.pro_nome, 
                vw_est_produtos_relac.und_id, 
                vw_est_produtos_relac.und_id_compra, 
                vw_est_produtos_relac.und_sigla_compra, 
                vw_est_produtos_relac.und_sigla
            ORDER BY
                vw_est_produtos_relac.pro_nome
                            
        ";
        $db = db_connect('dbEstoque');
        $query = $db->query($query);
        $result = $query->getResult(); // Resultados como objetos
        // Ou
        $result_array = $query->getResultArray(); 
        
        // debug($result_array);
        return $result_array;        
    }
    
}
?>