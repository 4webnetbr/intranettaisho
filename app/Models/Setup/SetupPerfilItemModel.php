<?php namespace App\Models\Setup;

use CodeIgniter\Model;

class SetupPerfilItemModel extends Model {

    protected $table      = 'setup_perfil_item';
    protected $primaryKey = 'pit_id';

    protected $returnType     = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = ['pit_id',
                                'pit_perfil_id',
                                'pit_modulo_id',
                                'pit_classe_id',
                                'pit_permissao'
                                ];
                                
    public function excluiItemPerfil($chave, $valor)
    {
        $this->builder()->where($chave, $valor);
        $this->builder()->delete();
    }                                

    public function getItemPerfil($perfil){
        $db = db_connect();
        $builder = $db->table('vw_setup_perfil_item_relac');
        $builder->select('*'); 
        if($perfil){
            $builder->where("pit_perfil_id",$perfil);
        }
        $ret = $builder->get()->getResultArray();
        // d($this->db->getLastQuery());
        return $ret;
    }

    public function getItemPerfilClasse($perfil, $classe){
        $db = db_connect();
        $builder = $db->table('vw_setup_perfil_item_relac');
        $builder->select('*'); 
        $builder->where('pit_perfil_id', $perfil);
        $builder->where('clas_titulo', $classe);
        $builder->orWhere('pit_classe_id', $classe);
        $ret = $builder->get()->getResultArray();
        // d($this->db->getLastQuery());
        return $ret;
    }
}