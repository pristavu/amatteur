<?php

class Model_Comments {

	public static function getLatestComments($in) {
		
		$temp = array();
		foreach(explode(',',$in) AS $k) {
			if($k) {
				$temp[] = $k;
			}
		}
		$in = implode(',',$temp);
		
		if(trim($in)) {
			$db = JO_Db::getDefaultAdapter();	
			$query = $db
								->select()
								->from('pins_comments')
								->where('comment_id IN (?)', new JO_Db_Expr($in))
								->order('comment_id ASC')
								->limit(5);
								
			$results = $db->fetchAll($query);
			$data = array();
			if($results) {
				foreach($results AS $result) {
					$userdata = Model_Users::getUser($result['user_id'], false, Model_Users::$allowed_fields);
					if(!$userdata) {
						$userdata = array('fullname' => '', 'avatar' => '', 'store' => 'local');
					}
					$result['user'] = $userdata;
					$data[] = $result;
				}
			}
			return $data;
		}
		return array();
	}

	public static function getComments($data) {

		$db = JO_Db::getDefaultAdapter();	
		$query = $db
							->select()
							->from('pins_comments');
							
		if(isset($data['filter_pin_id'])) {
			$query->where('pins_comments.pin_id = ?', (string)$data['filter_pin_id']);
		}
		
		if(isset($data['start']) && isset($data['limit'])) {
			if($data['start'] < 0) {
				$data['start'] = 0;
			}
			$query->limit($data['limit'], $data['start']);
		}
		
		if(isset($data['sort']) && strtolower($data['sort']) == 'desc') {
			$sort = ' DESC';
		} else {
			$sort = ' ASC';
		}
		
		$allow_sort = array(
			'pins_comments.comment_id'
		);
		
		if(isset($data['order']) && in_array($data['order'], $allow_sort)) {
			$query->order($data['order'] . $sort);
		} else {
			$query->order('pins_comments.comment_id' . $sort);
		}
							
		$results = $db->fetchAll($query);
		$response = array();
		if($results) {
			foreach($results AS $result) {
				$userdata = Model_Users::getUser($result['user_id'], false, Model_Users::$allowed_fields);
				if(!$userdata) {
					$userdata = array('fullname' => '', 'avatar' => '', 'store' => 'local');
				}
				$result['user'] = $userdata;
				$response[] = $result;
			}
		}
		return $response;

	}

	public static function getTotalComments($pin_id) {

		$db = JO_Db::getDefaultAdapter();	
		$query = $db
							->select()
							->from('pins_comments', 'COUNT(comment_id)')
							->where('pin_id = ?', $pin_id);
		
		return $db->fetchOne($query);

	}
	
}

?>