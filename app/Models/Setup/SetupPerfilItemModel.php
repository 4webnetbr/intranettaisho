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
                                'pit_menu_id',
                                'pit_permissao'
                                ];
                                
    public function excluiItemPerfil($chave, $valor)
    {
        $this->builder()->where($chave, $valor);
        $this->builder()->delete();
    }                                

    public function getItemPerfil($perfil, $menu){
        $this->builder()->where('pit_perfil_id', $perfil);
        $this->builder()->where('pit_menu_id', $menu);
        $ret = $this->builder()->get()->getResultArray();
        // d($this->db->getLastQuery());  
        return $ret;
    }

    public function getItemPerfilClasse($perfil, $classe){
        $this->builder()
        ->select("{$this->table}.*")
        ->join('setup_menu menu', 'menu.men_id = setup_perfil_item.pit_menu_id AND menu.men_excluido IS NULL')
        ->join('setup_classe clas', 'clas.clas_id = menu.men_classe_id AND clas.clas_excluido IS NULL');
        $this->builder()->where('pit_perfil_id', $perfil);
        $this->builder()->where('clas_titulo', $classe);
        return $this->builder()->get()->getResultArray();
    }
}