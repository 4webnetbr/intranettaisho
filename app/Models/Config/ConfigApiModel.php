<?php

namespace App\Models\Config;

use App\Libraries\MyCampo;
use App\Models\LogMonModel;
use CodeIgniter\Model;

class ConfigApiModel extends Model
{
    protected $DBGroup          = 'dbConfig';
    protected $table            = 'cfg_api';
    protected $view             = 'cfg_api';
    protected $primaryKey       = 'api_id';
    protected $useAutoIncremodt = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields    = ['api_id',
                                    'api_nome',
                                    'api_descricao',
                                    'api_url',
                                    'api_login',
                                    'api_usuario',
                                    'api_acckey',
                                    'api_tokenkey',                                    
                                ];

    protected $deletedField  = 'api_excluido';

    protected $validationRules = [
        'api_nome' => 'required|is_unique[cfg_api.api_nome, api_id,{api_id}]',
    ];

    protected $validationMessages = [
        'api_nome' => [
            'required' => 'O campo Nome do Api é Obrigatório',
            'is_unique' => 'Já existe um Api com o nome informado!',
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

    public function getApi($api_id = false)
    {
        $db = db_connect('dbConfig');
        $sql = "SELECT * FROM `cfg_api` WHERE `api_excluido` IS NULL ";
        if($api_id){
            $sql .= "AND `api_id` = '".$api_id."'";
        }
        $query = $db->query($sql);
        $ret = $query->getResultArray();
        $lq = $query = $db->getLastQuery();
        // debug($lq);
        // debug($ret);
        return $ret;
    }

    public function getApisSearch($termo)
    {
        $db = db_connect('dbConfig');
        $query = $db->query("SELECT * FROM `cfg_api` WHERE `api_excluido` IS NULL AND `api_nome` LIKE '".$termo."%'");
        $ret = $query->getResultArray();
        $lq = $query = $db->getLastQuery();
        // debug($lq);
        // debug($ret);
        return $ret;
    }

    public function defCampos($dados = false)
    {
        $ret = [];
        $mid            = new MyCampo('cfg_api', 'api_id');
        $mid->valor     = (isset($dados['api_id'])) ? $dados['api_id'] : '';
        $ret['api_id']   = $mid->crOculto();

        $nome           =  new MyCampo('cfg_api', 'api_nome');
        $nome->valor    = (isset($dados['api_nome'])) ? $dados['api_nome'] : '';
        $nome->obrigatorio = true;
        $ret['api_nome'] = $nome->crInput();

        $url           =  new MyCampo('cfg_api', 'api_url');
        $url->tipo     = 'url';
        $url->valor    = (isset($dados['api_url'])) ? $dados['api_url'] : '';
        $ret['api_url'] = $url->crInput();

        $log           =  new MyCampo('cfg_api','api_login');
        $log->valor    = (isset($dados['api_login'])) ? $dados['api_login'] : '';
        $ret['api_login'] = $log->crInput();

        $usu           =  new MyCampo('cfg_api', 'api_usuario');
        $usu->valor    = (isset($dados['api_usuario'])) ? $dados['api_usuario'] : '';
        $ret['api_usuario'] = $usu->crInput();

        $key           =  new MyCampo('cfg_api', 'api_acckey');
        $key->valor    = (isset($dados['api_acckey'])) ? $dados['api_acckey'] : '';
        $ret['api_acckey'] = $key->crInput();

        $tok           =  new MyCampo('cfg_api', 'api_tokenkey');
        $tok->valor    = (isset($dados['api_tokenkey'])) ? $dados['api_tokenkey'] : '';
        $ret['api_tokenkey'] = $tok->crInput();

        $des           =  new MyCampo('cfg_api', 'api_descricao');
        $des->valor    = (isset($dados['api_descricao'])) ? $dados['api_descricao'] : '';
        $ret['api_descricao'] = $des->crInput();

        return $ret;
    }    
}
