<?php

class Model_History extends JO_Model {

	const REPIN = 1;
	
	const FOLLOW = 2;
	const UNFOLLOW = 3;
	
	const FOLLOW_USER = 4;
	const UNFOLLOW_USER = 5;
	
	const ADDPIN = 6;
	
	const ADDBOARD = 7;
	
	const LIKEPIN = 8;
	const UNLIKEPIN = 9;
	
	const COMMENTPIN = 10;
        
	const LIKEUSER = 11;
	const UNLIKEUSER = 12;
        
	const COMMENTUSER = 13;
        
	const MESSAGEUSER = 14;
	const UNMESSAGEUSER = 15;        
	
	public static function getType($type) {
		
		static $result = array();
		if(isset($result[$type])) { return $result[$type]; }
		
		$array = array(
			self::REPIN => self::translate('repinned your pin.'),
			self::FOLLOW => self::translate('is now following your pins.'),
			self::UNFOLLOW => self::translate('has unfollow your pins.'),
			self::FOLLOW_USER => self::translate('is now following you'),
			self::UNFOLLOW_USER => self::translate('has unfollow you'),
			self::ADDPIN => self::translate('Pinned to'),
			self::ADDBOARD => self::translate('Created'),
			self::LIKEPIN => self::translate('Like your pin'),
			self::UNLIKEPIN => self::translate('Unlike your pin'),
			self::COMMENTPIN => self::translate('Comment your pin'),
			self::LIKEUSER => self::translate('Like your user'),
			self::UNLIKEUSER => self::translate('Unlike your user'),
			self::COMMENTUSER => self::translate('Comment his board'),                    
			self::MESSAGEUSER => self::translate('Message in his board'),
			self::UNMESSAGEUSER => self::translate('Delete message in his board'),                    
		);
		
		if(isset($array[$type])) {
			$result[$type] = $array[$type];
			return $array[$type];
		} else {
			return false;	
		}
	}
	
	public static function addHistory($to, $type, $pin_id = 0, $board_id = 0, $comment = '') {
		if($to == JO_Session::get('user[user_id]')) {
			return;
		} else if(!JO_Session::get('user[user_id]')) {
			return;
		}
                
                
		$db = JO_Db::getDefaultAdapter();
		$db->insert('users_history', array(
			'date_added' => new JO_Db_Expr('NOW()'),
			'from_user_id' => (string)JO_Session::get('user[user_id]'),
			'to_user_id' => (string)$to,
			'history_action' => (int)$type,
			'pin_id' => (string)$pin_id,
			'board_id' => (string)$board_id,
			'comment' => $comment
		));
		
		$history_id = $db->lastInsertId();
		if($history_id) {
			if(self::FOLLOW == $type) {
				$db->delete('users_history', array('to_user_id = ?' => (string)$to,'from_user_id = ?' => (string)JO_Session::get('user[user_id]'), 'history_action = ?' => self::UNFOLLOW, 'board_id = ?' => (string)$board_id));
			} elseif(self::UNFOLLOW == $type) {
				$db->delete('users_history', array('to_user_id = ?' => (string)$to,'from_user_id = ?' => (string)JO_Session::get('user[user_id]'), 'history_action = ?' => self::FOLLOW, 'board_id = ?' => (string)$board_id));
			} elseif(self::FOLLOW_USER == $type) {
				$db->delete('users_history', array('to_user_id = ?' => (string)$to,'from_user_id = ?' => (string)JO_Session::get('user[user_id]'), 'history_action = ?' => self::UNFOLLOW_USER));
			} elseif(self::UNFOLLOW_USER == $type) {
				$db->delete('users_history', array('to_user_id = ?' => (string)$to,'from_user_id = ?' => (string)JO_Session::get('user[user_id]'), 'history_action = ?' => self::FOLLOW_USER));
			} elseif(self::LIKEPIN == $type) {
				$db->delete('users_history', array('to_user_id = ?' => (string)$to,'from_user_id = ?' => (string)JO_Session::get('user[user_id]'), 'history_action = ?' => self::UNLIKEPIN, 'pin_id = ?' => (string)$pin_id));
			} elseif(self::UNLIKEPIN == $type) {
				$db->delete('users_history', array('to_user_id = ?' => (string)$to,'from_user_id = ?' => (string)JO_Session::get('user[user_id]'), 'history_action = ?' => self::LIKEPIN, 'pin_id = ?' => (string)$pin_id));
			} elseif(self::LIKEUSER == $type) {
				$db->delete('users_history', array('to_user_id = ?' => (string)$to,'from_user_id = ?' => (string)JO_Session::get('user[user_id]'), 'history_action = ?' => self::UNLIKEUSER));
			} elseif(self::UNLIKEUSER == $type) {
				$db->delete('users_history', array('to_user_id = ?' => (string)$to,'from_user_id = ?' => (string)JO_Session::get('user[user_id]'), 'history_action = ?' => self::LIKEUSER));
			}
		}
	}
	
	public static function getHistory($data, $row = 'to_user_id', $user_id = 0) {
		$db = JO_Db::getDefaultAdapter();
		
		if(!$user_id) {
			$user_id = (string)JO_Session::get('user[user_id]');
		}
		
		$query = $db->select()
					->from('users_history')
					->where($row . ' = ?', $user_id);
					
		if(isset($data['filter_history_action']) && (int)$data['filter_history_action']) {
			$query->where('history_action = ?', (int)$data['filter_history_action']);
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
			'history_id'
		);
		
		if(isset($data['order']) && in_array($data['order'], $allow_sort)) {
			$query->order($data['order'] . $sort);
		} else {
			$query->order('history_id' . $sort);
		}

		$results = $db->fetchAll($query); 
		$data = array();
		if($results) {
			foreach($results AS $result) {
				$result['text_type'] = self::getType($result['history_action']);
				if($result['text_type']) {
					$result['date_dif'] = array_shift( WM_Date::dateDiff($result['date_added'], time()) );
					if($row == 'to_user_id') {
						$result['user'] = Model_Users::getUser($result['from_user_id']);
					} else {
						$result['user'] = Model_Users::getUser($result['to_user_id']);
					}
					$data[] = $result;
				}
			}
		}
		return $data;
		
	}
	
	public static function getHistoryToday($data) {
		$db = JO_Db::getDefaultAdapter();
		
		$arr = array(
			'comments' => self::COMMENTPIN,
			'follow' => self::FOLLOW_USER,
			'like' => self::LIKEPIN,
			'repin' => self::REPIN
		);
		
		$sql = $db->select()
					->from('users_history', 'to_user_id');
					
		if(isset($data['today']) && !is_null($data['today'])) {
			$sql->where('DATE(users_history.date_added) = ?', $data['today']);
		} elseif( isset($data['week_range'])) {
			$sql->where("DATE(users_history.date_added) BETWEEN '".$data['week_range']['from']."' AND '".$data['week_range']['to']."'");
		}
					
		$sql->where('history_action IN ('.implode(',',$arr).')')
				->group('to_user_id');
	
		$query = $db->select()
					->from('users')
					->where(isset($data['week_range'])?'1':'email_interval = 2')
					->where('user_id IN (?)', $sql);
			

		$results = $db->fetchAll($query);
		$return = array();
		if($results) {
			foreach($results AS $result) {
				
				foreach( $arr AS $k => $v ) {
					$sql2 = $db->select()
								->from('users_history', new JO_Db_Expr('DISTINCT from_user_id'))
								->where('to_user_id = ?', $result['user_id'])
								->where('`history_action` = ?', $v);
					if(isset($data['today']) && !is_null($data['today'])) {
						$sql2->where('DATE(users_history.date_added) = ?', $data['today']);
					} elseif( isset($data['week_range'])) {
						$sql->where("DATE(users_history.date_added) BETWEEN '".$data['week_range']['from']."' AND '".$data['week_range']['to']."'");
					}
					
					$result['history_'.$k] = $db->fetchAll( $db->select()->from('users', array('user_id', 'avatar', 'fullname' => "CONCAT(firstname,' ',lastname)", 'store'))->where('user_id IN (?)', $sql2) );
					
				}
				
				$return[] = $result;
			}
		}
					
		return $return;
		
	}
	
}

?>