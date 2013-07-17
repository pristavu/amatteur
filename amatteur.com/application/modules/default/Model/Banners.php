<?php

class Model_Banners {
	
	public static function getBanners(JO_Db_Expr $where) {
		$db = JO_Db::getDefaultAdapter();
        
		$query = $db
					->select()
					->from('banners')
					->where($where)
					->order('position ASC')
					->limit(50);
	
		$data = $db->fetchAll($query);
		$result = array();
		if($data) {
			foreach($data AS $r) {
				$result[$r['position']][] = $r;
			}
		}
		
		return $result; 
	}

}

?>