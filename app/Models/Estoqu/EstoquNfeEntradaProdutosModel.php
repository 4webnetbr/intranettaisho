<?php

namespace App\Models\Estoqu;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class EstoquNfeEntradaProdutosModel extends Model
{
    protected $DBGroup          = 'dbEstoque';

    protected $table            = 'est_nfe_entrada_produtos';
    protected $primaryKey       = 'nfp_id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'nfp_id',
        'nfe_id',

        'nfp_numero_item',
        'nfp_codigo_produto',
        'nfp_descricao',
        'nfp_ncm',
        'nfp_cfop',
        'nfp_unidade',

        'nfp_quantidade',
        'nfp_valor_unitario',
        'nfp_valor_total',

        'nfp_origem',
        'nfp_cst',
        'nfp_csosn',
        'nfp_icms',
        'nfp_ipi',
        'nfp_pis',
        'nfp_cofins',

    ];

    // protected $deletedField = 'nfp_excluido';

    protected $allowCallbacks = true;
    protected $afterInsert   = ['depoisInsert'];
    protected $afterUpdate   = ['depoisUpdate'];
    protected $afterDelete   = ['depoisDelete'];

    protected function depoisInsert(array $data)
    {
        if (!isset($data['id'])) {
            return $data;
        }

        $log = new LogMonModel();
        $registro = is_array($data['id']) ? ($data['id'][0] ?? null) : $data['id'];

        if ($registro) {
            $log->insertLog($this->table, 'Incluído', $registro, $data['data'] ?? []);
        }

        return $data;
    }

    protected function depoisUpdate(array $data)
    {
        if (!isset($data['id']) || empty($data['id'][0])) {
            return $data; // update sem ID
        }

        $log = new LogMonModel();
        $registro = $data['id'][0];

        $log->insertLog($this->table, 'Alterado', $registro, $data['data'] ?? []);

        return $data;
    }

    protected function depoisDelete(array $data)
    {
        // Quando não existe nada a excluir, o CI4 manda id = null
        if (!isset($data['id']) || empty($data['id'][0])) {
            return $data; // nada a logar
        }

        $log = new LogMonModel();
        $registro = $data['id'][0];

        $log->insertLog($this->table, 'Excluído', $registro, $data['data'] ?? []);

        return $data;
    }
    /**
     * Retorna produtos de uma NF específica
     */
    public function getProdutos($nfe_id)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table($this->table);
        $builder->select('*');
        $builder->where("nfe_id", $nfe_id);
        $builder->orderBy("nfp_numero_item");

        return $builder->get()->getResultArray();
    }
}
