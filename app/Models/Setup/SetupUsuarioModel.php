<?php namespace App\Models\Setup;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class SetupUsuarioModel extends Model
{
    protected $table      = 'setup_usuario';
    protected $primaryKey = 'usu_id';
    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = ['usu_id',
                                'usu_nome',
                                'usu_login',
                                'usu_senha',
                                'usu_perfil_id',
                                'usu_status',
                                'usu_tipo'
                                ];

    protected $deletedField  = 'usu_excluido';

    protected $skipValidation     = true;    
     
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
     * usuLogonSetup
     * Validação do Login de Usuário de SETUP
     *
     * @param mixed $data - WHERE MONTADO NA FUNÇÃO LOGON da Classe Login 
     * @return void
     */
    public function usuLogonSetup($data){
        $this->builder()->select('setup_usuario.*, per.per_id, per.per_nome');
        $this->builder()->join('setup_perfil per', 'per.per_id = setup_usuario.usu_perfil_id');
        $this->builder()->where($data);
        $this->builder()->where('usu_excluido',null);
        return $this->builder()->get()->getResultArray();
    }

    /**
     * getUsuarioId
     *
     * Retorna os dados do Usuário de Setup, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getUsuarioId($id = false)
    {
        $db = db_connect();
        $builder = $db->table('vw_setup_usuario_relac');
        $builder->select('*'); 
        if($id){
            $builder->where('usu_id', $id);
        }
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }                 
 
    /**
     * getUsuarioSearch
     *
     * Retorna os dados do Usuário de Setup, pelo termo (nome) informado
     * Utilizado nas Seleções de Usuário
     *  
     * @param mixed $termo 
     * @return array
     */
    public function getUsuarioSearch($termo)
    {
        $array = ['usu_nome' => $termo.'%'];
        $db = db_connect();
        $builder = $db->table('vw_setup_usuario_relac');
        $builder->select('*'); 
        $builder->like($array);
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }                 

}
