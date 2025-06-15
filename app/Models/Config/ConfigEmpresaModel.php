<?php

namespace App\Models\Config;

use App\Libraries\MyCampo;
use App\Models\LogMonModel;
use CodeIgniter\Model;

class ConfigEmpresaModel extends Model
{
	private $emp_user;
	protected $DBGroup          = 'dbConfig';

	protected $table            = 'cfg_empresa';
	protected $view             = 'cfg_empresa';
	protected $primaryKey       = 'emp_id';
	protected $useAutoIncrement = true;

	protected $returnType       = 'array';
	protected $useSoftDeletes   = true;

	protected $allowedFields    = [
		'emp_id',
		'emp_nome',
		'emp_apelido',
		'emp_cnpj',
		'emp_ie',
		'emp_status',
		'emp_codempresa',
		'emp_codfilial',
		'emp_cnae',
		'emp_cnae_desc',
		'emp_obs',
		'emp_endereco',
	];


	protected $skipValidation   = true;

	protected $deletedField  = 'emp_excluido';

	// Callbacks
	protected $allowCallbacks = true;

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

	public function getEmpresa($id = FALSE, $relac = false, $tipo = 0)
	{
		$db = db_connect('dbConfig');
		$builder = $db->table('cfg_empresa emp');
		$builder->select('*');
		if ($id) {
			if (gettype($id) == 'array') {
				if (gettype($id[0]) == 'array') {
					$builder->whereIn('emp.emp_id', $id[0]);
				} else {
					$builder->whereIn('emp.emp_id', $id);
				}
			} else {
				$builder->where('emp.emp_id', $id);
			}
		}
		if ($relac) {
			$builder->groupStart();
			$builder->where('emp.emp_nome', $relac);
			$builder->orWhere('emp.emp_nome', 0);
			$builder->groupEnd();
		}
		if ($tipo == 0) {
			$builder->groupStart();
			$builder->where('emp.emp_status', 1);
			$builder->orWhere('emp.emp_status', 2);
			$builder->groupEnd();
		}
		$builder->orderBy('emp_codempresa,emp_codfilial', 'ASC');
		// $sql = $builder->getCompiledSelect();
		// debug($sql,false);
		$ret = $builder->get()->getResultArray();

		return $ret;
	}

	public function getContatos($id)
	{
		if (!$id) {
			return false;
		}
		$db = db_connect();
		$builder = $db->table('vw_contatos_empresa cli');
		$builder->select('*');
		// $builder->join('cfg_categoria cat','cat.cat_id = emp.emp_cat_id','inner');
		$builder->where('emp.emp_id', $id);
		$builder->orderBy('emp_nome', 'asc');
		$ret = $builder->get()->getResultArray();
		$sql = $this->db->getLastQuery();
		return $ret;
	}


	public function getEmpresaCNPJ($cnpj)
	{
		$db = db_connect('dbConfig');
		$builder = $db->table('cfg_empresa emp');
		$builder->select('*');
		// $builder->join('cfg_categoria cat','cat.cat_id = emp.emp_cat_id','inner');
		$builder->where(TRIM('emp.emp_cnpj'), trim($cnpj));
		$builder->orderBy('emp_nome', 'asc');
		$ret = $builder->get()->getResultArray();

		// debug($this->db->getLastQuery(), false);

		return $ret;
	}


	public function getListaCliente($cidade = '')
	{
		if ($cidade != '') {
			$db = db_connect();
			$builder = $db->table('vw_empresa_cliente_relac cli');
			$builder->select("*");
			// $builder->join('cfg_categoria cat','cat.cat_id = emp.emp_cat_id','inner');
			$builder->where('emp.emp_cidade', $cidade);

			$builder->orderBy('emp.emp_nome', 'asc');
			$ret = $builder->get()->getResultArray();

			// debug($this->db->getLastQuery(), false);

			return $ret;
		} else {
			return array();
		}
	}

	public function getPesqCliente($nome = '', $empresa = false)
	{
		$db = db_connect();
		$builder = $db->table('vw_empresa_cliente_relac cli');
		$builder->select("emp.emp_id, emp.emp_nome, emp.emp_nome_apelido ");
		$builder->groupStart();
		$builder->like('emp.emp_nome', $nome);
		$builder->orLike('emp.emp_apelido', $nome);
		$builder->groupEnd();

		if ($empresa) {
			$builder->groupStart();
			$builder->where('emp.emp_nome', $empresa);
			$builder->orWhere('emp.emp_nome', 0);
			$builder->groupEnd();
		}

		$builder->orderBy('emp.emp_nome', 'asc');
		$ret = $builder->get()->getResultArray();

		// debug($this->db->getLastQuery(), false);

		return $ret;
	}

	public function getCodigo($codigo = FALSE)
	{
		if ($codigo) {
			$db = db_connect();
			$builder = $db->table('vw_empresa_cliente_relac cli');
			$builder->select('*');
			$builder->where('TRIM(emp.emp_codigo)', trim($codigo));

			$builder->orderBy('emp_nome', 'asc');
			$ret = $builder->get()->getResultArray();

			// debug($this->db->getLastQuery(), false);

			return $ret;
		} else
			return false;
	}

	public function getPreCadastrados($desde = FALSE)
	{
		$db = db_connect();
		$builder = $db->table('vw_empresa_cliente_relac cli');
		$builder->select('*');

		$builder->where('emp.emp_status', 2);

		if ($desde)
			$builder->where('emp.emp_data_cad >= ', $desde);
		$builder->orderBy('emp_nome', 'asc');
		$ret = $builder->get()->getResultArray();

		// debug($this->db->getLastQuery(), false);

		return $ret;
	}

	public function getListaMaterialFornec($idforn = false)
	{
		$db = db_connect();
		$builder = $db->table('cfg_cliente forn');
		$builder->select('mpc.*, mat.*, pdc.*, forn.emp_nome as forn_nome');
		$builder->join('cfg_compras pdc', 'pdc.pdc_id_fornecedor = forn.emp_id', 'left');
		$builder->join('cfg_compras_mat mpc', 'mpc.mpc_id_pdc = pdc.pdc_id', 'left');
		$builder->join('cfg_materiais mat', 'mat.mat_id = mpc.mpc_id_material', 'left');
		if ($idforn)
			$builder->where('pdc.pdc_id_fornecedor', $idforn);

		$builder->groupBy('mat.mat_id');


		$builder->orderBy('pdc.pdc_data', 'DESC');
		$builder->orderBy('pdc.pdc_id', 'DESC');
		$builder->orderBy('forn.emp_nome', 'asc');

		$ret = $builder->get()->getResultArray();

		// debug($this->db->getLastQuery(), false);

		return $ret;
	}

	public function getFornecedor($id = FALSE, $tipo = 0)
	{
		$db = db_connect();
		$builder = $db->table('vw_empresa_fornecedor_relac cli');
		$builder->select('*');
		// $builder->join('cfg_categoria cat','cat.cat_id = emp.emp_cat_id','inner');
		if ($id) {
			$builder->where('emp.emp_id', $id);
		}
		if ($tipo == 0) {
			$builder->groupStart();
			$builder->where('emp.emp_status', 1);
			$builder->orWhere('emp.emp_status', 2);
			$builder->groupEnd();
		}
		$builder->orderBy('emp_nome', 'asc');
		$ret = $builder->get()->getResultArray();
		$sql = $this->db->getLastQuery();
		// debug($sql,false);
		// debug($this->db->getLastQuery(), false);

		return $ret;
	}

	public function getFornecedorSearch($nome = false)
	{
		if (!$nome) {
			return false;
		}
		$db = db_connect();
		$builder = $db->table('vw_empresa_fornecedor_relac cli');
		$builder->select('*');
		$builder->groupStart();
		$builder->like('emp.emp_nome', $nome);
		$builder->orLike('emp.emp_apelido', $nome);
		$builder->groupEnd();
		$builder->orderBy('emp_nome', 'asc');
		$ret = $builder->get()->getResultArray();
		$sql = $this->db->getLastQuery();
		// debug($sql,false);
		// debug($this->db->getLastQuery(), false);

		return $ret;
	}

	public function getTransportadoraSearch($nome = false)
	{
		if (!$nome) {
			return false;
		}
		$db = db_connect();
		$builder = $db->table('vw_empresa_transportadora_relac cli');
		$builder->select('*');
		// $builder->join('cfg_categoria cat','cat.cat_id = emp.emp_cat_id','inner');
		$builder->groupStart();
		$builder->like('emp.emp_nome', $nome);
		$builder->orLike('emp.emp_apelido', $nome);
		$builder->groupEnd();
		$builder->orderBy('emp_nome', 'asc');
		$ret = $builder->get()->getResultArray();
		$sql = $this->db->getLastQuery();
		// debug($sql,false);
		// debug($this->db->getLastQuery(), false);

		return $ret;
	}

	public function inscompra($dados)
	{
		$db = db_connect();
		$builder = $db->table('cfg_fornecedor_compra');
		if ($dados['foc_id'] != '') {
			$grav = $builder->update($dados, 'foc_id = ' . $dados['foc_id']);
			if ($grav) {
				return $dados['foc_id'];
			}
		} else {
			$grav = $builder->insert($dados);
			if ($grav) {
				return $grav;
			}
		}
	}

	public function defCampos($dados = false, $show = false)
	{
		$nome           =  new MyCampo('cfg_empresa', 'emp_nomfil');
		$nome->valor    = (isset($dados['emp_nomfil'])) ? $dados['emp_nomfil'] : '';
		$nome->obrigatorio = true;
		$nome->leitura  = $show;
		$ret['emp_nomfil'] = $nome->crInput();

		$apel           =  new MyCampo('cfg_empresa', 'emp_sigfil');
		$apel->valor    = (isset($dados['emp_sigfil'])) ? $dados['emp_sigfil'] : '';
		$apel->obrigatorio = true;
		$apel->leitura  = $show;
		$ret['emp_sigfil'] = $apel->crInput();

		$code           =  new MyCampo('cfg_empresa', 'emp_codemp');
		$code->valor    = (isset($dados['emp_codemp'])) ? $dados['emp_codemp'] : '';
		$code->obrigatorio = true;
		$code->leitura  = $show;
		$ret['emp_codemp'] = $code->crInput();

		$codf           =  new MyCampo('cfg_empresa', 'emp_codfil', true);
		$codf->valor    = (isset($dados['emp_codfil'])) ? $dados['emp_codfil'] : '';
		$codf->obrigatorio = true;
		$codf->leitura  = $show;
		$ret['emp_codfil'] = $codf->crInput();

		$cnpj           =  new MyCampo('cfg_empresa', 'emp_numcgc');
		$cnpj->valor    = (isset($dados['emp_numcgc'])) ? $dados['emp_numcgc'] : '';
		$cnpj->obrigatorio = true;
		$cnpj->leitura  = $show;
		$ret['emp_numcgc'] = $cnpj->crInput();

		$inest           =  new MyCampo('cfg_empresa', 'emp_insest');
		$inest->valor    = (isset($dados['emp_insest'])) ? $dados['emp_insest'] : '';
		$inest->obrigatorio = true;
		$inest->leitura  = $show;
		$ret['emp_insest'] = $inest->crInput();

		return $ret;
	}
}
