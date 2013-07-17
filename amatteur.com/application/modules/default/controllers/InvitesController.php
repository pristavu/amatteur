<?php

class InvitesController extends JO_Action {
	
	/**
	 * @var WM_Facebook
	 */
	protected $facebook;
	
	public function init() {
		if(!JO_Session::get('user[user_id]')) {
			$this->redirect( WM_Router::create( $this->getRequest()->getBaseUrl() . '?controller=users&action=login' ) );
		}
		$this->facebook = JO_Registry::get('facebookapi');
	}
	
	public function indexAction(){
		
		$request = $this->getRequest();
		
		$this->view->invate_limit = 5;
		
		if($request->isPost()) {
			$emails = array();
			$this->view->send = array();
			for($i = 1; $i < $this->view->invate_limit; $i++) {
				
				$this->view->send[$i] = array(
					'success' => false,
					'error' => false
				);
				
				$validate = new Helper_Validate(); 
				if($request->getPost('email-' . $i) != $this->translate('Email Adress ' . $i)) {
					$validate->_set_rules($request->getPost('email-' . $i), $this->translate('Email Adress ' . $i), 'not_empty;min_length[5];max_length[100];email');
				
					if($validate->_valid_form()) {
						
						$shared_content = Model_Users::sharedContentInvate($request->getPost('email-' . $i));
						if($shared_content == 1) {
							$this->view->send[$i]['error'] = $this->translate('With this email address is already registered users!');
						} else if($shared_content == 2) {
							$this->view->send[$i]['error'] = $this->translate('To this email has been sent an invitation!');
						} else {
							$inser_key = Model_Users::sharedContent($request->getPost('email-' . $i));
							if($inser_key == -1) {
								$this->view->send[$i]['error'] = $this->translate('There was an error. Please try again later!');
							} else {
								
				    			$this->view->shared_content = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=register&user_id=' . JO_Session::get('user[user_id]') . '&key=' . $inser_key);
								$this->view->header_title = JO_Registry::get('site_name');
								$this->view->self_firstname = JO_Session::get('user[firstname]');
				    			$this->view->Recipient_message = $request->getPost('note') != $this->translate('Add a personal note') ? $request->getPost('note') : '';
				    			
				    	        $result = Model_Email::send(
				    	        			$request->getPost('email-' . $i), 
				    	        			JO_Registry::get('noreply_mail'), 
				    	        			sprintf($this->translate('New invate for %s from %s'), JO_Registry::get('site_name'), JO_Session::get('user[firstname]') . ' ' . JO_Session::get('user[lastname]')), 
				    	        			$this->view->render('send_invate', 'mail'));
				    	        
				    	        if($result) {
				    	        	$this->view->send[$i]['success'] = $this->translate('The invitation was sent successfully!');
				    	        } else {
				    	        	$this->view->send[$i]['error'] = $this->translate('There was an error. Please try again later!');
				    	        }
				    	        
							}
						}
						
					} else {
						$this->view->send[$i]['error'] = strip_tags($validate->_get_error_messages());
					}
				
				}
			}
			
			if($request->isXmlHttpRequest()) {
				echo $this->renderScript('json');
				exit;
			} else {
				JO_Session::set('result_from_invate', $this->view->send);
				$this->redirect( WM_Router::create( $request->getBaseUrl() . '?controller=invites' ) );
			}
		}
		
		if( JO_Session::get('result_from_invate') ) {
			$this->view->result_from_invate = JO_Session::get('result_from_invate');
			JO_Session::clear('result_from_invate');
		}
		
		$this->view->invites = WM_Router::create( $request->getBaseUrl() . '?controller=invites' );
		$this->view->invites_fb = WM_Router::create( $request->getBaseUrl() . '?controller=invites&action=facebook' );
		
		$this->view->children = array(
        	'header_part' 	=> 'layout/header_part',
        	'footer_part' 	=> 'layout/footer_part'
        );
	}
	
	private function getFriends() {
		static $results_array = null;
		if($results_array !== null) return $results_array;
		
//		$session = $this->facebook->getSession();
//		echo '<pre>';var_dump($session); exit;
//		echo '<pre>';
//		var_dump($session , WM_Date::format($session['expires'],'dd.mm.yy H:i:s'),$session['expires'] < time(), !($me = $this->facebook->api('/me') )); exit;
//		var_dump( date('d.m.Y H:i:s'), WM_Date::format($session['expires'],'dd.mm.yy H:i:s') ); exit;
//		if(isset($_REQUEST["code"])) {
//			
////			exit;
//		}
		
//		if($session && $session['expires'] < time()) {
//			$next = WM_Router::create( $this->getRequest()->getBaseUrl() . '?controller=invites&action=facebook' );
//			$this->facebook->setNextUrl( WM_Router::create( $this->getRequest()->getBaseUrl() . '?controller=facebook&action=login&next=' . urlencode($next) ) );
//			$url = $this->facebook->getLoginUrl(array('req_perms' => 'user_status,user_photos,offline_access,read_friendlists'));
//			$this->redirect($url);
//		}
		
//		$this->facebook->setSession($session);
		
		$session = $this->facebook->getUser();
		
//		$url = 'https://www.facebook.com/dialog/oauth?access_token='.$session['access_token'].'&client_id='.JO_Registry::get('oauth_fb_key') . '&redirect_uri=' . urlencode(WM_Router::create($this->getRequest()->getBaseUrl() . '?redirect=settings')) . '&scope=user_status,publish_stream,user_photos,offline_access,read_friendlists&state=' . md5(uniqid(rand(), TRUE));
		
		if( !($me = $this->facebook->api('/me') ) ) {
			$url = $this->facebook->getLoginUrl(array(
					'redirect_uri' => WM_Router::create( $this->getRequest()->getBaseUrl() . '?controller=invites&action=facebook' ),
					'req_perms' => 'email,user_birthday,status_update,user_videos,user_status,user_photos,offline_access,read_friendlists'
			));
			$this->redirect($url);
		}
		
		
		$fbData = null;
		if($session) {
			$fbData = $this->facebook->api('/me/friends?limit=300');
		}
		
		$results_array = array();
		if(isset($fbData['data']) && $fbData['data']) {
			$results_array = $fbData['data'];
		}
		
		$has_others = true;
		$pages = 1;
		while( $has_others ) {
			if($pages > 10) {$has_others=false; break; }
			if(isset($fbData['paging']['next'])) {
				$results = @file_get_contents($fbData['paging']['next'] . '&access_token=' . $session['access_token']);
				if($results) { 
					$fbData = json_decode($results, true);
					if(isset($fbData['data']) && $fbData['data']) {
						$results_array = array_merge($results_array, $fbData['data']);
					} else {
						$fbData = null;
						$has_others = false;
					}
				} else {
					$fbData = null;
					$has_others = false;
				}
			} else {
				$fbData = null;
				$has_others = false;
			}
		}
		
		return $results_array;
	}
	/* END GET FRIENDS */
	
	public function facebook_connect2Action() {
	
		$request = $this->getRequest();

		if($request->getQuery('state') && $request->getQuery('state') == JO_Session::get('state')) {
			JO_Session::clear('state');
			echo '<script>window.close();</script>';
			exit;
		}
		
		if($request->getQuery('enable_timeline') == 1) {
		
			$state = md5(uniqid(rand(), TRUE));
			
			$url = 'https://www.facebook.com/dialog/oauth?client_id='.JO_Registry::get('oauth_fb_key') . '&redirect_uri=' . urlencode(WM_Router::create($this->getRequest()->getBaseUrl() . '?redirect=invites')) . '&scope='.$this->getRequest()->getQuery('scope').'&state=' . $state;
			
			JO_Session::set('state', $state);
			
			$this->redirect($url);
		
		} else {
			echo '<script>window.close();</script>';
			exit;
		}
		
	}
	
	public function facebookAction(){
		
		
		$request = $this->getRequest();
		
		$this->view->friends = array();
		
		$this->view->getfriends = WM_Router::create( $this->getRequest()->getBaseUrl() . '?controller=invites&action=getFriends' );
		
//		$this->view->invate_href = WM_Router::create( $this->getRequest()->getBaseUrl() . '?controller=users&action=register&user_id=' . JO_Session::get('user[user_id]') . '&code=' );
		$this->view->invate_href = WM_Router::create( $this->getRequest()->getBaseUrl() . '?controller=invited&code=' );
		$this->view->add_to_invate = WM_Router::create( $this->getRequest()->getBaseUrl() . '?controller=users&action=addInvate' );
		
		$this->view->invites = WM_Router::create( $request->getBaseUrl() . '?controller=invites' );
		$this->view->invites_fb = WM_Router::create( $request->getBaseUrl() . '?controller=invites&action=facebook' );
			
		
		$this->view->baseUrl = $request->getBaseUrl();
		/*$this->view->site_logo = $request->getBaseUrl() . 'data/images/logo.png';
		if(JO_Registry::get('site_logo') && file_exists(BASE_PATH .'/uploads'.JO_Registry::get('site_logo'))) {
		    $this->view->site_logo = $request->getBaseUrl() . 'uploads' . JO_Registry::get('site_logo'); 
		}*/
		
		$avatar = Helper_Uploadimages::avatar(JO_Session::get('user'), '_B');
		$this->view->user_avatar = $avatar['image'];
		
		$this->view->site_name = JO_Registry::get('site_name');
		$this->view->meta_description = JO_Registry::get('meta_description');
		
		$this->view->oauth_fb_key = JO_Registry::get('oauth_fb_key');
		$this->view->fb_session = true;//$this->facebook->getSession();
		
		$this->view->facebook_connect2 = WM_Router::create( $request->getBaseUrl() . '?controller=invites&action=facebook_connect2' );
		
//		var_dump($this->view->fb_session); exit;
		/**/
		
		$facebook_friends = $this->getFriends(); 
		
		$follows = Model_Users::getFacebookFriends();
		
		$this->view->friends = array();
		$this->view->friends_not_follow = array();
		if($facebook_friends) {
			$friends = $this->formatUsers($facebook_friends);
			if($friends) {
				$model_images = new Helper_Images();
				foreach($friends AS $friend) {
					if( array_key_exists($friend['id'], $follows) ) {
						$user_data = Model_Users::getUser($follows[$friend['id']]);
						if($user_data) {
							$avatar = Helper_Uploadimages::avatar($user_data, '_A');
							$user_data['avatar'] = $avatar['image'];
							$user_data['profile'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $user_data['user_id'] );
							$user_data['follow_user'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=follow&user_id=' . $user_data['user_id'] );
							$this->view->friends[] = $user_data;
						}
					} else if( ($user_data = Model_Users::getFacebookFriendsNotFollow($friend['id'])) !== false ) {
						if($user_data) {
							$avatar = Helper_Uploadimages::avatar($user_data, '_A');
							$user_data['avatar'] = $avatar['image'];
							$user_data['profile'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $user_data['user_id'] );
							$user_data['follow_user'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=follow&user_id=' . $user_data['user_id'] );
							$this->view->friends_not_follow[] = $user_data;
						}
					}
				}
			}
		}
		
		$this->view->children = array(
        	'header_part' 	=> 'layout/header_part',
        	'footer_part' 	=> 'layout/footer_part'
        );
		
	}
	
	public function getFriendsAction() {
		
		$facebook_friends = $this->getFriends();
		
		$follows = Model_Users::getFacebookFriends();
		$send_invates = Model_Users::checkIsInvateFacebookFriend();
		
		$this->view->friends = array();
		if($facebook_friends) {
			$friends = $this->formatUsers($facebook_friends);
			if($friends) {
				foreach($friends AS $friend) {
					if( !array_key_exists($friend['id'], $follows) && !array_key_exists($friend['id'], $send_invates) ) {
						$this->view->friends[] = $friend;
					}
				}
			}
		}
		
		echo $this->renderScript('json');
	}
	
	private function formatUsers($data) {
		$friends = array();
		foreach($data AS $fr) {
			$friends[] = array(
				'id' => $fr['id'],
				'key' => md5($fr['id']),
				'name' => $fr['name'],
				'avatar' => 'http://graph.facebook.com/'.$fr['id'].'/picture'
			);
		}
		
		return $friends;
		
	}
	
}

?>