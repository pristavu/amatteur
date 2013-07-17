<?php

class Model_Statistics {

	public static function getStatistics(JO_Db_Expr $where) {
		$db = JO_Db::getDefaultAdapter();
		
		$query = $db->select()
					->from('statistics')
					->where($where)
					->order('id ASC');
		return $db->fetchAll($query);
	}

	public static function getTotalStatistics(JO_Db_Expr $where, $type) {
		$db = JO_Db::getDefaultAdapter();
		
		$query = $db->select()
					->from('statistics', 'SUM(total)')
					->where($where)
					->where('`type` = ?', (int)$type)
					->limit(1);
		return $db->fetchOne($query);
	}

	public static function getTotalStatistics2($table) {
		$db = JO_Db::getDefaultAdapter();
		
		if(!in_array('information_schema', self::showDatabases())) {
			return 'NaN';
		}
		
		$query = $db->select()
					->from('TABLES', 'TABLE_ROWS', 'information_schema')
					->where('TABLE_SCHEMA = ?', $db->getConfig('dbname'))
					->where('TABLE_NAME = ?', $table)
					->limit(1);
		return $db->fetchOne($query);
	}

	public static function getMin() {
		$db = JO_Db::getDefaultAdapter();
		
		$query = $db->select()
					->from('statistics', 'id')
					->order('id ASC')
					->limit(1);
		return $db->fetchOne($query);
	}
	
	public static function showDatabases() {
		$db = JO_Db::getDefaultAdapter();
		
		$results = $db->fetchAll('show databases');
		$data = array();
		if($results) {
			foreach($results AS $result) {
				$data[] = $result['Database'];
			}
		}
		return $data;
	}
	
}

?>