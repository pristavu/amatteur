<?php

class TwitterController extends JO_Action {
	
	public function indexAction() {
		
		$request = $this->getRequest();
		
		if( JO_Session::get('user[user_id]') ) {
			$this->redirect( WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . JO_Session::get('user[user_id]') ) );
		}
	
		
		$twitteroauth = new JO_Api_Twitter_OAuth(JO_Registry::get('oauth_tw_key'), JO_Registry::get('oauth_tw_secret'));
		
		$next = '';
		if($request->issetQuery('next')) {
			$next = '&next=' . urlencode(html_entity_decode($request->getQuery('next')));
		}
		
		// Requesting authentication tokens, the parameter is the URL we will be redirected to
		$request_token = $twitteroauth->getRequestToken( WM_Router::create( $request->getBaseUrl() . '?controller=twitter&action=login' . $next ) );
//		$request_token = $twitteroauth->getRequestToken( 'oob' );
		$request_token_url = $twitteroauth->getAuthorizeURL($request_token['oauth_token']);
//		$request_token = $twitteroauth->getRequestToken( $request_token );
		if($twitteroauth->http_code == 200) {
			if(isset($request_token['oauth_token']) && $request_token['oauth_token_secret']) {
				JO_Session::set('twitter', $request_token);
				$this->redirect( $request_token_url );
			}
		}
		
//		$user_info = $twitteroauth->get('account/verify_credentials');
		
//		echo '<pre>';
//		var_dump($request_token);
//		
//		exit;

		$this->setViewChange('no_account');
					
		$page_login_trouble = Model_Pages::getPage( JO_Registry::get('page_login_trouble') );
		if($page_login_trouble) {
			$this->view->page_login_trouble = array(
				'title' => $page_login_trouble['title'],
				'href' => WM_Router::create( $request->getBaseUrl() . '?controller=pages&action=read&page_id=' . $page_login_trouble['page_id'] )
			);
		}

		$this->view->children = array(
        	'header_part' 	=> 'layout/header_part',
        	'footer_part' 	=> 'layout/footer_part'
        );
		
	}
	
	public function loginAction() {
		
		$request = $this->getRequest();
		
		if(JO_Session::get('user[user_id]')) {
			/* @var $twitteroauth JO_Api_Twitter_OAuth */
			$twitteroauth = new JO_Api_Twitter_OAuth(JO_Registry::get('oauth_tw_key'), JO_Registry::get('oauth_tw_secret'), JO_Session::get('twitter[oauth_token]'), JO_Session::get('twitter[oauth_token_secret]'));
			$access_token = $twitteroauth->getAccessToken($request->getQuery('oauth_verifier'));
			$user_info = $twitteroauth->get('account/verify_credentials');
			if($user_info && $user_info->id) {
				Model_Users::edit(JO_Session::get('user[user_id]'), array(
					'twitter_connect' => 1,
					'twitter_id' => $user_info->id,
					'twitter_username' => $user_info->screen_name
				));
				$this->redirect( WM_Router::create( $request->getBaseUrl() . '?controller=settings' ) );
			}
		}
		
		
		$twitteroauth = new JO_Api_Twitter_OAuth(JO_Registry::get('oauth_tw_key'), JO_Registry::get('oauth_tw_secret'), JO_Session::get('twitter[oauth_token]'), JO_Session::get('twitter[oauth_token_secret]'));
//		$data = $twitteroauth->getAccessToken( );
//		echo '<pre>';
//		var_dump(JO_Session::get('twitter[oauth_token]'), JO_Session::get('twitter[oauth_token_secret]'),$twitteroauth->getAccessToken()); exit;
		
		if(!JO_Session::get('user_info_twitteroauth')) {
			$access_token = $twitteroauth->getAccessToken($request->getQuery('oauth_verifier'));
			$user_info = $twitteroauth->get('account/verify_credentials');
			JO_Session::set('user_info_twitteroauth', $user_info);
			JO_Session::set('access_token_twitteroauth', $access_token);
		} else {
			$user_info = JO_Session::get('user_info_twitteroauth');
		}

		if($request->issetQuery('next')) {
			JO_Session::set('next', html_entity_decode($request->getQuery('next')));
		}
		
//		$access_token = $twitteroauth->getAccessToken($request->getQuery('oauth_verifier'));
//		$user_info = $twitteroauth->get('account/verify_credentials');
		
		if(isset($user_info->id) && $user_info->id) {
			
			if(!self::loginInit($user_info->id)) {
				
				$this->setViewChange('no_account');
					
				$page_login_trouble = Model_Pages::getPage( JO_Registry::get('page_login_trouble') );
				if($page_login_trouble) {
					$this->view->page_login_trouble = array(
						'title' => $page_login_trouble['title'],
						'href' => WM_Router::create( $request->getBaseUrl() . '?controller=pages&action=read&page_id=' . $page_login_trouble['page_id'] )
					);
				}
		
				$this->view->children = array(
		        	'header_part' 	=> 'layout/header_part',
		        	'footer_part' 	=> 'layout/footer_part'
		        );
				
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
		
			$this->view->children = array(
	        	'header_part' 	=> 'layout/header_part',
	        	'footer_part' 	=> 'layout/footer_part'
	        );
		}
		
		
		
	}
	
	private function loginInit($id, $session = null) {
		$user_data = WM_Users::checkLoginFacebookTwitter($id, 'twitter', $session);
		if($user_data) { 
			JO_Session::set(array('user' => $user_data));
			JO_Session::clear('user_info_twitteroauth');
			JO_Session::clear('access_token_twitteroauth');
			if(JO_Session::issetKey('next') && JO_Session::get('next')) {
				$this->redirect( ( urldecode(JO_Session::get('next')) ) );
			} else {
				$this->redirect( WM_Router::create( $this->getRequest()->getBaseUrl() ) );
			}
		}
		return $user_data;
	}
	
}

?>