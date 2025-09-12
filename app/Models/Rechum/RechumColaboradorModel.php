<?php 
namespace App\Models\Rechum;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class RechumColaboradorModel extends Model
{
    protected $DBGroup          = 'dbRh';

    protected $table            = 'rh_colaborador';
    protected $view             = 'vw_rh_colaborador_lista_relac';
    protected $primaryKey       = 'col_id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'App\Entities\Colaborador';
    protected $useSoftDeletes   = true;

    protected $allowedFields    = [ 'col_id',
                                    'emp_id',
                                    'emp_id_registro',
                                    'cag_id',
                                    'set_id',
                                    'col_cpf',
                                    'col_matricula',
                                    'col_nome',
                                    'col_nome_social',
                                    'col_vinculo',
                                    'col_data_nascimento',
                                    'col_cargahoraria',
                                    'col_genero',
                                    'col_estado_civil',
                                    'col_cep',
                                    'col_endereco',
                                    'col_numero',
                                    'col_complemento',
                                    'col_bairro',
                                    'col_cidade',
                                    'col_estado',
                                    'col_telefone_celular',
                                    'col_email',
                                    'col_data_admissao',
                                    'col_data_demissao',
                                    'col_tipo',
                                    'col_salario',
                                    'col_premio',
                                    'col_pctparticipacao',
                                    'col_excluido',
                                    'col_situacao',
                                    'col_vale',
                                    'col_vt',
                                    'col_folgasemana',
                                    'col_folgadomingo',
                                    'col_metropolitana',        
                                ];

                                
    protected $skipValidation   = false;  

    protected $deletedField  = 'col_excluido';
   
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
     * getColaborador
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getColaborador($col_id = false, $emp_id = false)
    {
        $db = db_connect('dbRh');
        $builder = $db->table($this->view);
        $builder->select('*'); 
        if($col_id){
            $builder->where("col_id", $col_id);
        }
        if($emp_id){
            $builder->where("emp_id", $emp_id);
        }
        $builder->orderBy("col_nome");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);
        
        return $ret;

    }                 

    /**
     * getColaborador
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getColaboradorSetor($emp_id = false, $set_id = false, $compet = false)
    {
        $db = db_connect('dbRh');
        $builder = $db->table('vw_rh_funcoes');
        $builder->select('set_nome, set_pctdistribui, cag_id,cag_nome,num_colab'); 
        if($set_id){
            $builder->where("set_id", $set_id);
        }
        if($emp_id){
            $builder->where("emp_id", $emp_id);
        }
        if($compet){
            $builder->where("hol_mesanocompetencia", $compet);
        }
        $builder->orderBy("set_nome, cag_nome");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);
        
        return $ret;

    }                 

    /**
     * getColaborador
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getCPF($cpf = false)
    {
        $db = db_connect('dbRh');
        $cpf = apenasNumeros($cpf);
        $builder = $db->table('rh_colaborador');
        $builder->select('*'); 
        if($cpf){
            $builder->where(TRIM("col_cpf_numero"), trim($cpf));
        }
        $builder->orderBy("col_nome");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);
        
        return $ret;

    }                 

}