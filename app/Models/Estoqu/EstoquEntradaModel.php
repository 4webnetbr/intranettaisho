<?php 
namespace App\Models\Estoqu;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class EstoquEntradaModel extends Model
{
    protected $DBGroup          = 'dbEstoque';

    protected $table            = 'est_entrada';
    protected $viewcompl        = 'vw_est_entradas_relac';
    protected $view             = 'vw_est_entradas_lista_relac';
    protected $primaryKey       = 'ent_id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields    = [ 'ent_id',
                                    'com_id',
                                    'emp_id',
                                    'dep_id',
                                    'for_id',
                                    'ent_data',
                                    'ent_valor',
                                    'ent_status',
                                    'ent_excluido',
                                    
                                ];

                                
    protected $skipValidation   = true;  

    protected $deletedField  = 'ent_excluido';
   
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
     * getEntrada
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getEntrada($pro_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table($this->viewcompl);
        $builder->select('*'); 
        if($pro_id){
            $builder->where("ent_id", $pro_id);
        }
        $empresas           = explode(',',session()->get('usu_empresa'));
        $builder->whereIn("emp_id", $empresas);

        $builder->orderBy("ent_data");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);
        
        return $ret;

    }                 

    /**
     * getEntradaLista
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getEntradaLista($ent_id = false, $emp_id = false)
    {
        $dia5 = new \DateTime("-5 days");
        $dia5 = $dia5->format('Y-m-d');
        $db = db_connect('dbEstoque');
        $builder = $db->table($this->view);
        $builder->select('*'); 
        if($ent_id){
            $builder->where("ent_id", $ent_id);
        }
        if($emp_id){
            $builder->where("emp_id", $emp_id);
        }
        $empresas           = explode(',',session()->get('usu_empresa'));
        $builder->whereIn("emp_id", $empresas);
        $builder->where("ent_data >=", $dia5);

        $builder->orderBy("ent_data DESC");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);
        
        return $ret;

    }                 

        /**
     * getEntradaProd
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getEntradaProd($pro_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_entrada_produtos_relac');
        $builder->select('*'); 
        if($pro_id){
            $builder->where("ent_id", $pro_id);
        }
        $builder->orderBy("ent_data");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);
        
        return $ret;

    }                 

    
    /**
     * getProdutoEntrada
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getProdutoEntrada($ent_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_entradas_relac');
        $builder->select('*'); 
        if($ent_id){
            $builder->where("ent_id", $ent_id);
        }
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);        
        return $ret;

    }                 

    /**
     * getProdutoEntradaMarca
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getProdutoEntradaMarca($codigo = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_entradas_relac');
        $builder->select('*'); 
        if($codigo){
            $builder->where("TRIM(mar_codigo)", trim($codigo));
        }
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);        
        return $ret;

    }                 

    /**
     * getEntradaSearch
     *
     * Retorna os dados da Linha, pelo termo (nome) informado
     * Utilizado nas Seleções de Linha
     *  
     * @param mixed $termo 
     * @return array
     */
    public function getEntradaSearch($termo)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_entradas_relac');
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
        $builder->selectMax('ent_id'); 
        $ret = $builder->get()->getResultArray();

        return $ret;
    }                 
    
    /**
     * getTotalEntrada
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getTotalEntrada($pro_id = false,$dep_id = false, $data = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_total_entrada');
        $builder->select('SUM(qtia_entrada) as tot_entrada'); 
        if($pro_id){
            $builder->where("pro_entrada", $pro_id);
        }
        if($dep_id){
            $builder->where("dep_entrada", $dep_id);
        }
        if($data){
            $builder->where("data_entrada >=", $data);
        }
        $builder->orderBy("data_entrada");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);
        
        return $ret;

    }                 

    /**
     * getRelEntrada
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getRelEntrada($dep_id = false, $emp_id = false, $inicio = false, $fim = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_entrada_produtos_relac');
        $builder->select('*'); 
        if($dep_id){
            $builder->where("dep_id", $dep_id);
        }
        if($emp_id){
            $builder->where("emp_id", $emp_id);
        }
        if($inicio){
            $builder->where("ent_data >=", $inicio);
        }
        if($fim){
            $builder->where("ent_data <=", $fim);
        }
        $builder->orderBy("pro_nome, ent_data");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);
        
        return $ret;

    }                 

}

?>