<?php namespace App\Models\Setup;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class SetupClasseModel extends Model
{
    protected $table            = 'setup_classe';
    protected $primaryKey       = 'clas_id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields    = ['clas_id',
                                    'clas_modulo_id',
                                    'clas_titulo',
                                    'clas_icone',
                                    'clas_controler',
                                    'clas_tabela',
                                    'clas_descricao',
                                    'clas_regras_gerais',
                                    'clas_regras_cadastro',
                                    'clas_texto_botao'
                                ];

                                
    protected $skipValidation   = true;  

    // protected $createdField  = 'clas_criado';
    // protected $updatedField  = 'clas_alterado';
    protected $deletedField  = 'clas_excluido';
   
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
     * getClasseId
     *
     * Retorna os dados da Classe, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getClasseId($clas_id = false)
    {
        $db = db_connect();
        $builder = $db->table('vw_setup_classe_relac');
        $builder->select('*'); 
        if($clas_id){
            $builder->where("clas_id", $clas_id);
        }
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);
        
        return $ret;

    }                 

    /**
     * getClasseSearch
     *
     * Retorna os dados da Classe, pelo termo (nome) informado
     * Utilizado nas Seleções de Classe
     *  
     * @param mixed $termo 
     * @return array
     */
    public function getClasseSearch($termo)
    {
        $s_titulo = ['clas_titulo' => $termo.'%'];
        $s_contro = ['clas_controler' => $termo.'%'];
        $db = db_connect();
        $builder = $db->table('vw_setup_classe_relac');
        $builder->select('*'); 
        $builder->like($s_contro);
        $builder->orLike($s_titulo);
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }                 

    /**
     * getClasseSearch
     *
     * Retorna os dados da Classe, pelo perfil (id) informado
     * Utilizado nas Seleções de Classe
     *  
     * @param mixed $perfil
     * @return array
     */
    public function getClassePerfil($perfil = false)
    {
        $db = db_connect();
        $builder = $db->table('vw_setup_classe_relac');
        $builder->select('*'); 
        if($perfil){
            $builder->join('setup_perfil_item pit','pit.pit_classe_id = vw_setup_classe_relac.clas_id AND pit.pit_perfil_id = '.$perfil,'left');
            // $builder->where('pit.pit_perfil_id',$perfil);
        }
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }                 


}
