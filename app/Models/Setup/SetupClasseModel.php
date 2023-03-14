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
                                    'clas_titulo',
                                    'clas_icone',
                                    'clas_controler',
                                    'clas_tabela',
                                    'clas_descricao',
                                    'clas_regras_gerais',
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
    public function getClasseId($id = false)
    {
        $this->builder()
                ->select('*');
        if ($id) {
            $this->builder()->where('clas_id', $id);
        }
        $this->builder()->where('clas_excluido',null);
        $this->builder()->orderBy('clas_titulo');
        return $this->find();
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
        $this->builder()
        ->select('setup_classe.*, menu.men_id')
        ->join('setup_menu menu', 'menu.men_classe_id = setup_classe.clas_id AND menu.men_excluido IS NULL','left')
        ->where(trim(strtoupper('clas_controler')), trim(strtoupper($termo)));
        return $this->find();
    }                 

    /**
     * getClasseTitulo
     *
     * Retorna os dados da Classe, pelo Título informado
     * Utilizado nas Seleções de Classe
     *  
     * @param mixed $termo 
     * @return array
     */
    public function getClasseTitulo($termo)
    {
        $array = ['clas_titulo' => $termo.'%'];
        $this->builder()->select(['clas_id','clas_titulo','clas_icone']);
        $this->builder()->where('clas_excluido',null);
        $this->builder()->like($array);
    
        return $this->builder()->get()->getResultArray();
    }            
    
}
