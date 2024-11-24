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

    protected $allowedFields    = [ 'ped_id',
                                    'emp_id',
                                    'ped_data',
                                    'ped_datains',
                                    'pro_id',
                                    'ped_qtia',
                                    'ped_status',
    
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
     * getPedido
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getPedido($ped_id = false, $empresa = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table($this->view);
        $builder->select("*"); 
        if($ped_id){
            $builder->where("ped_id", $ped_id);
        }
        if($empresa){
            $builder->where("emp_id", $empresa);
        }
        $builder->where("ped_status", 'P');
        $builder->orderBy("ped_data DESC");
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
    public function getPedidoProd($ped_id = false, $pro_id = false, $emp_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_pedidos_relac');
        $builder->select('*'); 
        if($ped_id){
            $builder->where("ped_id", $ped_id);
        }
        if($pro_id){
            $builder->where("pro_id", $pro_id);
        }
        if($emp_id){
            $builder->where("emp_id", $emp_id);
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
        if($codigo){
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
    public function getRelPedido($for_id = false, $emp_id = false, $inicio = false, $fim = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_pedidos_produto_relac');
        $builder->select('*'); 
        if($for_id){
            $builder->where("for_id", $for_id);
        }
        if($emp_id){
            $builder->where("emp_id", $emp_id);
        }
        if($inicio){
            $builder->where("ped_data >=", $inicio);
        }
        if($fim){
            $builder->where("ped_data <=", $fim);
        }
        $builder->orderBy("emp_apelido, for_razao DESC, ped_data, pro_nome ");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);
        
        return $ret;

    }                 
    
}
?>