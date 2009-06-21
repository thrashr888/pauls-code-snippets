<?php

class qDB{
	/**
	 * @class qDB
	 * I'm just using this to abstract the selects without too much overhead.
	 * @TODO
	 * 		Switch to Prepared Statements
	 * 		incorporate memcached
	 * 		add insert/delete capability
	 * -PaulT 2009-05-11
	 */
	private $conn = false,
			$log = array();
	private static $instance = array();

	public function __construct($conn){
		$this->conn = $conn;
	}

	public function getLog(){
		return $this->log;
	}

	/**
	 * @param $conn db connection
	 * @return singleton instance
	 * @TODO test to make sure this actually works as i doubt you can actually use resources as keys :(
	 */
	public static function getInstance($conn){
		if(isset(self::$instance[$conn])){
			self::$instance[$conn] = new qDB($conn);
		}
		return self::$instance[$conn];
	}

	public function doQuery($sql){
		// TODO: memcached this
		$results = mysql_query($sql, $this->conn);
		$return  = array();
		if($results && mysql_num_rows($results) > 0) {
			while ($row = mysql_fetch_assoc($results)) {
				$return[] = $row;
			}
		}
		$this->log[] = array($sql, $return);
		return $return ? $return : null;
	}

	public function doSelectOne($sql){
		$return = $this->doQuery($sql);
		return $return ? $return[0] : null;
	}

	public function doSelectCount($sql){
		$return = $this->doQuery($sql);
		return $return ? $return[0]['count'] : null;
	}
}