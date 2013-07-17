<?php

class JO_Cache_Sql extends JO_Cache_Abstract {
	
	private $live_time = 3600;
	
	private $table = '___jo___cache___',
			$md5key = 'md5key',
			$key = 'key',
			$data = 'data',
			$time = 'date_added';
	
	
	public function __construct($options = array()) {
		parent::__construct($options);
		$this->install();
		$this->deleteExpired();
	}
	
	public function install() {
		$db = JO_Db::getDefaultAdapter();
		$db->query("CREATE TABLE IF NOT EXISTS `" . $this->table . "` (
				`cache_id` bigint(20) NOT NULL auto_increment,
				`" . $this->getTime() . "` datetime NOT NULL,
				`" . $this->getData() . "` longtext NOT NULL,
				`" . $this->getMd5Key() . "` varchar(32) NOT NULL,
				`" . $this->getKey() . "` varchar(255) NOT NULL,
				PRIMARY KEY  (`cache_id`),
				KEY `" . $this->key . "` (`" . $this->key . "`),
				KEY `" . $this->time . "` (`" . $this->time . "`)
		) ENGINE=MyISAM;");
	}
	
	public function setTime($value) {
		$this->time = $value;
		return $this;
	}
	
	public function getTime() {
		return $this->time;
	}
	
	public function setData($value) {
		$this->data = $value;
		return $this;
	}
	
	public function getData() {
		return $this->data;
	}
	
	public function setKey($value) {
		$this->key = $value;
		return $this;
	}
	
	public function getKey() {
		return $this->key;
	}
	
	public function getMd5Key() {
		return $this->md5key;
	}
	
	public function setMd5Key($value) {
		$this->md5key = $value;
		return $this;
	}
	
	public function setTable($value) {
		$this->table = $value;
		return $this;
	}
	
	public function getTable() {
		return $this->table;
	}
	
	public function setLiveTime($time) {
		$this->live_time = $time;
		return $this;
	}
	
	public function getLiveTime() {
		return $this->live_time;
	}
	
	public function store($key, $data) {
		$db = JO_Db::getDefaultAdapter();
		return $db->insertIgnore($this->getTable(),array(
			$this->getMd5Key() => md5($key),
			$this->getKey() => $key,
			$this->getTime() => new JO_Db_Expr('NOW()'),
			$this->getData() => JO_Json::encode($data)
		));
	}
	
	public function add($key, $data) {
		$db = JO_Db::getDefaultAdapter();
		return $db->insertIgnore($this->getTable(),array(
			$this->getMd5Key() => md5($key),
			$this->getKey() => $key,
			$this->getTime() => new JO_Db_Expr('NOW()'),
			$this->getData() => JO_Json::encode($data)
		));
	}
	
	public function get($key) {
		$db = JO_Db::getDefaultAdapter();
		$query = $db->select()
					->from($this->getTable(), $this->getData())
					->where($this->getMd5Key().' = ?', md5($key));
		$result = $db->fetchOne($query);
		if($result) {
			return JO_Json::decode($result, true);
		}
		return false;
	}
	
	public function clear() {
		$db = JO_Db::getDefaultAdapter();
		return $db->query("TRUNCATE TABLE `" . $this->getTable() . "`");
	}
	
	public function delete($key) {
		$db = JO_Db::getDefaultAdapter();
		return $db->delete($this->getTable(), array($this->getKey() . ' = ?' => $key));
	}
	
	public function deleteRegExp($regExp) {
		$db = JO_Db::getDefaultAdapter();
		return $db->delete($this->getTable(), array($this->getKey() . ' REGEXP ?' => $regExp));
	}
	
	public function deleteStrPos($pos) {
		$db = JO_Db::getDefaultAdapter();
		return $db->delete($this->getTable(), array($this->getKey() . ' LIKE ?' => '%' . $pos . '%'));
	}
	
	public function deleteExpired() {
		$db = JO_Db::getDefaultAdapter();
		return $db->delete($this->getTable(), array($this->getTime() . ' < ?' => (time() - $this->getLiveTime())));
	}
	
}