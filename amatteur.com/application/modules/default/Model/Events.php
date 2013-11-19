<?php

class Model_Events extends JO_Model {
	
	private static $thumb_sizes = array(
		'50x50' => '_A',
		'180x0' => '_B'
	);
	
	public static $allowed_fields = array('user_id', 'username', 'firstname', 'lastname', 'following', 'followers', 'liking', 'likers', 'avatar', 'gender', 'location', 'website', 'date_added', 'boards', 'pins', 'likes', 'latest_pins', 'description', 'dont_search_index', 'groups_pin_email', 'comments_email', 'likes_email', 'repins_email', 'follows_email', 'email_interval', 'digest_email', 'news_email', 'store', 'width', 'height');
	

	
	public static function isExistEventname($eventname) {
	        
		$db = JO_Db::getDefaultAdapter();
                $query = $db->select()
					->from('events', new JO_Db_Expr('COUNT(event_id)'))
					->where('eventname = ?', $eventname);
		
		return $db->fetchOne($query)>0 ? true : false;
	}
	

	
	public static function getEventSolo($event_id) {
		$db = JO_Db::getDefaultAdapter();
        
		$query = $db
					->select()
					->from('events')
					->where('event_id = ?', $event_id)
					->limit(1);
		
		return $db->fetchRow($query);
		
	}
        
	public static function getEvent($data = array()) {
		
		$key = md5(serialize($data));
		
		static $result = array();
		if(isset($result[$key])) { return $result[$key]; }
		
		$db = JO_Db::getDefaultAdapter();
        
		$query = $db
					->select()
					->from('events', array('events.*'));
	
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
			'events.user_id',
			'events.eventname',
			'events.organiza',
			'events.date_event',
                        'events.sport_category'
		);
		if(isset($data['filter_like_event_id'])) {
			$allow_sort[] = 'events_likes.like_id';
		}
		
		if(isset($data['order']) && in_array($data['order'], $allow_sort)) {
			$query->order($data['order'] . $sort);
		} else {
			$query->order('eventname' . $sort);
		}
		
		////////////filter
		if(isset($data['filter_event_id']) && $data['filter_event_id']) {
			$query->where('events.event_id = ?', (string)$data['filter_event_id']);
		}
                
		if(isset($data['filter_user_id']) && $data['filter_user_id']) {
			$query->where('events.user_id = ?', (string)$data['filter_user_id']);
		}

		if(isset($data['filter_eventname']) && $data['filter_eventname']) {
			$query->where('events.eventname = ?', (string)$data['filter_eventname']);
		}
                		
		if(isset($data['filter_location']) && $data['filter_location']) {
			$query->where('events.location = ?', (string)$data['filter_location']);
		}

                
                if(isset($data['filter_sport_category']) && $data['filter_sport_category']) {
			$query->where('(events.sport_category = ?', $data['filter_sport_category']);
		}

  //error_log (" QUERY $query");
		$result = $db->fetchRow($query);

                $userinfo = Model_Users::getUser($result['user_id'], false, array('*'));
		
		if(!$userinfo) {
			return false;
		}
		
		//$result['user_via'] = Model_Users::getUser($result['via'], false, $fields);
		//$result['source'] = Model_Source::getSource($result['source_id']);
		$result['user'] = $userinfo;
		//$result['board'] = Model_Boards::getBoardTitle($result['board_id']);
		//$result['board_data'] = Model_Boards::getBoard($result['board_id']);
		$result['latest_comments'] = $result['comments'] ? self::getComments(array(
			'filter_event_id' => $result['event_id']
		)) : 0;
		//$result['liked'] = $result['likes'] ? self::pinIsLiked($result['pin_id']) : 0;
		
                
		return $result;
	}
	
	public static function getEvents($data = array()) {
		
		$key = md5(serialize($data));
		
		static $result = array();
		if(isset($result[$key])) { return $result[$key]; }
		
		$db = JO_Db::getDefaultAdapter();
        
		$query = $db
					->select()
					->from('events', array('events.*'));
	
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
			'events.user_id',
			'events.eventname',
			'events.organiza',
			'events.date_event',
                        'events.sport_category'
		);
		if(isset($data['filter_like_event_id'])) {
			$allow_sort[] = 'pins_likes.like_id';
		}
		
		if(isset($data['order']) && in_array($data['order'], $allow_sort)) {
			$query->order($data['order'] . $sort);
		} else {
			$query->order('eventname' . $sort);
		}
		
		////////////filter
		if(isset($data['filter_event_id']) && $data['filter_event_id']) {
			$query->where('events.event_id = ?', (string)$data['filter_event_id']);
		}
                
		if(isset($data['filter_user_id']) && $data['filter_user_id']) {
			$query->where('events.user_id = ?', (string)$data['filter_user_id']);
		}

		if(isset($data['filter_eventname']) && $data['filter_eventname']) {
			$query->where('events.eventname LIKE ?', '%' . str_replace(' ', '%', $data['filter_eventname']) . '%');
		}
                		
		if(isset($data['filter_location']) && $data['filter_location']) {
                    if ($data['filter_location'] != "Introduce una ubicación")
                    {
			$query->where('events.location LIKE ?', '%' . str_replace(',', '%', (string)$data['filter_location']) . '%');
                    }
		}
                
                if(isset($data['filter_sport_category']) && $data['filter_sport_category']) {
			$query->where('events.sport_category = ?', $data['filter_sport_category']);
		}

                if(isset($data['filter_event_date1']) && $data['filter_event_date1']) {
			$query->where('events.date_event >= ?', $data['filter_event_date1']);
		}

                if(isset($data['filter_event_date2']) && $data['filter_event_date2']) {
			$query->where('events.date_event <= ?', $data['filter_event_date2']);
		}
                
		if(isset($data['filter_compartir']) && $data['filter_compartir']) {
			$query->where('events.compartir = ?', (string)$data['filter_compartir']);
		}
                
		if(isset($data['filter_cron']) && $data['filter_cron']) {
			$query->where('sport_category IN (select sport_category from users_sports where user_id = ?) OR user_id IN (select following_id from users_following_user where user_id = ?)', (string)$data['filter_cron']);
		}

		if(isset($data['filter_delete_event']) && $data['filter_delete_event']) {
			$query->where('events.delete_event <> ? OR events.delete_event IS NULL', (string)$data['filter_delete_event']);
		}
                
//error_log (" QUERY $query");
		$results = $db->fetchAll($query);

		
		return $results;
	}        

        public static function getFollowingEvents($data = array()) {
		
		$key = md5(serialize($data));
		
		static $result = array();
		if(isset($result[$key])) { return $result[$key]; }
		
		$db = JO_Db::getDefaultAdapter();
        
		$query = $db
					->select()
					->from('events', array('events.*', 'month(date_event) as month', 'year(date_event) as year'));
	
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
			'events.date_event'
		);
		
		if(isset($data['order']) && in_array($data['order'], $allow_sort)) {
			$query->order($data['order'] . $sort);
		} else {
			$query->order('date_event' . $sort);
		}
		
		////////////filter
		if(isset($data['filter_user_id']) && $data['filter_user_id']) {
			$query->where('event_id in (select event_id from events_following where user_id = ?) OR user_id = ?', (string)$data['filter_user_id']);
		}

                
//error_log (" QUERY $query");
		$results = $db->fetchAll($query);

		
		return $results;
	}        
        
        public static function getLikingEvents($data = array()) {
		
		$key = md5(serialize($data));
		
		static $result = array();
		if(isset($result[$key])) { return $result[$key]; }
		
		$db = JO_Db::getDefaultAdapter();
        
		$query = $db
					->select()
					->from('events', array('events.*', 'month(date_event) as month', 'year(date_event) as year'));
	
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
			'events.date_event'
		);
		
		if(isset($data['order']) && in_array($data['order'], $allow_sort)) {
			$query->order($data['order'] . $sort);
		} else {
			$query->order('date_event' . $sort);
		}
		
		////////////filter
		if(isset($data['filter_user_id']) && $data['filter_user_id']) {
			$query->where('event_id in (select event_id from events_likes where user_id = ?)', (string)$data['filter_user_id']);
		}

                
//error_log (" QUERY $query");
		$results = $db->fetchAll($query);

		
		return $results;
	}                
        
	public static function getTotalLike($data = array()) {
		$db = JO_Db::getDefaultAdapter();
		
		$query = $db->select()
					->from('events_like');
					
		if(isset($data['filter_event_id']) && (int)$data['filter_event_id']) {
			$query->where('event_id = ?', (int)$data['filter_event_id']);
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
			'date_added'
		);
		
		if(isset($data['order']) && in_array($data['order'], $allow_sort)) {
			$query->order($data['order'] . $sort);
		} else {
			$query->order('date_added' . $sort);
		}

//                error_log($query);
		$results = $db->fetchAll($query); 
		$data = array();
		if($results) {
			foreach($results AS $result) {
                                $result['user'] = Model_Users::getUser($result['user_id']);
                                $result['date_dif'] = array_shift( WM_Date::dateDiff($result['date_added'], time()) );
                                $data[] = $result;
                        }
		}
		return $data;
		
	}

	public static function isLikeEvent($event_id, $user_id) {
            /*
		if($user_id2 === null) {
			if(!(string)JO_Session::get('user[user_id]') || (string)JO_Session::get('user[user_id]') == $user_id) {
				return false;
			}
			$user_id2 = JO_Session::get('user[user_id]');
		}
             * 
             */
		$db = JO_Db::getDefaultAdapter();
                if ($event_id != "")
                {
                	$query = $db->select()
					->from('events_likes', 'COUNT(event_like_id)')
					->where('event_id = ?', (string)$event_id)
					->where('user_id = ?', (string)$user_id)
					->limit(1);
                }
                else
                {
        		$query = $db->select()
					->from('events_likes', 'COUNT(event_like_id)')
					->where('user_id = ?', (string)$user_id)
					->limit(1);
                    
                }
//error_log($query);
		return $db->fetchOne($query);
	}

        public static function LikeEvent($event_id, $user_id) {
		if(!(string)JO_Session::get('user[user_id]')) {
			return false;
		}
		$db = JO_Db::getDefaultAdapter();
		
		$uf_id = true;
		if(!Model_Events::isLikeEvent($event_id, $user_id)) {
			$db->insert('events_likes', array(
				'user_id' => (string)$user_id,
                                'date_added' => new JO_Db_Expr('NOW()'),
				'event_id' => (string)$event_id
			));
			
			$uf_id = $db->lastInsertId();
		}
		/*
		if($uf_id) {
			$db->update('boards', array(
				'followers' => new JO_Db_Expr('followers+1')
			), array('board_id = ?' => (string)$board_id));
			
			$db->delete('users_following_ignore', array(
				'user_id = ?' => (string)JO_Session::get('user[user_id]'),
				'board_id = ?' => (string)$board_id,
				'following_id = ?' => (string)$user_id
			));
			
		}
		*/
		return $uf_id;
	}
	
	public static function UnLikeEvent($event_id, $user_id) {
		if(!(string)JO_Session::get('user[user_id]')) {
			return false;
		}
		$db = JO_Db::getDefaultAdapter();
		$row = $db->delete('events_likes', array(
			'user_id = ?' => (string)$user_id,
			'event_id = ?' => (string)$event_id
		));
                /*
		$flw = false;
		if($row || $flw = Model_Users::isFollowUser($user_id)) {
			$db->update('boards', array(
				'followers' => new JO_Db_Expr('followers-1')
			), array('board_id = ?' => (string)$board_id));
			
			if($flw) {
				$db->insert('users_following_ignore', array(
					'user_id' => (string)JO_Session::get('user[user_id]'),
					'board_id' => (string)$board_id,
					'following_id' => (string)$user_id
				));
				$row = $db->lastInsertId();
			}
		}
		*/
		return $row;
	}
        
	public static function FollowEvent($event_id, $user_id) {
		if(!(string)JO_Session::get('user[user_id]')) {
			return false;
		}
		$db = JO_Db::getDefaultAdapter();
		
		$uf_id = true;
		if(!Model_Events::isFollowEvent($event_id, $user_id)) {
			$db->insert('events_following', array(
				'user_id' => (string)$user_id,
                                'date_added' => new JO_Db_Expr('NOW()'),
				'event_id' => (string)$event_id
			));
			
			$uf_id = $db->lastInsertId();
		}
		/*
		if($uf_id) {
			$db->update('boards', array(
				'followers' => new JO_Db_Expr('followers+1')
			), array('board_id = ?' => (string)$board_id));
			
			$db->delete('users_following_ignore', array(
				'user_id = ?' => (string)JO_Session::get('user[user_id]'),
				'board_id = ?' => (string)$board_id,
				'following_id = ?' => (string)$user_id
			));
			
		}
		*/
		return $uf_id;
	}
	
	public static function UnFollowEvent($event_id, $user_id) {
		if(!(string)JO_Session::get('user[user_id]')) {
			return false;
		}
		$db = JO_Db::getDefaultAdapter();
		$row = $db->delete('events_following', array(
			'user_id = ?' => (string)$user_id,
			'event_id = ?' => (string)$event_id
		));
                /*
		$flw = false;
		if($row || $flw = Model_Users::isFollowUser($user_id)) {
			$db->update('boards', array(
				'followers' => new JO_Db_Expr('followers-1')
			), array('board_id = ?' => (string)$board_id));
			
			if($flw) {
				$db->insert('users_following_ignore', array(
					'user_id' => (string)JO_Session::get('user[user_id]'),
					'board_id' => (string)$board_id,
					'following_id' => (string)$user_id
				));
				$row = $db->lastInsertId();
			}
		}
		*/
		return $row;
	}
	
	public static function isFollowEvent($event_id, $user_id) {
            /*
		if($user_id2 === null) {
			if(!(string)JO_Session::get('user[user_id]') || (string)JO_Session::get('user[user_id]') == $user_id) {
				return false;
			}
			$user_id2 = JO_Session::get('user[user_id]');
		}
             * 
             */
		$db = JO_Db::getDefaultAdapter();
                if ($event_id != "")
                {
                	$query = $db->select()
					->from('events_following', 'COUNT(event_following_id)')
					->where('event_id = ?', (string)$event_id)
					->where('user_id = ?', (string)$user_id)
					->limit(1);
                }
                else
                {
        		$query = $db->select()
					->from('events_following', 'COUNT(event_following_id)')
					->where('user_id = ?', (string)$user_id)
					->limit(1);
                    
                }
//error_log($query);
		return $db->fetchOne($query);
	}

        
        
        public static function followingEvents($event_id) {
		$db = JO_Db::getDefaultAdapter();
		$query = $db->select()
					->from('events_following', '*')
					->where('event_id = ?', (string)$event_id);
		return $db->fetchAll($query);
	}

        public static function getTotalFollow($data = array()) {
		$db = JO_Db::getDefaultAdapter();
		
		$query = $db->select()
					->from('events_following');
					
		if(isset($data['filter_event_id']) && (int)$data['filter_event_id']) {
			$query->where('event_id = ?', (int)$data['filter_event_id']);
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
			'date_added'
		);
		
		if(isset($data['order']) && in_array($data['order'], $allow_sort)) {
			$query->order($data['order'] . $sort);
		} else {
			$query->order('date_added' . $sort);
		}

//                error_log($query);
		$results = $db->fetchAll($query); 
		$data = array();
                
		if($results) {
			foreach($results AS $result) {
                                $result['user'] = Model_Users::getUser($result['user_id']);
                                $result['date_dif'] = array_shift( WM_Date::dateDiff($result['date_added'], time()) );
                                $data[] = $result;
                        }
		}
		return $data;
		
	}
        
        public function getEventUser($user_id, $event_id){
		$db  = JO_Db::getDefaultAdapter();
		//$sql = "select * from users_activate where user_id = {$user_id}";
		//$result = $db->fetchOne($sql);
		$query =  $db->select()->from('events',array('*'))->where('user_id = ?',$user_id)->where('event_id = ?',$event_id);
                
                $result= $db->fetchRow($query);
		return $result; 
	}

    public static function describeTable($table, $row = '') {
        $db = JO_Db::getDefaultAdapter();
        $result = $db->describeTable($table);
        $data = array();
        foreach($result AS $res) {
            $data[$row . $res['COLUMN_NAME']] = $res['COLUMN_NAME'];
        }
        return $data;
    }
        
    public static function createEvent($user_id, $event_id, $data) {
		$db = JO_Db::getDefaultAdapter();
		
		$rows = self::describeTable('events');
                
		//$user_info_get = self::getUser($user_id);
		
		//$created = WM_Date::format($user_info_get['created'], 'yy-mm-dd H:i:s');
                
		$update = array();
		$avatar = '';
                $followers = false;
		foreach($rows AS $row) {
			if( array_key_exists($row, $data) ) {
				
				if($row == 'avatar') {
					if($data[$row]) {
						JO_Session::clear('upload_avatar');
						$avatar = $data[$row];
					} else {
						//$update[$row] = $data[$row];
					}
				} else {
					$update[$row] = $data[$row];
                                        if($row == 'compartir' && $data[$row] == 'followers')
                                        {
                                            $followers = true;
                                        }
                                        
                                        if($row == 'date_event')
                                        {
                                            $fecha = str_replace("/", "-" , $data[$row]);
                                            $update[$row] = WM_Date::format($fecha, 'yy-mm-dd H:i:s');
                                        }
				}
			}
		}
		
		if(!$update) {
			if(!$avatar) {
				return false;
			}
		}
		
                $user_data="";
                if ($event_id)
                {
                    $user_data = Model_Events::getEventUser((string)$user_id, $event_id );
                    if (!$user_data)
                    {
                        $db->insert('events', $update);

                        $event_id = $db->lastInsertId();

                        $users = Model_Users::getUsers(array(
                            'filter_following_user_id' => (string)$user_id,
                            ));
                        if ($users)
                        {
                            foreach ($users AS $key => $user)
                            {
                                self::FollowEvent($event_id, $user['user_id']);
                            }                        
                        }


                        if(!$user_id) {
                            return false;
                        }

                    }
                    else
                    {
                        $result = $db->update('events', $update, 
                                array('user_id = ' . (string)$user_id . ' AND event_id = ' .(string)$event_id));

                        $users = Model_Users::getUsers(array(
                            'filter_following_user_id' => (string)$user_id,
                            ));
                        if ($users)
                        {
                            foreach ($users AS $key => $user)
                            {
                                self::FollowEvent($event_id, $user['user_id']);
                            }                        
                        }


                        if (!$result)
                        {
                            return false;
                        }

                    }
                }
                else
                {
                        $db->insert('events', $update);

                        $event_id = $db->lastInsertId();

                        $users = Model_Users::getUsers(array(
                            'filter_following_user_id' => (string)$user_id,
                            ));
                        if ($users)
                        {
                            foreach ($users AS $key => $user)
                            {
                                self::FollowEvent($event_id, $user['user_id']);
                            }                        
                        }


                        if(!$user_id) {
                            return false;
                        }
                }
                
		if($avatar) {
			
			///// upload images
			//error_log("Vamos a subir la imagen");
			$front = JO_Front::getInstance();
			$request = JO_Request::getInstance();
			$upload_model = Helper_Pin::formatUploadModule(JO_Registry::get('default_upload_method'));
			$upload_model_file = $front->getModuleDirectoryWithDefault($request->getModule()) . '/' . $front->classToFilename($upload_model);
			if(!file_exists($upload_model_file)) {
				$upload_model = Helper_Pin::formatUploadModule('locale');
				$upload_model_file = $front->getModuleDirectoryWithDefault($request->getModule()) . '/' . $front->classToFilename($upload_model);
			}
				
			$image = false;
			if(file_exists($upload_model_file)) {
				//error_log("EXISTE FILE");
				$image = call_user_func(array($upload_model, 'uploadEventImage'), $avatar, $event_id );
			}
			
			if($image) {
				//error_log("EXISTE FILE");
				$result = $db->update('events', array(
					'avatar' => $image['image'],
					'store' => $image['store'],
					'height' => $image['height'],
					'width' => $image['width'],
					'last_action_datetime' => new JO_Db_Expr('NOW()')
				), array('user_id = ' . (string)$user_id . ' AND event_id = ' .(string)$event_id));
			
                                
				if($user_data) { 
                                        if($user_data['avatar']) {
                                            if($user_data['avatar'] != $image['image']) {
                                                    call_user_func(array(Helper_Pin::formatUploadModule($user_data['store']), 'deleteEventImage'), $user_data );
                                            }
                                        }
				}
			}
		}

                
		return true;
	}
        
        
        public function getEventsLatLen($lat, $len){
		$db  = JO_Db::getDefaultAdapter();
		//$sql = "select * from users_activate where user_id = {$user_id}";
		//$result = $db->fetchOne($sql);
		$query =  $db->select()->from('events',array('*'))->where('lat = ?',$lat)->where('len = ?',$len);
                $result= $db->fetchRow($query);
		return $result; 
	}

	public static function getEventReportCategories() {
		$db = JO_Db::getDefaultAdapter();
		$query = $db->select()
					->from('pins_reports_categories', array('prc_id', 'title'))
					->order('sort_order ASC');
		return $db->fetchPairs($query);
	}
	

        
	public static function reportComment($comment_id, $prc_id, $message = '') {
		if(self::commentIsReported($comment_id)) {
			return false;
		}
		$db = JO_Db::getDefaultAdapter();
		
		$db->insert('events_reports_comments', array(
			'prc_id' => (string)$prc_id,
			'user_id' => (string)JO_Session::get('user[user_id]'),
			'date_added' => new JO_Db_Expr('NOW()'),
			'comment_id' => (string)$comment_id,
			'user_ip' => JO_Request_Server::encode_ip(JO_Request::getInstance()->getClientIp()),
			'message' => (string)$message
		));
		
		return $db->lastInsertId();
	}        
        
        public static function commentIsReported($comment_id) {
		$db = JO_Db::getDefaultAdapter();
		$query = $db->select()
					->from('events_reports_comments', 'COUNT(pr_id)')
					->where('comment_id = ?', (string)$comment_id)
					->where('checked = 0')
					->limit(1);

		if((string)JO_Session::get('user[user_id]')) {
			$query->where("user_id = '" . (string)JO_Session::get('user[user_id]') . "' OR user_ip = '" . JO_Request_Server::encode_ip(JO_Request::getInstance()->getClientIp()) . "'");
		} else {
			$query->where("user_ip = ?", JO_Request_Server::encode_ip(JO_Request::getInstance()->getClientIp()));
		}
		
		return $db->fetchOne($query);
	}

	public static function addComment($data, $latest_comments, $fields = array('*')) {
		$db = JO_Db::getDefaultAdapter();
		$db->insert('events_comments', array(
			'event_id' => (string)$data['event_id'],
			'user_id' => (string)JO_Session::get('user[user_id]'),
			'comment' => $data['write_comment'],
			'date_added' => new JO_Db_Expr('NOW()')
		));
		
		$com_id = $db->lastInsertId();
		if(!$com_id) {
			return false;
		}
		
		$query = $db->select()
					->from('events_comments')
					->where('comment_id = ?', $com_id)
					->limit('1');
		$result = $db->fetchRow($query);
		if(!$result) {
			return false;
		}

		
		$db->update('events', array(
			'comments' => new JO_Db_Expr("(SELECT COUNT(comment_id) FROM events_comments WHERE event_id = '".(string)$data['event_id']."')"),
			'latest_comments' => new JO_Db_Expr("(SELECT GROUP_CONCAT(comment_id ORDER BY comment_id ASC) FROM (SELECT comment_id FROM events_comments WHERE event_id = '" . (string)$data['event_id'] . "' ORDER BY comment_id ASC LIMIT 4) AS tmp)")
		), array('event_id = ?' => (string)$data['event_id']));
		
		$userdata = Model_Users::getUser(JO_Session::get('user[user_id]'), false, $fields);
		if(!$userdata) {
			$userdata = array('fullname' => '', 'avatar' => '');
		}
		
		//self::rebuildCache($data['event_id']);
		
		$result['user'] = $userdata;
		return $result;
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
			$db->update('event', array(
				'comments' => new JO_Db_Expr("(SELECT COUNT(comment_id) FROM events_comments WHERE pin_id = '".(string)$info['event_id']."')"),
//				'latest_comments' => (string)implode(',',$fcm)
				'latest_comments' => new JO_Db_Expr("(SELECT GROUP_CONCAT(comment_id ORDER BY comment_id ASC) FROM (SELECT comment_id FROM events_comments WHERE pin_id = '" . (string)$info['event_id'] . "' ORDER BY comment_id ASC LIMIT 4) AS tmp)")
			), array('pin_id = ?' => (string)$info['pin_id']));
			
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

	public static function reportEvent($event_id, $prc_id, $message = '') {
		if(self::eventIsReported($event_id)) {
			return false;
		}
		$db = JO_Db::getDefaultAdapter();
		
		$db->insert('events_reports', array(
			'prc_id' => (string)$prc_id,
			'user_id' => (string)JO_Session::get('user[user_id]'),
			'date_added' => new JO_Db_Expr('NOW()'),
			'event_id' => (string)$event_id,
			'user_ip' => JO_Request_Server::encode_ip(JO_Request::getInstance()->getClientIp()),
			'message' => (string)$message
		));
		
		return $db->lastInsertId();
	}

        
        public static function eventIsReported($event_id) {
		$db = JO_Db::getDefaultAdapter();
		$query = $db->select()
					->from('events_reports', 'COUNT(pr_id)')
					->where('event_id = ?', (string)$event_id)
					->where('checked = 0')
					->limit(1);

		if((string)JO_Session::get('user[user_id]')) {
			$query->where("user_id = '" . (string)JO_Session::get('user[user_id]') . "' OR user_ip = '" . JO_Request_Server::encode_ip(JO_Request::getInstance()->getClientIp()) . "'");
		} else {
			$query->where("user_ip = ?", JO_Request_Server::encode_ip(JO_Request::getInstance()->getClientIp()));
		}
		
		return $db->fetchOne($query);
	}

}

?>