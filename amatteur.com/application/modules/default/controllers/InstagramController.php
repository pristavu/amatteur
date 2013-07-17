<?php

class InstagramController extends JO_Action {
	
	/**
	 * @var WM_Instagram
	 */
	private $instagram = null;
	private $user_data = null;
	
	public function init() {
		
		if(JO_Session::get('user[user_id]')) {
		
			if(JO_Session::get('next') && JO_Validate::validateHost(JO_Session::get('next'))) {
				$next = JO_Session::get('next');
				if($this->getRequest()->getQuery('code')) {
					$next .= (strpos($next, '?')!==false ? '&code=' : '?code=') . $this->getRequest()->getQuery('code');
				}
				if($this->getRequest()->getQuery('state')) {
					$next .= (strpos($next, '?')!==false ? '&state=' : '?state=') . $this->getRequest()->getQuery('state');
				}
				JO_Session::clear('next');
				$this->redirect($next);
			}
		
		}
		
	}
	
	private function initInstagram(array $options = array()) {
		
		$config = array(
				'client_id' => JO_Registry::get('oauth_in_key'),
				'client_secret' => JO_Registry::get('oauth_in_secret'),
				'grant_type' => 'authorization_code',
				'redirect_uri' => WM_Router::create($this->getRequest()->getBaseUrl() . '?controller=instagram'),
		);
		
		$this->instagram = new WM_Instagram(
			JO_Array::array_extend($config, $options)
		);
		
		if($this->user_data) {
			return true;
		}
		
		if(JO_Session::get('user[user_id]')) {
			if(JO_Session::get('user[instagram_connect]') != 1 && !$this->getRequest()->getParam('state')) {
				return false;
			}
		}
		
		$InstagramAccessToken = $this->instagram->getAccessToken();
		$user_data = JO_Json::decode($this->instagram->getUser(), true);
		if( isset($user_data['meta']['code']) && $user_data['meta']['code'] == 200 ) {
			JO_Session::set('InstagramAccessToken', $InstagramAccessToken);
			$this->user_data = $user_data['data'];
		} elseif($InstagramAccessToken) {
			JO_Session::set('InstagramAccessToken', $InstagramAccessToken);
			$this->instagram->setAccessToken($InstagramAccessToken);
		} elseif(JO_Session::get('InstagramAccessToken')) {
			$this->instagram->setAccessToken(JO_Session::get('InstagramAccessToken'));
		} elseif(JO_Session::get('user[instagram_token]')) {
			$this->instagram->setAccessToken(JO_Session::get('user[instagram_token]'));
		}
		if(!$this->user_data) {
			$user_data = JO_Json::decode($this->instagram->getUser(), true);
			if( isset($user_data['meta']['code']) && $user_data['meta']['code'] == 200 ) {
				$this->user_data = $user_data['data'];
			}
		}

		return $this->user_data ? true : false;
		
	}
	
	private function loginInit($id) {
		$user_data = WM_Users::checkLoginFacebookTwitter($id, 'instagram_profile');
		if($user_data) { 
			JO_Session::set(array('user' => $user_data));
			if($this->instagram) {
				WM_Users::edit2( JO_Session::get('user[user_id]'), array(
						'instagram_token' => $this->instagram->getAccessToken()
				) );
			}
			if(JO_Session::issetKey('next') && JO_Session::get('next')) {
				$this->redirect( ( urldecode(JO_Session::get('next')) ) );
			} else {
				$this->redirect( WM_Router::create( $this->getRequest()->getBaseUrl() ) );
			}
		}
		return $user_data;
	}
	
	
	public function indexAction() {
		
		$request = $this->getRequest();
		
		if($request->getQuery('next')) {
			JO_Session::set('next', urldecode(html_entity_decode($request->getQuery('next'))));
		}

		$this->initInstagram();
		
		if($this->user_data) {
			
			if(!$this->loginInit($this->user_data['id'])) {
			
				$this->setViewChange('no_account');
				
				$page_login_trouble = Model_Pages::getPage( JO_Registry::get('page_login_trouble') );
				if($page_login_trouble) {
					$this->view->page_login_trouble = array(
							'title' => $page_login_trouble['title'],
							'href' => WM_Router::create( $this->getRequest()->getBaseUrl() . '?controller=pages&action=read&page_id=' . $page_login_trouble['page_id'] )
					);
				}
				
				$this->view->children = array(
						'header_part' 	=> 'layout/header_part',
						'footer_part' 	=> 'layout/footer_part'
				);
			
			}

		} else {
			$this->instagram->openAuthorizationUrl();
		}
		
	}
	
	
	
	public function isInstagramUserAction() {
		$this->view->isUser = $this->initInstagram();
		$this->view->redirect = false;
		if(!$this->view->isUser) {
			$this->view->redirect = WM_Router::create($this->getRequest()->getBaseUrl() . '?controller=instagram&next=' . WM_Router::create($this->getRequest()->getBaseUrl() . '?controller=instagram&action=media') );
		}
		echo $this->renderScript('json');
	}
	
	public function mediaAction() {
		
		$request = $this->getRequest();
		
		if( !JO_Session::get('user[user_id]') ) {
			$this->redirect( WM_Router::create( $request->getBaseUrl() . '?controller=users&action=login') );
		}
		
		$request = $this->getRequest();
		
		///////////// boards
		$this->view->createBoard = WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=create' );
		
		$boards = Model_Boards::getBoards(array(
				'filter_user_id' => JO_Session::get('user[user_id]'),
				'order' => 'boards.sort_order',
				'sort' => 'ASC',
				'friendly' => JO_Session::get('user[user_id]')
		));
		
		$this->view->boards = array();
		if($boards) {
			foreach($boards AS $board) {
				$this->view->boards[] = array(
						'board_id' => $board['board_id'],
						'title' => $board['title']
				);
			}
		}
		
		/////// add media
		
		$this->view->add_media_href = WM_Router::create($this->getRequest()->getBaseUrl() . '?controller=instagram&action=pinMediaCheck');
		
		//$this->initInstagram();
		
		$this->view->checkLoginInstagram = WM_Router::create($this->getRequest()->getBaseUrl() . '?controller=instagram&action=isInstagramUser');
		$this->view->getMediaInstagramFirst = WM_Router::create($this->getRequest()->getBaseUrl() . '?controller=instagram&action=getMedias&first=true');
		$this->view->getMediaInstagram = WM_Router::create($this->getRequest()->getBaseUrl() . '?controller=instagram&action=getMedias');
		
		
		/////////////curl request to get instagram media's
		$curl = new JO_Http();
		$curl->initialize(array(
				'target' => WM_Router::create( $this->getRequest()->getBaseUrl() . '?controller=instagram&action=cronfirst&user=' . JO_Session::get('user[user_id]') ),
				'method' => 'GET',
				'timeout' => 2
		));
		$curl->useCurl(true);
		$curl->execute();
// 			var_dump($curl->result); exit;

		
		$this->view->children = array(
				'header_part' 	=> 'layout/header_part',
				'footer_part' 	=> 'layout/footer_part'
		);
		
	}
	
	public function pinMediaCheckAction() {
		
		$request = $this->getRequest();
		
		$this->view->media = array();
		
		if(JO_Session::get('user[user_id]')) {
			
			$media_ids = $request->getPost('media_id');
			$board_info = Model_Boards::getBoard($request->getPost('board_id'));
			if(is_array($media_ids) && count($media_ids) > 0) {
				if($board_info) {
					$data = array(
							'filter_user_id' => JO_Session::get('user[user_id]'),
							'media_id_in' => $media_ids,
							'limit' => 'none'
					);
						
					$meduas = Model_Instagram::getUserMediasData($data);
					$medias = array();
					foreach($meduas AS $image) {
						$medias[] = $image['media_id'];
					}
					
					$instagram_media = array(
						'media_id' => $medias,
						'board_id' => $board_info['board_id']
					);
					
					if($medias) {
						JO_Session::set('instagram_media', $instagram_media);
						$this->view->location = WM_Router::create($this->getRequest()->getBaseUrl() . '?controller=instagram&action=pinMedia');
					} else {
						$this->view->error = $this->translate('You must select media to pinit!');
					}
				} else {
					$this->view->error = $this->translate('You must select board to pinit!');
				}
			} else {
				$this->view->error = $this->translate('You must select media to pinit!');
			}
		} else {
			$this->view->location = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=login');
		}
		
		echo $this->renderScript('json');
	}
	
	public function pinMediaCallbackAction() {
		
		$request = $this->getRequest();
		
		if(JO_Session::get('user[user_id]')) {
			
			$media = Model_Instagram::getMedia($request->getPost('media_id'));
			if($media) {
				if($media['user_id'] == JO_Session::get('user[user_id]')) {
					
					$result = Model_Pins::create(array(
						'title' => $media['title'],
						'from' => $media['from'],
						'image' => $media['media'],
						'description' => $media['title'],
						'board_id' => JO_Session::get('instagram_media[board_id]')
					));
					
					if($result) {
						
						Model_Instagram::setPinMedia($media['media_id'], $result);
						
						Model_History::addHistory(JO_Session::get('user[user_id]'), Model_History::ADDPIN, $result);
						
						$session_user = JO_Session::get('user[user_id]');
						
						$group = Model_Boards::isGroupBoard($request->getPost('board_id'));
						if($group) {
							$users = explode(',',$group);
							foreach($users AS $user_id) {
								if($user_id != $session_user) {
									$user_data = Model_Users::getUser($user_id);
						
									if($user_data && $user_data['email_interval'] == 1 && $user_data['groups_pin_email']) {
										$this->view->user_info = $user_data;
										$this->view->profile_href = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . JO_Session::get('user[user_id]'));
										$this->view->full_name = JO_Session::get('user[firstname]') . ' ' . JO_Session::get('user[lastname]');
										$this->view->pin_href = WM_Router::create( $request->getBaseUrl() . '?controller=pin&pin_id=' . $result );
										$board_info = Model_Boards::getBoard($request->getPost('board_id'));
										if($board_info) {
											$this->view->board_title = $board_info['title'];
											$this->view->board_href = WM_Router::create($request->getBaseUrl() . '?controller=boards&action=view&user_id=' . $board_info['user_id'] . '&board_id=' . $board_info['board_id']);
										}
										Model_Email::send(
												$user_data['email'],
												JO_Registry::get('noreply_mail'),
												JO_Session::get('user[firstname]') . ' ' . JO_Session::get('user[lastname]') . ' ' . $this->translate('added new pin to a group board'),
												$this->view->render('group_board', 'mail')
										);
									}
						
								}
							}
						}
						$this->view->ok = true;
						
					} else {
						$this->view->error = $this->translate('There was a problem with the record. Please try again!');
					}
					
				} else {
					$this->view->error = $this->translate('Private media!');
				}
			} else {
				$this->view->error = $this->translate('Media not found!');
			}
			
		} else {
			$this->view->location = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=login');
		}
		
		echo $this->renderScript('json');
		
	}
	
	public function pinMediaAction() {
		
		$request = $this->getRequest();
		
		if( !JO_Session::get('user[user_id]') ) {
			$this->redirect( WM_Router::create( $request->getBaseUrl() . '?controller=users&action=login') );
		}
		
		$media_ids = JO_Session::get('instagram_media[media_id]');
		$board_id = JO_Session::get('instagram_media[board_id]');
		
		$data = array(
				'filter_user_id' => JO_Session::get('user[user_id]'),
				'media_id_in' => $media_ids,
				'limit' => 'none'
		);
			
		$meduas = Model_Instagram::getUserMediasData($data);
		$this->view->medias = array();
		foreach($meduas AS $image) {
			$old_image = basename($image['media']);
			$new_image = str_replace($old_image, str_replace('_7', '_5', $old_image), $image['media']);
			$this->view->medias[] = array(
					'title' => $image['title'],
					'media_id' => $image['media_id'],
					'thumb' => $new_image
			);
		}
		
		$this->view->pin_media = WM_Router::create($this->getRequest()->getBaseUrl() . '?controller=instagram&action=pinMediaCallback');
		$this->view->pin_media_fetch = WM_Router::create($this->getRequest()->getBaseUrl() . '?controller=instagram&action=media');
		
		$this->view->children = array(
				'header_part' 	=> 'layout/header_part',
				'footer_part' 	=> 'layout/footer_part'
		);
		
	}
	
	public function registerAction() {
		
		$request = $this->getRequest();
		
		if( JO_Session::get('user[user_id]') ) {
			$this->redirect( WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . JO_Session::get('user[user_id]') ) );
		}
		
		$this->initInstagram(array(
			'redirect_uri' => WM_Router::create($request->getBaseUrl() . '?controller=instagram&next=' . urlencode( WM_Router::create($request->getBaseUrl() . '?controller=instagram&action=register') ))
		));
		
		
		if($this->user_data) {
			
			$this->loginInit($this->user_data['id']);
			
			$this->view->baseUrl = $request->getBaseUrl();
			
			if($request->issetPost('email')) {
				$this->view->email = $request->getPost('email');
			} else {
				if(isset($this->user_data['email'])) {
					$this->view->email = $this->user_data['email'];
				} else {
					$this->view->email = '';
				}
			}
			
			if($request->issetPost('username')) {
				$this->view->username = $request->getPost('username');
			} else {
				if(isset($this->user_data['username'])) {
					$this->view->username = $this->user_data['username'];
				} else {
					$this->view->username = '';
				}
			}
			
			$this->view->profile_picture = $this->user_data['profile_picture'];
			
			$this->view->password = $request->getPost('password');
			
			$this->view->error = false;
			if($request->isPost()) {
			
				$validate = new Helper_Validate();
				$validate->_set_rules($request->getPost('username'), $this->translate('Username'), 'not_empty;min_length[3];max_length[100];username');
				$validate->_set_rules($request->getPost('email'), $this->translate('Email'), 'not_empty;min_length[5];max_length[100];email');
				$validate->_set_rules($request->getPost('password'), $this->translate('Password'), 'not_empty;min_length[4];max_length[30]');
				if($validate->_valid_form()) {
					if( Model_Users::isExistEmail($request->getPost('email')) ) {
						$validate->_set_form_errors( $this->translate('This e-mail address is already used') );
						$validate->_set_valid_form(false);
					}
					if( Model_Users::isExistUsername($request->getPost('username')) ) {
						$validate->_set_form_errors( $this->translate('This username is already used') );
						$validate->_set_valid_form(false);
					}
				}
				
				if($validate->_valid_form()) {
					
					$image = '';
					if( @getimagesize($this->user_data['profile_picture'])) {
						$image = $this->user_data['profile_picture'];
					}

					$name_arr = explode(' ',$this->user_data['full_name']);
					$first_name = array_shift($name_arr);
					$last_name = implode(' ', $name_arr);
					
					$result = Model_Users::create(array(
							'instagram_profile_id' => $this->user_data['id'],
							'gender' => (isset($this->user_data['gender']) ? $this->user_data['gender'] : ''),
							'avatar' => ($image ? $image : ''),
							'website' => (isset($this->user_data['website']) ? $this->user_data['website'] : ''),
							'username' => $request->getPost('username'),
							'firstname' => $first_name,
							'lastname' => $last_name,
							'email' => $request->getPost('email'),
							'password' => $request->getPost('password'),
							'instagram_connect' => 1,
							'instagram_token' => $this->instagram->getAccessToken()
					));
					
					if($result) {
						$this->loginInit($this->user_data['id']);
					} else {
						$this->view->error = $this->translate('There was a problem with the record. Please try again!');
					}
					
				} else {
					$this->view->error = $validate->_get_error_messages();
				}
				
			}
			
			
			if($this->getLayout()->meta_title) {
				$this->getLayout()->placeholder('title', ($this->getLayout()->meta_title . ' - ' . JO_Registry::get('meta_title')));
			} else {
				$this->getLayout()->placeholder('title', JO_Registry::get('meta_title'));
			}
			
			if($this->getLayout()->meta_description) {
				$this->getLayout()->placeholder('description', $this->getLayout()->meta_description);
			} else {
				$this->getLayout()->placeholder('description', JO_Registry::get('meta_description'));
			}
			
			if($this->getLayout()->meta_keywords) {
					$this->getLayout()->placeholder('keywords', $this->getLayout()->meta_keywords);
			} else {
				$this->getLayout()->placeholder('keywords', JO_Registry::get('meta_keywords'));
			}
			
			$this->getLayout()->placeholder('site_name', JO_Registry::get('site_name'));
			
			$this->view->site_name = JO_Registry::get('site_name');
			$this->view->meta_title = JO_Registry::get('meta_title');
			
			$this->getLayout()->placeholder('google_analytics', html_entity_decode(JO_Registry::get('google_analytics'), ENT_QUOTES, 'utf-8'));
			
			$this->view->baseUrl = $request->getBaseUrl();
			$this->view->site_logo = $request->getBaseUrl() . 'data/images/logo.png';
			if(JO_Registry::get('site_logo') && file_exists(BASE_PATH .'/uploads'.JO_Registry::get('site_logo'))) {
				$this->view->site_logo = $request->getBaseUrl() . 'uploads' . JO_Registry::get('site_logo');
			}
			
			$this->view->login = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=login' );
		
			$this->view->check_username = WM_Router::create( $request->getBaseUrl() . '?controller=instagram&action=check_username' );
			$this->view->check_email = WM_Router::create( $request->getBaseUrl() . '?controller=instagram&action=check_email' );
		
		
			$this->view->children = array(
				'header_part' 	=> 'layout/header_part',
				'footer_part' 	=> 'layout/footer_part'
			);
			
		} else {
			
			if(JO_Session::get('check_login_instagram')) {
				
				$this->setViewChange('error_login');
					
				$page_login_trouble = Model_Pages::getPage( JO_Registry::get('page_login_trouble') );
				if($page_login_trouble) {
					$this->view->page_login_trouble = array(
							'title' => $page_login_trouble['title'],
							'href' => WM_Router::create( $this->getRequest()->getBaseUrl() . '?controller=pages&action=read&page_id=' . $page_login_trouble['page_id'] )
					);
				}
				
				$this->view->children = array(
						'header_part' 	=> 'layout/header_part',
						'footer_part' 	=> 'layout/footer_part'
				);
				
				JO_Session::clear('check_login_instagram');
			
			} else {
				JO_Session::set('check_login_instagram',1);
				$this->instagram->openAuthorizationUrl();
			}
			
		}
		
	}
	
	public function check_emailAction(){
		
		$request = $this->getRequest();
		
		$username = trim($request->getPost('raw'));
		
		if(strlen($username) < 5) {
//			$this->view->error = $this->translate('Please use at least 5 characters');
		} else {
			$validate = new Helper_Validate();
			$validate->_set_rules($username, $this->translate('Email'), 'not_empty;min_length[5];max_length[100];email');
			if($validate->_valid_form()) {
				if( Model_Users::isExistEmail($username) ) {
					$validate->_set_form_errors( $this->translate('This email is already used') );
					$validate->_set_valid_form(false);
				}
			}
			
			if($validate->_valid_form()) {
				$this->view->success = $this->translate('Available');
			} else {
				$this->view->error = $validate->_get_error_messages();
			}
		}
		
		
		echo $this->renderScript('json');
	}
	
	public function check_usernameAction(){
		
		$request = $this->getRequest();
		
		$username = trim($request->getPost('raw'));
		
		if(strlen($username) < 3) {
			$this->view->error = $this->translate('Please use at least 3 characters');
		} else {
			$validate = new Helper_Validate();
			$validate->_set_rules($username, $this->translate('Username'), 'not_empty;min_length[3];max_length[100];username');
			if($validate->_valid_form()) {
				if( Model_Users::isExistUsername($username) ) {
					$validate->_set_form_errors( $this->translate('This username is already used') );
					$validate->_set_valid_form(false);
				}
			}
			
			if($validate->_valid_form()) {
				$this->view->success = $this->translate('Available');
			} else {
				$this->view->error = $validate->_get_error_messages();
			}
		}
		
		
		echo $this->renderScript('json');
	}
	
	public function getMediasAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {

			$media_id_not_in = JO_Session::get('media_id_not_in_' . JO_Session::get('user[user_id]'));
			if($request->getQuery('first') == 'true') {
				$media_id_not_in = array();
			}
			
			if(!is_array($media_id_not_in)) { $media_id_not_in = array(); }
			
			$data = array(
				'filter_user_id' => JO_Session::get('user[user_id]'),
				'media_id_not_in' => $media_id_not_in
			);
			
			$meduas = Model_Instagram::getUserMediasData($data);
			
			$medias = array();
			foreach($meduas AS $image) {
				$old_image = basename($image['media']);
				$new_image = str_replace($old_image, str_replace('_7', '_5', $old_image), $image['media']);
				$medias[] = array(
					'title' => $image['title'],
					'media_id' => $image['media_id'],
					'thumb' => $new_image	
				);
				$media_id_not_in[$image['media_id']] = $image['media_id'];
			}
			
			JO_Session::set('media_id_not_in_' . JO_Session::get('user[user_id]'), $media_id_not_in);
			echo 'addResponseData('.JO_Json::encode($medias).');';
			
		}
		exit;
	}
	
// 	public function loginAction() {
		
		
		
// 	}
	
	public function cronfirstAction() {
		
		set_time_limit(0);
		ignore_user_abort(true);
		
		$max_id = $this->getRequest()->getParam('max_id');
		
		$ud = Model_Users::getUser($this->getRequest()->getParam('user'));
		
		if(!$ud) {
			exit;
		}
		
		JO_Session::set('user', $ud);
		
		$InstagramAccessToken = $ud['instagram_token'];
		$user_id = $ud['user_id'];
		$instagram_id = $ud['instagram_profile_id'];
		
// 		$this->initInstagram();
		
		$params = array(
			'access_token' => $InstagramAccessToken, 
			'count' => 60, 
			'max_id' => $max_id ? $max_id : ''
		);
		
		$result = $this->getMediaData($instagram_id, 300, $params);
		
		if( isset($result['meta']['code']) && $result['meta']['code'] == 200 ) {
			
			$return = (array)$result['data'];
			if($return) {
				
				foreach($return AS $img) {
					list($instagram_media_id, $instagram_profile_id) = explode('_', $img['id']);
					Model_Instagram::addMedia(array(
							'user_id' => $user_id,
							'instagram_media_id' => $instagram_media_id,
							'width' => $img['images']['standard_resolution']['width'],
							'from' => $img['link'],
							'height' => $img['images']['standard_resolution']['height'],
							'media' => $img['images']['standard_resolution']['url'],
							'instagram_profile_id' => $instagram_profile_id,
							'md5key' => md5($img['id']),
							'title' => (string)(isset($img['caption']['text'])?$img['caption']['text']:$img['user']['username']),
							'pin_id' => ($this->checkDisabled($img['images']['standard_resolution']['url']) ? '0' : '-1')
					));
				}
				
				if (array_key_exists('next_url', $result['pagination'])) {
					$curl = new JO_Http();
					$curl->initialize(array(
							'target' => WM_Router::create( $this->getRequest()->getBaseUrl() . '?controller=instagram&action=cronfirst&user=' . $instagram_id . '&user_id='.$user_id.'&max_id=' . $result['pagination']['next_max_id'] ),
							'method' => 'GET',
							'timeout' => 10
					));
					$curl->useCurl(true);
					$curl->execute();
				}
			}
		}
		
		exit;
	}
	

	
	///////////////////// HELP FUNCTIONS ///////////////////////////////////
	
	/*public function getResults($user_id, $max_media_id = '', $min_media_id = '') {
		
		$result = JO_Json::decode($this->instagram->getUserRecent($user_id, $max_media_id, $min_media_id), true);
		
		$return = array();
		
		if( isset($result['meta']['code']) && $result['meta']['code'] == 200 ) {
			$return = (array)$result['data'];
			if (array_key_exists('next_url', $result['pagination'])) {
				$next_page = $this->getResults($user_id, $result['pagination']['next_max_id'], $min_media_id);
				if($next_page) {
					$return = array_merge($return, $next_page);
				}
			}
		}
		
		return $return;
	}*/
	
	private function getMediaData($user_id, $timeout = 30, array $params) {
		$curl = new JO_Http();
		$curl->initialize(array(
				'target' => 'https://api.instagram.com/v1/users/' . $user_id . '/media/recent',
				'method' => 'GET',
				'timeout' => $timeout,
				'params' => $params
		));
		$curl->useCurl(true);
		$curl->execute();
		return JO_Json::decode($curl->result, true);
	}
	
	private function checkDisabled($url) {
		$curl = new JO_Http();
		$curl->initialize(array(
				'target' => $url,
				'method' => 'GET',
				'timeout' => 10
		));
		$curl->useCurl(true);
		$curl->execute();
		return $curl->status == 200;
	}
	
}

?>