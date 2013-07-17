<?php

class FacebookController extends JO_Action {
	
	/**
	 * @var WM_Facebook
	 */
	protected $facebook;
	
	public function init() {
		$this->facebook = JO_Registry::get('facebookapi');
	}
	
	public function indexAction(){
		
		$request = $this->getRequest();
		
		if( JO_Session::get('user[user_id]') ) {
			$this->redirect( WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . JO_Session::get('user[user_id]') ) );
		}
		
		$next = '';
		if($request->issetQuery('next')) {
			$next = '&next=' . urlencode(html_entity_decode($request->getQuery('next')));
		}
		
// 		$this->facebook->setNextUrl( WM_Router::create( $request->getBaseUrl() . '?controller=facebook&action=login' . $next ) );
		
		$url = $this->facebook->getLoginUrl(array(
			'redirect_uri' => WM_Router::create( $request->getBaseUrl() . '?controller=facebook&action=login' . $next ),
			'req_perms' => 'email,user_birthday,status_update,user_videos,user_status,user_photos,offline_access,read_friendlists'
		));
		
// 		$url = $this->facebook->getLoginUrl(array('req_perms' => 'user_status,user_photos,offline_access,read_friendlists'));
//		$url = $this->facebook->getLoginUrl(array('req_perms' => 'user_status,publish_stream,user_photos,offline_access,read_friendlists'));
//		echo $url; exit;
		$this->redirect($url);
	}
	
	public function loginAction() {
		
		$request = $this->getRequest();
		
//		if( JO_Session::get('user[user_id]') ) {
//			$this->redirect( WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . JO_Session::get('user[user_id]') ) );
//		}

		/*if($request->getQuery('session')) {
			$session = JO_Json::decode( html_entity_decode($request->getQuery('session')), true );
			if($session) {
				$this->facebook->setSession($session);
				if($request->getQuery('next')) {
					JO_Session::set('next', $request->getQuery('next'));
				}
			}
		}*/
		
		$session = $this->facebook->getUser();
		
		$fbData = null;
		if($session) {
			$fbData = $this->facebook->api('/me');
		}
		

		if($fbData) {
			if(!isset($fbData['email'])) {
		    	$fbData['email'] = '';
		    }
		    
			if(!self::loginInit($fbData['id'], $session)) {
				
				//if(!self::loginInit($fbData['email'], $session, 'email')) {
					
					if(JO_Registry::get('enable_free_registration')) {
						$this->forward('facebook', 'register', array('fbData'=>$fbData, 'session' => $session, 'shared_content' => array()));
					}
					
					$shared_content = Model_Users::checkInvateFacebookID($fbData['id']);
					
					if( $shared_content ) {
						$this->forward('facebook', 'register', array('fbData'=>$fbData, 'session' => $session, 'shared_content' => $shared_content));
					} else {
					
						$this->setViewChange('no_account');
						
						$page_login_trouble = Model_Pages::getPage( JO_Registry::get('page_login_trouble') );
						if($page_login_trouble) {
							$this->view->page_login_trouble = array(
								'title' => $page_login_trouble['title'],
								'href' => WM_Router::create( $request->getBaseUrl() . '?controller=pages&action=read&page_id=' . $page_login_trouble['page_id'] )
							);
						}
					
					}
				//}
			
			}
		    
		} else {
			$this->setViewChange('error_login');
			
			$page_login_trouble = Model_Pages::getPage( JO_Registry::get('page_login_trouble') );
			if($page_login_trouble) {
				$this->view->page_login_trouble = array(
					'title' => $page_login_trouble['title'],
					'href' => WM_Router::create( $request->getBaseUrl() . '?controller=pages&action=read&page_id=' . $page_login_trouble['page_id'] )
				);
			}
		}
		

		$this->view->children = array(
        	'header_part' 	=> 'layout/header_part',
        	'footer_part' 	=> 'layout/footer_part'
        );
		
	}
	
	public function registerAction($data = null) {
		
		$request = $this->getRequest();
		
		if( JO_Session::get('user[user_id]') ) {
			$this->redirect( WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . JO_Session::get('user[user_id]') ) );
		}
		
		if(!$data) {
			$this->redirect( WM_Router::create( $this->getRequest()->getBaseUrl() . '?controller=users&action=login' ) );
		}
		
		$fbData = $data['fbData'];
		$session = $data['session'];
		$shared_content = isset($data['shared_content'])?$data['shared_content']:'';
		
		
		self::loginInit($fbData['id'], $session);
		
		$ph = new WM_Facebook_Photo();
		$image = $ph->getRealUrl('http://graph.facebook.com/'.$fbData['id'].'/picture?type=large');
		if( !@getimagesize($image) ) {
			$image = '';
		}
		
		$this->view->error = false;
		if($request->isPost()) {
			
			$validate = new Helper_Validate();
			$validate->_set_rules($request->getPost('username'), $this->translate('Username'), 'not_empty;min_length[3];max_length[100];username');
//			$validate->_set_rules($request->getPost('firstname'), $this->translate('First name'), 'not_empty;min_length[3];max_length[100]');
//			$validate->_set_rules($request->getPost('lastname'), $this->translate('Last name'), 'not_empty;min_length[3];max_length[100]');
			$validate->_set_rules($request->getPost('email'), $this->translate('Email'), 'not_empty;min_length[5];max_length[100];email');
			$validate->_set_rules($request->getPost('password'), $this->translate('Password'), 'not_empty;min_length[4];max_length[30]');
//			$validate->_set_rules($request->getPost('password2'), $this->translate('Confirm password'), 'not_empty;min_length[4];max_length[30]');
			
			if($validate->_valid_form()) {
//				if( md5($request->getPost('password')) != md5($request->getPost('password2')) ) {
//					$validate->_set_form_errors( $this->translate('Password and Confirm Password should be the same') );
//					$validate->_set_valid_form(false);
//				}
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
				$reg_key = sha1($request->getPost('email').$request->getPost('username'));
				
				$result = Model_Users::create(array(
					'facebook_id' => $fbData['id'],
					'gender' => (isset($fbData['gender']) ? $fbData['gender'] : ''),
					'avatar' => ($image ? $image : ''),
					'location' => (isset($fbData['hometown']['name']) ? $fbData['hometown']['name'] : ''),
					'website' => (isset($fbData['website']) ? $fbData['website'] : ''),
					'username' => $request->getPost('username'),
					'firstname' => isset($fbData['first_name'])?$fbData['first_name']:'',
					'lastname' => isset($fbData['last_name'])?$fbData['last_name']:'',
					'email' => $request->getPost('email'),
					'password' => $request->getPost('password'),
					'delete_email' => isset($fbData['email']) ? $fbData['email'] : '',
					'facebook_session' => $session,
					'delete_code' => isset($shared_content['if_id']) ? $shared_content['if_id'] : '',
					'following_user' => isset($shared_content['user_id']) ? $shared_content['user_id'] : '',
					'facebook_connect' => 1,
					'confirmed' => '0',
					'regkey'=>$reg_key
				));
				
				if($result) {
					//self::loginInit($fbData['id'], $session);
					
					if(self::sendMail($result)){
						self::loginInit($fbData['id']);
						$this->redirect( WM_Router::create( $this->getRequest()->getBaseUrl() ) );
					};
					
				} else {
					$this->view->error = $this->translate('There was a problem with the record. Please try again!');
				}
				
			} else {
				$this->view->error = $validate->_get_error_messages();
			}
		}
		
		$this->view->user_id_fb = $fbData['id'];


		$this->view->baseUrl = $request->getBaseUrl();
		
		if($request->issetPost('email')) {
			$this->view->email = $request->getPost('email');
		} else {
			if(isset($fbData['email'])) {
				$this->view->email = $fbData['email'];
			} else {
				$this->view->email = '';
			}
		}
		
		if($request->issetPost('firstname')) {
			$this->view->firstname = $request->getPost('firstname');
		} else {
			if(isset($fbData['first_name'])) {
				$this->view->firstname = $fbData['first_name'];
			} else {
				$this->view->firstname = '';
			}
		}
//		
//		if($request->issetPost('lastname')) {
//			$this->view->lastname = $request->getPost('lastname');
//		} else {
//			if(isset($fbData['last_name'])) {
//				$this->view->lastname = $fbData['last_name'];
//			} else {
//				$this->view->lastname = '';
//			}
//		}
		
		if($request->issetPost('username')) {
			$this->view->username = $request->getPost('username');
		} else {
			if(isset($fbData['username'])) {
				$this->view->username = $fbData['username'];
			} else {
				$this->view->username = '';
			}
		}
		
		$this->view->password = $request->getPost('password');
//		$this->view->password2 = $request->getPost('password2');
		
		
		$this->setViewChange('register');
		
		
		
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
		
		$this->view->check_username = WM_Router::create( $request->getBaseUrl() . '?controller=facebook&action=check_username' );
		$this->view->check_email = WM_Router::create( $request->getBaseUrl() . '?controller=facebook&action=check_email' );
		
		
		$this->view->children = array(
       		'header_part' 	=> 'layout/header_part',
       		'footer_part' 	=> 'layout/footer_part'
		);
		
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
	
	private function loginInit($id, $session, $row = 'id') {
		$user_data = WM_Users::checkLoginFacebookTwitter($id, 'facebook', $session, false, $row);
		if($user_data) { 
			JO_Session::set(array('user' => $user_data));
			JO_Session::clear('fb_login');
			/*if(JO_Session::issetKey('next') && JO_Session::get('next')) {
				$this->redirect( ( urldecode(JO_Session::get('next')) ) );
			} else {
				$this->redirect( WM_Router::create( $this->getRequest()->getBaseUrl() ) );
			}*/
			$this->redirect( WM_Router::create( $this->getRequest()->getBaseUrl() ) );
		}
		
		return $user_data;
	}
	
	
	private function sendMail($userId){
		$this->noViewRenderer(true);
		$this->noLayout(true);
		//$userId = '462';
		$user =  Model_Users::getUser($userId);
		$url = WM_Router::create(JO_Request::getInstance()->getBaseUrl()."?controller=welcome&action=finishRegistration&key=".sha1($user['email'].$user['username']));
		
		$body = "Hola y bienvenid@ a ".JO_Registry::get('site_name')."! <br /> Para verificar tu email y finalizar el registro, por favor haz clic en el vinculo a continuacion: <br/><a href=\"{$url}\">{$url}</a>";
		//var_dump($user);
		$to = $user['email'];
		$from = JO_Registry::forceGet('noreply_mail');
		$title = "amatteur - por favor verifica tu email";
		
			
		if(Model_Email::send($to, $from, $title, $body)){
			//$this->redirect(WM_Router::create(JO_Request::getInstance()->getBaseUrl()."?controller=users&action=verificationRequired"));
			return true;
		};
	}

}

?>