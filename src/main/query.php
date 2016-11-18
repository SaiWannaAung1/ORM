<?php

class Query {
	private $pdo;
	private $select = "*";
	private $sorting = "id ASC";
	private $where = "1";
	private $limit = "";

	function __construct() {
		$data = json_decode(file_get_contents(__DIR__."/../../app/config/config.json"));
		$this->pdo = new MedruPDO (
    		'mysql:host='.$data->dataBaseHost.';dbname='.$data->dataBaseName,
   			$data->dataBaseUser,
    		$data->dataBasePass
		);
	}
	public function select($params) {
    if($params != null){
      foreach($params as $value){
        $this->query .=",";
        $i++;
        $this->query .= $value;
      }
    }else{
      $this->query = "SELECT *"
    }

	}

	public function where($params) {
		$i = 0;
		$this->where = "";
		foreach ($params as $key => $value) {
			if ($i > 0) $this->where .= " AND ";
			$i++;
			$this->where .= $value;
		}
		if ($this->where == "") $this->where = "1";
	}

	public function limit($limit, $offset = 0) {
		$this->limit = "LIMIT $offset, $limit";
	}

	public function orderBy($param, $type = 'ASC') {
		$this->sorting = "$param $type";
	}

	public function find($name) {
		$sth = $this->pdo->prepareAndExecuteQuery("SELECT $this->select FROM $name WHERE $this->where ORDER BY $this->sorting $this->limit;");
		$result = [];
		foreach ($sth->fetchAll() as $key => $value) {
		 	array_push($result, $this->createEntity($name, $value));
		}
		$this->sorting = "id ASC";
		$this->where = "1";
		$this->select = "*";
		$this->limit = "";
		return $result;
	}

	public function findAll($name) {
		$sth = $this->pdo->prepareAndExecuteQuery("SELECT * FROM $name;");
		$result = [];
		foreach ($sth->fetchAll() as $key => $value) {
		 	array_push($result, $this->createEntity($name, $value));
		}
		$this->sorting = "id ASC";
		$this->where = "1";
		$this->select = "*";
		$this->limit = "";
		return $result;
	}

	public function findOne($name) {
		$sth = $this->pdo->prepareAndExecuteQuery("SELECT $this->select FROM $name WHERE $this->where ORDER BY $this->sorting LIMIT 1;");
		$result = $this->createEntity($name, $sth->fetchAll()[0]);
		$this->sorting = "id ASC";
		$this->where = "1";
		$this->select = "*";
		$this->limit = "";
		return $result;
	}

	public function count($name) {
		$result = $this->find($name);
		$this->sorting = "id ASC";
		$this->where = "1";
		$this->select = "*";
		$this->limit = "";
		return count($result);
	}

	public function isEntity($name) {
		$result = $this->find($name);
		$this->sorting = "id ASC";
		$this->where = "1";
		$this->select = "*";
		$this->limit = "";
		return count($result) > 0;
	}

	public function createEntity($name, $datas) {
		$newEntity = new $name();

		foreach ($datas as $key => $value) {
			if (gettype($key) != 'integer') $function = 'set'.$key;
			$newEntity->$function($value);
		}
		return $newEntity;
	}

	public function save($object) {
		$array = $object->getProperties();
		$table = strtolower(get_class($object));
		$set = "";
		$id = $object->getId();
		if ($id == null) {
			$sth = $this->pdo->prepareAndExecuteQuery("INSERT INTO $table (id) VALUES (DEFAULT);");
			$id = $this->pdo->lastInsertId();
		}

		$set = "";
		$i = 0;
		foreach ($array as $key => $value) {
			$key = str_replace("*", "", $key);
			if ($value != null) {
				if ($i > 0) $set .= ",";
				$set .= " $key = '$value'";
				$i++;
			}
		}
		$sth = $this->pdo->prepareAndExecuteQuery("UPDATE $table SET $set WHERE id = '$id';");
	}

	public function delete($object) {
		$id = $object->getId();
		$table = strtolower(get_class($object));
		$sth = $this->pdo->prepareAndExecuteQuery("DELETE FROM $table WHERE id = $id");
	}
}
