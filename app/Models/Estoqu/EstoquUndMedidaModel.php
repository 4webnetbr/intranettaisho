<?php

namespace App\Models\Estoqu;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class EstoquUndMedidaModel extends Model
{
    protected $DBGroup          = 'dbEstoque';

    protected $table            = 'est_unidades';
    protected $view             = 'est_unidades';
    protected $primaryKey       = 'und_id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields    = [
        'und_id',
        'und_sigla',
        'und_nome',
        'und_descricao',
    ];


    protected $skipValidation   = true;

    protected $deletedField  = 'und_excluido';

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
     * getUndMedida
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getUndMedida($und_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('est_unidades');
        $builder->select("*, concat(und_sigla,' - ',und_nome) as und_completa");
        if ($und_id) {
            $builder->where("und_id", $und_id);
        }
        $builder->where("und_excluido", null);
        $builder->orderBy("und_sigla");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);

        return $ret;
    }

    /**
     * getUndMedidaSearch
     *
     * Retorna os dados da Linha, pelo termo (nome) informado
     * Utilizado nas Seleções de Linha
     *  
     * @param mixed $termo 
     * @return array
     */
    public function getUndMedidaSearch($termo)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('est_unidades');
        $builder->select('*');
        $builder->groupStart();
        $builder->like('und_sigla', $termo);
        $builder->orLike('und_nome', $termo);
        $builder->groupEnd();
        $builder->where("und_excluido", null);
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }

    /**
     * getConversao
     *
     * Retorna a tabela de conversoes da medida
     * 
     * @param bool $id 
     * @return array
     */
    public function getConversao($und_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('est_conversao');
        $builder->select('*');
        if ($und_id) {
            $builder->where("cvs_id_origem", $und_id);
        }
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);

        return $ret;
    }

    /**
     * getConversaoDePara
     *
     * Retorna a tabela de conversoes da medida
     * 
     * @param mixed $und_de 
     * @param mixed $und_para 
     * @return array
     */
    public function getConversaoDePara($und_de, $und_para)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('est_conversao');
        $builder->select('*');
        $builder->where("cvs_id_origem", $und_de);
        $builder->where("cvs_id_destino", $und_para);
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);

        return $ret;
    }
}
