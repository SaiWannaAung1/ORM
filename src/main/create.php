<?php

class Create {

  private $pdo;

	function __construct() {
		$data = json_decode(file_get_contents(__DIR__."/../../app/config/config.json"));
		$this->pdo = new PDO(
    		'mysql:host='.$data->dataBaseHost.';dbname='.$data->dataBaseName,
   			$data->dataBaseUser,
    		$data->dataBasePass
		);
	}


  public function createDatabase($dbName) {

    $request = $this->pdo->prepare("CREATE DATABASE IF NOT EXISTS $database;");
    $request->execute();
    if ($request->errorInfo()) throw new Exception('Error Processing Request : ' . $request->errorInfo()[2]);
    return true;

	}


  private function createTable($tableName) {
    if($tableName != null){
      $request = $this->pdo->prepare("CREATE TABLE IF NOT EXISTS $tableName (id INTEGER);");
  		$request->execute();
  		$request = $this->pdo->prepare("ALTER TABLE $tableName ADD PRIMARY KEY(`id`);");
  		$request->execute();
  		$request = $this->pdo->prepare("ALTER TABLE $tableName MODIFY COLUMN id INT auto_increment;");
  		$request->execute();
    }else{
      throw new Exception("Error, Argument cannot be null");

    }

	}

  private function addColumn($tableName, $columnName, $columnType) {
  		$request = $this->pdo->prepare("ALTER TABLE $tableName ADD $columnName $columnType;");
  		$request->execute();
      if($request->errorInfo()) throw new Exception("Error Processing Request : " .$request->errorInfo()[2]);

  	}


  private function createEntity($className, $fields) {
    $flow = "<?php \n\n class" .ucfirst($className). " extends Entity { \n";

    foreach($fields as $field){
      $flow .= "private $" .$field . ";\n";
            . "public function get" .ucfirst($field) . "(){ \n"
            . "return $this->" .$field . ";\n } \n"
            . "public function set" . ucfirst($field) . "($" .$field . "){ \n"
            . "$this->" . $field . " = " .$field . "; \n } \n";
    }

    $flow .= "}\n ?>";
  }


}
