<?php

class SettingsController extends JO_Action {
	
	/**
	 * @var WM_Facebook
	 */
	protected $facebook;
	
	public function init() {
		
		$request = $this->getRequest();
		
		if(!JO_Session::get('user[user_id]')) {
			$this->redirect( WM_Router::create( $request->getBaseUrl() . '?controller=users&action=login' ) );
		}
		
		$this->facebook = JO_Registry::get('facebookapi');
	}


	public function indexAction() {
		
		$request = $this->getRequest();
		
                //////////// Categories ////////////
                $this->view->categories =  array();
                $categories = Model_Categories::getCategories(array(
                        'filter_status' => 1
                ));

                foreach ($categories as $category){
                        $category['subcategories'] = Model_Categories::getSubcategories($category['category_id']);
                        $this->view->categories[] = $category;
                }

                
                //////////// User Type ////////////
                $this->view->user_types =  array();
                $user_types = Model_Users::getUserType(array(
                        'filter_status' => 1
                ));

                foreach ($user_types as $user_type){
                        $user_type['subuser_types'] = Model_Users::getSubUserType($user_type['user_type_id']);
                        $this->view->user_types[] = $user_type;
                }                
                
                
                /////////// activate //////////
                $_SESSION["activate_url"] = WM_Router::create( $request->getBaseUrl() . '?controller=settings'); 
                $this->view->popup_activate = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=activate'); 

                $_SESSION["deportes_url"] = WM_Router::create( $request->getBaseUrl() . '?controller=settings');                 
                $this->view->deportes = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=deportes');                 
                
                $this->view->user_sports =  array();
                $users_sports = Model_Users::getUserSports(JO_Session::get('user[user_id]'));
                $i=0;
                foreach ($users_sports as $user_sports){
                    if ($user_sports['sport_category'] != 1)
                    {
                        if (!Model_Boards::isCategoryParent($user_sports['sport_category']))
                        {
                            $this->view->user_sports[] = Model_Boards::getCategoryTitle($user_sports['sport_category']);
                            $i++;
                        }
                    }
                }                
                $this->view->sportcounter = $i;
                
                
		$user_data = Model_Users::getUser( JO_Session::get('user[user_id]') );
		
		$upload = new JO_Upload_SessionStore();
		$upload->setName('upload_avatar');
		$info = $upload->getFileInfo();
		
		if(JO_Session::get('successfu_edite')) {
                    $this->view->successfu_edite = true;
                    JO_Session::clear('successfu_edite'); 
                }
		
		if( $request->isPost() ) {

                        $validate = new Helper_Validate();
			$validate->_set_rules($request->getPost('username'), $this->translate('Username'), 'not_empty;min_length[3];max_length[100];username');
			$validate->_set_rules($request->getPost('firstname'), $this->translate('First name'), 'not_empty;min_length[3];max_length[100]');
			//$validate->_set_rules($request->getPost('lastname'), $this->translate('Last name'), 'not_empty;min_length[3];max_length[100]');
			$validate->_set_rules($request->getPost('email'), $this->translate('Email'), 'not_empty;min_length[5];max_length[100];email');
                        if($request->issetPost('type_user')) {
                            if($request->getPost('type_user') != 1 && $request->getPost('type_user') != 5 && $request->getPost('type_user') != 12) {
                                $validate->_set_rules($request->getPost('location'), $this->translate('Location'), 'not_empty;min_length[3];max_length[100]');                                        
                            }
                        }
                        $validate->_set_rules($request->getPost('sports'), $this->translate('Category_id1'), 'not_empty;min_length[3];max_length[100]');
                        //is_nan() sino
                        /*
                        if($request->getPost('sport_category_1') == "" && $request->getPost('sport_category_2') == "" && $request->getPost('sport_category_3') == "") {
                            $validate->_set_rules($request->getPost('sport_category'), $this->translate('Category_id'), 'not_empty;min_length[3];max_length[100]');

                        }
                         * */
                        //$validate->_set_rules($request->getPost('sport_category_1'), $this->translate('Category_id1'), 'not_empty;min_length[3];max_length[100]');
                        //$validate->_set_rules($request->getPost('sport_category_2'), $this->translate('Category_id2'), 'not_empty;min_length[3];max_length[100]');
                        //$validate->_set_rules($request->getPost('sport_category_3'), $this->translate('Category_id3'), 'not_empty;min_length[3];max_length[100]');
                        $validate->_set_rules($request->getPost('type_user'), $this->translate('User_type_id'), 'not_empty;min_length[1];max_length[100]');

			
			$data = $request->getPost();
		
			if($validate->_valid_form()) {
                            
				if( Model_Users::isExistEmail($request->getPost('email'), JO_Session::get('user[email]')) ) {
					$validate->_set_form_errors( $this->translate('This e-mail address is already used') );
					$validate->_set_valid_form(false);
				}
				if( Model_Users::isExistUsername($request->getPost('username'), JO_Session::get('user[username]')) ) {
					$validate->_set_form_errors( $this->translate('This username is already used') );
					$validate->_set_valid_form(false);
				}
			}
			
			if($validate->_valid_form()) {
				
				$data['dont_search_index'] = (int)$request->issetPost('dont_search_index');
				$data['facebook_timeline'] = (int)$request->issetPost('facebook_timeline');
				
				if($info) {
					if(!@file_exists(BASE_PATH . '/cache/avatar/') || !is_dir(BASE_PATH . '/cache/avatar/')) {
						mkdir(BASE_PATH . '/cache/avatar/');
					}
					$filename = BASE_PATH . '/cache/avatar/' . md5(mt_rand().time()) . $upload->get_extension($info['name']);
					if( file_put_contents( $filename, $info['data'] ) ) {
						$data['avatar'] = $filename;
					}
				}
				
				$new_email_key = md5( JO_Session::get('user[email]') . mt_rand() . time() );
				if(JO_Session::get('user[email]') != $request->getPost('email')) {
					$data['new_email_key'] = $new_email_key;
				} else {
					$data['new_email_key'] = '';
				}
				
				$data['new_email'] = $data['email'];
				unset($data['email']);
				/*
                                $lat = $data['lat'];
                                $len = $data['len'];
                                
                                while(Model_Users::getUsersLatLen($lat,$len))
                                {

                                    $posLat = strpos($lat, ".");
                                    $longLat = strlen(substr((string)$lat, $posLat));
                                    $cantLat = 0;
                                    for ($i = 0; $i < ($longLat - 4); $i++)
                                    {
                                        if ($i == 0)
                                        {
                                            $cantLat .= ".0";
                                        }
                                        else
                                        {
                                            $cantLat .= "0";
                                        }
                                    }
                                    $cantLat .= "1";
                                    $lat = $lat + $cantLat;

                                    $posLen = strpos($len, ".");
                                    $longLen = strlen(substr((string)$len, $posLen));
                                    $cantLen = 0;
                                    for ($i = 0; $i < ($longLen - 4); $i++)
                                    {
                                        if ($i == 0)
                                        {
                                            $cantLen .= ".0";
                                        }
                                        else
                                        {
                                            $cantLen .= "0";
                                        }
                                    }
                                    $cantLen .= "1";
                                    $len = $len + $cantLen;
                                }
                                
                                $data['lat'] = $lat;
                                $data['len'] = $len;
                                */
				if(Model_Users::edit( JO_Session::get('user[user_id]'), $data )) {
					JO_Session::set('successfu_edite', true);
					$upload->getFileInfo(true);
					if(JO_Session::get('user[email]') != $request->getPost('email')) {
						
						$this->view->verify_email_href = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=login&user_id='.JO_Session::get('user[user_id]').'&verify=' . $new_email_key );
						$this->view->user_info = $user_data;
						Model_Email::send(
                                                    $request->getPost('email'),
                                                    JO_Registry::get('noreply_mail'),
                                                    $this->translate('Please verify your email'),
                                                    $this->view->render('verify_email', 'mail')
                                                );
						
					}
                                        if (!Model_Users::getUserTypeNotOthers($user_data['type_user']))
                                        {
                                            $data['activate'] = 0;
                                            //borrar activate
                                            if(Model_Users::createActivate( JO_Session::get('user[user_id]'), $data )) {

                                            }
                                        }
                                        
                                        if (Model_Users::deleteUsersLocation(JO_Session::get('user[user_id]')))
                                        {
                                            for($i = 0; $i <= $request->getPost('locationcounter'); $i++)
                                            {
                                                $location = 'location'.$i;
                                                $lat = 'lat'.$i;
                                                $len = 'len'.$i;
                                                if ($request->issetPost($location))
                                                {
                                                    if ($request->getPost($location) != "")
                                                    {
                                                        $lat = $request->getPost($lat);
                                                        $len = $request->getPost($len);

                                                        while(Model_Users::getLocationUsersLatLen($lat,$len))
                                                        {
                                                            $posLat = strpos($lat, ".");
                                                            $longLat = strlen(substr((string)$lat, $posLat));
                                                            $cantLat = 0;
                                                            for ($i = 0; $i < ($longLat - 4); $i++)
                                                            {
                                                                if ($i == 0)
                                                                {
                                                                    $cantLat .= ".0";
                                                                }
                                                                else
                                                                {
                                                                    $cantLat .= "0";
                                                                }
                                                            }
                                                            $cantLat .= "1";
                                                            $lat = $lat + $cantLat;

                                                            $posLen = strpos($len, ".");
                                                            $longLen = strlen(substr((string)$len, $posLen));
                                                            $cantLen = 0;
                                                            for ($i = 0; $i < ($longLen - 4); $i++)
                                                            {
                                                                if ($i == 0)
                                                                {
                                                                    $cantLen .= ".0";
                                                                }
                                                                else
                                                                {
                                                                    $cantLen .= "0";
                                                                }
                                                            }
                                                            $cantLen .= "1";
                                                            $len = $len + $cantLen;
                                                        }
                                                        if (Model_Users::createUsersLocation(JO_Session::get('user[user_id]'), $request->getPost($location), $lat, $len))
                                                        {}
                                                    }
                                                }
                                            }
                                        }

					
					$this->redirect( WM_Router::create( $request->getBaseUrl() . '?controller=settings' ) );
				} else {
					$this->view->error = $this->translate('There was a problem with the record. Please try again!');
				}
			} else {
				$this->view->error = $validate->_get_error_messages();
			}
			
			foreach($data AS $k=>$v) {
				if(isset($user_data[$k])) {
					$user_data[$k] = $v;
				}
			}
		} 
		
		if($info) {
			$user_data['avatar'] = WM_Router::create( $request->getBaseUrl() . '?controller=settings&action=temporary_avatar&s=' . microtime(true) );
			$user_data['has_avatar'] = true;
		} else {
			$avatar = Helper_Uploadimages::avatar($user_data, '_B');
			$user_data['avatar'] = $avatar['image'] . '?s=' . microtime(true);
			$user_data['has_avatar'] = @getimagesize($user_data['avatar']) ? true : false;
		}
                
                
                //////////// User location ////////////
                $this->view->user_location =  array();
                $this->view->user_lat = array();
                $this->view->user_len = array();
                $this->view->locationcounter = 0;
		if($request->issetPost('location1')) {
			$user_location = array();
                        $user_lat = array();
                        $user_len = array();
                        for($i = 1; $i <= $request->getPost('locationcounter'); $i++)
                        {
                            $location = 'location'.$i;
                            $lat = 'lat'.$i;
                            $len = 'len'.$i;
                            if ($request->issetPost($location)){
                                if ($request->getPost($location) != "")
                                {
                                     $user_location[] = $request->getPost($location);
                                     $user_lat[] = $request->getPost($lat);
                                     $user_len[] = $request->getPost($len);
                                }
                            }
                        }
                        $this->view->user_location = $user_location;
                        $this->view->user_lat = $user_lat;
                        $this->view->user_len = $user_len;                        
                        $this->view->locationcounter = $request->getPost('locationcounter');
		} 
                else
                {
                    $users_location = Model_Users::getUserLocation(JO_Session::get('user[user_id]'));
                    $i=0;
                    foreach ($users_location as $user_location){
                        $this->view->user_location[] = $user_location['location'];
                        $this->view->user_lat[] = $user_location['lat'];
                        $this->view->user_len[] = $user_location['len'];
                        $i++;
                    }                
                    $this->view->locationcounter = $i;
                }
                

                
                $this->view->location = '';
		if($request->issetPost('location')) {
			$this->view->location = $request->getPost('location');
		} else {
			$this->view->location = '';
		}
                $this->view->cat_title1 = '';
                $this->view->sport_category_1 = '';
		if($request->issetPost('sport_category_1')) {
			$this->view->sport_category_1 = $request->getPost('sport_category_1');
                        if ($request->getPost('sport_category_1') != "")
                        {
                            if ($request->getPost('sport_category_1') == 1)
                            {
                                $this->view->cat_title1 = "Todo";
                            }
                            else
                            {
                                $this->view->cat_title1 = Model_Boards::getCategoryTitle($request->getPost('sport_category_1'));
                            }
                        }
		} else if ($user_data['sport_category_1'] != "") {
			$this->view->sport_category_1 = $user_data['sport_category_1'];
                        if ($user_data['sport_category_1'] == 1)
                        {
                            $this->view->cat_title1 = "Todo";
                        }
                        else
                        {
                            $this->view->cat_title1 = Model_Boards::getCategoryTitle($user_data['sport_category_1']);
                        }
		}
                $this->view->cat_title2 = '';
                $this->view->sport_category_2 = '';
		if($request->issetPost('sport_category_2')) {
			$this->view->sport_category_2 = $request->getPost('sport_category_2');
                        if ($request->getPost('sport_category_2') != "")
                        {
                            if ($request->getPost('sport_category_2') == 1)
                            {
                                $this->view->cat_title2 = "Todo";
                            }
                            else
                            {
                                $this->view->cat_title2 = Model_Boards::getCategoryTitle($request->getPost('sport_category_2'));
                            }
                        }
		} elseif ($user_data['sport_category_2'] != "") {
			$this->view->sport_category_2 = $user_data['sport_category_2'];
                        if ($user_data['sport_category_2'] == 1)
                        {
                            $this->view->cat_title2 = "Todo";
                        }
                        else
                        {
                            $this->view->cat_title2 = Model_Boards::getCategoryTitle($user_data['sport_category_2']);
                        }
		}
                $this->view->cat_title3 = '';
                $this->view->sport_category_3 = '';
		if($request->issetPost('sport_category_3')) {
			$this->view->sport_category_3 = $request->getPost('sport_category_3');
                        if ($request->getPost('sport_category_3') != "")
                        {
                            if ($request->getPost('sport_category_3') == 1)
                            {
                                $this->view->cat_title3 = "Todo";
                            }
                            else
                            {
                                $this->view->cat_title3 = Model_Boards::getCategoryTitle($request->getPost('sport_category_3'));
                            }
                        }
		} elseif ($user_data['sport_category_3'] != "") {
			$this->view->sport_category_3 = $user_data['sport_category_3'];
                        if ($user_data['sport_category_3'] == 1)
                        {
                            $this->view->cat_title3 = "Todo";
                        }
                        else
                        {
                            $this->view->cat_title3 = Model_Boards::getCategoryTitle($user_data['sport_category_3']);
                        }
		}
                $this->view->usertype_title = '';
                $this->view->type_user = '';
                if($request->issetPost('type_user')) {
                        $this->view->type_user = $request->getPost('type_user');
                        if ($request->getPost('type_user') != "")
                        {
                            $this->view->usertype_title = Model_Users::getUserTypeTitle($request->getPost('type_user'));
                        }
		} elseif ($user_data['type_user'] != "") {
			$this->view->type_user = $user_data['type_user'];
                        $this->view->usertype_title = Model_Users::getUserTypeTitle($user_data['type_user']);
		}

                if($request->issetPost('activate')) {
                     $this->view->activate = $request->getPost('activate');
                }
                else
                {
                    $activate = Model_Users::getActivateUser( JO_Session::get('user[user_id]') );
                    if ($activate)
                    {
                        $this->view->activate = $activate["activate"];
                    }
                    else
                    {
                        $this->view->activate = "";
                    }
                }
                
		$this->view->instagram_enable = JO_Registry::get('oauth_in_key');
		$this->view->twitteroauth_enable = JO_Registry::get('oauth_tw_key');
		$this->view->facebook_enable = JO_Registry::get('oauth_fb_key');
        
                $this->view->user_data = $user_data;
		
		$this->view->form_action = WM_Router::create( $request->getBaseUrl() . '?controller=settings&action=upload_avatar' );
		$this->view->invites_fb = WM_Router::create( $request->getBaseUrl() . '?controller=invites&action=facebook' );
		$this->view->facebook_connect = WM_Router::create( $request->getBaseUrl() . '?controller=settings&action=facebook_connect' );
		$this->view->twitter_connect = WM_Router::create( $request->getBaseUrl() . '?controller=settings&action=twitter_connect' );
		$this->view->instagram_connect = WM_Router::create( $request->getBaseUrl() . '?controller=settings&action=instagram_connect' );
		$this->view->instagram_fetch = WM_Router::create( $request->getBaseUrl() . '?controller=instagram&action=media' );
		$this->view->instagram_fetch_cron = WM_Router::create( $request->getBaseUrl() . '?controller=settings&action=instagram_cron' );
		$this->view->facebook_connect_avatar = WM_Router::create( $request->getBaseUrl() . '?controller=settings&action=facebook_connect_avatar' );
		$this->view->check_username = WM_Router::create( $request->getBaseUrl() . '?controller=settings&action=check_username' );
		$this->view->delete_username = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=delete&user_id=' . $user_data['user_id'] );
		$this->view->facebook_connect2 = WM_Router::create( $request->getBaseUrl() . '?controller=settings&action=facebook_connect2' );
		
		$this->view->prefs_action = WM_Router::create( $request->getBaseUrl() . '?controller=prefs' );
		
		$this->view->new_password = WM_Router::create( $request->getBaseUrl() . '?controller=password&action=change' );
		
		$this->view->site_name = JO_Registry::get('site_name');
		$this->view->base_href = $request->getBaseUrl();
		
		$this->view->delete_account = '';
		if( JO_Registry::get('delete_account') ) {
			$page_description = Model_Pages::getPage(JO_Registry::get('delete_account'));
			if($page_description) {
				$this->view->delete_account = html_entity_decode($page_description['description'], ENT_QUOTES, 'utf-8');
			}
		}
		
                // si llama a los deportes
                if (isset($_SESSION["email"]))
                {
                    $this->view->email = $_SESSION["email"];
                    $_SESSION["email"] = null;
                }
                if (isset($_SESSION["firstname"]))
                {
                    $this->view->firstname = $_SESSION["firstname"];
                    $_SESSION["firstname"] = null;
                }
                if (isset($_SESSION["username"]))
                {
                    $this->view->username = $_SESSION["username"];
                    $_SESSION["username"] = null;
                }
                if (isset($_SESSION["password"]))
                {
                    $this->view->password = $_SESSION["password"];
                    $_SESSION["password"] = null;
                }
                if (isset($_SESSION["password2"]))
                {
                    $this->view->password2 = $_SESSION["password2"];
                    $_SESSION["password2"] = null;
                }
                if (isset($_SESSION["info"]))
                {
                    $this->view->info = $_SESSION["info"];
                    $_SESSION["info"] = null;
                }
                if (isset($_SESSION["location"]))
                {
                    $this->view->location = $_SESSION["location"];
                    $_SESSION["location"] = null;
                }
                if (isset($_SESSION["lat"]))
                {
                    $this->view->lat = $_SESSION["lat"];
                    $_SESSION["lat"] = null;
                }
                if (isset($_SESSION["len"]))
                {
                    $this->view->len = $_SESSION["len"];
                    $_SESSION["len"] = null;
                }
                if (isset($_SESSION["type_user"]))
                {
                    if ($_SESSION["type_user"] != "")
                    {
                        $this->view->type_user = $_SESSION["type_user"];
                        $this->view->usertype_title = Model_Users::getUserTypeTitle($_SESSION["type_user"]);
                    }
                    $_SESSION["type_user"] = null;
                }
                if (isset($_SESSION["location1"]))
                {
                    $user_location = array();
                    for($i = 1; $i <= $_SESSION['locationcounter']; $i++)
                    {
                        $location = 'location'.$i;
                        if (isset($_SESSION[$location])){
                            if ($_SESSION[$location] != "")
                            {
                                 $user_location[] = $_SESSION[$location];
                                 $_SESSION[$location] = null;
                            }
                        }
                    }
                    $this->view->user_location = $user_location;
                    $this->view->locationcounter = $_SESSION['locationcounter'];
                    $_SESSION['locationcounter'] = null;
                }                
                
		$this->view->children = array(
        	'header_part' 	=> 'layout/header_part',
        	'footer_part' 	=> 'layout/footer_part'
        );
	}
	
	public function check_usernameAction(){
		
		$request = $this->getRequest();
		
		$username = trim($request->getPost('raw'));
		
		if($username && $request->getPost('raw') == JO_Session::get('user[username]')) {
			$this->view->success = $this->translate('Thats *your* username');
		} else {
			$validate = new Helper_Validate();
			$validate->_set_rules($username, $this->translate('Username'), 'not_empty;min_length[3];max_length[100];username');
			if($validate->_valid_form()) {
				if( Model_Users::isExistUsername($username, JO_Session::get('user[username]')) ) {
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
	
	public function upload_avatarAction(){
		
		$request = $this->getRequest();
		
		$upload = new JO_Upload_SessionStore($request->getFile('file'));
		$upload->setName('upload_avatar');
		if( $upload->upload(true) ) {
			$info = $upload->getFileInfo();
			$this->view->success = WM_Router::create( $request->getBaseUrl() . '?controller=settings&action=temporary_avatar&hash=' . microtime(true) );//'data:'.$info['type'].';base64,'.base64_encode($info['data']);
		} else {
			$this->view->error = $upload->getError();
		}
		
		echo $this->renderScript('json');
	}
	
	public function facebook_connect_avatarAction(){
		
		$request = $this->getRequest();
		
		if(!$request->isXmlHttpRequest() || !JO_Session::get('user[user_id]')) {
			exit('[]');
		}
		
		$session = JO_Registry::get('facebookapi')->getUser();
		
		if($session) {
			$fbData = $this->facebook->api('/me');
			
			if($fbData) {
				$ph = new WM_Facebook_Photo();
				$image = $ph->getRealUrl('http://graph.facebook.com/'.$fbData['id'].'/picture?type=large');
				
				$image_info = @getimagesize($image);
				if( $image_info ) {
					$image_data = @file_get_contents($image);
					if($image_data) {
						JO_Session::set('upload_avatar', array(
							'name' => basename($image),
							'type' => $image_info['mime'],
							'data' => $image_data
						));
						$this->view->success = WM_Router::create( $request->getBaseUrl() . '?controller=settings&action=temporary_avatar&hash=' . microtime(true) );
					}
				}
			} else {
				$this->view->error = $this->translate('There is no established connection with facebook!');
			}
			
		} else {
			$this->view->error = $this->translate('There is no established connection with facebook!');
		}
		
		echo $this->renderScript('json');
	}
	
	public function temporary_avatarAction(){
		
		if(!JO_Session::get('user[user_id]')) {
			exit;
		}
		
		$upload = new JO_Upload_SessionStore();
		$upload->setName('upload_avatar');
		$info = $upload->getFileInfo();
		if($info) {
			$this->getResponse()->addHeader('Content-Type: ' . $info['type']);
			echo $info['data'];
		}
		$this->noViewRenderer(true);
	}
	
	public function facebook_connect_onAction(){

		$request = $this->getRequest();
		
		$facebook = JO_Registry::get('facebookapi'); 

		if(JO_Session::get('user[user_id]')) {
			$session = $facebook->getUser();
			
			//var_dump(WM_Date::format($session['expires'], 'dd-mm-yy H:i:s'), WM_Date::format(null, 'dd-mm-yy H:i:s')); exit;
			if($session && $user_data = $facebook->api('/me')) {
				Model_Users::edit2( JO_Session::get('user[user_id]'), array(
					'facebook_connect' => '1',
					'facebook_id' => $user_data['id'],
					'facebook_session' => serialize($session)
				) );
			} else if($request->getQuery('session')) {
				//$facebook->setSession( JO_Json::decode(html_entity_decode($request->getQuery('session')), true) );
				$session = $facebook->getUser();
				if($session && $user_data = $facebook->api('/me')) {
					Model_Users::edit2( JO_Session::get('user[user_id]'), array(
						'facebook_connect' => '1',
						'facebook_id' => $user_data['id']
					) );
				}
			}
		} 

		$this->redirect( WM_Router::create( $request->getBaseUrl() . '?controller=settings' ) );
	}
	
	public function facebook_connectAction(){
		
		$request = $this->getRequest();
		
		if(!$request->isXmlHttpRequest() || !JO_Session::get('user[user_id]')) {
			exit('[]');
		}
		
		if($request->getQuery('state') && $request->getQuery('state') == JO_Session::get('state')) {
			$state = JO_Session::get('state');
		} else {
			$state = md5(uniqid(rand(), TRUE));
			JO_Session::set('state', $state);
		}
		
		if( $request->getPost('facebook_connect') == 'on' ) {
			
			$facebook_api = JO_Registry::get('facebookapi');
			
			$user = $facebook_api->getUser();
			
			if($user) {
				try {
					$user_profile = $facebook_api->api('/me');
					if($user_profile) {
						//var_dump($user_profile); exit;
						Model_Users::edit2( JO_Session::get('user[user_id]'), array(
								'facebook_connect' => '1',
								'facebook_id' => $user_profile['id'],
								'facebook_session' => serialize($user_profile)
						));
					} else {
// 						$facebook_api = new WM_Facebook_Api($config);
						$this->view->login = $facebook_api->getLoginUrl(array(
							'redirect_uri' => WM_Router::create( $request->getBaseUrl() . '?redirect=settings_facebook_connect&state=' . $state ),
							'req_perms' => 'email,user_birthday,status_update,user_videos,user_status,user_photos,offline_access,read_friendlists',
							'state' => $state
						));
					}
				} catch (WM_Facebook_ApiException $e) {
					
				}
			} else {
				$this->view->login = $facebook_api->getLoginUrl(array(
						'redirect_uri' => WM_Router::create( $request->getBaseUrl() . '?redirect=settings_facebook_connect&state=' . $state ),
						'req_perms' => 'email,user_birthday,status_update,user_videos,user_status,user_photos,offline_access,read_friendlists',
						'state' => $state
				));
			}
			
			
		} else {
			$facebook_api = JO_Registry::get('facebookapi');
			//$facebook_api->destroySession();
			Model_Users::edit2( JO_Session::get('user[user_id]'), array(
				'facebook_connect' => '0',
				'facebook_id' => '0',
				'facebook_session' => ''
			) ); 
		}
		
		if( !$this->view->login ) {
			$ud = Model_Users::getUser(JO_Session::get('user[user_id]'), true);
			if($ud) {
				$this->view->connected = $ud['facebook_connect'] ? 'on' : 'off';
			} else {
				$this->view->connected = 'off';
			}
		}
		
		echo $this->renderScript('json');
		
	}
	
	public function twitter_connectAction() {
		
		$request = $this->getRequest();
		
		if( $request->getPost('twitter_connect') == 'on' ) {
			
			/* @var $twitteroauth JO_Api_Twitter_OAuth */
			$twitteroauth = JO_Registry::get('twitterapi');
			$request_token = $twitteroauth->getRequestToken( WM_Router::create( $request->getBaseUrl() . '?controller=twitter&action=login&next=' . urlencode(WM_Router::create( $request->getBaseUrl() . '?controller=settings' )) ) );
			$request_token_url = $twitteroauth->getAuthorizeURL($request_token['oauth_token']);
			if($twitteroauth->http_code == 200) {
				if(isset($request_token['oauth_token']) && $request_token['oauth_token_secret']) {
					JO_Session::set('twitter', $request_token);
					$this->view->login = $request_token_url;
				} else {
					$this->view->error = $this->translate('There is no established connection with twitter!');
				}
			} else {
				$this->view->error = $this->translate('There is no established connection with twitter!');
			}
			
		} else {
			Model_Users::edit2( JO_Session::get('user[user_id]'), array(
				'twitter_connect' => '0'
			) ); 
		}
		
		echo $this->renderScript('json');
		
	}
	
	public function facebook_connect2Action() {
	
		$request = $this->getRequest();

		if($request->getQuery('state') && $request->getQuery('state') == JO_Session::get('state')) {
			JO_Session::clear('state');
			echo '<script>window.close();</script>';
			Model_Users::edit2(JO_Session::get('user[user_id]'), array(
				'facebook_timeline' => 1
			));
			exit;
		}
		
		if($request->getQuery('enable_timeline') == 1) {
		
			$state = md5(uniqid(rand(), TRUE));
			
			$url = 'https://www.facebook.com/dialog/oauth?client_id='.JO_Registry::get('oauth_fb_key') . '&redirect_uri=' . urlencode(WM_Router::create($this->getRequest()->getBaseUrl() . '?redirect=settings')) . '&scope='.$this->getRequest()->getQuery('scope').'&state=' . $state;
			
			JO_Session::set('state', $state);
			
			$this->redirect($url);
		
		} else {
			echo '<script>window.close();</script>';
			Model_Users::edit2(JO_Session::get('user[user_id]'), array(
				'facebook_timeline' => 0
			));
			exit;
		}
		
	}
	
	/////////////instagram
	public function instagram_connectAction() {
		
		$request = $this->getRequest();

        if( !JO_Session::get('user[user_id]') ) {
            if(!$request->isXmlHttpRequest()) {
                $this->redirect( WM_Router::create($this->getRequest()->getBaseUrl() . '?controller=settings') );
            }
            exit('[]');
        }
        
		if($request->getQuery('state') && $request->getQuery('state') == JO_Session::get('state')) {
			$state = JO_Session::get('state');
		} else {
			$state = md5(uniqid(rand(), TRUE));
			JO_Session::set('state', $state);
		}
		
		$config = array(
				'client_id' => JO_Registry::get('oauth_in_key'),
				'client_secret' => JO_Registry::get('oauth_in_secret'),
				'grant_type' => 'authorization_code',
				'redirect_uri' => WM_Router::create($this->getRequest()->getBaseUrl() . '?controller=instagram&next=' . urlencode( WM_Router::create($request->getBaseUrl() . '?controller=settings&action=instagram_connect&state=' . $state) )),
		);
		
		$instagram = new WM_Instagram($config);

        $InstagramAccessToken = $instagram->getAccessToken(); 
        $user_data = JO_Json::decode($instagram->getUser(), true);
        
        if( isset($user_data['meta']['code']) && $user_data['meta']['code'] == 200 ) {
            JO_Session::set('InstagramAccessToken', $InstagramAccessToken);
        } elseif($InstagramAccessToken) {
            JO_Session::set('InstagramAccessToken', $InstagramAccessToken);
            $instagram->setAccessToken($InstagramAccessToken);
        } elseif(JO_Session::get('InstagramAccessToken')) {
			$instagram->setAccessToken(JO_Session::get('InstagramAccessToken'));
		} elseif(JO_Session::get('user[instagram_token]')) {
			JO_Session::set('InstagramAccessToken', JO_Session::get('user[instagram_token]'));
			$instagram->setAccessToken(JO_Session::get('user[instagram_token]'));
		}
        
        if(!isset($user_data['meta']['code']) || $user_data['meta']['code'] != 200) {
		    $user_data = JO_Json::decode($instagram->getUser(), true);
        } 
        
		
		if($request->getQuery('state') && $request->getQuery('state') == JO_Session::get('state') && isset($user_data['meta']['code']) && $user_data['meta']['code'] == 200) {
			JO_Session::clear('state');
			Model_Users::edit2( JO_Session::get('user[user_id]'), array(
					'instagram_connect' => '1',
					'instagram_profile_id' => $user_data['data']['id'],
					'instagram_token' => (string)JO_Session::get('InstagramAccessToken')
			) );
			$this->redirect( WM_Router::create($this->getRequest()->getBaseUrl() . '?controller=settings') );
			exit;
		}
		
		if( $request->getPost('instagram_connect') == 'on' ) {
			if( isset($user_data['meta']['code']) && $user_data['meta']['code'] == 200 ) {
				Model_Users::edit2( JO_Session::get('user[user_id]'), array(
						'instagram_connect' => '1',
						'instagram_profile_id' => $user_data['data']['id'],
						'instagram_token' => (string)JO_Session::get('InstagramAccessToken')
				) );
			} else {
				JO_Session::set('InstagramAccessToken', false);
				JO_Session::set('user[instagram_token]', array_merge((array)JO_Session::get('user'), array('instagram_token' => null)));
				$this->view->login = WM_Router::create($this->getRequest()->getBaseUrl() . '?controller=instagram&next=' . urlencode( WM_Router::create($request->getBaseUrl() . '?controller=settings&action=instagram_connect&state=' . $state) ));
			}
			
		} else {
			Model_Users::edit2( JO_Session::get('user[user_id]'), array(
				'instagram_connect' => '0',
				'instagram_profile_id' => '0',
				'instagram_token' => ''
			) ); 
		}
		
		if( !$this->view->login ) {
			$ud = Model_Users::getUser(JO_Session::get('user[user_id]'), true);
			if($ud) {
				$this->view->connected = $ud['instagram_connect'] ? 'on' : 'off';
			} else {
				JO_Session::set('InstagramAccessToken', false);
				JO_Session::set('user[instagram_token]', array_merge((array)JO_Session::get('user'), array('instagram_token' => null)));
				$this->view->connected = 'off';
			}
		}
		
		if(!$request->isXmlHttpRequest()) {
			$this->redirect( WM_Router::create($this->getRequest()->getBaseUrl() . '?controller=settings') );
			exit;
		}
		
		
		
		echo $this->renderScript('json');
		
	}
	
	public function instagram_cronAction() {
		$ud = Model_Users::getUser(JO_Session::get('user[user_id]'));
		
		if($ud) {
			$curl = new JO_Http();
			$curl->initialize(array(
					'target' => WM_Router::create( $this->getRequest()->getBaseUrl() . '?controller=instagram&action=cronfirst&user=' . $ud['user_id'] ),
					'method' => 'GET',
					'timeout' => 30
			));
			$curl->useCurl(true);
			$curl->execute();
		}
		exit('[]');
	}
	
	
	
}

?>