<?php

class Apiv1Controller extends JO_Action {
	
	private $error = false;
	
	public function indexAction() {
		$this->forward('error', 'error404');
	}
	
	//====== timeline ====//
	public function timelineAction() {
		
		$this->noViewRenderer(true);
		
		$request = $this->getRequest();
		$response = $this->getResponse();
		
		$page = (int)$request->getRequest('page');
		if($page < 1) { $page = 1; }
		
		$limit = (int)$request->getRequest('limit');
		if($limit < 1 || $limit > 100) { $limit = 50; } //$limit = JO_Registry::get('config_front_limit'); }
		
		$callback = $request->getRequest('callback');
		if(!preg_match('/^([a-z0-9_.]{1,})$/', $callback)) {
			$callback = false;
		}
		
		$data = array(
			'start' => ( $limit * $page ) - $limit,
			'limit' => $limit
		);
                
		$url = '&limit=' . $limit . '&page=' . ($page+1);
		
                /*
		if($request->getRequest('popular') == 'true') {
			$data['filter_like_repin_comment'] = true;
			$url .= '&popular=true';
		}
		if( (int)$request->getRequest('fromPrice') ) {
			$data['filter_price_from'] = (int)$request->getRequest('fromPrice');
			$url .= '&fromPrice=' . (int)$request->getRequest('fromPrice');
		}
		if( (int)$request->getRequest('toPrice') ) {
			$data['filter_price_to'] = (int)$request->getRequest('toPrice');
			$url .= '&toPrice=' . (int)$request->getRequest('toPrice');
		}
		if( $request->getRequest('source') ) {
			$data['filter_source_id'] = $request->getRequest('source');
			$url .= '&source=' . $request->getRequest('source');
		}
                 * 
                 */
		if( $request->getRequest('categoryId') ) {
			$data['filter_category_id'] = $request->getRequest('categoryId');
			$url .= '&categoryId=' . $request->getRequest('categoryId');
		}
                /*
		if($request->issetRequest('video')) {
			$data['filter_is_video'] = (int)$request->getRequest('video');
			$url .= '&video=' . (int)$request->getRequest('video');
		}
		if( $request->getRequest('board') ) {
			$data['filter_board_id'] = $request->getRequest('board');
			$url .= '&board=' . $request->getRequest('board');
		}
                 * 
                 */
		if( $request->getRequest('userId') ) {
			$data['filter_user_id'] = $request->getRequest('userId');
			$url .= '&user=' . $request->getRequest('userId');
		}
/*
		if( $request->getRequest('search') ) {
			$data['filter_description'] = $request->getRequest('search');
			$url .= '&search=' . urlencode($request->getRequest('search'));
		}
		if( $request->getRequest('following') == 'true' ) {
			if(( $user_data = $this->isLoged() ) !== false) {
				$data['following_users_from_user_id'] = $user_data['user_id'];
				$url .= '&following=true';
			}
		}
		*/
		$return = array();
		
                error_log("token ". $_POST['token']);
                error_log("user " . md5($_POST['user_id']));
                
        if (isset($_POST['token']) && $_POST['token'] == md5($_POST['user_id'])) 
            {
                $_SESSION['token'] = $_POST['token'];
                
		if(!$this->error) {
			$pins = Model_Pins::getPinsAPP($data);
			if($pins) {
				$model_images = new Helper_Images();
				foreach($pins AS $pin) {
					$images = array();
					
					$image = call_user_func(array(Helper_Pin::formatUploadModule($pin['store']), 'getPinImage'), $pin, '_B');
					if($image) {
						$images['thumb']['src'] = $image['image'];
						$images['thumb']['width'] = $image['width'];
						$images['thumb']['height'] = $image['height'];
						$images['original'] = $image['original'];
					} 
					
					
                                        $categorias = array();
                                        $categories = Model_Categories::getCategory($pin['category_id']);
                                        if ($categories['parent_id'] != 0)
                                        {
                                            $categorias['category_id'] = $categories['parent_id'];
                                            $categorias['subcategory_id'] = $categories['category_id'];
                                        }
                                        else
                                        {
                                            $categorias['category_id'] = $categories['category_id'];
                                            $categorias['subcategory_id'] = $categories['parent_id'];
                                        }
                                        
                                        $pos = strripos($pin['image'], '.');
                                        
					$return['data'][] = array(
						'pin_id' => $pin['pin_id'],
						'pinFooter' => $pin['description'],
                                                'category_id' => $categorias['category_id'],
                                                'subcategory_id' => $categorias['subcategory_id'],
						//'source_id' => $pin['source_id'],
						//'images' => $images,
                                            /*
						'pinImage' => $image['image'],
						'pinWidth' => $image['width'],
						'pinHeight' => $image['height'],
                                                 */
						'pinImage' =>  substr_replace($pin['image'], '_B.', $pos, 1),
						'pinWidth' => $pin['width'],
						'pinHeight' => $pin['height'],
                                            
						'pinUrl' => WM_Router::create( $request->getBaseUrl() . '?controller=pin&pin_id=' . $pin['pin_id'] ),
						//'comments' => $pin['comments'],
						//'likes' => $pin['likes'],
						//'repins' => $pin['repins'],
                                                'userId' => $pin['user_id'],
                                                'username' => $pin['username'],
                                                'avatar' => $pin['avatar'],
                                                'userLike' => $pin['liked'],
                                                'pinVideo' => $pin['from'],
                				//'width' => $img_size[0],
                                		//'height' => $img_size[1]
							//etc		
					);
				}
				$return['next_page'] = WM_Router::create( $request->getBaseUrl() . '?controller=apiv1&action=timeline' . $url );
			}
		} else {
			$return = array('error' => $this->error);
		}
        } else {
//no existe la sesión / no existe el dato recibido por post / el token no es igual.
            $return = array('error' =>1, 'description' =>$this->translate('wrong token'));
}

            
		if($callback) {
			$return = $callback . '(' . JO_Json::encode($return) . ')';
		} else {
			$response->addHeader('Cache-Control: no-cache, must-revalidate');
			$response->addHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			$response->addHeader('Content-type: application/json');
			$return = JO_Json::encode($return);
		}
		
		$response->appendBody($return);
		
	}
	
	//===== COMMENTS =====//
	public function commentsAction() {
		
		$this->noViewRenderer(true);
		
		$request = $this->getRequest();
		$response = $this->getResponse();
		
		$page = (int)$request->getRequest('page');
		if($page < 1) {
			$page = 1;
		}
		
		$callback = $request->getRequest('callback');
		if(!preg_match('/^([a-z0-9_.]{1,})$/', $callback)) {
			$callback = false;
		}
		
		$return = array();
		
		$pin_id = (int)$request->getRequest('pin_id');
		$pin_info = Model_Pins::getPin($pin_id);
		if(!$pin_info) {
			$return = array('error' => $this->translate('There was a problem with the record. Please try again!'));
		} else {
		
			if($request->getParam('comments') == 'post') {
				
				if($this->isLoged()) {
					
					///add comment
					
				} else {
					$return = array('error' => $this->error);
				}
				
			} else {
			
				$data = array(
						'filter_pin_id' => $pin_id
				);
				
				$return = array('data' => array());
				
				$comments = Model_Comments::getComments($data);
				
				if($comments) {
					foreach($comments AS $comment) {
						$return['data'][] = array(
								'comment_id' => $comment['comment_id'],
								'comment' => $comment['comment'],
								'date_added' => $comment['date_added'],
								'user_fullname' => $comment['user']['fullname'],
								//etc
						);
					}
				}
				
			}
		
		}
		
		
		if($callback) {
			$return = $callback . '(' . JO_Json::encode($return) . ')';
		} else {
			$response->addHeader('Cache-Control: no-cache, must-revalidate');
			$response->addHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			$response->addHeader('Content-type: application/json');
			$return = JO_Json::encode($return);
		}
		
		$response->appendBody($return);
		
	}
	
	//===== HELP AS =====//
	
	public function isLoged() {
		$request = $this->getRequest();
		
		$validate = new Helper_Validate();
		$validate->_set_rules($request->getPost('email'), $this->translate('Email'), 'not_empty;min_length[5];max_length[100];email');
		$validate->_set_rules($request->getPost('password'), $this->translate('Password'), 'not_empty;min_length[4];max_length[30]');
		
		if($validate->_valid_form()) {
			if( is_array($user_data = Model_Users::checkLogin($request->getPost('email'), $request->getPost('password'))) ) {
				JO_Session::set(array('user' => $user_data));
				return $user_data;
			} else {
				$this->error = $this->translate('E-mail address and password do not match');
				return false;
			}
		} else {
			$this->error = $this->translate('E-mail address and password do not match');
			return false;
		}
	}
	
        
        //====== login ====//
    public function loginAction() 
    {
            $this->noViewRenderer(true);

            $request = $this->getRequest();
            $response = $this->getResponse();

            $page = (int)$request->getRequest('page');
            if($page < 1) {
                    $page = 1;
            }

            $callback = $request->getRequest('callback');
            if(!preg_match('/^([a-z0-9_.]{1,})$/', $callback)) {
                    $callback = false;
            }

            $return = array();


            $result = Model_Users::checkLogin($request->getRequest('email'), $request->getRequest('password'));
            if($result) 
            {
		if($result['status']) 
                {
                    @setcookie('csrftoken_', md5($result['user_id'] . $request->getDomain() . $result['date_added'] ), (time() + ((86400*366)*5)), '/', '.'.$request->getDomain());                
                    JO_Session::set(array('user' => $result));
                    
                    //$token = md5(uniqid(rand(), true));
                    $token = md5($result['user_id']);
                    $_SESSION['token'] = $token;
                    
                    $return = array('id' => $result['user_id'], 
                                    'username' => $result['username'], 
                                    'token'  => $token,
                                    'firstname' => $result['firstname'], 
                                    'lastname' => $result['lastname']); // $user_data;  
                } 
                else 
                {
                        $return = array('error' =>2, 'description' => $this->translate('This profile is not active.'));
                }
            } 
            else 
            {
                     $return = array('error' =>1, 'description' =>  $this->translate('E-mail address and password do not match'));
            }
            if($callback) {
                    $return = $callback . '(' . JO_Json::encode($return) . ')';
            } else {
                    $response->addHeader('Cache-Control: no-cache, must-revalidate');
                    $response->addHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
                    $response->addHeader('Content-type: application/json; charset=utf-8');
                    $return = JO_Json::encode($return);
            }

            $response->appendBody($return);
        
    }
        
        //====== register ====//
    public function registerAction() 
    {
        //echo"entrando";
		$this->noViewRenderer(true);
		
		$request = $this->getRequest();
		$response = $this->getResponse();
		
		$page = (int)$request->getRequest('page');
		if($page < 1) {
			$page = 1;
		}
		
		$callback = $request->getRequest('callback');
		if(!preg_match('/^([a-z0-9_.]{1,})$/', $callback)) {
			$callback = false;
		}
		
		$return = array();
		

		
		if( JO_Session::get('user[user_id]') ) {
			$this->redirect( WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . JO_Session::get('user[user_id]') ) );
		}
		
		$shared_content = Model_Users::checkSharedContent( $request->getParam('key'), $request->getParam('user_id') );
		
		if(!JO_Registry::get('enable_free_registration')) {
			if(!$shared_content) {
				$this->redirect( WM_Router::create( $request->getBaseUrl() . '?controller=landing' ) );
			}
		} else {
                    /*
			$this->view->fb_register = null;
			$fb_ses = JO_Registry::get('facebookapi');
			$session = $fb_ses->getUser();
			if( JO_Registry::get('oauth_fb_key') && JO_Registry::get('oauth_fb_secret') ) {
				$this->view->fb_register = $this->facebook->getLoginUrl(array(
						'redirect_uri' => WM_Router::create( $request->getBaseUrl() . '?controller=facebook&action=login' ),
						'req_perms' => 'email,user_birthday,status_update,user_videos,user_status,user_photos,offline_access,read_friendlists'
				));
			}
                     * 
                     */
		}
		
		if(JO_Registry::get('oauth_in_key')) {
			$this->view->instagram_register = WM_Router::create($this->getRequest()->getBaseUrl() . '?controller=instagram&action=register&next=' . urlencode( WM_Router::create($request->getBaseUrl() . '?controller=instagram&action=register') ));
		}
		
		$this->view->error = false;
		if($request->isPost()) {
			
			$validate = new Helper_Validate();
			$validate->_set_rules($request->getRequest('username'), $this->translate('Username'), 'not_empty;min_length[3];max_length[100];username');
			$validate->_set_rules($request->getRequest('firstname'), $this->translate('First name'), 'not_empty;min_length[3];max_length[100]');
			$validate->_set_rules($request->getRequest('lastname'), $this->translate('Last name'), 'not_empty;min_length[3];max_length[100]');
			$validate->_set_rules($request->getRequest('email'), $this->translate('Email'), 'not_empty;min_length[5];max_length[100];email');
			$validate->_set_rules($request->getRequest('password'), $this->translate('Password'), 'not_empty;min_length[4];max_length[30]');
			$validate->_set_rules($request->getRequest('password2'), $this->translate('Confirm password'), 'not_empty;min_length[4];max_length[30]');
			
			if($validate->_valid_form()) {
				if( md5($request->getRequest('password')) != md5($request->getRequest('password2')) ) {
					$validate->_set_form_errors( $this->translate('Password and Confirm Password should be the same') );
					$validate->_set_valid_form(false);
				}
				if( Model_Users::isExistEmail($request->getRequest('email')) ) {
					$validate->_set_form_errors( $this->translate('This e-mail address is already used') );
					$validate->_set_valid_form(false);
				}
				if( Model_Users::isExistUsername($request->getRequest('username')) ) {
					$validate->_set_form_errors( $this->translate('This username is already used') );
					$validate->_set_valid_form(false);
				}
			}
			
			if($validate->_valid_form()) {
				$reg_key = sha1($request->getRequest('email').$request->getRequest('username'));
				
				$result = Model_Users::create(array(
					'username' => $request->getRequest('username'),
					'firstname' => $request->getRequest('firstname'),
					'lastname' => $request->getRequest('lastname'),
					'email' => $request->getRequest('email'),
					'password' => $request->getRequest('password'),
					'delete_email' => isset($shared_content['email']) ? $shared_content['email'] : '',
					'delete_code' => isset($shared_content['if_id']) ? $shared_content['if_id'] : '',
					'following_user' => isset($shared_content['user_id']) ? $shared_content['user_id'] : '',
					'facebook_id' => isset($shared_content['facebook_id']) ? $shared_content['facebook_id'] : 0,
					'confirmed' => '0',
					'regkey'=>$reg_key
				));
				
				if($result) {
					if(self::sendMail($result)){
						//self::loginInit($result);
					};
					$return = array('id' => $result); //['user_id']); 
				} else {
					$return = array('error' =>1, 'description' =>   $this->translate('There was a problem with the record. Please try again!'));
				}
				
			} else {
				$return = array('error' =>1, 'description' =>   $validate->_get_error_messages());
			}
		}
		

		$this->view->baseUrl = $request->getBaseUrl();
		
		if($request->issetPost('email')) {
			$this->view->email = $request->getRequest('email');
		} else {
			if(isset($shared_content['email'])) {
				$this->view->email = $shared_content['email'];
			} else {
				$this->view->email = '';
			}
		}
		
		if($request->issetPost('firstname')) {
			$this->view->firstname = $request->getRequest('firstname');
		} else {
			$this->view->firstname = '';
		}
		
		if($request->issetPost('lastname')) {
			$this->view->lastname = $request->getRequest('lastname');
		} else {
			$this->view->lastname = '';
		}
		
		if($request->issetPost('username')) {
			$this->view->username = $request->getRequest('username');
		} else {
			$this->view->username = '';
		}
		
		$this->view->password = $request->getRequest('password');
		$this->view->password2 = $request->getRequest('password2');
		
		

                
                
		if($callback) {
			$return = $callback . '(' . JO_Json::encode($return) . ')';
		} else {
			$response->addHeader('Cache-Control: no-cache, must-revalidate');
			$response->addHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			$response->addHeader('Content-type: application/json; charset=utf-8');
			$return = JO_Json::encode($return);
		}
		
		$response->appendBody($return);
        
    }
    
    private function sendMail($userId){
		$this->noViewRenderer(true);
		$this->noLayout(true);
		//$userId = '462';
		$user =  Model_Users::getUser($userId);
		$url = WM_Router::create(JO_Request::getInstance()->getBaseUrl()."?controller=welcome&action=finishRegistration&key=".sha1($user['email'].$user['username']));
		
		$body = "Hola y bienvenid@ a ".JO_Registry::get('site_name')."! <br /> Para verificar tu email y finalizar el registro, por favor haz clic en el vinculo a continuación.<br/>  <br />Nota: Estamos solucionando un problema de compatibilidad con Hotmail y Outlook, en caso no puedas hacer clic en el siguiente vinculo, por favor cópialo, pégalo en la barra de direcciones y dale al ‘intro’. Disculpa las molestias.<br/> <br /><br/><a href=\"{$url}\">{$url}</a>";
		//var_dump($user);
		$to = $user['email'];
		$from = JO_Registry::forceGet('noreply_mail');
		$title = "amatteur - por favor verifica tu email";
		
			
		if(Model_Email::send($to, $from, $title, $body)){
			//$this->redirect(WM_Router::create(JO_Request::getInstance()->getBaseUrl()."?controller=users&action=verificationRequired"));
			return true;
		};
	}
    

        //====== user ====//
    public function userAction() 
    {
        $this->noViewRenderer(true);
		
        $request = $this->getRequest();
        $response = $this->getResponse();

        $page = (int)$request->getRequest('page');
        if($page < 1) {
                $page = 1;
        }

        $callback = $request->getRequest('callback');
        if(!preg_match('/^([a-z0-9_.]{1,})$/', $callback)) {
                $callback = false;
        }

        $return = array();

        if (isset($_SESSION['token']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['token']) 
            {
        
//guardar o manipular datos.
        $result = Model_Users::checkLogin($request->getRequest('email'), $request->getRequest('password'));
        if($result) 
        {
            if($result['status']) 
            {
                @setcookie('csrftoken_', md5($result['user_id'] . $request->getDomain() . $result['date_added'] ), (time() + ((86400*366)*5)), '/', '.'.$request->getDomain());                
                JO_Session::set(array('user' => $result));
                $return = array('id' => $result['user_id']); // $user_data;  
            } 
            else 
            {
                    $return = array('error' =>2, 'description' => $this->translate('This profile is not active.'));
            }
        } 
        else 
        {
                 $return = array('error' =>1, 'description' =>$this->translate('E-mail address and password do not match'));
        }
        
        } else {
//no existe la sesión / no existe el dato recibido por post / el token no es igual.
            $return = array('error' =>1, 'description' =>$this->translate('wrong token'));
}

        
        if($callback) {
                $return = $callback . '(' . JO_Json::encode($return) . ')';
        } else {
                $response->addHeader('Cache-Control: no-cache, must-revalidate');
                $response->addHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
                $response->addHeader('Content-type: application/json; charset=utf-8');
                $return = JO_Json::encode($return);
        }

        $response->appendBody($return);

    }

        //====== pin detail ====//
    	public function pindetailAction() {
		
		$this->noViewRenderer(true);
		
		$request = $this->getRequest();
		$response = $this->getResponse();
		
		$page = (int)$request->getRequest('page');
		if($page < 1) { $page = 1; }
		
		$limit = (int)$request->getRequest('limit');
		if($limit < 1 || $limit > 100) { $limit = 50; } //$limit = JO_Registry::get('config_front_limit'); }
		
		$callback = $request->getRequest('callback');
		if(!preg_match('/^([a-z0-9_.]{1,})$/', $callback)) {
			$callback = false;
		}
		
		$data = array(
			'start' => ( $limit * $page ) - $limit,
			'limit' => $limit
		);
                
		$url = '&limit=' . $limit . '&page=' . ($page+1);
		
                /*
		if($request->getRequest('popular') == 'true') {
			$data['filter_like_repin_comment'] = true;
			$url .= '&popular=true';
		}
		if( (int)$request->getRequest('fromPrice') ) {
			$data['filter_price_from'] = (int)$request->getRequest('fromPrice');
			$url .= '&fromPrice=' . (int)$request->getRequest('fromPrice');
		}
		if( (int)$request->getRequest('toPrice') ) {
			$data['filter_price_to'] = (int)$request->getRequest('toPrice');
			$url .= '&toPrice=' . (int)$request->getRequest('toPrice');
		}
		if( $request->getRequest('source') ) {
			$data['filter_source_id'] = $request->getRequest('source');
			$url .= '&source=' . $request->getRequest('source');
		}
                 * 
                 */
		if( $request->getRequest('pinId') ) {
			$data['filter_pin_id'] = $request->getRequest('pinId');
			$url .= '&pinId=' . $request->getRequest('pinId');
		}
                /*
		if($request->issetRequest('video')) {
			$data['filter_is_video'] = (int)$request->getRequest('video');
			$url .= '&video=' . (int)$request->getRequest('video');
		}
		if( $request->getRequest('board') ) {
			$data['filter_board_id'] = $request->getRequest('board');
			$url .= '&board=' . $request->getRequest('board');
		}
                 * 
                 */
		if( $request->getRequest('userId') ) {
			$data['filter_user_id'] = $request->getRequest('userId');
			$url .= '&user=' . $request->getRequest('userId');
		}
/*
		if( $request->getRequest('search') ) {
			$data['filter_description'] = $request->getRequest('search');
			$url .= '&search=' . urlencode($request->getRequest('search'));
		}
		if( $request->getRequest('following') == 'true' ) {
			if(( $user_data = $this->isLoged() ) !== false) {
				$data['following_users_from_user_id'] = $user_data['user_id'];
				$url .= '&following=true';
			}
		}
		*/
		$return = array();
		
		if(!$this->error) {
			$pins = Model_Pins::getPinsAPP($data);
			if($pins) {
				$model_images = new Helper_Images();
				foreach($pins AS $pin) {
					$images = array();
					
					$image = call_user_func(array(Helper_Pin::formatUploadModule($pin['store']), 'getPinImage'), $pin, '_B');;
					if($image) {
						$images['thumb']['src'] = $image['image'];
						$images['thumb']['width'] = $image['width'];
						$images['thumb']['height'] = $image['height'];
						$images['original'] = $image['original'];
					} 
                                        
                                        $categorias = array();
                                        
                                        $categories = Model_Categories::getCategory($pin['category_id']);
                                        if ($categories['parent_id'] != 0)
                                        {
                                            $categorias['category_id'] = $categories['parent_id'];
                                            $categorias['subcategory_id'] = $categories['category_id'];
                                        }
                                        else
                                        {
                                            $categorias['category_id'] = $categories['category_id'];
                                            $categorias['subcategory_id'] = $categories['parent_id'];
                                        }
                                        //error_log("datos entrada ". $pin['pin_id'] ."-". $pin['user_id']);
                                        
                                        $boards = Model_Boards::getBoardAPP($pin['board_id'], $pin['user_id'], "", WM_Router::create( $request->getBaseUrl()));

                                        $comments = Model_Pins::commentIsReportedAPP($pin['pin_id'], $pin['user_id']);
                                        
                                        $likes = Model_Pins::pinIsLikedAPP($pin['pin_id']);
                                        
                                        $shared = Model_Pins::repinAPP($pin['pin_id']);
                                        
                                        $next_pin = Model_Pins::getNextPin($pin['pin_id']);
                                        $next = "";
                                        if($next_pin) {
                                                $next = WM_Router::create( $request->getBaseUrl() . '?controller=pin&pin_id=' . $next_pin['pin_id'] );
                                        }
                                        $prev_pin = Model_Pins::getPrevPin($pin['pin_id']);
                                        $prev = "";
                                        if($prev_pin) {
                                                $prev = WM_Router::create( $request->getBaseUrl() . '?controller=pin&pin_id=' . $prev_pin['pin_id'] );
                                        }

//                                            error_log("valor boarder ". $comments,0)  ;                                        
//                                        if($comments)
//                                        {
//                                            error_log("valor board ". $comments,0)  ;
//                                        foreach ($comments as &$value)
//                                        {
//                                            error_log("valor board ". $value["title"],0)  ;
//                                        }
//                                        }
                                        $pos = strripos($pin['image'], '.');                                        
                                        
                                        $return['data'][] = array(
						'pin_id' => $pin['pin_id'],
						'pinFooter' => $pin['description'],
                                                'category_id' => $categorias['category_id'],
                                                'subcategory_id' => $categorias['subcategory_id'],
						//'source_id' => $pin['source_id'],
						//'images' => $images,
                                                /*
						'pinImage' => $image['image'],
						'pinWidth' => $image['width'],
						'pinHeight' => $image['height'],
                                                 */
						'pinImage' =>  substr_replace($pin['image'], '_B.', $pos, 1),
						'pinWidth' => $pin['width'],
						'pinHeight' => $pin['height'],
						'pinUrl' => WM_Router::create( $request->getBaseUrl() . '?controller=pin&pin_id=' . $pin['pin_id'] ),
						'comments' => $comments ? $comments : "", 
						'likes' => $likes ? $likes : "", 
                                                'folder' => $boards ? array($boards['title'],WM_Router::create( $request->getBaseUrl(). $pin['username']."/".$boards['title']), $boards['image']) : "",
                                                'shared' => $shared ? $shared : "", 
                                                'next' => $next ? $next : "",
                                                'previous' => $prev ? $prev : "",
						//'repins' => $pin['repins'],
                                                'userId' => $pin['user_id'],
                                                'username' => $pin['username'],
                                                'avatar' => $pin['avatar'],
                                                'userLike' => $pin['liked'],
                                                'pinVideo' => $pin['from'],
							//etc		
					);
				}
				$return['next_page'] = WM_Router::create( $request->getBaseUrl() . '?controller=apiv1&action=pindetail' . $url );
			}
		} else {
			$return = array('error' => $this->error);
		}
		
		if($callback) {
			$return = $callback . '(' . JO_Json::encode($return) . ')';
		} else {
			$response->addHeader('Cache-Control: no-cache, must-revalidate');
			$response->addHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			$response->addHeader('Content-type: application/json');
			$return = JO_Json::encode($return);
		}
		
		$response->appendBody($return);
		
	}
        

  

        //====== category ====//
    public function categoryAction() 
    {
        //echo"entrando";
		$this->noViewRenderer(true);
		
		$request = $this->getRequest();
		$response = $this->getResponse();
		
		$page = (int)$request->getRequest('page');
		if($page < 1) {
			$page = 1;
		}
		
		$callback = $request->getRequest('callback');
		if(!preg_match('/^([a-z0-9_.]{1,})$/', $callback)) {
			$callback = false;
		}
		
		$return = array();
		
		//////////// Categories ////////////
		//$this->view->categories = array();
		//$this->view->category_active = false;
                //array('id' => $result['user_id']); // $user_data;
		$categories = Model_Categories::getCategories(array(
			'filter_status' => 1
		));
                
/*
		foreach($categories AS $category) {
			$category['subcategories'] = Model_Categories::getSubcategories($category['category_id']);
			$category['href'] = WM_Router::create( $request->getBaseUrl() . '?controller=category&category_id=' . $category['category_id'] );
			$category['active'] = $category['category_id'] == $request->getRequest('category_id');
			if($category['active']) {
				//$this->view->category_active = $category['title'];
			} else {
				
			}
			
			//$this->view->categories[] = $category;
                        echo "sub ". $category['subcategories'];
		}
                */
                $return = array($categories); 
                
                if($callback) {
			$return = $callback . '(' . JO_Json::encode($return) . ')';
		} else {
			$response->addHeader('Cache-Control: no-cache, must-revalidate');
			$response->addHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			$response->addHeader('Content-type: application/json; charset=utf-8');
			$return = JO_Json::encode($return);
		}
		
		$response->appendBody($return);
        
    }
        //====== subcategory ====//
    public function subcategoryAction() 
    {
        //echo"entrando";
		$this->noViewRenderer(true);
		
		$request = $this->getRequest();
		$response = $this->getResponse();
		
		$page = (int)$request->getRequest('page');
		if($page < 1) {
			$page = 1;
		}
		
		$callback = $request->getRequest('callback');
		if(!preg_match('/^([a-z0-9_.]{1,})$/', $callback)) {
			$callback = false;
		}
		
		$return = array();
		
                $subCategories = "";
                if ($request->getRequest('category_id') != "")
                {
                    $subCategories = Model_Categories::getSubCategoriesAPP($request->getRequest('category_id'));
                    
                    $return = $subCategories;
                }
                else
                {
                    $return = array('error' =>1, 'description' =>'Subcategoría vacía');
                }
                
/*
		foreach($categories AS $category) {
			$category['subcategories'] = Model_Categories::getSubcategories($category['category_id']);
			$category['href'] = WM_Router::create( $request->getBaseUrl() . '?controller=category&category_id=' . $category['category_id'] );
			$category['active'] = $category['category_id'] == $request->getRequest('category_id');
			if($category['active']) {
				//$this->view->category_active = $category['title'];
			} else {
				
			}
			
			//$this->view->categories[] = $category;
                        echo "sub ". $category['subcategories'];
		}
                */

                
                
                if($callback) {
			$return = $callback . '(' . JO_Json::encode($return) . ')';
		} else {
			$response->addHeader('Cache-Control: no-cache, must-revalidate');
			$response->addHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			$response->addHeader('Content-type: application/json; charset=utf-8');
			$return = JO_Json::encode($return);
		}
		
		$response->appendBody($return);
        
    }
  
	//====== User Info ====//
	public function userinfoAction() {
		
            
            $this->noViewRenderer(true);

            $request = $this->getRequest();
            $response = $this->getResponse();

            $page = (int)$request->getRequest('page');
            if($page < 1) {
                    $page = 1;
            }

            $callback = $request->getRequest('callback');
            if(!preg_match('/^([a-z0-9_.]{1,})$/', $callback)) {
                    $callback = false;
            }

	
            $data = array();
            $url = "";
            
            $data['filter_user_id'] = $request->getRequest('userId');
            $url .= '&user=' . $request->getRequest('userId');
            
            $return = array();

            $userinfo = Model_Users::getUser($request->getRequest('userId'), false, Model_Users::$allowed_fields);
            if($userinfo) 
            {
                    $boards = Model_Boards::getBoardAPP("", $userinfo['user_id'], $userinfo['username'], WM_Router::create( $request->getBaseUrl()));
                    
                    $result['userId'] = $userinfo["user_id"];
                    $result['userName'] = $userinfo["username"];
                    $result['userIcon'] = $userinfo["avatar"];
                    $result['userDescription'] = $userinfo["username"];
                    $result['folderQty'] = Model_Boards::getTotalBoards($data);;
                    $result['imageQty'] = Model_Pins::getTotalPins($data);
                    $result['likeQty'] = $userinfo["likes"];
                    $result['followingQty'] = $userinfo["following"];
                    $result['followersQty'] = $userinfo["followers"];
                    $result['folders'] = $boards ? $boards : "";
                    $return[] = $result;
                
            } else {
                    $return = array('error' => $this->error);
            }

            if($callback) {
                    $return = $callback . '(' . JO_Json::encode($return) . ')';
            } else {
                    $response->addHeader('Cache-Control: no-cache, must-revalidate');
                    $response->addHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
                    $response->addHeader('Content-type: application/json');
                    $return = JO_Json::encode($return);
            }

            $response->appendBody($return);
		
	}
    
    
}

?>