<?php

namespace App\Models\Rechum;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class RechumHoleriteModel extends Model
{
    protected $DBGroup          = 'dbRh';

    protected $table            = 'rh_holerite';
    protected $view             = 'vw_rh_holerite_lista_relac';
    protected $primaryKey       = 'hol_id';
    protected $useAutoIncrement = true;

    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'hol_id',
        'emp_id',
        'col_id',
        'hol_competencia',
        'hol_dataemissao',
        'hol_calculo',
        'hol_horaemissao',
        'hol_situacao',
        'hol_proventos',
        'hol_descontos',
        'hol_informativa',
        'hol_informativa_dedutora',
        'hol_liquido',
        'hol_nd',
        'hol_nf',
        'hol_baseinss',
        'hol_excedente_inss',
        'hol_basefgts',
        'hol_valor_fgts',
        'hol_baseirrf',
        'hol_observacao',

    ];


    protected $returnType = 'App\Entities\Holerite';
    protected $skipValidation   = false;

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
     * getHolerite
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getHolerite($hol_id = false, $emp_id = false, $competencia = false)
    {
        $db = db_connect('dbRh');
        $builder = $db->table($this->view);
        $builder->select('*');
        if ($hol_id) {
            $builder->where("hol_id", $hol_id);
        }
        if ($emp_id) {
            $builder->where("emp_id", $emp_id);
        }
        if ($competencia) {
            $builder->where("hol_mesanocompetencia", $competencia);
        }
        $builder->orderBy("col_nome, hol_competencia, emp_id");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);

        return $ret;
    }

    /**
     * getHoleriteUnico
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getHoleriteUnico($emp_id, $col_id, $competencia)
    {
        $db = db_connect('dbRh');
        $builder = $db->table($this->view);
        $builder->select('*');
        $builder->where("emp_id", $emp_id);
        $builder->where("col_id", $col_id);
        $builder->where("hol_competencia", $competencia);
        $builder->orderBy("hol_competencia, emp_id, col_nome");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);

        return $ret;
    }


    /**
     * getHoleriteSearch
     *
     * Retorna os dados da Linha, pelo termo (nome) informado
     * Utilizado nas Seleções de Linha
     *  
     * @param mixed $termo 
     * @return array
     */
    public function getHoleriteSearch($termo)
    {
        $db = db_connect('dbRh');
        $builder = $db->table($this->table);
        $builder->select('*');
        $builder->groupStart();
        $builder->like('hol_competencia', $termo);
        $builder->groupEnd();
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }

    /**
     * getHoleriteSearch
     *
     * Retorna os dados da Linha, pelo termo (nome) informado
     * Utilizado nas Seleções de Linha
     *  
     * @param mixed $empresa
     * @return array
     */
    public function getCompetencia($empresa)
    {
        $db = db_connect('dbRh');
        $builder = $db->table('vw_rh_competencia');
        $builder->select('*');
        $builder->where('emp_id', $empresa);
        $builder->orderBy('data_competencia', 'DESC');
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }
}
