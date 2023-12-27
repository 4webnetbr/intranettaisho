<?php namespace App\Models\Config;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class ConfigTelaModel extends Model
{
    protected $DBGroup          = 'dbConfig';

    protected $table            = 'cfg_tela';
    protected $primaryKey       = 'tel_id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields    = ['tel_id',
                                    'mod_id',
                                    'tel_nome',
                                    'tel_icone',
                                    'tel_controler',
                                    'tel_metodo',
                                    'tel_texto_botao',
                                    'tel_tabela',
                                    'tel_descricao',
                                    'tel_regras_gerais',
                                    'tel_regras_cadastro',
                                    'tel_lista'
                                ];


    protected $skipValidation   = true;

    // protected $createdField  = 'tel_criado';
    // protected $updatedField  = 'tel_alterado';
    protected $deletedField  = 'tel_excluido';

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
        $logdb->insertLog($this->table, 'Incluído', $registro, $data['data']);
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
        $logdb->insertLog($this->table, 'Alteração', $registro, $data['data']);
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
        $logdb->insertLog($this->table, 'Excluído', $registro, $data['data']);
        return $data;
    }

    /**
     * getTelaId
     *
     * Retorna os dados da Tela, pelo ID informado
     *
     * @param bool $id
     * @return array
     */
    public function getTelaId($tel_id = false)
    {
        $db = db_connect('dbConfig');
        $builder = $db->table('vw_cfg_tela_relac');
        $builder->select('*');
        if ($tel_id) {
            $builder->where("tel_id", $tel_id);
        }
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);

        return $ret;
    }

    /**
     * getTelaSearch
     *
     * Retorna os dados da Tela, pelo termo (nome) informado
     * Utilizado nas Seleções de Tela
     *
     * @param mixed $termo
     * @return array
     */
    public function getTelaSearch($termo)
    {
        $s_nome = ['tel_nome' => $termo];
        $s_contro = ['tel_controler' => $termo];
        $db = db_connect('dbConfig');
        $builder = $db->table('vw_cfg_tela_relac');
        $builder->select('*');
        $builder->like($s_contro);
        // $builder->like($s_nome);
        // $builder->orLike($s_nome);
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }

    /**
     * getTelaModulo
     *
     * Retorna os dados da Tela, pelo termo (nome) informado
     * Utilizado nas Seleções de Tela
     *
     * @param mixed $termo
     * @return array
     */
    public function getTelaModulo($modu)
    {
        $s_modu = ['mod_id' => $modu];
        $db = db_connect('dbConfig');
        $builder = $db->table('vw_cfg_tela_relac');
        $builder->select('*');
        $builder->where($s_modu);
        // $builder->like($s_nome);
        // $builder->orLike($s_nome);
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }

    /**
     * getTelaPerfil
     *
     * Retorna os dados da Tela, pelo perfil (id) informado
     * Utilizado nas Seleções de Tela
     *  
     * @param mixed $perfil
     * @return array
     */
    public function getTelaPerfil($perfil = false)
    {
        $db = db_connect('dbConfig');
        $builder = $db->table('vw_cfg_tela_relac');
        $builder->select('*');
        if ($perfil) {
            // $builder->join('cfg_perfil_item pit', 'pit.tel_id = vw_cfg_tela_relac.tel_id 
            //                 AND pit.prf_id = ' . $perfil, 'left');
            $builder->where('prf_id', $perfil);
            $builder->orWhere('prf_id', null);
        }
        $builder->orderBy('mod_nome');
        $ret = $builder->get()->getResultArray();
        // debug($ret);
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }
}
