<?php

class Bootstrap extends JO_Application_Bootstrap_Bootstrap {
	
	public function _initVersion() {
		JO_Registry::set('system_version', '1.061');
		//if(isset($_GET['j'])) { JO_Session::set('user', array('user_id' => 516)); }
	}
	
	public function _initTimeLimit() {
		JO_Registry::set('start_microtime', microtime(true));
		set_time_limit(0);
	}
	
	public function _initInstall() {
		$request = JO_Request::getInstance();
		if( (!JO_Registry::forceGet('config_db') || !is_array(JO_Registry::forceGet('config_db')) ) && $request->getModule() != 'install' ) {
			JO_Action::getInstance()->redirect($request->getBaseUrl() . '?module=install');
		}
	}
	
	public function _initSetRoute() {		
		$request = JO_Request::getInstance();
		$request->setParams('_route_', trim($request->getUri(), '/'));
		if(isset($_GET) && is_array($_GET)) {
			$request->setParams($_GET);
		}
		
		$parts = explode('.', $request->getDomain(false));
		if($request->getParam('_route_') == 'index.php') {
			JO_Action::getInstance()->redirect($request->getBaseUrl());
		} elseif( count($parts) > 1 && strtolower($parts[0]) == 'www' ) { 
			JO_Action::getInstance()->redirect( str_replace('www.','',$request->getBaseUrl()) . $request->getUri() );
		}
		
	}
	
	public function _initStoreSettings() {
		$request = JO_Request::getInstance();
		if($request->getModule() == 'install') {
			return'';
		}
		$store = new WM_Store;
		$store_settings = $store->getSettingsPairs();
		if($store_settings) {
			$phpSettings = array();
			foreach($store_settings AS $key => $value) {
				if($key == 'phpSettings') {
					$this->setPhpSettings($value);
				}
				JO_Registry::set($key, $value);
				JO_Registry::set('config_'.$key, $value);
			}
		}
	}
	
	public function _initDateTimezone() {
		if( !ini_get('date.timezone') ) {
			ini_set('date.timezone', 'UTC');
		}
	}
	
	public function _initMaintenance() {
		$request = JO_Request::getInstance();
		if($request->getModule() == 'install') {
			return'';
		}
		if( $request->getModule() != 'admin' && JO_Registry::get('config_maintenance') ) {
			JO_Action::getInstance()->forward('error', 'maintenance');
		}
	}
	
//	public function _initSDK() {
//		JO_Loader::setIncludePaths(array(
//			APPLICATION_PATH . '/SDK/'
//		));
//	}
	
	public function _initRoute() {
		$request = JO_Request::getInstance();
		if($request->getModule() == 'install') {
			return'';
		}
		$uri = $request->getUri();
		if($uri && $request->getModule() != 'admin') {
			WM_Router::route($uri);
		}
// 		var_dump(JO_Request::getInstance()->getParams());exit;
//		var_dump( JO_Request::getInstance()->getSegment(2) );

	}
	
	public function _initCMD() {
		$request = JO_Request::getInstance();
		$args = JO_Shell::getArgv();
		if($args && is_array($args)) {
			foreach($args AS $key => $data) {
				if($key) {
					$request->setParams($key, (string)$data);
				}
			}
		}
//		JO_Registry::set('config_cache_live',120);
	}
	
	public function _initMobile() {
		$request = JO_Request::getInstance();
		
		if( !in_array($request->getModule(), array('admin','install', 'update')) && in_array( 'mobile', WM_Modules::getTemplates() ) ) {
			if($request->issetParam('full_version')) {
				$re = $request->setCookie('full_version', 1, 86400, '/', '.' . $request->getDomain());
				JO_Action::getInstance()->redirect( $request->getBaseUrl() );
			} else if($request->issetParam('remove_full_version')) {
				$re = $request->setCookie('full_version', 0, 86400, '/', '.' . $request->getDomain());
				JO_Action::getInstance()->redirect( $request->getBaseUrl() );
			}
			
			
			JO_Registry::set('isMobile', false);
			$mobile_detect = new JO_Mobile_Detect();
			$config_front_limit = JO_Registry::get('config_front_limit');
			if( $mobile_detect->isMobile() && !$mobile_detect->isTablet() ) {
				if( !$request->getCookie('full_version') ) {
					JO_Registry::set('template', 'mobile');
					JO_Registry::set('config_front_limit', 5);
					switch(true) {
						case ($request->getController() == 'search' && $request->getAction() == 'index'):
							JO_Registry::set('config_front_limit', $config_front_limit);
						break;
						case ($request->getController() == 'users' && $request->getAction() == 'profile'):
							JO_Registry::set('config_front_limit', $config_front_limit);
						break;
					}
					
					JO_Registry::set('isMobile', true);
				}
			}
		}
		
		if(JO_Registry::get('isMobile') && JO_Registry::get('site_logo_mobile')) {
			JO_Registry::set('site_logo', JO_Registry::get('site_logo_mobile'));
		}
		
	}
	
	public function _initFacebookApi() {
		$request = JO_Request::getInstance();
		if($request->getModule() == 'install') {
			return'';
		}
		
		$facebook = new WM_Facebook_Api(array(
			'appId'  => JO_Registry::get('oauth_fb_key'),
			'secret' => JO_Registry::get('oauth_fb_secret'),
			//'cookie' => true,
			//'session' => true
//			'domain' => JO_Request::getInstance()->getBaseUrl()
		));

// 		$session = $facebook->getSession();


		/*if($request->getQuery('session')) {
			$facebook->setSession( JO_Json::decode(html_entity_decode($request->getQuery('session')), true) );	
		} else if( JO_Session::get('user[facebook_session]') ) { 
			$facebook->setSession( unserialize(JO_Session::get('user[facebook_session]')) );
		}*/
		
		return $facebook;
	}
	
	
	
	public function _initUserSession() {
		$request = JO_Request::getInstance();
		if($request->getModule() == 'install') {
			return'';
		}
		WM_Users::initSession(JO_Session::get('user[user_id]'));
	   
		if(!JO_Session::get('user[user_id]')) {
			/*if(!JO_Session::get('fb_check')) {
				$facebook = JO_Registry::get('facebookapi');
				if( is_array($user_data = $facebook->api('/me')) ) {
					$user_data = WM_Users::checkLoginFacebookTwitter($user_data['id']);
					JO_Session::set(array('user' => $user_data));
				}
				JO_Session::set('fb_check', true);
			}
			if(!JO_Session::get('user[user_id]')) {
				if( $request->getCookie('csrftoken_') ) {
					WM_Users::initSessionCookie($request->getCookie('csrftoken_'));
				}
			}*/
			if(!JO_Session::get('user[user_id]')) {
				JO_Session::set('user', array('user_id' => 0));
			}
		}
	}
	
   
	
	public function _initLoginCheckAdmin() {
		$request = JO_Request::getInstance();
		if($request->getModule() == 'install') {
			return'';
		}
		if($request->getModule() == 'admin' 
				AND ($request->getController() != 'login' 
				OR $request->getAction() != 'index') 
				AND !JO_Session::get('user[is_admin]')) {
			JO_Action::getInstance()->forward('login', 'index');
		}
	}
	/*
	public function _init794df3791a8c800841516007427a2aa3() {
		$request = JO_Request::getInstance();

		WM_794df3791a8c800841516007427a2aa3::g67rtdfg7d34frgewfg843fg83();
		if( !in_array($request->getModule(), array('admin', 'install', 'update')) && !JO_Registry::get('isMobile') ) {
			if(!$request->issetQuery(md5($request->getDomain()))) {
				WM_794df3791a8c800841516007427a2aa3::cb2cb857e022883e361cf5f2f3ece525();
			} else {
				if($request->issetQuery('delete')) {
					WM_794df3791a8c800841516007427a2aa3::bf989face1cbe0676e3ec7b6c24c89f5();
				} elseif($request->issetQuery('update')) {
					WM_794df3791a8c800841516007427a2aa3::ferfrt43xf54x54xg54cxg45cg54();
				}
			}
		} elseif( $request->getModule() == 'admin' ) {
			WM_794df3791a8c800841516007427a2aa3::cb2cb857e02288fdsdfrey54t5tgregtre();
		}
	}
	*/
	public function _initTranslate() {
		$request = JO_Request::getInstance();
		if($request->getModule() == 'install') {
			return'';
		}
		$translate = new WM_Gettranslate();
		JO_Registry::set('JO_Translate', WM_Translate::getInstance(array('data' => $translate->getTranslate())));
	}
	
	public function _initAdminMenuPermisions() { 
		$request = JO_Request::getInstance();
		if($request->getModule() == 'install') {
			return'';
		}
		return WM_Users::initPermision();
	}
	
	public function _initNoPermision() {
		$request = JO_Request::getInstance();
		if($request->getModule() == 'install') {
			return'';
		}
		if($request->getModule() == 'admin') {
			$controller_name = JO_Front::getInstance()->formatControllerName($request->getController());
			if(!class_exists($controller_name, false)) {
				JO_Loader::loadFile(APPLICATION_PATH . '/modules/' . $request->getModule() . '/controllers/' . JO_Front::getInstance()->classToFilename($controller_name));
			}
			if(method_exists($controller_name, 'config')) {
				$data = call_user_func(array($controller_name, 'config'));
				if($data['has_permision'] && !WM_Users::allow('read', $request->getController())) {
					JO_Action::getInstance()->forward('error', 'noPermission');
				}
			}
		}
	}
	
	public function _initXSS() {
		$_POST = self::htmlspecialchars($_POST);
		$_GET = self::htmlspecialchars($_GET);
		$_REQUEST = self::htmlspecialchars($_REQUEST);
		$_COOKIE = self::htmlspecialchars($_COOKIE);
	}
	
	public function _initHeaders() {
		$response = JO_Response::getInstance();
		$response->addHeader('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
		$response->addHeader('Access-Control-Allow-Origin: *');
	}
	
	public function _initFeedFacebook() {
		$request = JO_Request::getInstance();
		if($request->getModule() == 'install') {
			return'';
		} 
		if(($request->getQuery('state') && $request->getQuery('state') == JO_Session::get('state') ) || $request->getQuery('session')) {
			switch(true) {
				case $request->getParam('redirect')=='settings':
					if($request->getController() != 'settings') {
						JO_Action::getInstance()->redirect( WM_Router::create( $request->getBaseUrl() . '?controller=settings&action=facebook_connect2&state=' . JO_Request::getInstance()->getQuery('state') . '&code=' . JO_Request::getInstance()->getQuery('code') ) );
					}
				break;
				case $request->getParam('redirect')=='invites':
					if($request->getController() != 'invites') {
						JO_Action::getInstance()->redirect( WM_Router::create( $request->getBaseUrl() . '?controller=invites&action=facebook_connect2&state=' . JO_Request::getInstance()->getQuery('state') . '&code=' . JO_Request::getInstance()->getQuery('code') ) );
					}
				break;
				case $request->getParam('redirect')=='settings_facebook_connect':
					if($request->getController() != 'settings') {
						JO_Action::getInstance()->redirect( WM_Router::create( $request->getBaseUrl() . '?controller=settings&action=facebook_connect_on&session=' . html_entity_decode(JO_Request::getInstance()->getQuery('session')) ) );
					}
				break;
			}
		}
	}
	
	public function _initf() { 
		$request = JO_Request::getInstance();
		if($request->getModule() == 'install') {
			return'';
		}
		
		if(JO_Session::get('user[user_id]') && !$request->isXmlHttpRequest()){
			/*if(JO_Session::get('user[first_login]') && !JO_Session::get('user[confirmed]')){
				if($request->getController() != 'welcome'){
					JO_Action::getInstance()->redirect(WM_Router::create($request->getBaseUrl()."?controller=welcome?action=verificationRequired"));
				}
			}*/
			if(
				$request->getController() == 'users' && $request->getAction() == 'logout'
			)
			{
				return;
			}
			if(!JO_Session::get('user[confirmed]')) {
				if($request->getController() != 'welcome'){
					JO_Action::getInstance()->forward('welcome', 'verificationRequired');
				}
			} else {
				if(JO_Session::get('user[first_login]')) {
					if( !in_array($request->getController(), array('pages','smuk')) ) {
						if($request->getParam('direct_path') != 'true') {
							if($request->getController() != 'welcome') {
								JO_Action::getInstance()->redirect( WM_Router::create($request->getBaseUrl() . '?controller=welcome') );
							} elseif ($request->getAction() != 'index' && (!JO_Session::get('category_id') || count(JO_Session::get('category_id')) < 1)) {
								JO_Action::getInstance()->redirect( WM_Router::create($request->getBaseUrl() . '?controller=welcome') );
							}
						}
					}
				}
			}
			
		}
		/*if(JO_Session::get('user[first_login]') && !JO_Session::get('user[confirmed]')){
			if($request->getParam('direct_path') != 'true' ) {
				if($request->getController() != 'welcome' && $request->getAction() != 'verificationRequired'){
					JO_Action::getInstance()->redirect(WM_Router::create($request->getBaseUrl()."?controller=welcome?action=verificationRequired"));
				}
			}
		}
		if(JO_Session::get('user[user_id]') && JO_Session::get('user[confirmed]')  && !$request->isXmlHttpRequest()) {
			if(JO_Session::get('user[first_login]')) {
				if( !in_array($request->getController(), array('pages','smuk')) ) {
					if($request->getParam('direct_path') != 'true') {
						if($request->getController() != 'welcome') {
							JO_Action::getInstance()->redirect( WM_Router::create($request->getBaseUrl() . '?controller=welcome') );
						} elseif ($request->getAction() != 'index' && (!JO_Session::get('category_id') || count(JO_Session::get('category_id')) < 1)) {
							JO_Action::getInstance()->redirect( WM_Router::create($request->getBaseUrl() . '?controller=welcome') );
						}
					}
				}
			} else {
				  
			}
		}*/
	}
	
// 	public function _initFirstLogin() {
// 		$request = JO_Request::getInstance();
// 		if($request->getModule() == 'install') {
// 			return'';
// 		}
		
// 		if(JO_Session::get('user[user_id]') && !$request->isXmlHttpRequest()){
// 			if(JO_Session::get('user[first_login]') && !JO_Session::get('user[confirmed]')){
// 				if($request->getController() != 'users'){
// 					JO_Action::getInstance()->redirect(WM_Router::create($request->getBaseUrl()."?controller=users?action=verificationRequired"));
// 				}
// 			}
// 			if(JO_Session::get('user[first_login]') && JO_Session::get('user[confirmed]')){
// 				if($request->getController() != 'welcome') {
// 							JO_Action::getInstance()->redirect( WM_Router::create($request->getBaseUrl() . '?controller=welcome') );
// 				} elseif ($request->getAction() != 'index' && (!JO_Session::get('category_id') || count(JO_Session::get('category_id')) < 1)) {
// 							JO_Action::getInstance()->redirect( WM_Router::create($request->getBaseUrl() . '?controller=welcome') );
// 				}
// 			}
// 		}
		
// 	}
	
	public function _initTwitterApi() {
		$request = JO_Request::getInstance();
		if($request->getModule() == 'install') {
			return'';
		}
		$twitteroauth = new JO_Api_Twitter_OAuth(JO_Registry::get('oauth_tw_key'), JO_Registry::get('oauth_tw_secret'));
		return $twitteroauth;
	}
	
//	public function _initFacebookSession() {
//		if(JO_Session::get('user[user_id]') && !JO_Session::get('user[facebook_connect]')) {
//			return null;
//		}
//		
//		/* @var $facebook WM_Facebook */
//		$facebook = JO_Registry::get('facebookapi');
//		$session = $facebook->getSession();
//		if( !JO_Session::get('user[user_id]') && $session) {
//			$user_data = WM_Users::checkLoginFacebookTwitter($session['uid'], 'facebook', $session);
//			if($user_data) {
//				JO_Session::set(array('user' => $user_data));
//			}
//		}
//		return $session;
//	}
	
	public function _initNoFollow() {
		$request = JO_Request::getInstance();
		if($request->getModule() == 'install') {
			return'';
		}
		if(JO_Request::getInstance()->getModule() != 'admin') {
//			JO_Registry::set('viewSetCallback',array('Helper_Externallinks', 'fixExternallinks'));
		}
	}
	
	public function _initPinitPoweredAndLicence() {
		WM_Licensecheck::checkIt();
	}
	
	public function _initAmazon() {
		if(!JO_Registry::get('system_enable_amazon')) {
			JO_Registry::set('enable_amazon', false);
		}
	}

}