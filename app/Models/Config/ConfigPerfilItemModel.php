<?php

namespace App\Models\Config;

use CodeIgniter\Model;

class ConfigPerfilItemModel extends Model
{
    protected $DBGroup          = 'dbConfig';

    protected $table      = 'cfg_perfil_item';
    protected $primaryKey = 'pit_id';

    protected $returnType     = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = ['pit_id',
                                'prf_id',
                                'mod_id',
                                'tel_id',
                                'pit_permissao'
                                ];

    public function excluiItemPerfil($chave, $valor)
    {
        $this->builder()->where($chave, $valor);
        $this->builder()->delete();
    }

    public function getItemPerfil($perfil)
    {
        $db = db_connect('dbConfig');
        $builder = $db->table('vw_cfg_perfil_item_relac');
        $builder->select('*');
        if ($perfil) {
            $builder->where("prf_id", $perfil);
        }
        $ret = $builder->get()->getResultArray();
        // d($this->db->getLastQuery());
        return $ret;
    }

    public function getItemPerfilClasse($perfil, $classe)
    {
        $db = db_connect('dbConfig');
        $builder = $db->table('vw_cfg_perfil_item_relac');
        $builder->select('*');
        if($perfil != 0){
            $builder->where('prf_id', $perfil);
        }
        $builder->groupStart();
        $builder->where('tel_nome', $classe);
        $builder->orWhere('tel_id', $classe);
        $builder->groupEnd();
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery());
        return $ret;
    }
}
