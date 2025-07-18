<?php

namespace App\Models\Estoqu;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class EstoquMarcaModel extends Model
{
    protected $DBGroup          = 'dbEstoque';

    protected $table            = 'est_marca';
    protected $view             = 'vw_est_marcas_relac';
    protected $primaryKey       = 'mar_id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields    = [
        'mar_id',
        'pro_id',
        'mar_codigo',
        'mar_nome',
        'und_id',
        'mar_apresenta',
        'mar_conversao',
    ];


    protected $skipValidation   = false;
    protected $validationRules = [
        'mar_codigo' => 'required|is_unique[est_marca.mar_codigo, mar_id,{mar_id}]',
    ];

    protected $validationMessages = [
        'mar_codigo' => [
            'required' => 'O campo código é Obrigatório.',
            'is_unique' =>  'Esse código de barras, já está cadastrado'
        ],

    ];

    protected $deletedField  = 'mar_excluido';

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
     * getMarca
     *
     * Retorna os dados da Linha, pelo ID informado
     *
     * @param bool $id
     * @return array
     */
    public function getMarca($mar_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_marcas_relac');
        $builder->select('*');
        if ($mar_id) {
            $builder->where("mar_id", $mar_id);
        }
        $builder->orderBy("pro_nome");
        $ret = $builder->get()->getResultArray();

        // debug($db->getLastQuery(), false);

        return $ret;
    }

    /**
     * getMarca
     *
     * Retorna os dados da Linha, pelo ID informado
     *
     * @param bool $id
     * @return array
     */
    public function getMarcaProd($pro_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_marcas_relac');
        $builder->select('*');
        if ($pro_id) {
            $builder->where("pro_id", $pro_id);
        }
        $builder->orderBy("mar_nome");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);

        return $ret;
    }

    /**
     * getMarcaCod
     *
     * Retorna os dados da Linha, pelo ID informado
     *
     * @param bool $id
     * @return array
     */
    public function getMarcaCod($codigo = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_marcas_relac');
        $builder->select('*');
        if ($codigo) {
            $codigo12 = substr($codigo, 0, 12); // Pegando os 12 primeiros dígitos de $codigo
            $builder->where("LEFT(mar_codigo, 12)", $codigo12);
        }
        $builder->orderBy("pro_nome");
        $ret = $builder->get()->getResultArray();

        $sql = $this->db->getLastQuery();
        // log_message('info', 'SQL getMarcaCod: ' . $sql);

        return $ret;
    }

    /**
     * getMarcaSearch
     *
     * Retorna os dados da Linha, pelo termo (nome) informado
     * Utilizado nas Seleções de Linha
     *
     * @param mixed $termo
     * @return array
     */
    public function getMarcaSearch($termo)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_marcas_relac');
        $builder->select('*');
        $builder->groupStart();
        $builder->like('pro_nome', $termo);
        $builder->groupEnd();
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }
}
