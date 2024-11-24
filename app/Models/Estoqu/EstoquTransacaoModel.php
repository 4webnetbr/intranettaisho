<?php

namespace App\Models\Estoqu;

use App\Libraries\MyCampo;
use App\Models\LogMonModel;
use CodeIgniter\Model;

class EstoquTransacaoModel extends Model
{
    protected $DBGroup          = 'dbEstoque';
    protected $table            = 'est_sap_transacao';
    protected $view             = 'est_sap_transacao';
    protected $primaryKey       = 'tns_codtns';
    // protected $useAutoIncremodt = false;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [ 'tns_codtns',
                                    'tns_germvp',
                                    'tns_dettns',
                                ];

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
        $log = $logdb->insertLog($this->table, 'Alteração', $registro, $data['data']);
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

    public function getTransacao($dep_id = false)
    {
        $this->builder()->select('*');
        if ($dep_id) {
            $this->builder()->where('tns_codtns', $dep_id);
        }
        return $this->builder()->get()->getResultArray();
    }

    public function getTransacaoSearch($termo)
    {
        $array = ['tns_codtns' => $termo . '%'];
        $this->builder()->select('*');
        $this->builder()->like($array);

        return $this->builder()->get()->getResultArray();
    }

    public function defCampos($dados = false, $show = false)
    {
        $cdep           =  new MyCampo('est_sap_transacao', 'tns_codtns', true);
        $cdep->valor    = (isset($dados['tns_codtns'])) ? $dados['tns_codtns'] : '';
        $cdep->obrigatorio = true;
        $cdep->leitura  = $show;
        $ret['tns_codtns'] = $cdep->crInput();

        $ddep           =  new MyCampo('est_sap_transacao', 'tns_germvp');
        $ddep->valor    = (isset($dados['tns_germvp'])) ? $dados['tns_germvp'] : '';
        $ddep->obrigatorio = true;
        $ddep->leitura  = $show;
        $ret['tns_germvp'] = $ddep->crInput();

        $acng           =  new MyCampo('est_sap_transacao', 'tns_dettns');
        $acng->valor    = (isset($dados['tns_dettns'])) ? $dados['tns_dettns'] : '';
        $acng->obrigatorio = true;
        $acng->leitura  = $show;
        $ret['tns_dettns'] = $acng->crInput();
        
        return $ret;
    }    

}
