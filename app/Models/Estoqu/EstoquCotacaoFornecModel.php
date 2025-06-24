<?php

namespace App\Models\Estoqu;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class EstoquCotacaoFornecModel extends Model
{
    protected $DBGroup          = 'dbEstoque';

    protected $table            = 'est_cotacao_fornec';
    protected $view             = '';
    protected $primaryKey       = 'cof_id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'cof_id',
        'cot_id',
        'cop_id',
        'pro_id',
        'mar_id',
        'for_id',
        'cof_preco',
        'cof_precoundcompra',
        'cof_validade',
        'cof_previsao',
        'cof_observacao',
    ];


    protected $skipValidation   = true;

    protected $deletedField  = 'cot_excluido';

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
     * Exclui registros da cotação para um determinado fornecedor
     *
     * @param int $cot_id
     * @param int $for_id
     * @return bool
     */
    public function excluirPorCotacaoEFornecedor(int $cot_id, int $for_id): bool
    {
        return $this->builder()
            ->where('cot_id', $cot_id)
            ->where('for_id', $for_id)
            ->delete();
    }
}
