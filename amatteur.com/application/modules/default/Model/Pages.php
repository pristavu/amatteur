<?php
class Model_Pages {
    
     public static function getPagesFooter() {
		
		$db = JO_Db::getDefaultAdapter();
        $query = $db->select()
					->from('pages')
					->where('pages.status = 1')
					->where('pages.in_footer = 1')
					->order('pages.sort_order ASC');
		
		return $db->fetchAll($query);
    }
	
	public static function getPages($data = array()) {
		$db = JO_Db::getDefaultAdapter();

		$query = $db->select()
					->from('pages')
					->where('pages.status = 1')
					->order('pages.sort_order ASC');

		if(isset($data['parent_id'])) {
			$query->where('parent_id = ?', (int)$data['parent_id']);
		}
					
		if(isset($data['start']) && isset($data['limit'])) {
			if($data['start'] < 0) {
				$data['start'] = 0;
			}
			$query->limit($data['limit'], $data['start']);
		}

		return $db->fetchAll($query);
	}
    
     public static function getPage($page_id) {
		$db = JO_Db::getDefaultAdapter();

		$query = $db->select()
					->from('pages')
					->where('pages.page_id = ?', (int)$page_id);

		return $db->fetchRow($query);
    }
    
}