<?php

namespace App\Models\Estoqu;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class EstoquFornecedorModel extends Model
{
    protected $DBGroup          = 'dbEstoque';

    protected $table            = 'est_fornecedor';
    protected $view             = 'est_fornecedor';
    protected $primaryKey       = 'for_id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields    = [
        'for_id',
        'for_razao',
        'for_fantasia',
        'for_cnpj',
        'for_pessoa',
        'for_cep',
        'for_rua',
        'for_bairro',
        'for_numero',
        'for_complemento',
        'for_cidade',
        'for_estado',
        'for_fone',
        'for_contato',
        'for_grupo',
        'for_minimo',
    ];


    protected $skipValidation   = true;

    protected $deletedField  = 'for_excluido';

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
     * getFornecedor
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $id 
     * @return array
     */
    public function getFornecedor($for_id = false, $emp_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('est_fornecedor');
        $builder->select('*');
        if ($for_id) {
            $builder->where("for_id", $for_id);
        }
        if ($emp_id) {
            $builder->where("emp_id", $emp_id);
        }
        $builder->orderBy("for_razao, for_fantasia");
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);

        return $ret;
    }

    /**
     * getFornecedorCNPF
     *
     * Retorna os dados da Linha, pelo ID informado
     * 
     * @param bool $cnpj 
     * @return array
     */
    public function getFornecedorCNPJ($cnpj = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table($this->view);
        $builder->select('*');
        
        if ($cnpj) {
            // Remove os caracteres da variável $cnpj
            $cleanCnpj = preg_replace('/[^0-9]/', '', $cnpj);

            // Remove os caracteres do campo for_cnpj na consulta SQL
            $builder->where("REPLACE(REPLACE(REPLACE(for_cnpj, '.', ''), '/', ''), '-', '')", $cleanCnpj);
        }

        $builder->orderBy("for_fantasia");
        $ret = $builder->get()->getResultArray();
        debug($this->db->getLastQuery(), false);

        return $ret;
    }

    /**
     * getFornecedorSearch
     *
     * Retorna os dados da Linha, pelo termo (nome) informado
     * Utilizado nas Seleções de Linha
     *  
     * @param mixed $termo 
     * @return array
     */
    public function getFornecedorSearch($termo)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table($this->view);
        $builder->select('*');
        $builder->groupStart();
        $builder->like('for_razao', $termo);
        $builder->orLike('for_fantasia', $termo);
        $builder->groupEnd();
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }
}
