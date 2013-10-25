<?php

class Model_Users extends JO_Model {
	
	private static $thumb_sizes = array(
		'50x50' => '_A',
		'180x0' => '_B'
	);
	
	public static $allowed_fields = array('user_id', 'username', 'firstname', 'lastname', 'following', 'followers', 'liking', 'likers', 'avatar', 'gender', 'location', 'website', 'date_added', 'boards', 'pins', 'likes', 'latest_pins', 'description', 'dont_search_index', 'groups_pin_email', 'comments_email', 'likes_email', 'repins_email', 'follows_email', 'email_interval', 'digest_email', 'news_email', 'store', 'width', 'height');
	
	public function getUserByName($username, $namers, $avatar) {
		$db = JO_Db::getDefaultAdapter();
		if(self::isExistUsername($username)) {
			return $db->fetchOne($db->select()->from('users', 'user_id')->where('username=?',(string)$username));
		} else {
			$exp = explode(' ',$namers);
			$firsname = array_shift($exp);
			$last = implode(' ', $exp);
			return self::create(array(
				'username' => (string)$username,
				'firstname' => (string)$firsname,
				'lastname' => (string)$last,
				'avatar' => (string)$avatar,
				'email' => mt_rand(),
				'first_login' => 0
			));
		}
	}
	
	public function editDescription($description) {
		if(!JO_Session::get('user[user_id]')) {
			return;
		}
		$db = JO_Db::getDefaultAdapter();
		$db->update('users', array(
			'description' => $description
		), array('user_id = ?' => (string)JO_Session::get('user[user_id]')));
	}
	
	
        
	public static function getFacebookFriends() {
		$db = JO_Db::getDefaultAdapter();
		
		static $results = null;
		if($results !== null) return $results;
		
		$query = $db->select()
					->from('users_following_user', '')
					->joinLeft('users', 'users_following_user.following_id = users.user_id','')
					->where('users_following_user.user_id = ?', (string)JO_Session::get('user[user_id]'))
					->where('users.facebook_id != 0')
					->columns(array('users.facebook_id', 'users_following_user.following_id'));
		
		$results = $db->fetchPairs($query);
		return $results;
	}
	
	public static function checkInvateFacebook($key, $user_id = 0) {
		$db = JO_Db::getDefaultAdapter();
        $query = $db->select()
					->from('invate_facebook')
					->where('`code` = ?', (string)$key)
//					->where('user_id = ?', (string)$user_id)
					->limit(1);
		
		return $db->fetchRow($query);
	}
	
	public static function checkInvateFacebookID($facebook_id) {
		$db = JO_Db::getDefaultAdapter();
        $query = $db->select()
					->from('invate_facebook')
					->where('`facebook_id` = ?', (string)$facebook_id)
//					->where('user_id = ?', (string)$user_id)
					->limit(1);
		
		return $db->fetchRow($query);
	}
	
	public static function checkIsInvateFacebookFriend() {
		$db = JO_Db::getDefaultAdapter();
        $query = $db->select()
					->from('invate_facebook', array('facebook_id', 'facebook_id'))
					->where('user_id = ?', (string)JO_Session::get('user[user_id]'));
		
		return $db->fetchPairs($query);
	}
	
	public static function addInvateFacebook($user_id) {
		$db = JO_Db::getDefaultAdapter();
		$db->insert('invate_facebook', array(
			'user_id' => (string)JO_Session::get('user[user_id]'),
			'code' => md5($user_id),
			'facebook_id' => (string)$user_id
		));
		return $db->lastInsertId();
	}
	
	public static function checkSharedContent($key, $user_id) {
		$db = JO_Db::getDefaultAdapter();
        $query = $db->select()
					->from('shared_content')
					->where('`key` = ?', (string)$key)
					->where('user_id = ? OR -1 = ?', (string)$user_id)
					->limit(1);
		
		return $db->fetchRow($query);
	}
    
    public static function describeTable($table) {
        $db = JO_Db::getDefaultAdapter();
        $result = $db->describeTable($table);
        $data = array();
        foreach($result AS $res) {
            $data[] = $res['COLUMN_NAME'];
        }
        return $data;
    }
	
	
	public static function create($data) {
		$db = JO_Db::getDefaultAdapter();
		
		$rows = self::describeTable('users');
		
		$date_added = WM_Date::format(time(), 'yy-mm-dd H:i:s');
		$data['date_added'] = $date_added;
		$data['status'] = 1;
		$data['last_action_datetime'] = $date_added;
		$data['ip_address'] = JO_Request_Server::encode_ip( JO_Request::getInstance()->getClientIp() );
		
		
		$insert = array();
		$avatar = '';
		foreach($rows AS $row) {
			if( array_key_exists($row, $data) ) {
				
				if($row == 'avatar') {
					if($data[$row]) {
						$avatar = $data[$row];
					} else {
						//$insert[$row] = $data[$row];
					}
				} /* end avatar */ elseif($row == 'password') {
					$insert[$row] = md5($data[$row]);
				} else {
					$insert[$row] = $data[$row];
				}
			}
		}
		
		
		if(!$insert) {
			return false;
		}
		
		$insert['new_email'] = $insert['email'];
		$insert['store'] = JO_Registry::get('default_upload_method');
		if(!$insert['store']) {
			$insert['store'] = 'locale';
		}
		
		$db->insert('users', $insert);
		
		$user_id = $db->lastInsertId();
		
		if(!$user_id) {
			return false;
		}
		
		if($avatar) {
			
			///// upload images
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
				$image = call_user_func(array($upload_model, 'uploadUserAvatar'), $avatar, $user_id );
			}
			
			if($image) {
				$db->update('users', array(
					'avatar' => $image['image'],
					'store' => $image['store'],
					'height' => $image['height'],
					'width' => $image['width'],
				), array('user_id = ?' => (string)$user_id));
			}
		}
		
		$db->insert('url_alias', array(
			'query' => 'user_id=' . (string)$user_id,
			'keyword' => $data['username'],
			'path' => $data['username'],
			'route' => 'users/profile'
		));
		
                
                //******************************************************************************************
                //******************************************************************************************
                // hay que anular esto y crear una nueva pantalla para elegir la nueva carpeta de inicio
                // salva
		$total_boards = 0;
		if( is_array(JO_Registry::forceGet('default_boards')) ) {
			foreach(JO_Registry::get('default_boards') AS $def) {
				$res = Model_Boards::createBoard(array(
					'category_id' => $def['category_id'],
					'title' => $def['title'],
					'user_id' => (string)$user_id
				));
				if($res) {
					$total_boards++;
				}
			}
			
			$db->update('users', array(
				'boards' => $total_boards
			), array('user_id = ?' => (string)$user_id));
			
		}
		//******************************************************************************************
                //******************************************************************************************
                
		if( isset($data['delete_email']) && $data['delete_email'] ) {
			$db->delete('shared_content', array('email = ?' => $data['delete_email']));
		}
		
		if( isset($data['delete_code']) && $data['delete_code'] ) {
			$db->delete('invate_facebook', array('if_id = ?' => (string)$data['delete_code']));
		}
		
		if( isset($data['following_user']) && $data['following_user'] && $data['following_user'] != -1 ) {
			if( $db->insert('users_following_user', array(
				'user_id' => (string)$user_id,
				'following_id' => (string)$data['following_user']
			)) ) {
			
				/*$db->update('users', array(
					'following' => new JO_Db_Expr('following+1')
				), array('user_id = ?' => (string)$user_id));
				$db->update('users', array(
					'followers' => new JO_Db_Expr('followers+1')
				), array('user_id = ?' => (string)$data['following_user']));*/
			}
			if( $db->insert('users_following_user', array(
				'user_id' => (string)$data['following_user'],
				'following_id' => (string)$user_id
			)) ) {
			
				/*$db->update('users', array(
					'following' => new JO_Db_Expr('following+1')
				), array('user_id = ?' => (string)$data['following_user']));
				$db->update('users', array(
					'followers' => new JO_Db_Expr('followers+1')
				), array('user_id = ?' => (string)$user_id));*/
			}
		}
		
		$db->update('users', array(
			'boards' => new JO_Db_Expr('(SELECT COUNT(DISTINCT board_id) FROM boards WHERE user_id = users.user_id)'),
			'following' => new JO_Db_Expr('(SELECT COUNT(DISTINCT following_id) FROM users_following_user WHERE user_id = users.user_id AND following_id != users.user_id)'),
			'followers' => new JO_Db_Expr('(SELECT COUNT(DISTINCT user_id) FROM users_following_user WHERE following_id = users.user_id AND user_id != users.user_id)')
		), array('user_id = ?' => (string)$user_id));
		
		if(isset($data['facebook_session']) && $data['facebook_session']) {
			$db->update('users', array(
					'facebook_session' => is_array($data['facebook_session'])?serialize($data['facebook_session']):''
				), array('user_id = ?' => (string)$user_id));
		}
		
		return $user_id;
	}
	
	public static function edit2($user_id, $data) {
		$db = JO_Db::getDefaultAdapter();
		
		$rows = self::describeTable('users');
		
		$user_info_get = self::getUser($user_id);
		
		$date_added = WM_Date::format($user_info_get['date_added'], 'yy-mm-dd H:i:s');
		
		$update = array();
		$avatar = '';
		foreach($rows AS $row) {
			if( array_key_exists($row, $data) ) {
		
				if($row == 'avatar') {
					if($data[$row]) {
						JO_Session::clear('upload_avatar');
						$avatar = $data[$row];
					} else {
						//$update[$row] = $data[$row];
					}
				} /* end avatar */ elseif($row == 'password' || $row == 'new_password') {
					$update[$row] = md5($data[$row]);
				} else {
					$update[$row] = $data[$row];
				}
			}
		}
		
		if(!$update) {
			return false;
		}
		
		$rebuild = $result = $db->update('users', $update, array('user_id = ?' => (string)$user_id));
	}
	
	public static function edit($user_id, $data) {
		$db = JO_Db::getDefaultAdapter();
		
		$rows = self::describeTable('users');
		
		$user_info_get = self::getUser($user_id);
		
		$date_added = WM_Date::format($user_info_get['date_added'], 'yy-mm-dd H:i:s');
		
		$update = array();
		$avatar = '';
		foreach($rows AS $row) {
			if( array_key_exists($row, $data) ) {
				
				if($row == 'avatar') {
					if($data[$row]) {
						JO_Session::clear('upload_avatar');
						$avatar = $data[$row];
					} else {
						//$update[$row] = $data[$row];
					}
				} /* end avatar */ elseif($row == 'password' || $row == 'new_password') {
					$update[$row] = md5($data[$row]);
				} else {
					$update[$row] = $data[$row];
				}
			}
		}
		
		if(!$update) {
			if(!$avatar) {
				return false;
			}
		}
		
		$rebuild = $result = $db->update('users', $update, array('user_id = ?' => (string)$user_id));
		
		
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
				$image = call_user_func(array($upload_model, 'uploadUserAvatar'), $avatar, $user_id );
			}
			
			if($image) {
				//error_log("EXISTE FILE");
				$result = $db->update('users', array(
					'avatar' => $image['image'],
					'store' => $image['store'],
					'height' => $image['height'],
					'width' => $image['width'],
					'last_action_datetime' => new JO_Db_Expr('NOW()')
				), array('user_id = ?' => (string)$user_id));
			
				if($user_info_get && $user_info_get['avatar']) {
					if($user_info_get['avatar'] != $image['image']) {
						call_user_func(array(Helper_Pin::formatUploadModule($user_info_get['store']), 'deleteUserImage'), $user_info_get );
					}
				}
				
				if(!$rebuild) { $rebuild = $result; }
			}
		}
		
		if(isset($data['username'])) {
			$db->query("DELETE FROM url_alias WHERE query = 'user_id=" . (string)$user_id . "'");
			$db->insert('url_alias', array(
				'query' => 'user_id=' . (string)$user_id,
				'keyword' => $data['username'],
				'path' => $data['username'],
				'route' => 'users/profile'
			));
		}
		
		if($rebuild) {
			$total = $db->update('pins', array(
					'date_modified' => WM_Date::format(time(), 'yy-mm-dd H:i:s')
			), array('user_id = ? OR (pin_id IN (SELECT DISTINCT pin_id FROM pins_comments WHERE user_id = ?))' => (string)$user_id));
		} 
		
		$db->update('users', array(
			'pins' => new JO_Db_Expr('(SELECT COUNT(DISTINCT pin_id) FROM pins WHERE user_id = users.user_id)'),
			'boards' => new JO_Db_Expr('(SELECT COUNT(DISTINCT board_id) FROM boards WHERE user_id = users.user_id)'),
			'likes' => new JO_Db_Expr('(SELECT COUNT(DISTINCT pin_id) FROM pins_likes WHERE user_id = users.user_id)'),
			'following' => new JO_Db_Expr('(SELECT COUNT(DISTINCT following_id) FROM users_following_user WHERE user_id = users.user_id AND following_id != users.user_id)'),
			'followers' => new JO_Db_Expr('(SELECT COUNT(DISTINCT user_id) FROM users_following_user WHERE following_id = users.user_id AND user_id != users.user_id)'),
			'liking' => new JO_Db_Expr('(SELECT COUNT(DISTINCT user_like_id) FROM users_likes WHERE user_id = users.user_id AND user_like_id != users.user_id)'),
			'likers' => new JO_Db_Expr('(SELECT COUNT(DISTINCT user_id) FROM users_likes WHERE user_like_id = users.user_id AND user_id != users.user_id)')
		), array('user_id = ?' => (string)$user_id));
		
		return true;
	}
	
	public static function isExistUsername($username, $old_username=FALSE) {
	    if($username==$old_username) {
			return false;
	    }
	    
	    $disabled_names = WM_Modules::getControllers();
		$disabled_names[] = 'admin';
		$disabled_names[] = 'default';
		if( in_array( strtolower($username), $disabled_names ) ) {
			return 1;
		}
	        
		$db = JO_Db::getDefaultAdapter();
        $query = $db->select()
					->from('users', new JO_Db_Expr('COUNT(user_id)'))
					->where('username = ?', $username);
		
		return $db->fetchOne($query)>0 ? true : false;
	}
	
	public static function isExistEmail($email, $usermail=FALSE) {
	    if($email==$usermail) {
			return false;
	    }
	        
		$db = JO_Db::getDefaultAdapter();
        $query = $db->select()
					->from('users', new JO_Db_Expr('COUNT(user_id)'))
					->where('email = ?', $email);
		
		return $db->fetchOne($query)>0 ? true : false;
	}
	
	public static function forgotPassword($email) {
	        
		$db = JO_Db::getDefaultAdapter();
        $query = $db->select()
					->from('users', array('*', 'fullname' => "CONCAT(firstname,' ',lastname)"))
					->where('email = ?', $email)
					->limit(1);
		
		return $db->fetchRow($query);
	}
	
	public static function forgotPasswordCheck($key, $user_id) {
	        
		$db = JO_Db::getDefaultAdapter();
        $query = $db->select()
					->from('users')
					->where('user_id = ?', (string)$user_id)
					->where('new_password_key = ?', (string)$key)
					->limit(1);
		
		$data = $db->fetchRow($query);
		if($data) {
			return $db->update('users', array(
				'password' => new JO_Db_Expr('`new_password`'),
				'new_password' => '',
				'new_password_key' => ''
			), array('user_id = ?' => $data['user_id']));
		}
		
		return false;
	}
	
	public static function verifyEmailCheck($key, $user_id) {
	        
		$db = JO_Db::getDefaultAdapter();
        $query = $db->select()
					->from('users')
					->where('user_id = ?', (string)$user_id)
					->where('new_email_key = ?', (string)$key)
					->limit(1);
		
		$data = $db->fetchRow($query);
		if($data) {
			return $db->update('users', array(
				'email' => new JO_Db_Expr('`new_email`'),
				'new_email_key' => ''
			), array('user_id = ?' => $data['user_id']));
		}
		
		return false;
	}
	
	public static function getUserByBoard($user_id, $board_id) {
		static $result = array();
		if(isset($result[$user_id . '_' . $board_id])) { return $result[$user_id . '_' . $board_id]; }
		
		$db = JO_Db::getDefaultAdapter();	
		$query = $db
							->select()
							->from('users_boards')
							->joinLeft('users', 'users_boards.user_id=users.user_id', array('*', 'fullname' => "CONCAT(firstname,' ',lastname)"))
							->where('users_boards.board_id = ?', (string)$board_id)
							->limit(1);
							
		if($user_id) {
			$query->where('users_boards.user_id = ?', (string)$user_id);
		}
						
		$result[$user_id . '_' . $board_id] = $db->fetchRow($query);
		return $result[$user_id . '_' . $board_id];
	}
	
	public static function getUser($user_id, $reset = false, array $fields = array('*')) {
		static $result = array();
		$fields = array_merge(array('fullname' => "CONCAT(firstname,' ',lastname)"), $fields);
		
		if(!$reset && isset($result[$user_id . serialize($fields)])) { return $result[$user_id . serialize($fields)]; }
		
		$db = JO_Db::getDefaultAdapter();	
		$query = $db
							->select()
							->from('users', $fields)
							->where('user_id = ?', (string)$user_id)
							->limit(1); 

		$result[$user_id . serialize($fields)] = $db->fetchRow($query);
		return $result[$user_id . serialize($fields)];
	}
        
	public static function getUsername($user_id) {
		$db = JO_Db::getDefaultAdapter();
                $query = $db->select()
                                    ->from('users', 'users.username')
                                    ->where('user_id = ?', (string)$user_id)
                                    ->limit(1); 
		$result = $db->fetchOne($query);
		return $result;
	}
        
	
//	public static function updateLatestPins($pin_id) {
//		$db = JO_Db::getDefaultAdapter();
//		$board_info = self::getUser( JO_Session::get('user[user_id]') );
//		
//		if($board_info) {
//			$latest = explode(',',$board_info['latest_pins']);
//			$latest_add = array($pin_id);
//			for($i=0; $i<min(15, count($latest)); $i++) {
//				if(isset($latest[$i]) && $latest[$i]) {
//					$latest_add[] = $latest[$i];
//				}
//			} 
//			$db->update('users', array(
//				'latest_pins' => implode(',',$latest_add),
//				'pins' => new JO_Db_Expr('pins + 1')
//			), array('user_id = ?' => (string)JO_Session::get('user[user_id]')));
//			
//		}
//	}
	
	public static function updateLatestPins($pin_id = 0) {
		$db = JO_Db::getDefaultAdapter();
		$board_info = self::getUser( JO_Session::get('user[user_id]') );
		
		if($board_info) {
			$pins = Model_Pins::getPins(array(
				'filter_user_id' => (string)JO_Session::get('user[user_id]'),
				'sort' => 'DESC',
				'order' => 'pins.pin_id',
				'start' => 0,
				'limit' => 15
			));
			$latest_add = array();
			if($pins) {
				foreach($pins AS $p) {
					$latest_add[] = $p['pin_id'];
				}
			}
			$db->update('users', array(
				'latest_pins' => implode(',',$latest_add),
				'pins' => new JO_Db_Expr("(SELECT COUNT(pin_id) FROM pins WHERE user_id = '".(string)JO_Session::get('user[user_id]')."')")
			), array('user_id = ?' => (string)JO_Session::get('user[user_id]')));
			
		}
	}
	
	public static function getUsers($data = array()) {
		
		$key = md5(serialize($data));
		
		static $result = array();
		if(isset($result[$key])) { return $result[$key]; }
		
		$db = JO_Db::getDefaultAdapter();
        
		$query = $db
                    ->select()
                    ->from('users', array('users.*', 'fullname' => "CONCAT(firstname,' ',lastname)"));
	
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
			'users.user_id',
			'users.username',
			'users.firstname',
			'users.status',
                        'users.likers'
		);
		if(isset($data['filter_like_pin_id'])) {
			$allow_sort[] = 'pins_likes.like_id';
		}
		
		if(isset($data['order']) && in_array($data['order'], $allow_sort)) {
			$query->order($data['order'] . $sort);
		} else {
			$query->order('firstname' . $sort);
		}
		
		////////////filter
		
		/*if(isset($data['filter_welcome']) && is_array($data['filter_welcome'])) {
			$query->joinLeft('boards', 'boards.user_id = users.user_id', array())
			->where('boards.category_id IN (?)', new JO_Db_Expr(implode(',',$data['filter_welcome'])))
			->group('users.user_id');
		}*/
		
		if(isset($data['filter_welcome']) && is_array($data['filter_welcome'])) {
			$query->where('users.user_id IN (SELECT user_id FROM boards WHERE category_id IN (?) OR category_id IN (SELECT category_id FROM category WHERE parent_id IN (?))) ', new JO_Db_Expr(implode(',',$data['filter_welcome'])));
		}
		
		
		if(isset($data['filter_followers_user_id']) && $data['filter_followers_user_id']) {
			$query->joinLeft('users_following_user', 'users.user_id = users_following_user.following_id', array())
			->where('users_following_user.user_id = ?', (string)$data['filter_followers_user_id']);
		}
		
		if(isset($data['filter_following_user_id']) && $data['filter_following_user_id']) {
			$query->joinLeft('users_following_user', 'users.user_id = users_following_user.user_id', array())
			->where('users_following_user.following_id = ?', (string)$data['filter_following_user_id']);
		}
		
		if(isset($data['filter_likers_user_id']) && $data['filter_likers_user_id']) {
			$query->joinLeft('users_likes', 'users.user_id = users_likes.user_like_id', array())
			->where('users_likes.user_id = ?', (string)$data['filter_likers_user_id']);
		}
		
		if(isset($data['filter_liking_user_id']) && $data['filter_liking_user_id']) {
			$query->joinLeft('users_likes', 'users.user_id = users_likes.user_id', array())
			->where('users_likes.user_like_id = ?', (string)$data['filter_liking_user_id']);
		}
                
                if(isset($data['filter_profile_top_10']) && !is_null($data['filter_profile_top_10'])) {
			$query->where('users.likers > 0 ');
			//$ignore_in = true;
		}

                if(isset($data['filter_profile_top_10_7']) && !is_null($data['filter_profile_top_10_7'])) {
			$query->where('users.likers > 0 AND DATEDIFF(curdate(), last_action_datetime) < ? ', (int)$data['filter_profile_top_10_7']);
			//$ignore_in = true;
		}
                
		if(isset($data['filter_like_pin_id']) && $data['filter_like_pin_id']) {
			$query->joinLeft('pins_likes', 'users.user_id = pins_likes.user_id')
			->where('pins_likes.pin_id = ?', (string)$data['filter_like_pin_id']);
		}
		
		if(isset($data['filter_user_id']) && $data['filter_user_id']) {
			$query->where('users.user_id = ?', (string)$data['filter_user_id']);
		}

		if(isset($data['filter_firstname']) && $data['filter_firstname']) {
			$query->where('users.firstname = ?', (string)$data['filter_firstname']);
		}
                		
		if(isset($data['filter_location']) && $data['filter_location']) {
                    if ($data['filter_location'] != "Introduce una ubicación")
                    {
			$query->where('users.location LIKE ?', "%".str_replace(",", "%", (string)$data['filter_location'])."%");
                    }
		}

		if(isset($data['filter_gender']) && $data['filter_gender']) {
			$query->where('users.gender = ?', (string)$data['filter_gender']);
		}
                
                if(isset($data['filter_sport_category']) && $data['filter_sport_category']) {
			$query->where('(users.sport_category_1 = ? OR users.sport_category_2 = ? OR users.sport_category_3 = ?)', $data['filter_sport_category']);
		}

                if(isset($data['filter_sport_category_1']) && $data['filter_sport_category_1'] ) {
			$query->where('users.sport_category_1 = ? ', $data['filter_sport_category_1']);
		}

                if(isset($data['filter_sport_category_2']) && $data['filter_sport_category_2']) {
			$query->where('users.sport_category_2 = ? ', $data['filter_sport_category_2']);
		}

                if(isset($data['filter_sport_category_3']) && $data['filter_sport_category_3']) {
			$query->where('users.sport_category_3 = ? ', $data['filter_sport_category_3']);
		}

                
                if(isset($data['filter_typeuser']) && $data['filter_typeuser']) {
                        $datos = 0;
                        foreach($data['filter_typeuser'] as $type_user) {
                            if ($type_user != "")
                            {
                                $query->where('users.type_user = ?', (string)$type_user);
                                $datos = 1;
                            }
                        }
                        if ($datos == 0)
                        {
                            if(isset($data['filter_typeuser_profesional']) && $data['filter_typeuser_profesional']) {
                                          $query->where('users.type_user in (2, 3, 4, 6, 7, 8, 9, 10, 11)');
                            }
                        }
		}

		if(isset($data['filter_username']) && $data['filter_username']) {
			$query->where('CONCAT(users.firstname,users.lastname) LIKE ? OR users.firstname LIKE ? OR users.lastname LIKE ? OR users.username LIKE ?', '%' . str_replace(' ', '%', $data['filter_username']) . '%');
		}
                
//		echo $query; exit;
                //error_log("query" . $query);
		$results = $db->fetchAll($query);

		$result[$key] = array();
		if($results) {
			foreach($results AS $data) {
				$data['pins_array'] = array();
				if(trim($data['latest_pins'])) {
					$data['pins_array'] = $db->fetchAll($db->select()->from('pins')->where("pin_id IN ('?')", new JO_Db_Expr(implode("','", explode(',',$data['latest_pins']))))->order('pin_id DESC')->limit(15));
				}
				
				$result[$key][] = $data;
			}
		}
		
		return $result[$key];
	}
	
	public static function getTotalUsers($data = array()) {
		$db = JO_Db::getDefaultAdapter();
		
		$query = $db
					->select()
					->from('users', 'COUNT(DISTINCT users.user_id)');
		
		////////////filter
		
		if(isset($data['filter_followers_user_id']) && $data['filter_followers_user_id']) {
			$query->joinLeft('users_following_user', 'users.user_id = users_following_user.following_id', array())
			->where('users_following_user.user_id = ?', (string)$data['filter_followers_user_id']);
		}
		
		if(isset($data['filter_following_user_id']) && $data['filter_following_user_id']) {
			$query->joinLeft('users_following_user', 'users.user_id = users_following_user.user_id', array())
			->where('users_following_user.following_id = ?', (string)$data['filter_following_user_id']);
		}
		
		if(isset($data['filter_likers_user_id']) && $data['filter_likers_user_id']) {
			$query->joinLeft('users_likes', 'users.user_id = users_likes.user_like_id', array())
			->where('users_likes.user_id = ?', (string)$data['filter_likers_user_id']);
		}
		
		if(isset($data['filter_liking_user_id']) && $data['filter_liking_user_id']) {
			$query->joinLeft('users_likes', 'users.user_id = users_likes.user_id', array())
			->where('users_likes.user_like_id = ?', (string)$data['filter_liking_user_id']);
		}
                
		if(isset($data['filter_like_pin_id']) && $data['filter_like_pin_id']) {
			$query->joinLeft('pins_likes', 'users.user_id = pins_likes.user_id')
			->where('pins_likes.pin_id = ?', (string)$data['filter_like_pin_id']);
		}
		
		if(isset($data['filter_user_id']) && $data['filter_user_id']) {
			$query->where('users.user_id = ?', (string)$data['filter_user_id']);
		}
		
		if(isset($data['filter_username']) && $data['filter_username']) {
			$query->where('CONCAT(users.firstname,users.lastname) LIKE ? OR users.firstname LIKE ? OR users.lastname LIKE ? OR users.username LIKE ?', '%' . str_replace(' ', '%', $data['filter_username']) . '%');
		}
		
		return $db->fetchOne($query);
	}
	
	public static function getTotalPinLike($user_id) {
		$db = JO_Db::getDefaultAdapter();
		
		$query = $db
					->select()
					->from('users', 'users.likes')
                                        ->where('user_id = ?', (string)$user_id)
                                        ->limit(1); 
		//$result = $db->fetchOne($query);

		
		return $db->fetchOne($query);
	}
        
        public static function getTotalUserLike($user_id) {
		$db = JO_Db::getDefaultAdapter();
		
		$query = $db
					->select()
					->from('users', 'users.likes')
                                        ->where('user_id = ?', (string)$user_id)
                                        ->limit(1); 
		//$result = $db->fetchOne($query);

		
		return $db->fetchOne($query);
	}

        
	public static function isIgnoreFollow($data = array()) {
		if(!(string)JO_Session::get('user[user_id]')) {
			return false;
		}
		$db = JO_Db::getDefaultAdapter();
		$query = $db->select()
					->from('users_following_ignore', 'COUNT(users_following_id)')
					->where('user_id = ?', (string)JO_Session::get('user[user_id]'))
					->limit(1);
					
		if(isset($data['user_id'])) {
			$query->where('following_id = ?', (string)$data['user_id']);
		}
		if(isset($data['board_id'])) {
			$query->where('board_id = ?', (string)$data['board_id']);
		} else {
			return false;
		}
		return $db->fetchOne($query);
	}
	
	public static function isFollow($data = array()) {
		if(!(string)JO_Session::get('user[user_id]')) {
			return false;
		}
		$db = JO_Db::getDefaultAdapter();
		$query = $db->select()
					->from('users_following', 'COUNT(users_following_id)')
					->where('user_id = ?', (string)JO_Session::get('user[user_id]'))
					->limit(1);
					
		if(isset($data['user_id'])) {
			$query->where('following_id = ?', (string)$data['user_id']);
		}
					
		if(isset($data['board_id'])) {
			$query->where('board_id = ?', (string)$data['board_id']);
		}
					
//		if(isset($data['ub_id'])) {
//			$query->where('ub_id = ?', (string)$data['ub_id']);
//		}
		
		$result = $db->fetchOne($query);
		

		if(!$result) {
			if(isset($data['board_id'])) {
				$board_info = Model_Boards::getBoard($data['board_id']);
			} elseif(isset($data['user_id'])) {
				$board_info = array(
					'user_id' => $data['user_id']
				);
			}
			if(isset($board_info['user_id'])) {
				$result = Model_Users::isFollowUser($board_info['user_id']);
				if($result) {
					$is_ignore = self::isIgnoreFollow($data);
					if($is_ignore) {
						$result = false;
					}
				}
			}
		} else {
			$is_ignore = self::isIgnoreFollow($data);
			if($is_ignore) {
				$result = false;
			}
		}

		
		return $result;
	}
	
        
        
	public static function FollowBoard($user_id, $board_id) {
		if(!(string)JO_Session::get('user[user_id]')) {
			return false;
		}
		$db = JO_Db::getDefaultAdapter();
		
		$uf_id = true;
		if(!Model_Users::isFollowUser($user_id)) {
			$db->insert('users_following', array(
				'user_id' => (string)JO_Session::get('user[user_id]'),
				'following_id' => (string)$user_id,
				'board_id' => (string)$board_id
			));
			
			$uf_id = $db->lastInsertId();
		}
		
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
		
		return $uf_id;
	}
	
	public static function UnFollowBoard($user_id, $board_id) {
		if(!(string)JO_Session::get('user[user_id]')) {
			return false;
		}
		$db = JO_Db::getDefaultAdapter();
		$row = $db->delete('users_following', array(
			'user_id = ?' => (string)JO_Session::get('user[user_id]'),
			'board_id = ?' => (string)$board_id
		));
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
		
		return $row;
	}
	
	public static function isFollowUser($user_id, $user_id2 = null) {
		if($user_id2 === null) {
			if(!(string)JO_Session::get('user[user_id]') || (string)JO_Session::get('user[user_id]') == $user_id) {
				return false;
			}
			$user_id2 = JO_Session::get('user[user_id]');
		}
		$db = JO_Db::getDefaultAdapter();
		$query = $db->select()
					->from('users_following_user', 'COUNT(ufu_id)')
					->where('user_id = ?', (string)$user_id2)
					->where('following_id = ?', (string)$user_id)
					->limit(1);
		
		return $db->fetchOne($query);
	}

        public static function followingUsers($user_id) {
		$db = JO_Db::getDefaultAdapter();
		$query = $db->select()
					->from('users_following_user', 'users_following_user.following_id AS user_id')
					->where('user_id = ?', (string)$user_id);
		return $db->fetchAll($query);
	}

        public static function followersUsers($user_id) {
		$db = JO_Db::getDefaultAdapter();
		$query = $db->select()
					->from('users_following_user', 'users_following_user.user_id')
					->where('following_id = ?', (string)$user_id);
		return $db->fetchAll($query);
	}

        public static function isLikeUser($user_id, $user_id2 = null) {
		if($user_id2 === null) {
			if(!(string)JO_Session::get('user[user_id]') || (string)JO_Session::get('user[user_id]') == $user_id) {
				return false;
			}
			$user_id2 = JO_Session::get('user[user_id]');
		}
		$db = JO_Db::getDefaultAdapter();
		$query = $db->select()
					->from('users_likes', 'COUNT(like_id)')
					->where('user_id = ?', (string)$user_id2)
					->where('user_like_id = ?', (string)$user_id)
					->limit(1);
		
		return $db->fetchOne($query);
	}

        
	public static function isFriendUser($user_id, $following_id) {
		if($user_id == $following_id) {
			return true;
		}
		$db = JO_Db::getDefaultAdapter();
		$query = $db->select()
					->from('users_following_user', 'COUNT(ufu_id)')
					->where('user_id = ? OR following_id = ?', (string)$following_id)
					->where('user_id = ? OR following_id = ?', (string)$user_id)
					->limit(1);
		
		return $db->fetchOne($query);
	}
	
	public static function FollowUser($user_id) {
		if(!(string)JO_Session::get('user[user_id]') || (string)JO_Session::get('user[user_id]') == $user_id) {
			return false;
		}
		$db = JO_Db::getDefaultAdapter();
		$db->insert('users_following_user', array(
			'user_id' => (string)JO_Session::get('user[user_id]'),
			'following_id' => (string)$user_id
		));
		
		$uf_id = $db->lastInsertId();
		
		if($uf_id) {
			$db->update('users', array(
				'following' => new JO_Db_Expr('(SELECT COUNT(DISTINCT following_id) FROM users_following_user WHERE user_id = users.user_id AND following_id != users.user_id)')
			), array('user_id = ?' => (string)JO_Session::get('user[user_id]')));
			$db->update('users', array(
				'followers' => new JO_Db_Expr('(SELECT COUNT(DISTINCT user_id) FROM users_following_user WHERE following_id = users.user_id AND user_id != users.user_id)')
			), array('user_id = ?' => (string)$user_id));
			$db->delete('users_following_ignore', array(
				'user_id = ?' => (string)JO_Session::get('user[user_id]'),
				'following_id = ?' => (string)$user_id
			));
		}
		
		return $uf_id;
	}
	
	public static function UnFollowUser($user_id) {
		if(!(string)JO_Session::get('user[user_id]') || (string)JO_Session::get('user[user_id]') == $user_id) {
			return false;
		}
		$db = JO_Db::getDefaultAdapter();
		$row = $db->delete('users_following_user', array(
			'user_id = ?' => (string)JO_Session::get('user[user_id]'),
			'following_id = ?' => (string)$user_id
		));
		
		if($row) {
			$db->delete('users_following_ignore', array(
				'user_id = ?' => (string)JO_Session::get('user[user_id]'),
				'following_id = ?' => (string)$user_id
			));
			$db->delete('users_following', array(
				'user_id = ?' => (string)JO_Session::get('user[user_id]'),
				'following_id = ?' => (string)$user_id
			));
			$db->update('users', array(
					'following' => new JO_Db_Expr('(SELECT COUNT(DISTINCT following_id) FROM users_following_user WHERE user_id = users.user_id AND following_id != users.user_id)')
			), array('user_id = ?' => (string)JO_Session::get('user[user_id]')));
			$db->update('users', array(
					'followers' => new JO_Db_Expr('(SELECT COUNT(DISTINCT user_id) FROM users_following_user WHERE following_id = users.user_id AND user_id != users.user_id)')
			), array('user_id = ?' => (string)$user_id));
		}
		
		return $row;
	}

	public static function FollowUserAPP($user_id, $follower_Id) {
		if($follower_Id == $user_id) {
			return false;
		}
		$db = JO_Db::getDefaultAdapter();
		$db->insert('users_following_user', array(
			'user_id' => $user_id,
			'following_id' => $follower_Id
		));
		
		$uf_id = $db->lastInsertId();
		
		if($uf_id) {
			$db->update('users', array(
				'following' => new JO_Db_Expr('(SELECT COUNT(DISTINCT following_id) FROM users_following_user WHERE user_id = users.user_id AND following_id != users.user_id)')
			), array('user_id = ?' => $follower_Id));
			$db->update('users', array(
				'followers' => new JO_Db_Expr('(SELECT COUNT(DISTINCT user_id) FROM users_following_user WHERE following_id = users.user_id AND user_id != users.user_id)')
			), array('user_id = ?' => $follower_Id));
			$db->delete('users_following_ignore', array(
				'user_id = ?' => $user_id,
				'following_id = ?' => $follower_Id
			));
		}
		
		return $uf_id;
	}
	
	public static function UnFollowUserAPP($user_id, $follower_Id) {
		if($follower_Id == $user_id) {
			return false;
		}
		$db = JO_Db::getDefaultAdapter();
		$row = $db->delete('users_following_user', array(
			'user_id = ?' => $user_id,
			'following_id = ?' => $follower_Id
		));
		
		if($row) {
			$db->delete('users_following_ignore', array(
				'user_id = ?' => $user_id,
				'following_id = ?' => $follower_Id
			));
			$db->delete('users_following', array(
				'user_id = ?' => $user_id,
				'following_id = ?' => $follower_Id
			));
			$db->update('users', array(
					'following' => new JO_Db_Expr('(SELECT COUNT(DISTINCT following_id) FROM users_following_user WHERE user_id = users.user_id AND following_id != users.user_id)')
			), array('user_id = ?' => $follower_Id));
			$db->update('users', array(
					'followers' => new JO_Db_Expr('(SELECT COUNT(DISTINCT user_id) FROM users_following_user WHERE following_id = users.user_id AND user_id != users.user_id)')
			), array('user_id = ?' => $follower_Id));
		}
		
		return $row;
	}
        
        
        public static function LikeUser($user_id) {
		if(!(string)JO_Session::get('user[user_id]') || (string)JO_Session::get('user[user_id]') == $user_id) {
			return false;
		}
		$db = JO_Db::getDefaultAdapter();
		$db->insert('users_likes', array(
			'user_id' => (string)JO_Session::get('user[user_id]'),
			'user_like_id' => (string)$user_id
		));
		
		$like_id = $db->lastInsertId();
		
		if($like_id) {
			$db->update('users', array(
				'liking' => new JO_Db_Expr('(SELECT COUNT(DISTINCT user_like_id) FROM users_likes WHERE user_id = users.user_id AND user_like_id != users.user_id)')
			), array('user_id = ?' => (string)JO_Session::get('user[user_id]')));
			$db->update('users', array(
				'likers' => new JO_Db_Expr('(SELECT COUNT(DISTINCT user_id) FROM users_likes WHERE user_like_id = users.user_id AND user_id != users.user_id)')
			), array('user_id = ?' => (string)$user_id));
			/*
                        $db->delete('users_following_ignore', array(
				'user_id = ?' => (string)JO_Session::get('user[user_id]'),
				'following_id = ?' => (string)$user_id
			));
                         * 
                         */
		}
		
		return $like_id;
	}
	
	public static function UnLikeUser($user_id) {
		if(!(string)JO_Session::get('user[user_id]') || (string)JO_Session::get('user[user_id]') == $user_id) {
			return false;
		}
		$db = JO_Db::getDefaultAdapter();
		$row = $db->delete('users_likes', array(
			'user_id = ?' => (string)JO_Session::get('user[user_id]'),
			'user_like_id = ?' => (string)$user_id
		));
		
		if($row) {
                    /*
			$db->delete('users_following_ignore', array(
				'user_id = ?' => (string)JO_Session::get('user[user_id]'),
				'following_id = ?' => (string)$user_id
			));
                     * */
                     /*
			$db->delete('users_following', array(
				'user_id = ?' => (string)JO_Session::get('user[user_id]'),
				'following_id = ?' => (string)$user_id
			));
                      * 
                      */
			$db->update('users', array(
					'liking' => new JO_Db_Expr('(SELECT COUNT(DISTINCT user_like_id) FROM users_likes WHERE user_id = users.user_id AND user_like_id != users.user_id)')
			), array('user_id = ?' => (string)JO_Session::get('user[user_id]')));
			$db->update('users', array(
					'likers' => new JO_Db_Expr('(SELECT COUNT(DISTINCT user_id) FROM users_likes WHERE user_like_id = users.user_id AND user_id != users.user_id)')
			), array('user_id = ?' => (string)$user_id));
		}
		
		return $row;
	}

        
	public static function getUserFriends($data = array()) {
		$db = JO_Db::getDefaultAdapter();
        
		$query = $db->select()
					->from('users_following_user', array())
					->joinLeft('users', 'users.user_id = users_following_user.following_id', array('users.*', 'fullname' => "CONCAT(firstname,' ',lastname)"))
					->where('users_following_user.user_id = ?', (string)JO_Session::get('user[user_id]'));
	
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
			'users.user_id',
			'users.username',
			'users.firstname',
			'users.status'
		);
		
		if(isset($data['order']) && in_array($data['order'], $allow_sort)) {
			$query->order($data['order'] . $sort);
		} else {
			$query->order('firstname' . $sort);
		}
		
		////////////filter
		
		if(isset($data['filter_username']) && $data['filter_username']) {
			$query->where('users.firstname LIKE ? OR CONCAT(firstname," ",lastname) LIKE ?', $data['filter_username'] . '%');
		}
		
		
//	    echo $query;
//	    exit;
		return $db->fetchAll($query);
	}
	
	public static function getUserMailFriends($data = array()) {
		$db = JO_Db::getDefaultAdapter();
        
                if(isset($data['filter_activate']) && $data['filter_activate']) {                
                    $query = $db->select()
					->from('users', array('users.*', 'fullname' => "CONCAT(firstname,' ',lastname)"))
					->joinLeft('users_activate', 'users.user_id = users_activate.user_id')
					->where('users_activate.activate= 1');
                }
                else
                {
                    $query = $db->select()
					->from('users_following_user', array())
					->joinLeft('users', 'users.user_id = users_following_user.user_id', array('users.*', 'fullname' => "CONCAT(firstname,' ',lastname)"))
					->where('users_following_user.following_id = ?', (string)JO_Session::get('user[user_id]'));
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
			'users.user_id',
			'users.username',
			'users.firstname',
			'users.status'
		);
		
		if(isset($data['order']) && in_array($data['order'], $allow_sort)) {
			$query->order($data['order'] . $sort);
		} else {
			$query->order('firstname' . $sort);
		}
		
		////////////filter
		
		if(isset($data['filter_username']) && $data['filter_username']) {
			$query->where('users.firstname LIKE ? OR CONCAT(firstname," ",lastname) LIKE ?', $data['filter_username'] . '%');
		}
		$query->group('users.user_id');
                
	    //error_log("query " .$query);
//	    exit;
		return $db->fetchAll($query);
	}
	
	public static function sharedContentInvate($email) {
		$db = JO_Db::getDefaultAdapter();	
		$query = $db
					->select()
					->from('users')
					->where('email = ?', (string)$email)
					->limit(1);
		$user_data = $db->fetchRow($query);
		if($user_data) {
			return 1;
		}
		
		$query = $db
					->select()
					->from('shared_content')
					->where('email = ?', (string)$email)
					->limit(1);
		$user_data = $db->fetchRow($query);
		if($user_data) {
			return 2;
		}
		
		return false;
	}
	
	public static function addSharedContent($email) {
		$db = JO_Db::getDefaultAdapter();	
		
		$key = md5( time() . mt_rand() );
		
		$db->insert('shared_content', array(
			'user_id' => -1,
			'date_added' => new JO_Db_Expr('NOW()'),
			'key' => $key,
			'email' => $email,
			'send' => 0
		));
		
		$last = $db->lastInsertId();
		if(!$last) {
			return false;
		}
		
		return $key;
		
	}
	
	public static function sharedContent($email) {
		$db = JO_Db::getDefaultAdapter();	
		$query = $db
					->select()
					->from('users')
					->where('email = ?', (string)$email)
					->limit(1);
		$user_data = $db->fetchRow($query);
		if($user_data) {
			return -1;
		}
		
		$query = $db
					->select()
					->from('shared_content')
					->where('email = ?', (string)$email)
					->limit(1);
		$user_data = $db->fetchRow($query);
		if($user_data) {
			return $user_data['key'];
		}
		
		$key = md5( time() . mt_rand() );
		
		$db->insert('shared_content', array(
			'user_id' => JO_Session::get('user[user_id]'),
			'date_added' => new JO_Db_Expr('NOW()'),
			'key' => $key,
			'email' => $email,
			'send' => 1
		));
		
		$last = $db->lastInsertId();
		if(!$last) {
			return -1;
		}
		
		return $key;
		
	}
	
	public static function checkLogin($username, $password) {
		$db = JO_Db::getDefaultAdapter();	
		$query = $db
							->select()
							->from('users')
							->where('email = ? OR username = ?', (string)$username)
							->where('password = ?', (string)md5($password))
							->limit(1);
		$user_data = $db->fetchRow($query);
		
		if($user_data) {
			$groups = unserialize($user_data['groups']);
	    	if(is_array($groups) && count($groups) > 0) {
//	    		unset($user_data['groups']);
	    		$query_group = $db->select()
	    							->from('user_groups')
	    							->where("ug_id IN (?)", new JO_Db_Expr(implode(',', array_keys($groups))));
	    		$fetch_all = $db->fetchAll($query_group);
	    		$user_data['access'] = array();
	    		if($fetch_all) {
	    			foreach($fetch_all AS $row) {
	    				$modules = unserialize($row['rights']);
	    				if(is_array($modules)) {
	    				    foreach($modules AS $module => $ison) {
	    					    foreach($ison AS $m => $on) {
	    						    $user_data['access'][$module][$m] = $m;
	    					    }
	    					}
	    				}
	    			}
	    		}
	    	}
		}
    	
		return $user_data;
	}

        	public static function checkLoginAPP($user_id) {
		$db = JO_Db::getDefaultAdapter();	
		$query = $db
							->select()
							->from('users')
							->where('user_id = ?', (string)$user_id)
							->limit(1);
		$user_data = $db->fetchRow($query);
		
		if($user_data) {
			$groups = unserialize($user_data['groups']);
	    	if(is_array($groups) && count($groups) > 0) {
//	    		unset($user_data['groups']);
	    		$query_group = $db->select()
	    							->from('user_groups')
	    							->where("ug_id IN (?)", new JO_Db_Expr(implode(',', array_keys($groups))));
	    		$fetch_all = $db->fetchAll($query_group);
	    		$user_data['access'] = array();
	    		if($fetch_all) {
	    			foreach($fetch_all AS $row) {
	    				$modules = unserialize($row['rights']);
	    				if(is_array($modules)) {
	    				    foreach($modules AS $module => $ison) {
	    					    foreach($ison AS $m => $on) {
	    						    $user_data['access'][$module][$m] = $m;
	    					    }
	    					}
	    				}
	    			}
	    		}
	    	}
		}
    	
		return $user_data;
	}

	public static function getUserBoards($data = array()) {
		$db = JO_Db::getDefaultAdapter();	
		$query = $db->select()
					->from('users_boards')
					->joinLeft('boards', 'users_boards.board_id = boards.board_id', 'title');
					
		if(isset($data['filter_user-id']) && !is_null($data['filter_user-id'])) {
			$query->where('users_boards.user_id', (string)$data['filter_user-id']);
		}
		
		if(isset($data['start']) && isset($data['limit'])) {
			if($data['start'] < 0) {
				$data['start'] = 0;
			}
			$query->limit($data['limit'], $data['start']);
		}
		
		return $db->fetchAll($query);
		
	}
	
	public static function getUserAgenda($data = array()) {
		$db = JO_Db::getDefaultAdapter();	

		$query = $db->select()
					->from('users_agenda', 'users_agenda.*');
					
		if(isset($data['filter_user_id']) && !is_null($data['filter_user_id'])) {
			$query->where('user_id = ? ', '' . (string)$data['filter_user_id']. '');
			$query->order('created' . ' DESC');
		}
           
		return $db->fetchAll($query);	
	}
	public static function createAgenda($data) {
		$db = JO_Db::getDefaultAdapter();
		
		$rows = self::describeTable('users_agenda');
		
		$date_added = WM_Date::format(time(), 'yy-mm-dd H:i:s');
		$data['created'] = $date_added;
		
		
		$insert = array();
		foreach($rows AS $row) {
			if( array_key_exists($row, $data) ) {
				$insert[$row] = $data[$row];
			}
		}
		
		
		if(!$insert) {
			return false;
		}
		
		
		$db->insert('users_agenda', $insert);
		
		$message_id = $db->lastInsertId();
                

		if(!$message_id) {
			return false;
		}
		
		return $message_id;
	}
	
	
	public static function editAgenda($data,$agenda_id) {
		$db = JO_Db::getDefaultAdapter();
		$db->update('users_agenda', array(
			'texto' => $data
		), array('agenda_id = ?' => $agenda_id));
	}
	
	public static function deleteAgenda($data) {
		$db = JO_Db::getDefaultAdapter();
		
		$row = $db->query("DELETE FROM users_agenda WHERE agenda_id = ".  $data['agenda_id'] );

        
		if(!$row) {
			return false;
		}
		
		return $row;
	}
	
	public static function getUserMessages($data = array()) {
		$db = JO_Db::getDefaultAdapter();	
                $db->query("DELETE FROM users_messages WHERE DATEDIFF(curdate(), date_message) > 30 AND (users_messages.from_user_id = ". (string)$data['filter_user_id']." OR users_messages.to_user_id = ". (string)$data['filter_user_id']. ") ");

		$query = $db->select()
					->from('users_messages')
					->joinLeft('users', 'users.user_id = users_messages.from_user_id',  array('users.*', 'fullname' => "CONCAT(firstname,' ',lastname)", 'date_diff' => "DATEDIFF(curdate(), date_message)"))
					->order('date_message' . " DESC");
					
		if(isset($data['filter_user_id']) && !is_null($data['filter_user_id'])) {
			$query->where('(users_messages.from_user_id = ? OR users_messages.to_user_id = ?) AND users_messages.board_user_id = ? AND message_from_id='.$data['idPadre'], '' . (string)$data['filter_user_id']. '');
		}
		if(isset($data['start']) && isset($data['limit'])) {
			if($data['start'] < 0) {
				$data['start'] = 0;
			}
			$query->limit($data['limit'], $data['start']);
		}
                
		return $db->fetchAll($query);
		
	}
       
	public static function createMessage($data) {
		$db = JO_Db::getDefaultAdapter();
		
		$rows = self::describeTable('users_messages');
		
		$date_added = WM_Date::format(time(), 'yy-mm-dd H:i:s');
		$data['date_message'] = $date_added;
		
		
		$insert = array();
		foreach($rows AS $row) {
			if( array_key_exists($row, $data) ) {
				$insert[$row] = $data[$row];
			}
		}
		
		
		if(!$insert) {
			return false;
		}
		
		
		$db->insert('users_messages', $insert);
		
		$message_id = $db->lastInsertId();
                

		if(!$message_id) {
			return false;
		}
		
		return $message_id;
	}
        
	public static function deleteMessage($data) {
		$db = JO_Db::getDefaultAdapter();
		
		$row = $db->query("DELETE FROM users_messages WHERE message_id = ".  $data['message_id'] );
		
		$row2 = $db->query("DELETE FROM users_messages WHERE message_from_id = ".  $data['message_id'] );
        
		if(!$row) {
			return false;
		}
		
		return $row;
	}
        
    
	public static function generatePassword ($length = 8) {

	    // start with a blank password
	    $password = "";
	
	    // define possible characters - any character in this string can be
	    // picked for use in the password, so if you want to put vowels back in
	    // or add special characters such as exclamation marks, this is where
	    // you should do it
	    $possible = "2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ";
	
	    // we refer to the length of $possible a few times, so let's grab it now
	    $maxlength = strlen($possible);
	  
	    // check for length overflow and truncate if necessary
	    if ($length > $maxlength) {
			$length = $maxlength;
	    }
		
	    // set up a counter for how many characters are in the password so far
	    $i = 0; 
	    
	    // add random characters to $password until $length is reached
	    while ($i < $length) { 
	
			// pick a random character from the possible ones
			$char = substr($possible, mt_rand(0, $maxlength-1), 1);
	        
			// have we already used this character in $password?
			if (!strstr($password, $char)) { 
		        // no, so it's OK to add it onto the end of whatever we've already got...
		        $password .= $char;
		        // ... and increase the counter by one
		        $i++;
			}
		}
	    // done!
	    return $password;
	}
	
	public static function getFacebookFriendsNotFollow($facebook_id) {
		$db = JO_Db::getDefaultAdapter();
		$query = $db->select()
					->from('users', array('users.*', 'fullname' => "CONCAT(firstname,' ',lastname)"))
					->where('facebook_id = ?', (string)$facebook_id)
					->where('users.user_id NOT IN (SELECT following_id FROM users_following_user WHERE user_id = ?)', (string)JO_Session::get('user[user_id]'))
					->limit(1);
		
		$result = $db->fetchRow($query);
		if(!$result) {
			return false;
		}
					
		return $result;
	}
	
	
	public function getUserByRegKey($regkey){
		$db = JO_Db::getDefaultAdapter();
		
		$query = $db->select()
				->from('users','user_id')
				->where('regkey = ?',$regkey);
		
		$result = $db->fetchOne($query);
		return $result;
		
	}
	
	
	public function setKey($value,$user_id){
	
		$db = JO_Db::getDefaultAdapter();
		if($db->update('users', array('confirmed'=>$value),array('user_id = ?'=>$user_id))){
			return true;
		}
	}
        
	public function getUserType($data = array()) {
		$db = JO_Db::getDefaultAdapter();
		
		$query = $db->select()
					->from('user_type', array('*'))
					->where('parent_id = ? or parent_id is null',0)
					->where('status = ? ',1)
					->order('user_type.sort_order ASC');
					
		if(isset($data['start']) && isset($data['limit'])) {
			if($data['start'] < 0) {
				$data['start'] = 0;
			}
			$query->limit($data['limit'], $data['start']);
		}
		
		$data_info = $db->fetchAll($query);
		$result = array();
		if($data_info) { 
			$result = $data_info;
		}
		
		return $result;
	}        
        
	public static function getSubUserType($user_type_id){
		$db  = JO_Db::getDefaultAdapter();
		$query =  $db->select()->from('user_type',array('title','user_type_id','status'))->where('parent_id = ?',$user_type_id)->where('status = ? ',1)->order('user_type.sort_order ASC');
		$result= $db->fetchAll($query);
		return $result; 
	}

        function getUserTypeTitle($user_type_id){
		$db = JO_Db::getDefaultAdapter();
		$sql = "select title from user_type where user_type_id = {$user_type_id}";
		$result = $db->fetchOne($sql);
		return $result;
	}

        function getUserTypeNotOthers($user_type_id){
            if ($user_type_id != "")
            {
		$db = JO_Db::getDefaultAdapter();
		$sql = "select user_type_id from user_type where user_type_id = {$user_type_id} AND parent_id = 2";
		$result = $db->fetchOne($sql);
            }
		return $result;
	}
        
        
	public function getAge(){
		$db  = JO_Db::getDefaultAdapter();
		$query =  $db->select()->from('age',array('*'))->order('age.age_id ASC');
		$result= $db->fetchAll($query);
		return $result; 
	}	

        public function getAgeTitle($age_id){
		$db  = JO_Db::getDefaultAdapter();
		$sql = "select age_title from age where age_id = {$age_id}";
		$result = $db->fetchOne($sql);
		return $result; 
	}	

        public function getLevel(){
		$db  = JO_Db::getDefaultAdapter();
		$query =  $db->select()->from('level',array('*'))->order('level.level_id ASC');
		$result= $db->fetchAll($query);
		return $result; 
	}
        
        public function getLevelTitle($level_id){
		$db  = JO_Db::getDefaultAdapter();
		$sql = "select level_title from level where level_id = {$level_id}";
		$result = $db->fetchOne($sql);
		return $result; 
	}	
        
        public function getActivateUser($user_id){
		$db  = JO_Db::getDefaultAdapter();
		//$sql = "select * from users_activate where user_id = {$user_id}";
		//$result = $db->fetchOne($sql);
		$query =  $db->select()->from('users_activate',array('*'))->where('user_id = ?',$user_id);
                $result= $db->fetchRow($query);
		return $result; 
	}

        public function getActivateLatLen($lat, $len){
		$db  = JO_Db::getDefaultAdapter();
		//$sql = "select * from users_activate where user_id = {$user_id}";
		//$result = $db->fetchOne($sql);
		$query =  $db->select()->from('users_activate',array('*'))->where('lat = ?',$lat)->where('len = ?',$len);
                $result= $db->fetchRow($query);
		return $result; 
	}
        
        
        public function getUsersActivate($data = array()) {
		
		$key = md5(serialize($data));
		
		static $result = array();
		if(isset($result[$key])) { return $result[$key]; }
		
		$db = JO_Db::getDefaultAdapter();
        
		$query = $db
					->select()
					->from('users_activate', array('users_activate.*'))
                                        ->where('users_activate.activate = 1');
	
		if(isset($data['start']) && isset($data['limit'])) {
			if($data['start'] < 0) {
				$data['start'] = 0;
			}
			$query->limit($data['limit'], $data['start']);
		}
		
                /*
		if(isset($data['sort']) && strtolower($data['sort']) == 'desc') {
			$sort = ' DESC';
		} else {
			$sort = ' ASC';
		}
		
		$allow_sort = array(
			'users.user_id',
			'users.username',
			'users.firstname',
			'users.status',
                        'users.likers'
		);
		if(isset($data['filter_like_pin_id'])) {
			$allow_sort[] = 'pins_likes.like_id';
		}
		
		if(isset($data['order']) && in_array($data['order'], $allow_sort)) {
			$query->order($data['order'] . $sort);
		} else {
			$query->order('firstname' . $sort);
		}
		*/
		////////////filter
		
		/*if(isset($data['filter_welcome']) && is_array($data['filter_welcome'])) {
			$query->joinLeft('boards', 'boards.user_id = users.user_id', array())
			->where('boards.category_id IN (?)', new JO_Db_Expr(implode(',',$data['filter_welcome'])))
			->group('users.user_id');
		}*/
		
		
/*		
		if(isset($data['filter_followers_user_id']) && $data['filter_followers_user_id']) {
			$query->joinLeft('users_following_user', 'users.user_id = users_following_user.following_id', array())
			->where('users_following_user.user_id = ?', (string)$data['filter_followers_user_id']);
		}
		
		if(isset($data['filter_following_user_id']) && $data['filter_following_user_id']) {
			$query->joinLeft('users_following_user', 'users.user_id = users_following_user.user_id', array())
			->where('users_following_user.following_id = ?', (string)$data['filter_following_user_id']);
		}
		
		if(isset($data['filter_likers_user_id']) && $data['filter_likers_user_id']) {
			$query->joinLeft('users_likes', 'users.user_id = users_likes.user_like_id', array())
			->where('users_likes.user_id = ?', (string)$data['filter_likers_user_id']);
		}
		
		if(isset($data['filter_liking_user_id']) && $data['filter_liking_user_id']) {
			$query->joinLeft('users_likes', 'users.user_id = users_likes.user_id', array())
			->where('users_likes.user_like_id = ?', (string)$data['filter_liking_user_id']);
		}
                
		if(isset($data['filter_like_pin_id']) && $data['filter_like_pin_id']) {
			$query->joinLeft('pins_likes', 'users.user_id = pins_likes.user_id')
			->where('pins_likes.pin_id = ?', (string)$data['filter_like_pin_id']);
		}
*/		
		if(isset($data['filter_gender']) && $data['filter_gender']) {
			$query->where('users_activate.gender = ?', (string)$data['filter_gender']);
		}

                if(isset($data['filter_age']) && $data['filter_age']) {
			$query->where('users_activate.age = ?', (string)$data['filter_age']);
		}
		
		if(isset($data['filter_location']) && $data['filter_location']) {
                    if ($data['filter_location'] != "Introduce una ubicación")
                    {
			$query->where('users_activate.location LIKE ?', "%".str_replace(",", "%", (string)$data['filter_location'])."%");
                    }
		}
                
		if(isset($data['filter_sport_category']) && $data['filter_sport_category']) {
			$query->where('users_activate.sport_category = ?', (string)$data['filter_sport_category']);
		}
                
		if(isset($data['filter_level']) && $data['filter_level']) {
			$query->where('users_activate.level = ?', (string)$data['filter_level']);
		}

		if(isset($data['filter_option1']) && $data['filter_option1']) {
			$query->where('users_activate.option1 = ?', (string)$data['filter_option1']);
		}
                
		if(isset($data['filter_option2']) && $data['filter_option2']) {
			$query->where('users_activate.option2 = ?', (string)$data['filter_option2']);
		}
                
		if(isset($data['filter_option3']) && $data['filter_option3']) {
			$query->where('users_activate.option3 = ?', (string)$data['filter_option3']);
		}
                
		if(isset($data['filter_option4']) && $data['filter_option4']) {
			$query->where('users_activate.option4 = ?', (string)$data['filter_option4']);
		}
                
		if(isset($data['filter_option5']) && $data['filter_option5']) {
			$query->where('users_activate.option5 = ?', (string)$data['filter_option5']);
		}
                
		if(isset($data['filter_option6']) && $data['filter_option6']) {
			$query->where('users_activate.option6 = ?', (string)$data['filter_option6']);
		}
                
		if(isset($data['filter_option7']) && $data['filter_option7']) {
			$query->where('users_activate.option7 = ?', (string)$data['filter_option7']);
		}
                
		if(isset($data['filter_option8']) && $data['filter_option8']) {
			$query->where('users_activate.option8 = ?', (string)$data['filter_option8']);
		}
                
                /*
                if(isset($data['filter_welcome']) && is_array($data['filter_welcome'])) {
			$query->where('users.user_id IN (SELECT user_id FROM boards WHERE category_id IN (?) OR category_id IN (SELECT category_id FROM category WHERE parent_id IN (?))) ', new JO_Db_Expr(implode(',',$data['filter_welcome'])));
		}
*/
                $results  = array();

		//error_log("query " . $query); 
		$results = $db->fetchAll($query);

                return $results;
                /*
		$result[$key] = array();
		if($results) {
			foreach($results AS $data) {
				$data['pins_array'] = array();
				if(trim($data['latest_pins'])) {
					$data['pins_array'] = $db->fetchAll($db->select()->from('pins')->where("pin_id IN ('?')", new JO_Db_Expr(implode("','", explode(',',$data['latest_pins']))))->order('pin_id DESC')->limit(15));
				}
				
				$result[$key][] = $data;
			}
		}
		
		return $result[$key];
                 * 
                 */
	}

        
        public static function createActivate($user_id, $data) {
		$db = JO_Db::getDefaultAdapter();
		
		$rows = self::describeTable('users_activate');
		
		//$user_info_get = self::getUser($user_id);
		
		//$created = WM_Date::format($user_info_get['created'], 'yy-mm-dd H:i:s');
                
		$update = array();
		foreach($rows AS $row) {
			if( array_key_exists($row, $data) ) {
                            $update[$row] = $data[$row];
			}
		}
		
		if(!$update) {
				return false;
		}
		
                $user_data = Model_Users::getActivateUser( JO_Session::get('user[user_id]') );
                if (!$user_data)
                {
                    $db->insert('users_activate', $update);
		
                    $user_id = $db->lastInsertId();
		
                    if(!$user_id) {
			return false;
                    }

                }
                else
                {
                    $result = $db->update('users_activate', $update, array('user_id = ?' => (string)$user_id));

                    if (!$result)
                    {
                        return false;
                    }

                }
		
		
		return true;
	}
        
	public static function deleteUsersLocation($user_id) {
		$db = JO_Db::getDefaultAdapter();
		
		$row = $db->query("DELETE FROM users_location WHERE user_id = ". $user_id );
        
		if(!$row) {
			return false;
		}
		
		return $row;
	}
        
        
        public static function createUsersLocation($user_id, $location, $lat, $len) {
		$db = JO_Db::getDefaultAdapter();
		
		$rows = self::describeTable('users_location');
		
                
		$update = array();
		
                
                $db->insert('users_location', array(
                        'user_id' => $user_id,
                        'location' => (string)$location,
                        'lat' => (string)$lat,
                        'len' => (string)$len
                ));		

                $user_id = $db->lastInsertId();

                if(!$user_id) {
                    return false;
                }

		
		
		return true;
	}

        
        public function getUserLocation($user_id){
		$db  = JO_Db::getDefaultAdapter();
		$query =  $db->select()->from('users_location',array('*'))->where('user_id = ?',$user_id);
		$result= $db->fetchAll($query);
		return $result; 
	}

        public function getLocationUsers($location){
		$db  = JO_Db::getDefaultAdapter();
		$query =  $db->select()->from('users_location',array('*'))->where('location LIKE ?',"%".str_replace(",", "%", $location)."%");
		$result= $db->fetchAll($query);
		return $result; 
	}
        
        public function getLocationUsersLatLen($lat, $len){
		$db  = JO_Db::getDefaultAdapter();
		//$sql = "select * from users_activate where user_id = {$user_id}";
		//$result = $db->fetchOne($sql);
		$query =  $db->select()->from('users_location',array('*'))->where('lat = ?',$lat)->where('len = ?',$len);
                $result= $db->fetchRow($query);
		return $result; 
	}

        public function getUsersLatLen($lat, $len){
		$db  = JO_Db::getDefaultAdapter();
		//$sql = "select * from users_activate where user_id = {$user_id}";
		//$result = $db->fetchOne($sql);
		$query =  $db->select()->from('users',array('*'))->where('lat = ?',$lat)->where('len = ?',$len);
                $result= $db->fetchRow($query);
		return $result; 
	}

        
	public static function deleteUsersSports($user_id) {
		$db = JO_Db::getDefaultAdapter();
		
		$row = $db->query("DELETE FROM users_sports WHERE user_id = ". $user_id );
        
		if(!$row) {
			return false;
		}
		
		return $row;
	}
        
        
        public static function createUsersSports($user_id, $sport_id) {
		$db = JO_Db::getDefaultAdapter();
		
		$rows = self::describeTable('users_sports');
		
                
		$update = array();
		
                
                $db->insert('users_sports', array(
                        'user_id' => $user_id,
                        'sport_category' => (string)$sport_id
                ));		

                $user_id = $db->lastInsertId();


                if(!$user_id) {
                    return false;
                }

		
		
		return true;
	}
        
        public function getUserSports($user_id){
		$db  = JO_Db::getDefaultAdapter();
		$query =  $db->select()->from('users_sports',array('*'))->where('user_id = ?',$user_id);
		$result= $db->fetchAll($query);
		return $result; 
	}

        public function getUserIdSportsUser($sport_category){
		$db  = JO_Db::getDefaultAdapter();
		$query =  $db->select()->from('users_sports',array('user_id'))->where('sport_category = ?',$sport_category);
		$result= $db->fetchAll($query);
		return $result; 
	}
       
        
        public function getUserSportsById($user_id, $sport_category){
		$db  = JO_Db::getDefaultAdapter();
		$query =  $db->select()->from('users_sports','sport_category')->where('user_id = ?',$user_id)->where('sport_category = ?',$sport_category);
		$result= $db->fetchOne($query);
		return $result; 
	}
        
        
	public function editUserSports($user_id) {
		$db = JO_Db::getDefaultAdapter();
		$result= $db->update('users_sports', array(
			'user_id' => $user_id
                        ), array('user_id = ?' => '0'));
                return $result; 
	}
        

}

?>