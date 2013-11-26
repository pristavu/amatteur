<?php

class Model_Events {
	
	private function common() {
		
		static $data = null;
		
		if($data === null) {
			$db = JO_Db::getDefaultAdapter();
			$query = $db->select()
						->from('pins_ignore_dictionary', array('dic_id', 'word'));
			$data = $db->fetchPairs($query);
		}
		
		return $data;
		
	}
	
	public static function changeStatus($event_id) {
		$db = JO_Db::getDefaultAdapter();
		
		$event_info = self::getPin($event_id);
		self::deleteCache($event_info);
		
		return $db->update('pins', array(
			'vip' => new JO_Db_Expr('IF(vip = 1, 0, 1)')
		), array('pin_id = ?' => (string)$event_id));
		
	}
	
	public static $searchWordLenght = 3;
	
	private static $thumb_sizes = array(
		'75x75' => '_A',
		'194x0' => '_B',
		'223x150' => '_C',
		'582x0' => '_D'
	);
	
	public static function edit($event_id, $data) {

		$event_info = self::getEvent($event_id);
		if(!$event_info) {
			return;
		}
		
		$db = JO_Db::getDefaultAdapter();
		
		$date_modified = WM_Date::format(time(), 'yy-mm-dd H:i:s');
		
		
		$db->update('events', array(
			'last_action_datetime' => $date_modified,
			'description' => $data['description'],
                        'website' => $data['website'],
			'last_action_datetime' => new JO_Db_Expr('NOW()')
		), array('event_id = ?' => $event_id));	
		
	}
	
	/**
	 * @param JO_Db_Select $query
	 * @param array $data
	 * @return JO_Db_Select
	 */
	private static function FilterBuilder(JO_Db_Select $query, $data = array()) {
		
		$db = JO_Db::getDefaultAdapter();
		
		if(isset($data['filter_event_id']) && $data['filter_event_id']) {
			$query->where('p.event_id = ?', (string)$data['filter_event_id']);
		}
		
		if(isset($data['filter_user_id']) && $data['filter_user_id']) {
			$query->where('p.user_id = ?', (string)$data['filter_user_id']);
		}
		
		if(isset($data['filter_fullname']) && $data['filter_fullname']) {
			$query->where('u.firstname LIKE ? OR u.lastname LIKE ?', '%'.$data['filter_fullname'].'%');
		}
		
		if(isset($data['filter_username']) && $data['filter_username']) {
			$query->where('u.username LIKE ?', '%'.$data['filter_username'].'%');
		}
		
		
		if(isset($data['filter_description']) && $data['filter_description']) {
			$words = JO_Utf8::str_word_split( mb_strtolower($data['filter_description'], 'utf-8') , self::$searchWordLenght);
			
			if( count($words) > 0 ) {
				
				$sub = "SELECT `dic_id`, `dic_id` FROM `pins_dictionary` `d` WHERE ( ";
				foreach($words AS $key => $word) {
					if($key) {
						$sub .= ' OR ';
					}
					$sub .= "`d`.`word` = " . $db->quote($word) . " OR MATCH(`d`.`word`) AGAINST (" . $db->quote($word) . ")";
				}
				$sub .= ')';
				
				$dicts = $db->fetchPairs($sub);
				
				$tmp_dic_ids = array();
				if(COUNT($dicts) > 0) { 
					$query->joinLeft('pins_invert', 'p.pin_id = pins_invert.pin_id', 'dic_id')
					->where('pins_invert.`dic_id` IN (' . implode(',', $dicts) . ')')
					->group('p.pin_id');
				} else {
					$query->where('p.pin_id = 0');
				}
				
			} else {
				$query->where('p.pin_id = 0');
			}
		}
		
		return $query;
	}
	
	public static function getEvents($data = array()) {
		$db = JO_Db::getDefaultAdapter();
    
		if( (isset($data['filter_fullname']) && $data['filter_fullname']) || (isset($data['filter_username']) && $data['filter_username']) ) {
			
			$query = $db
					->select()
					->from(array('u' => 'users'), array('fullname' => "CONCAT(firstname,' ', lastname)",'u.username'))
					->joinRight(array('p' => 'events'), 'p.user_id = u.user_id');
	
		} else {
			
			$query = $db
					->select()
					->from(array('p' => 'events'))
					->joinLeft(array('u' => 'users'), 'p.user_id = u.user_id', array('fullname' => "CONCAT(firstname,' ', lastname)",'u.username'));
	
		}
		
		if(isset($data['start']) && isset($data['limit'])) {
			if($data['start'] < 0) {
				$data['start'] = 0;
			}
			$query->limit($data['limit'], $data['start']);
		}
		
		if(isset($data['sort']) && strtolower($data['sort']) == 'asc') {
			$sort = ' ASC';
		} else {
			$sort = ' DESC';
		}
		
		$allow_sort = array(
			'p.event_id',
			'p.eventname'
		);
		
		if(isset($data['order']) && in_array($data['order'], $allow_sort)) {
			$query->order($data['order'] . $sort);
		} else {
			$query->order('p.event_id' . $sort);
		}
		
		////////////filter
                error_log($query);
		
		$query = self::FilterBuilder($query, $data);
		
		return $db->fetchAll($query);
	}
	
	public static function getTotalEvents($data = array()) {
		
		$db = JO_Db::getDefaultAdapter();
        
		if( (isset($data['filter_fullname']) && $data['filter_fullname']) || (isset($data['filter_username']) && $data['filter_username']) ) {
			
			$query = $db
					->select()
					->from(array('u' => 'users'),'')
					->joinRight(array('p' => 'events'), 'p.user_id = u.user_id', 'COUNT(p.event_id)');
	
		} else {
			
			$query = $db
					->select()
					->from(array('p' => 'events'), 'COUNT(p.event_id)')
					->joinLeft(array('u' => 'users'), 'p.user_id = u.user_id', '');
	
		}
		
//		$query = $db
//					->select()
//					->from(array('p' => 'pins'), 'COUNT(p.pin_id)')
//					->limit(1);
		
		////////////filter
		
		$query = self::FilterBuilder($query, $data);
		
		return $db->fetchOne($query);
	}
	
	public static function getEvent($event_id) {
		$db = JO_Db::getDefaultAdapter();
        
		$query = $db
					->select()
					->from('events')
					->where('event_id = ?', $event_id)
					->limit(1);
		
		return $db->fetchRow($query);
		
	}

	public static function getCountLike($event_id) {
		$db = JO_Db::getDefaultAdapter();
		
		$query = $db->select()
                        ->from('events_likes','COUNT(*)')
			->where('event_id = ?', $event_id);

					

//                error_log($query);
		$results = $db->fetchOne($query); 
		return $results;
		
	}

	public static function getCountFollow($event_id) {
		$db = JO_Db::getDefaultAdapter();
		
		$query = $db->select()
                        ->from('events_following', 'COUNT(*)')
			->where('event_id = ?', $event_id);

					

//                error_log($query);
		$results = $db->fetchOne($query); 
		return $results;
		
	}

	public static function getCountComments($event_id) {
		$db = JO_Db::getDefaultAdapter();
		
		$query = $db->select()
                        ->from('events_comments', 'COUNT(*)')
			->where('event_id = ?', $event_id);

					

//                error_log($query);
		$results = $db->fetchOne($query); 
		return $results;
		
	}
        
	public static function getComment($com_id) {
		$db = JO_Db::getDefaultAdapter();
		
		$query = $db->select()
					->from('events_comments')
					->where('comment_id = ?', $com_id)
					->limit('1');
		return $db->fetchRow($query);
	}
	
	public static function deleteComment($com_id) {
		$db = JO_Db::getDefaultAdapter();
		
		$info = self::getComment($com_id);
		$results = false;
		if($info) {
			$results = $db->delete('events_comments', array('comment_id = ?' => $com_id));
			$db->delete('events_reports_comments', array('comment_id = ?' => $com_id));
			
			/*$comments = Model_Comments::getComments(array(
				'filter_pin_id' => (string)$info['pin_id'],
				'start' => 0,
				'limit' => 4,
				'sort' => 'ASC',
				'order' => 'events_comments.comment_id'
			));
			
			$fcm = array();
			if($comments) {
				foreach($comments AS $c) {
					if((string)$c['comment_id']) {
						$fcm[] = (string)$c['comment_id'];
					}
				}
			} */
			$db->update('events', array(
				'comments' => new JO_Db_Expr("(SELECT COUNT(comment_id) FROM events_comments WHERE event_id = '".(string)$info['event_id']."')"),
//				'latest_comments' => (string)implode(',',$fcm)
				'latest_comments' => new JO_Db_Expr("(SELECT GROUP_CONCAT(comment_id ORDER BY comment_id ASC) FROM (SELECT comment_id FROM events_comments WHERE event_id = '" . (string)$info['event_id'] . "' ORDER BY comment_id ASC LIMIT 4) AS tmp)")
			), array('event_id = ?' => (string)$info['event_id']));
			
			//self::rebuildCache($info['pin_id']);
			
		}
		return $results;
	}
        
	public static function getComments($data) {

		$db = JO_Db::getDefaultAdapter();	
		$query = $db
							->select()
							->from('events_comments');
							
		if(isset($data['filter_event_id'])) {
			$query->where('events_comments.event_id = ?', (string)$data['filter_event_id']);
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
			'events_comments.comment_id'
		);
		
		if(isset($data['order']) && in_array($data['order'], $allow_sort)) {
			$query->order($data['order'] . $sort);
		} else {
			$query->order('events_comments.comment_id' . $sort);
		}
							
		$results = $db->fetchAll($query);
		$response = array();
		if($results) {
			foreach($results AS $result) {
				$userdata = Model_Users::getUser($result['user_id']);
				if(!$userdata) {
					$userdata = array('fullname' => '', 'avatar' => '', 'store' => 'local');
				}
				$result['user'] = $userdata;
				$response[] = $result;
			}
		}
		return $response;

	}        
	public static function deleteFromServer($image) {
		if(JO_Registry::get('enable_amazon')) {
			$s3 = new JO_Api_Amazon(JO_Registry::get('awsAccessKey'), JO_Registry::get('awsSecretKey'));
			$s3->putBucket(JO_Registry::get('bucklet'), JO_Api_Amazon::ACL_PUBLIC_READ);
			if($s3->getBucketLogging(JO_Registry::get('bucklet'))) {
				$s3->deleteObject(JO_Registry::get('bucklet'), $image);
			}
		}
	}
	
	public static function deleteImagesAmazon($image) {
		if(!$image) {
			return;
		}
		
		$ext = strtolower(strrchr($image,"."));
		
		$thumbs = array($image);
		foreach(self::$thumb_sizes AS $size => $key) {
			$thumbs[] = preg_replace('/'.$ext.'$/i',$key.$ext,$image);
		}
		
		foreach($thumbs AS $thumb) {
			self::deleteFromServer($thumb);
		}
	}
	
	public static function delete($event_id) {
		$db = JO_Db::getDefaultAdapter();
		$event_info = self::getEvent($event_id);
		if(!$event_info) {
			return false;
		}
		
		if($event_info['store'] == 's3') {
			self::deleteImagesAmazon($event_info['image']);
		} else {
			$model_image = new Helper_Images();
			$model_image->deleteImages($event_info['image']);
		}

		$comments = Model_Events::getComments(array(
			'filter_event_id' => $event_id,
		));
		
		if($comments) {
			foreach($comments AS $comment) {
				$db->delete('events_comments', array('comment_id = ?' => $comment['comment_id']));
				$db->delete('events_reports_comments', array('comment_id = ?' => $comment['comment_id']));
			}
		}

		$del = $db->delete('events', array('event_id = ?' => $event_id));
		if(!$del) {
			return false;
		} else {
			

			$db->delete('events_likes', array('event_id = ?' => $event_id));
			$db->delete('events_reports', array('event_id = ?' => $event_id));
			$db->delete('events_following', array('event_id = ?' => $event_id));
			$db->delete('users_history', array('pin_id = ?' => $event_id));
			
			self::deleteCache($event_info);
			
			return true;
		}
		
	}
	
	public static function deleteCache($pin) {
		@unlink(BASE_PATH . '/cache/data/pins/' . WM_Date::format($pin['date_added'], 'yy/mm/dd/') . $pin['pin_id'] . '.cache');
		@unlink(BASE_PATH . '/cache/data/pins/' . WM_Date::format($pin['date_added'], 'yy/mm/dd/') . 'author/' . $pin['pin_id'] . '.cache');
		@unlink(BASE_PATH . '/cache/data/pins/' . WM_Date::format($pin['date_added'], 'yy/mm/dd/') . 'viewer/' . $pin['pin_id'] . '.cache');
		@unlink(BASE_PATH . '/cache/data/pins/' . WM_Date::format($pin['date_added'], 'yy/mm/dd/') . 'not_loged/' . $pin['pin_id'] . '.cache');
		@unlink(BASE_PATH . '/cache/data/pins/' . WM_Date::format($pin['date_added'], 'yy/mm/dd/') . 'activity/author/' . $pin['pin_id'] . '.cache');
		@unlink(BASE_PATH . '/cache/data/pins/' . WM_Date::format($pin['date_added'], 'yy/mm/dd/') . 'activity/viewer/' . $pin['pin_id'] . '.cache');
		@unlink(BASE_PATH . '/cache/data/pins/' . WM_Date::format($pin['date_added'], 'yy/mm/dd/') . 'activity/not_loged/' . $pin['pin_id'] . '.cache');
	}
	
    public static function getPinsR($data = array()) {
		$db = JO_Db::getDefaultAdapter();
    
		
			$query = $db
					->select()
					->from(array('rp' => 'pins_reports'))
					->joinLeft(array('prc' => 'pins_reports_categories'), 'rp.prc_id = prc.prc_id', array('category_title' => 'title'))
					->joinLeft(array('p' => 'pins'), 'p.pin_id = rp.pin_id')
					->joinLeft(array('u' => 'users'), 'rp.user_id = u.user_id', array('fullname' => "CONCAT(firstname,' ', lastname)",'u.username'))
					->joinLeft(array('b' => 'boards'), 'p.board_id = b.board_id', array('board_title' => 'title'));
	
		
		
		if(isset($data['start']) && isset($data['limit'])) {
			if($data['start'] < 0) {
				$data['start'] = 0;
			}
			$query->limit($data['limit'], $data['start']);
		}
		
		if(isset($data['sort']) && strtolower($data['sort']) == 'asc') {
			$sort = ' ASC';
		} else {
			$sort = ' DESC';
		}
		
		$allow_sort = array(
			'p.pin_id',
		    'rp.date_added'
		);
		
		if(isset($data['order']) && in_array($data['order'], $allow_sort)) {
			$query->order($data['order'] . $sort);
		} else {
			$query->order('rp.date_added' . $sort);
		}
		
        if(isset($data['filter_pr_id']) && $data['filter_pr_id']) {
			$query->where('pr_id = ?', (int)$data['filter_pr_id']);
		}
		
        if(isset($data['filter_prc_id']) && $data['filter_prc_id']) {
			$query->where('rp.prc_id = ?', (int)$data['filter_prc_id']);
		}
		

		return $db->fetchAll($query);
	}
	
	public static function getTotalPinsR($data = array()) {
		
		$db = JO_Db::getDefaultAdapter();
        
		
			$query = $db
					->select()
					->from(array('p' => 'pins_reports'), 'COUNT(p.pin_id)');

		
//		$query = $db
//					->select()
//					->from(array('p' => 'pins'), 'COUNT(p.pin_id)')
//					->limit(1);
		
		////////////filter
		
    	if(isset($data['filter_pr_id']) && $data['filter_pr_id']) {
			$query->where('pr_id = ?', (int)$data['filter_pr_id']);
		}
		
		return $db->fetchOne($query);
	}
	
    public static function deleteR($report_id) {
		$db = JO_Db::getDefaultAdapter();
			$db->delete('pins_reports', array('pr_id = ?' => $report_id));
			
			return true;
		
	}
	
    public static function getRPin($report_id) {
		$db = JO_Db::getDefaultAdapter();
        
		$query = $db
					->select()
					->from('pins_reports')
					->where('pr_id = ?', $report_id)
					->limit(1);
		
		return $db->fetchRow($query);
		
	}
	
    public static function deleteRP($report_id) {
    		$rp = self::getRPin($report_id);
    		self::delete($rp['pin_id']);
			
			return true;
		
	}
	
    public static function getPinReportCategories() {
		$db = JO_Db::getDefaultAdapter();
		$query = $db->select()
					->from('pins_reports_categories', array('prc_id', 'title'))
					->order('sort_order ASC');
		return $db->fetchAll($query);
	}


}

?>