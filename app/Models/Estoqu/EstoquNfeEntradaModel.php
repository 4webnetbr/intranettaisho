<?php

namespace App\Models\Estoqu;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class EstoquNfeEntradaModel extends Model
{
    protected $DBGroup          = 'dbEstoque';

    protected $table            = 'est_nfe_entrada';
    protected $primaryKey       = 'nfe_id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'nfe_id',
        'emp_id',
        'nfe_chave',
        'nfe_modelo',
        'nfe_serie',
        'nfe_numero',
        'nfe_data_emissao',
        'nfe_data_entrada',
        'for_id',
        'nfe_fornecedor_nome',
        'nfe_fornecedor_cnpj',
        'nfe_valor_total',
        'nfe_xml',
        'nfe_resumo',
        'nfe_protocolo',
        'nfe_tipo_evento',
        'nfe_status',
    ];

    // protected $deletedField = 'nfe_excluido';

    // protected $allowCallbacks = true;
    // protected $afterInsert   = ['depoisInsert'];
    // protected $afterUpdate   = ['depoisUpdate'];
    // protected $afterDelete   = ['depoisDelete'];

    /**
     * Logs
     */
    // protected function depoisInsert(array $data)
    // {
    //     $log = new LogMonModel();
    //     $registro = $data['id'];
    //     $log->insertLog($this->table, 'Incluído', $registro, $data['data']);
    //     return $data;
    // }

    // protected function depoisUpdate(array $data)
    // {
    //     $log = new LogMonModel();
    //     $registro = $data['id'][0];
    //     $log->insertLog($this->table, 'Alterado', $registro, $data['data']);
    //     return $data;
    // }

    // protected function depoisDelete(array $data)
    // {
    //     $log = new LogMonModel();
    //     $registro = $data['id'][0];
    //     $log->insertLog($this->table, 'Excluído', $registro, $data['data']);
    //     return $data;
    // }

    /**
     * getEntrada
     * Retorna a NF-e pelo ID ou pela chave.
     */
    public function getEntrada($nfe_id = false, $chave = false, $empresa = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table($this->table);
        $builder->select('*');

        if ($nfe_id) {
            $builder->where("nfe_id", $nfe_id);
        }

        if ($chave) {
            $builder->where("nfe_chave", $chave);
        }

        if ($empresa) {
            $builder->where("emp_id", $empresa);
        }

        $builder->orderBy("nfe_id DESC");

        return $builder->get()->getResultArray();
    }

    /**
     * getEntradaPorPeriodo
     */
    public function getEntradaPorPeriodo($empresa, $inicio, $fim)
    {
        if (!$empresa || !$inicio || !$fim) {
            return [];
        }

        $db = db_connect('dbEstoque');
        $builder = $db->table($this->table);

        $builder->select("*");
        $builder->where("emp_id", $empresa);
        $builder->where("CAST(nfe_data_emissao AS DATE) >=", $inicio);
        $builder->where("CAST(nfe_data_emissao AS DATE) <=", $fim);

        return $builder->get()->getResultArray();
    }
}
