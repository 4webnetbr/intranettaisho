<?php

namespace App\Models\Config;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class ConfigModuloModel extends Model
{
    protected $DBGroup          = 'dbConfig';
    protected $table            = 'cfg_modulo';
    protected $primaryKey       = 'mod_id';
    protected $useAutoIncremodt = true;

    protected $returnType       = 'array';
    // protected $useSoftDeletes   = true;

    protected $allowedFields    = ['mod_id',
                                    'mod_nome',
                                    'mod_icone',
                                    'mod_ativo'
                                ];

    protected $deletedField  = 'mod_excluido';

    protected $validationRules = [
        'mod_nome' => 'required|is_unique[cfg_modulo.mod_nome, mod_id,{mod_id}]',
    ];

    protected $validationMessages = [
        'mod_nome' => [
            'required' => 'O campo Nome do Módulo é Obrigatório',
            'is_unique' => 'Já existe um Módulo com o nome informado!',
        ],
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

    public function getModulo($mod_id = false)
    {
        $this->builder()->select('*');
        if ($mod_id) {
            $this->builder()->where('mod_id', $mod_id);
        }
        $this->builder()->where('mod_excluido', null);
        $this->builder()->orderBy('mod_order');
        return $this->builder()->get()->getResultArray();
    }

    public function getModulosSearch($termo)
    {
        $array = ['mod_nome' => $termo . '%'];
        $this->builder()->select(['mod_id','mod_nome','mod_icone']);
        $this->builder()->where('mod_excluido', null);
        $this->builder()->like($array);

        return $this->builder()->get()->getResultArray();
    }
}
