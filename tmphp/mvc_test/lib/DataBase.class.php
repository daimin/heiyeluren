<?php
class DB {
	protected $queryNum = 0;
	public function __construct($dbhost, $dbuser, $dbpw, $dbname = '', $charSet) {
		if(mysql_connect($dbhost, $dbuser, $dbpw)) {
			mysql_select_db($dbname);
			mysql_query("SET NAMES '$charSet'");
		} else {
			$this->throwException('无法连接到数据库');
		}
	}

	public function query($sql, $limit = null) {
		if(!empty($limit)){
			$sql = $sql . " LIMIT " . $limit;
		}
		$rs = mysql_query($sql);
		if ($rs) {
			$this->queryNum++;
			return $rs;
		} else{
			$this->throwException('无法执行sql语句'.$sql);
		}
	}
	public function fetch($rs, $result_type = MYSQL_ASSOC) {
		return mysql_fetch_array($rs, $result_type);
	}
	public function update($sql){
		$rs = mysql_query($sql);
		if ($rs) {
			$this->queryNum++;
			return true;
		} else {
			return false;
		}
	}
	public function getOne($sql) {
		if(!$rs = $this->query($sql,1)) {
			return false;
		}
		$row = $this->fetch($rs);
		$this->free($rs);
		return $row[0];
	}
	public function getRow($sql) {
		if (!$rs = $this->query($sql,1)) {
			return false;
		}
		$row = $this->fetch($rs);
		$this->free($rs);
		return $row;
	}
	public function getAll($sql, $limit = null) {
		if (!$rs = $this->query($sql, $limit)) {
			return false;
		}
		$all_rows = array();
		while($rows = $this->fetch($rs)) {
			$all_rows = $rows;
		}
		$this->free($rs);
		return $all_rows;
	} 
	public function insert($sql) {
		$this->query($sql);
		return mysql_insert_id();
	}
	private function free($rs){
		return mysql_free_result($rs);
	}
	public function close() {
		return mysql_close();
	}
	public function getQueryNum() {
		return $this->queryNum;
	}
	protected function throwException($message) {
		throw new Exception($message);
	}
}
?>