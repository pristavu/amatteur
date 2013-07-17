<?php

class Model_Categories {
	
	public static function getCategories($data = array()) {
		$db = JO_Db::getDefaultAdapter();
		
		$query = $db->select()
					->from('category', array('category_id', 'title', 'image'))
					->where('parent_id = ? or parent_id is null',0)
					->order('category.sort_order ASC');
					
		if(isset($data['start']) && isset($data['limit'])) {
			if($data['start'] < 0) {
				$data['start'] = 0;
			}
			$query->limit($data['limit'], $data['start']);
		}
		
		if(isset($data['filter_status'])) {
			$query->where('category.status = ?', (int)$data['filter_status']);
		}
		
		$data_info = $db->fetchAll($query);
		$result = array();
		if($data_info) { 
			$result = $data_info;
		}
		
		return $result;
	}
	
	public static function getTotalCategories($data = array()) {
		$db = JO_Db::getDefaultAdapter();
		
		$query = $db->select()
					->from('category', 'COUNT(category.category_id)');
		
		if(isset($data['filter_status'])) {
			$query->where('category.status = ?', (int)$data['filter_status']);
		}
		
		return $db->fetchOne($query);
	}
	
	public static function getCategory($category_id) {
		$db = JO_Db::getDefaultAdapter();
		
		$query = $db->select()
					->from('category')
					->where('category.category_id = ? ', (int)$category_id)
					->limit(1);
		
		return $db->fetchRow($query);
		
	}
	
	public static function getSubcategories($category_id){
		
		$db = JO_Db::getDefaultAdapter();
		
		$query  = $db->select()->from('category')->where('parent_id = ?',$category_id)->where('status = ?',1)->order('category.sort_order ASC');
		$result = $db->fetchAll($query);
		return $result;
	}
        
	public static function getSubCategoriesAPP($category_id){
		$db  = JO_Db::getDefaultAdapter();
		$query =  $db->select()->from('category',array('title','category_id','sort_order'))->where('parent_id = ?',$category_id)->where('status = ?',1)->order('category.sort_order ASC');
		$result= $db->fetchAll($query);
		return $result; 
	}
        

}

?>