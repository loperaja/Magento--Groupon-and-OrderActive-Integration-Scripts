<?php
// Could be useful for you to create a model object. I guess this is slightly outdated as php can hanfle mysql databases much easier now 
class Database {
	private $Host, $Username, $Db , $Password;
	
	public function __construct () {
		$this->Host = 'host';
		$this->Username = 'username';
		$this->Password = 'password';
		$this->Db = 'database';
	}
	
	public function connect () {
		$link = mysql_connect($this->Host, $this->Username, $this->Password)
			OR die ("There was a probem connecting");
		mysql_select_db($this->Db);
	}
	
}

class WarehouseIntegration {

	public function __construct(){
		$database = new Database;
		$database->connect();
	}
	
	
	public function all() {
		$query = "select * FROM table";
		$result = mysql_query($query);
		while ($row = mysql_fetch_array($result)){
			$result_array[] = $row;
		}
		return $result_array;
	}
	
	public function insert($data) {
		$query = "INSERT INTO `db`.`table` (`id`, `exp_order_no`, `exp_order_file`, `create_date`) VALUES (NULL, '{$data}', 'CAG-{$data}.xml', CURRENT_TIMESTAMP)";
		$result = mysql_query($query)
			OR die ("Couldn't insert data");
	}
	
	public function insert_groupon($orderinfo) {
		$order_no = $orderinfo['order_no'];
		$lineitem_no = $orderinfo['lineitem'];
		$carrier = $orderinfo['carrier'];
		$query = "INSERT INTO `db`.`table` (`id`, `exp_order_no`, `ci_lineitem_no`, `carrier`) VALUES (NULL, '{$order_no}', '{$lineitem_no}', '{$carrier}')";
		$result = mysql_query($query)
			OR die ("Couldn't insert data");
	}
	
	public function groupon_order_info($data) {
		$query = "SELECT * FROM `table` WHERE `exp_order_no` LIKE '{$data}'";
		$result = mysql_query($query);
		$array = mysql_fetch_array($result);
		return $array;
	}
	
	
	public function non_existing_order($data) {
		$query = "SELECT * FROM `table` WHERE `exp_order_no` LIKE '{$data}'";
		$result = mysql_query($query);
		if (mysql_num_rows($result)) {
			return false;
		} else {
			return true;
		}
	}	
}

?>
