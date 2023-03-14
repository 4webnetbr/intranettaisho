<?php namespace App\Models;

use App\Libraries\MongoDb;

class LogMonModel {

	private $database = 'MongoDB';
	private $collection = 'Logs';
	private $conn;

	function __construct() {
		$mongodb = new MongoDb();
		$this->conn = $mongodb->getConn();
	}

	function get_logs_list($tabela, $registro) {
		try {
			$filter = [
                'log_tabela'=>$tabela,
                'log_id_registro'=>$registro, 
                'log_data'=>[ '$gt'  =>  '' ]
            ];
            $options = [
                'projection' => ['_id' => 0],
                'sort' => ['log_data' => -1],
                'limit' => 1
            ];            
			$query = new \MongoDB\Driver\Query($filter, $options);

			$result = $this->conn->executeQuery($this->database . '.' . $this->collection, $query);
			
			return $result->toArray();
		} catch(\MongoDB\Driver\Exception\RuntimeException $ex) {
			show_error('Error while fetching logs: ' . $ex->getMessage(), 500);
		}
	}

	function get_logs_id($_id) {
		try {
			$filter = ['_id' => new \MongoDB\BSON\ObjectId($_id)];
			$query = new \MongoDB\Driver\Query($filter);

			$result = $this->conn->executeQuery($this->database.'.'.$this->collection, $query);

			foreach($result as $logs) {
				return $logs;
			}

			return null;
		} catch(\MongoDB\Driver\Exception\RuntimeException $ex) {
			show_error('Error while fetching logs: ' . $ex->getMessage(), 500);
		}
	}

    /**
     * insertLog
     *
     * Insere o Registro na Tabela de Log
     *  
     * @param string $tabela
     * @param string $operacao
     * @param int    $registro
     * @param array  $dados
     * @return void
     */
    public function insertLog($tabela, $operacao, $registro, $dados)
    {
		try {
            $sql_data = [
                'log_tabela'        => $tabela,
                'log_operacao'      => $operacao,
                'log_id_registro'   => strval($registro),
                'log_id_usuario'    => session()->get('usu_nome'),
                'log_data'          => date('Y-m-d H:i:s'),
                'log_dados'         => $dados
            ];

			$query = new \MongoDB\Driver\BulkWrite();
			$query->insert($sql_data);

			$result = $this->conn->executeBulkWrite($this->database.'.'.$this->collection, $query);

			if($result->getInsertedCount() == 1) {
				return true;
			}

			return false;
		} catch(\MongoDB\Driver\Exception\RuntimeException $ex) {
			show_error('Error while saving log: ' . $ex->getMessage(), 500);
		}
	}

	function update_log($_id, $name, $email) {
		try {
			$query = new \MongoDB\Driver\BulkWrite();
			$query->update(['_id' => new \MongoDB\BSON\ObjectId($_id)], ['$set' => array('name' => $name, 'email' => $email)]);

			$result = $this->conn->executeBulkWrite($this->database . '.' . $this->collection, $query);

			if($result->getModifiedCount()) {
				return true;
			}

			return false;
		} catch(\MongoDB\Driver\Exception\RuntimeException $ex) {
			show_error('Error while updating log: ' . $ex->getMessage(), 500);
		}
	}

	function delete_log($_id) {
		try {
			$query = new \MongoDB\Driver\BulkWrite();
			$query->delete(['_id' => new \MongoDB\BSON\ObjectId($_id)]);

			$result = $this->conn->executeBulkWrite($this->database . '.' . $this->collection, $query);

			if($result->getDeletedCount() == 1) {
				return true;
			}

			return false;
		} catch(\MongoDB\Driver\Exception\RuntimeException $ex) {
			show_error('Error while deleting log: ' . $ex->getMessage(), 500);
		}
	}

}