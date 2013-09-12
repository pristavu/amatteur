<?php

class UsersController extends JO_Action {
	
	/**
	 * @var WM_Facebook
	 */
	protected $facebook;
	
	private function loginInit($id) {
		$user_data = WM_Users::initSession($id);
		if($user_data) { 
			JO_Session::set(array('user' => $user_data));
		}
		$this->redirect( WM_Router::create( $this->getRequest()->getBaseUrl() ) );
	}
	
	public function init() {
		$this->facebook = JO_Registry::get('facebookapi');
	}
	
	public function indexAction() {
	    $this->forward('users', 'profile');
	}
	
	public function editDescriptionAction(){
		
		$request = $this->getRequest();
		
		if(!JO_Session::get('user[user_id]')) {
			if($request->isXmlHttpRequest()) {
				$this->view->redirect = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=login' );
			} else {
				$this->redirect( WM_Router::create( $request->getBaseUrl() . '?controller=users&action=login' ) );
			}
		} else {
			Model_Users::editDescription( $request->getPost('description') );
			$this->view->ok = $request->getPost('description');
		}

		echo $this->renderScript('json');
	}
	
	public function editAgendaAction(){
		
		$request = $this->getRequest();
		
		if(!JO_Session::get('user[user_id]')) {
			if($request->isXmlHttpRequest()) {
				$this->view->redirect = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=login' );
			} else {
				$this->redirect( WM_Router::create( $request->getBaseUrl() . '?controller=users&action=login' ) );
			}
		} else {
			Model_Users::editAgenda( $request->getPost('texto'),$request->getPost('agenda_id') );
                        
                        $data = Model_Users::followersUsers(JO_Session::get('user[user_id]'));
                        if ($data)
                        {
                            foreach ($data AS $key => $user)
                            {
                                        //add history
                                        Model_History::addHistory($user["user_id"], Model_History::COMMENTUSER, $request->getPost('texto'));
                            }
                        }
                        
                        $this->view->ok = $request->getPost('texto');
		}

		echo $this->renderScript('json');
	}

        
        private function profileHelp() {
		$request = $this->getRequest();
		$user_data = Model_Users::getUser( $request->getRequest('user_id') );
        
                if(!$user_data) {
                    $this->forward('error', 'error404');
                }

                if(!$user_data['facebook_connect']) {
                        $user_data['facebook_id'] = 0;
                }

                if(!$user_data['twitter_connect']) {
                        $user_data['twitter_id'] = 0;
                }

                JO_Registry::set('rss_feed', array(
                        'title' => $user_data['fullname'] . ' ('.$user_data['username'].') ' . sprintf($this->translate('on %s'), JO_Registry::get('site_name')),
                        'href' => WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $user_data['user_id'] . '&feed' )
                ));

                $this->getLayout()->meta_title = $user_data['fullname'] . ' ('.$user_data['username'].') ' . sprintf($this->translate('on %s'), JO_Registry::get('site_name'));

                $avatar = Helper_Uploadimages::avatar($user_data, '_B');
                $user_data['avatar'] = $avatar['image'];
       
		$user_data['image_href'] = $user_data['avatar'];
		if($user_data['user_id'] == JO_Session::get('user[user_id]')) {
			$user_data['image_href'] = WM_Router::create( $request->getBaseUrl() . '?controller=settings' );
		}

                $user_data['imageLikes'] = Model_Pins::likesPins($user_data['user_id']);
                $this->view->active = 'boards';
        
	
		if($user_data['website'] && !preg_match('/^https?:\/\//',$user_data['website'])) {
			$user_data['website'] = 'http://' . $user_data['website'];
		}
        
                $this->view->userdata = $user_data;  

                $this->getLayout()->meta_title = $user_data['fullname'] . ' ('.$user_data['username'].') ' . sprintf($this->translate('on %s'), JO_Registry::get('site_name'));

                $this->view->self_profile = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $user_data['user_id'] );
                $this->view->user_pins = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=pins&user_id=' . $user_data['user_id']  );
                $this->view->user_pins_likes = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=pins&user_id=' . $user_data['user_id'] . '&filter=likes' );
                $this->view->settings = WM_Router::create( $request->getBaseUrl() . '?controller=settings' );
                $this->view->user_activity = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=activity&user_id=' . $user_data['user_id']  );
		$this->view->user_followers = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=followers&user_id=' . $user_data['user_id']  );
		$this->view->user_following = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=following&user_id=' . $user_data['user_id']  );
		$this->view->user_likers = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=likers&user_id=' . $user_data['user_id']  );
		$this->view->user_liking = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=liking&user_id=' . $user_data['user_id']  );
                $this->view->edit_description = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=editDescription');
                $this->view->edit_agenda = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=editAgenda');                
       
		$this->view->enable_edit = JO_Session::get('user[user_id]') == $user_data['user_id'];
		
		$this->view->order_boards = WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=sort_order');
		
		$this->view->reload_page = $request->getFullUrl();
		
		if(JO_Session::get('user[user_id]') && $user_data['user_id'] != JO_Session::get('user[user_id]')) {
			$this->view->userIsFollow = Model_Users::isFollowUser($user_data['user_id']);
			
			$this->view->follow_user = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=follow&user_id=' . $user_data['user_id'] );
                        
                        
			$this->view->userIsLike = Model_Users::isLikeUser($user_data['user_id']);
			
			$this->view->like_user = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=likeUser&user_id=' . $user_data['user_id'] );
                }
                else
                {
			$this->view->userIsFollow = "";
			
			$this->view->follow_user = "";
                        
                        
			$this->view->userIsLike = "";
			
			$this->view->like_user = "";
                }
                
		$this->view->class_contaner = $request->getAction();
		
		$data = array(
			'start' => 0,
			'limit' => 3,
			'sort' => 'DESC',
			'order' => 'history_id',
			'filter_history_action' => Model_History::REPIN
		);
		$history = Model_History::getHistory($data, 'from_user_id', $user_data['user_id']);
		
		$this->view->history_data = array();
		
		$this->view->title_right = $this->translate('Repins from');
		
		if(!$history) {
			$this->view->title_right = $this->translate('Following');
			$data['filter_history_action'] = Model_History::FOLLOW_USER;
			$history = Model_History::getHistory($data, 'from_user_id', $user_data['user_id']);
                        
			$this->view->title_right = $this->translate('liking');
			$data['filter_history_action'] = Model_History::LIKEUSER;
			$history = Model_History::getHistory($data, 'from_user_id', $user_data['user_id']);
		}
		
		if($history) { 
			$model_images = new Helper_Images();
			foreach($history AS $r) {
				if(!isset($r['user']['store'])) {
					continue;
				}
				$avatar = Helper_Uploadimages::avatar($r['user'], '_B');
				
				$this->view->history_data[] = array(
					'title' => $r['user']['fullname'],
					'href' => WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $r['user']['user_id']),
					'thumb' => $avatar['image']
				);
			}
		}
		
		
		return $user_data;
	}
	
	public function profileAction() {
            
                $request = $this->getRequest();

                $method = $request->getSegment(2);

                if( method_exists($this, strtolower($method).'Action') ) {
                        $this->forward('users', $method);
                }
        
		$user_data = $this->profileHelp();
        
                $this->view->active = 'boards';
        
		$page = (int)$request->getRequest('page');
		if($page < 1) { $page = 1; }
		

		$boards = Model_Boards::getBoards(array(
			'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
			'limit' => JO_Registry::get('config_front_limit'),
			'filter_user_id' => $user_data['user_id'],
			'sort' => 'ASC',
			'order' => 'boards.sort_order',
			'friendly' => $user_data['user_id'],
//		    'where' => new JO_Db_Expr("boards.user_id = '".$user_data['user_id']."' OR boards.board_id IN (SELECT board_id FROM users_boards WHERE user_id = '".$user_data['user_id']."' AND allow = 1)")
		));
		
		$this->view->has_edit_boards = true;
		$this->view->enable_sort = true;
		$this->view->current_page = $page;
		
		$this->view->boards = '';
                $this->view->boards6 = '';
                $board_counter = 0;
                
		if($boards) {
			$view = JO_View::getInstance();
			$view->loged = JO_Session::get('user[user_id]');
			$view->enable_sort = true;
			$model_images = new Helper_Images();
			foreach($boards AS $board) {
                                $board_counter++;
                                $view->board_counter =$board_counter;
                            
				$board['href'] = WM_Router::create($request->getBaseUrl() . '?controller=boards&action=view&user_id=' . $user_data['user_id'] . '&board_id=' . $board['board_id']);
				$board['thumbs'] = array();
				$get_big = false;
				for( $i = 0; $i < 5; $i ++) {
					$image = isset( $board['pins_array'][$i] ) ? $board['pins_array'][$i]['image'] : false;
					if($image) {
						if($get_big) {
							$size = '_A';
						} else {
							$size = '_C';
							$get_big = true;
						}
						$data_img = Helper_Uploadimages::pin($board['pins_array'][$i], $size);
						if($data_img) {
							$board['thumbs'][] = $data_img['image'];
						} else {
							$board['thumbs'][] = false;
						}
					} else {
						$board['thumbs'][] = false;
					}
				}
				
				$board['boardIsFollow'] = Model_Users::isFollow(array(
					'board_id' => $board['board_id']
				));
				
				$board['userFollowIgnore'] = $board['user_id'] != JO_Session::get('user[user_id]');
				
				$board['follow'] = WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=follow&user_id=' . $user_data['user_id'] . '&board_id=' . $board['board_id'] );
				
				$board['edit'] = false; 
				if($board['user_id'] == JO_Session::get('user[user_id]') || Model_Boards::allowEdit($board['board_id'])) {
					$board['userFollowIgnore'] = false;
					$board['edit'] = WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=edit&user_id=' . $user_data['user_id'] . '&board_id=' . $board['board_id'] );
				}
				
				
				$view->board = $board;
                                if ($board_counter > 6)
                                {
                                    $this->view->boards6 .= $view->render('box', 'boards');
                                }
                                else
                                {
                                    $this->view->boards .= $view->render('box', 'boards');
                                }
			}
		}
		
		if($user_data['user_id'] == JO_Session::get('user[user_id]')) {
		    
		    $inv_boards = Model_Boards::getInvBoards();
		    
		    if($inv_boards) {
		        $this->view->iboard ='';
		         $view = JO_View::getInstance();
    			$model_images = new Helper_Images();
    			foreach($inv_boards AS $iboard) {
    				$iboard['href'] = WM_Router::create($request->getBaseUrl() . '?controller=boards&action=view&user_id=' . $iboard['uuser_id'] . '&board_id=' . $iboard['board_id']);
    				$iboard['accept'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=accinv&type=accept&ub_id=' . $iboard['ub_id'] . '&board_id=' . $iboard['board_id'] );
    				$iboard['decline'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=accinv&type=decline&ub_id=' . $iboard['ub_id'] . '&board_id=' . $iboard['board_id'] );
    				
    				$iboard['thumbs'] = array();
	    			$get_big = false;
					for( $i = 0; $i < 5; $i ++) {
						$image = isset( $iboard['pins_array'][$i] ) ? $iboard['pins_array'][$i]['image'] : false;
						if($image) {
							if($get_big) {
								$size = '_A';
							} else {
								$size = '_C';
								$get_big = true;
							}
							$data_img = Helper_Uploadimages::pin($iboard['pins_array'][$i], $size);
							if($data_img) {
								$iboard['thumbs'][] = $data_img['image'];
							} else {
								$iboard['thumbs'][] = false;
							}
						} else {
							$iboard['thumbs'][] = false;
						}
					}
					$avatar = Helper_Uploadimages::avatar(array(
						'store' => $iboard['ustore'],
						'avatar' => $iboard['avatar']
					), $size);
					$iboard['avatar'] = $avatar['image'];
    				
    				$iboard['user_href']  = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $iboard['uuser_id']);
    				
    				
    				$iboard['boardIsFollow'] = Model_Users::isFollow(array(
    					'board_id' => $iboard['board_id']
    				));
    				
    				$iboard['userFollowIgnore'] = false;
    				
    				$iboard['follow'] = WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=follow&user_id=' . $iboard['uuser_id'] . '&board_id=' . $iboard['board_id'] );
    				
    				$iboard['edit'] = false;
    				
    				$iboard['invited'] = true;
    				
    				$view->board = $iboard;

    				$this->view->iboard .= $view->render('box', 'boards');
		    }
		}
		}
				
				$agendas = Model_Users::getUserAgenda(array(
					'filter_user_id' => $user_data['user_id']
				));
				$this->view->has_agendas = false;
				$this->view->agendas_users="";
				if ($agendas)
				{
					$this->view->has_agendas = true;
					foreach($agendas AS $agenda) {
						$agenda['hrefDelete'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=agendaPopupDelete&agenda_id=' . $agenda['agenda_id'] . '&user_id=' . $user_data['user_id'] );
                    	$this->view->agenda = $agenda;
                        $this->view->agendas_users .= $this->view->render('agenda', 'users');
                    }
				}
				$session_user = JO_Session::get('user[user_id]');
				$this->view->popup_agenda = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=agendaPopup&user_id=' . $user_data['user_id']  );
				
                //no mover de esta ubicación
                
                $messages = Model_Users::getUserMessages(array(
					'start' => 0,
					'limit' => 100,
					'filter_user_id' => $user_data['user_id'],
					'idPadre' => 0
				));
                
                
                $this->view->has_messages = false;
				$this->view->messages_users="";
                if ($messages)			
                {
                    $this->view->has_messages = true;
                    foreach($messages AS $message) {
						$avatar = Helper_Uploadimages::avatar( $message, '_A');
                        $message['avatar'] = $avatar['image'];
						$message['href'] = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' .  $message['user_id']);
                        $message['hrefDelete'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=messagePopupDelete&message_id=' . $message['message_id'] .'&user_id=' . $user_data['user_id'] );
                        $message['hrefResponder'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=messagePopup&user_from=' . $session_user . '&user_to=' . $user_data['user_id'].'&board_user=' . $user_data['user_id'] .'&message_from_id=' . $message['message_id'] );
                        $this->view->message = $message;
                        $this->view->messages_users .= $this->view->render('message', 'users');
						//ahora vamos a consultar las respuestas a este:
						$messagesHijos = Model_Users::getUserMessages(array(
							'start' => 0,
							'limit' => 100,
							'filter_user_id' => $user_data['user_id'],
							'idPadre' => $message['message_id']
						));
						if ($messagesHijos)			
                		{	
							foreach($messagesHijos AS $messageHijo) {
								$avatar = Helper_Uploadimages::avatar( $messageHijo, '_A');
								$messageHijo['avatar'] = $avatar['image'];
								$messageHijo['href'] = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' .  $messageHijo['user_id']);
								$messageHijo['hrefDelete'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=messagePopupDelete&message_id=' . $messageHijo['message_id'] .'&user_id=' . $user_data['user_id'] );
								$messageHijo['hrefResponder'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=messagePopup&user_from=' . $session_user . '&user_to=' . $user_data['user_id'].'&board_user=' . $user_data['user_id'] .'&message_from_id=' . $messageHijo['message_id'] );
								$this->view->message = $messageHijo;
								$this->view->messages_users .= $this->view->render('message', 'users');
							}
						}
                    }
                }

                $this->view->popup_messages = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=messagePopup&user_from=' . $session_user . '&user_to=' . $user_data['user_id'].'&board_user=' . $user_data['user_id'] .'&message_from_id=0'  );
                $this->view->popup_activate = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=activatePopup&user_from=' . $session_user . '&user_to=' . $user_data['user_id'].'&board_user=' . $user_data['user_id'] .'&message_from_id=0'  );
                
                if(JO_Registry::get('isMobile'))
                {
                    $this->view->urlagenda = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=agenda&user_id=' . $user_data['user_id']   );
                    $this->view->urlmensajes = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=mensajes&user_id=' . $user_data['user_id']   );
                }
		
		if($request->isXmlHttpRequest()) {
			echo $this->view->boards;
			$this->noViewRenderer(true);
		} else {
			$this->view->children = array(
	        	'header_part' 	=> 'layout/header_part',
	        	'footer_part' 	=> 'layout/footer_part'
	        );
		}

	}
	
	public function agendaAction() {
            
                $request = $this->getRequest();

                //para las APP's
                if (isset($_POST['token']) && $_POST['token'] == md5($_POST['userid']))
                {
                    $_SESSION['token'] = $_POST['token'];
                    JO_Session::set('token', $_POST['token']);

                        $result = Model_Users::checkLoginAPP($_POST['userid']);
                        if ($result)
                        {
                            if ($result['status'])
                            {
                                @setcookie('csrftoken_', md5($result['user_id'] . $request->getDomain() . $result['date_added']), (time() + ((86400 * 366) * 5)), '/', '.' . $request->getDomain());
                                JO_Session::set(array('user' => $result));
                            }  
                        }

                }

		$user_data = $this->profileHelp();
        
        
		$page = (int)$request->getRequest('page');
		if($page < 1) { $page = 1; }
			$agendas = Model_Users::getUserAgenda(array(
					'filter_user_id' => $user_data['user_id']
				));
                
                
                if ($agendas)
				{
					$this->view->has_agendas = true;
					foreach($agendas AS $agenda) {
						$agenda['hrefDelete'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=agendaPopupDelete&agenda_id=' . $agenda['agenda_id'] . '&user_id=' . $user_data['user_id'] );
                    	$this->view->agenda = $agenda;
                        $this->view->agendas_users .= $this->view->render('agendasRender', 'users');
                    }
				}
                $session_user = JO_Session::get('user[user_id]');
                $this->view->popup_agenda = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=agendaPopup&user_id=' . $user_data['user_id']  );
		
		if($request->isXmlHttpRequest()) {
			echo $this->view->boards;
			$this->noViewRenderer(true);
		} else {
			$this->view->children = array(
	        	'header_part' 	=> 'layout/header_part',
	        	'footer_part' 	=> 'layout/footer_part'
	        );
		}

	}
	
	public function agendaPopupAction() {
		
		$request = $this->getRequest();
                
                $this->view->user_id = $request->getRequest('user_id');
	
                $this->view->from_url = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=agendaPopup' );
                
                if(JO_Registry::get('isMobile'))
                {
                    $this->view->urlagenda = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=agenda&user_id=' . $request->getRequest('user_id')   );
                }
                
		
		$this->view->popup_main_box = $this->view->render('agendaPopup','users');
		
		if( $request->isPost() ) {

                    $result = Model_Users::createAgenda(array(
				'user_id' => $request->getPost('user_id'),
				'texto' => $request->getPost('texto'),
			));
			if($result) {
     
				//Model_History::addHistory($request->getPost('user_id'), Model_History::MESSAGEUSER, $result, $request->getPost('board_user'), $request->getPost('text_message'));
			}
		}
		if($request->isXmlHttpRequest()) {
			$this->noViewRenderer(true);
			echo $this->view->popup_main_box;
			$this->view->is_popup = true;
		} else {
			$this->view->is_popup = false;
			$this->view->children = array(
	        	'header_part' 	=> 'layout/header_part',
	        	'footer_part' 	=> 'layout/footer_part',
	        	'left_part' 	=> 'layout/left_part'
                        );
		}
	}
	
	public function agendaPopupDeleteAction() {
		
		$request = $this->getRequest();
                
                $this->view->agenda_id = $request->getRequest('agenda_id');
                
                if(JO_Registry::get('isMobile'))
                {
                    $this->view->urlagenda = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=agenda&user_id=' . $request->getRequest('user_id')  );
                }


                $this->view->from_url = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=agendaPopupDelete' );
		
		$this->view->popup_main_box = $this->view->render('agendaPopupDelete','users');
		
		if( $request->isPost() ) {

                        $result = Model_Users::deleteAgenda(array(
				'agenda_id' => $request->getPost('agenda_id')
			));
			if($result) {
		
			}
			
		}
		if($request->isXmlHttpRequest()) {
			$this->noViewRenderer(true);
			echo $this->view->popup_main_box;
			$this->view->is_popup = true;
		} else {
			$this->view->is_popup = false;
			$this->view->children = array(
	        	'header_part' 	=> 'layout/header_part',
	        	'footer_part' 	=> 'layout/footer_part',
	        	'left_part' 	=> 'layout/left_part'
                        );
		}
	}
        
	public function mensajesAction() {
            
                $request = $this->getRequest();

                //error_log("antes de APP");
                //para las APP's
                if (isset($_SESSION['token']))
                {
                    //error_log("dentro de app");
                    if (isset($_SESSION['userid']))
                    {
                            $result = Model_Users::checkLoginAPP($_SESSION['userid']);
                            if ($result)
                            {
                                if ($result['status'])
                                {
                                    @setcookie('csrftoken_', md5($result['user_id'] . $request->getDomain() . $result['date_added']), (time() + ((86400 * 366) * 5)), '/', '.' . $request->getDomain());
                                    JO_Session::set(array('user' => $result));

                                    //error_log("fin de app");
                                }  
                            }
                    }

                }
                $view->loged = JO_Session::get('user[user_id]');

		$user_data = $this->profileHelp();
        
        
		$page = (int)$request->getRequest('page');
		if($page < 1) { $page = 1; }
			$messages = Model_Users::getUserMessages(array(
					'start' => 0,
					'limit' => 100,
					'filter_user_id' => $user_data['user_id'],
					'idPadre' => 0
				));
                
                
                if ($messages)			
                {
                    $session_user = JO_Session::get('user[user_id]');
                    
                    $this->view->has_messages = true;
                    foreach($messages AS $message) {
				$avatar = Helper_Uploadimages::avatar( $message, '_A');
                                $message['avatar'] = $avatar['image'];
				
				$message['href'] = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' .  $message['user_id']);
                                $message['hrefDelete'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=messagePopupDelete&message_id=' . $message['message_id'] .'&user_id=' . $user_data['user_id'] );
                                $message['hrefResponder'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=messagePopup&user_from=' . $session_user . '&user_to=' . $user_data['user_id'].'&board_user=' . $user_data['user_id'] .'&message_from_id=' . $message['message_id'] );
                        
                                    $this->view->message = $message;
                                    //$this->view->messages = $message['fullname'] ." ". $message['avatar'] ." ". $message['from_user_id'] . " ".$message['to_user_id'] ." ". $message['text_message'] . " " . $message['date_diff']  ." ". $message['date_message']." ". time()." " . $message['private_message'];
                                    $this->view->messages_users .= $this->view->render('message', 'users');
						//ahora vamos a consultar las respuestas a este:
						$messagesHijos = Model_Users::getUserMessages(array(
							'start' => 0,
							'limit' => 100,
							'filter_user_id' => $user_data['user_id'],
							'idPadre' => $message['message_id']
						));
						if ($messagesHijos)			
                		{	
							foreach($messagesHijos AS $messageHijo) {
								$avatar = Helper_Uploadimages::avatar( $messageHijo, '_A');
								$messageHijo['avatar'] = $avatar['image'];
								$messageHijo['href'] = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' .  $messageHijo['user_id']);
								$messageHijo['hrefDelete'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=messagePopupDelete&message_id=' . $messageHijo['message_id'] .'&user_id=' . $user_data['user_id'] );
								$messageHijo['hrefResponder'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=messagePopup&user_from=' . $session_user . '&user_to=' . $user_data['user_id'].'&board_user=' . $user_data['user_id'] .'&message_from_id=' . $messageHijo['message_id'] );
								$this->view->message = $messageHijo;
								$this->view->messages_users .= $this->view->render('message', 'users');
							}
						}
                    }
                }
                $session_user = JO_Session::get('user[user_id]');
                $this->view->popup_messages = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=messagePopup&user_from=' . $session_user . '&user_to=' . $user_data['user_id'].'&board_user=' . $user_data['user_id']  );
                //$this->view->popup_messages = $this->view->render('messagePopup', 'users');
                //error_log(" error  ". WM_Router::create( $request->getBaseUrl() . '?controller=users&action=messagePopup&user_from=' . $session_user . '&user_to=' . $user_data['user_id'].'&board_user=' . $user_data['user_id']  ) . " error");

		
		if($request->isXmlHttpRequest()) {
			echo $this->view->boards;
			$this->noViewRenderer(true);
		} else {
			$this->view->children = array(
	        	'header_part' 	=> 'layout/header_part',
	        	'footer_part' 	=> 'layout/footer_part'
	        );
		}

	}
        
	public function messagePopupAction() {
		
		$request = $this->getRequest();
                
                
                if(JO_Registry::get('isMobile'))
                {
                    $this->view->urlmensajes = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=mensajes&user_id=' . $request->getRequest('board_user')   );
                }

                $this->view->message_from_id = $request->getRequest('message_from_id');
                $this->view->user_from = $request->getRequest('user_from');
                $this->view->user_to = $request->getRequest('user_to');
                $this->view->board_user = $request->getRequest('board_user');
	
		//$this->view->form_action = WM_Router::create( $request->getBaseUrl() . '?controller=addpin&action=get_images' );
                $this->view->from_url = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=messagePopup' );
		
		$this->view->popup_main_box = $this->view->render('messagePopup','users');
		
		if( $request->isPost() ) {

                    $result = Model_Users::createMessage(array(
				'to_user_id' => $request->getPost('user_to'),
				'from_user_id' => $request->getPost('user_from'),
				'text_message' => $request->getPost('text_message'),
				'private_message' => $request->getPost('private_message'),
                                'board_user_id' => $request->getPost('board_user'),
                                'message_from_id' => $request->getPost('message_from_id')
			));
			if($result) {
                            //Model_History::addHistory($user["user_id"], Model_History::COMMENTUSER, $request->getPost('agenda'));
				Model_History::addHistory($request->getPost('user_to'), Model_History::MESSAGEUSER, $result, $request->getPost('board_user'), $request->getPost('text_message'));
			}
		}
		if($request->isXmlHttpRequest()) {
			$this->noViewRenderer(true);
			echo $this->view->popup_main_box;
			$this->view->is_popup = true;
		} else {
			$this->view->is_popup = false;
			$this->view->children = array(
	        	'header_part' 	=> 'layout/header_part',
	        	'footer_part' 	=> 'layout/footer_part',
	        	'left_part' 	=> 'layout/left_part'
                        );
		}
	}        

	public function messagePopupDeleteAction() {
		
		$request = $this->getRequest();
                
                $this->view->message_id = $request->getRequest('message_id');
	
                if(JO_Registry::get('isMobile'))
                {
                    $this->view->urlmensajes = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=mensajes&user_id=' . $request->getRequest('user_id')   );
                }

		//$this->view->form_action = WM_Router::create( $request->getBaseUrl() . '?controller=addpin&action=get_images' );
                $this->view->from_url = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=messagePopupDelete' );
		
		$this->view->popup_main_box = $this->view->render('messagePopupDelete','users');
		
		if( $request->isPost() ) {

                        $result = Model_Users::deleteMessage(array(
				'message_id' => $request->getPost('message_id')
			));
			if($result) {
			//	Model_History::addHistory(JO_Session::get('user[user_id]'), Model_History::ADDPIN, $result);                            
                            /*
				
			
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
				*/
				//$this->view->pin_url = WM_Router::create( $request->getBaseUrl() . '?controller=pin&pin_id=' . $result );
				//$this->view->popup_main_box = $this->view->render('success','addpin');
			}
			
		}
		
		
		//$this->setViewChange('profile');
		if($request->isXmlHttpRequest()) {
			$this->noViewRenderer(true);
			echo $this->view->popup_main_box;
			$this->view->is_popup = true;
		} else {
			$this->view->is_popup = false;
			$this->view->children = array(
	        	'header_part' 	=> 'layout/header_part',
	        	'footer_part' 	=> 'layout/footer_part',
	        	'left_part' 	=> 'layout/left_part'
                        );
		}
	}        
        
	public function activatePopupAction() {
		
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

                
                //////////// Age ////////////
                $this->view->ages =  array();
                $ages = Model_Users::getAge();
                $this->view->ages = $ages;

                //////////// Level ////////////
                $this->view->levels =  array();
                $levels = Model_Users::getLevel();
                $this->view->levels = $levels;


                
                if(JO_Registry::get('isMobile'))
                {
                    $this->view->urlmensajes = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=mensajes&user_id=' . $request->getRequest('board_user')   );
                }

                $this->view->message_from_id = $request->getRequest('message_from_id');
                $this->view->user_from = $request->getRequest('user_from');
                $this->view->user_to = $request->getRequest('user_to');
                $this->view->board_user = $request->getRequest('board_user');
	
		//$this->view->form_action = WM_Router::create( $request->getBaseUrl() . '?controller=addpin&action=get_images' );
                $this->view->from_url = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=messagePopup' );
		
		$this->view->popup_main_box = $this->view->render('activatePopup','users');
		
		if( $request->isPost() ) {
/*
                    $result = Model_Users::createMessage(array(
				'to_user_id' => $request->getPost('user_to'),
				'from_user_id' => $request->getPost('user_from'),
				'text_message' => $request->getPost('text_message'),
				'private_message' => $request->getPost('private_message'),
                                'board_user_id' => $request->getPost('board_user'),
                                'message_from_id' => $request->getPost('message_from_id')
			));
			if($result) {
                            //Model_History::addHistory($user["user_id"], Model_History::COMMENTUSER, $request->getPost('agenda'));
				Model_History::addHistory($request->getPost('user_to'), Model_History::MESSAGEUSER, $result, $request->getPost('board_user'), $request->getPost('text_message'));
			}
 */
		}
		if($request->isXmlHttpRequest()) {
			$this->noViewRenderer(true);
			echo $this->view->popup_main_box;
			$this->view->is_popup = true;
		} else {
			$this->view->is_popup = false;
			$this->view->children = array(
	        	'header_part' 	=> 'layout/header_part',
	        	'footer_part' 	=> 'layout/footer_part',
	        	'left_part' 	=> 'layout/left_part'
                        );
		}
	}        
        
	public function feedAction(){
		
		$request = $this->getRequest();
		
		$page = (int)$request->getRequest('page');
		if($page < 1) { $page = 1; }
		
		$data = array(
			'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
			'limit' => JO_Registry::get('config_front_limit'),
			'filter_marker' => $request->getRequest('marker'),
			'filter_user_id' => $request->getRequest('user_id')
		);
		
		if( $request->getQuery('filter') == 'likes' ) {
			unset($data['filter_user_id']);
			$data['filter_likes'] = $request->getRequest('user_id');
			$this->view->active = 'likes';
		}
		
		$user_data = Model_Users::getUser($request->getRequest('user_id'));
		if($user_data) {
			
			JO_Registry::set('meta_title', $user_data['fullname'] . ' - ' . JO_Registry::get('meta_title'));
	
			$pins = Model_Pins::getPins($data);
					
			$this->view->item = array();
			if($pins) {
				$model_images = new Helper_Images();
				foreach($pins AS $pin) {
					$data_img = Helper_Uploadimages::pin($pin, '_D');
					if(!$data_img) {
						continue;
					}
					$enclosure = $data_img['image'];
			
					$category_info = Model_Categories::getCategory($pin['category_id']);
					if($category_info) {
						$pin['board'] = $category_info['title'] . ' >> ' . $pin['board'];
					}
			
					$this->view->item[] = array(
							'guid' => $pin['pin_id'],
							'enclosure' => $enclosure,
							'description' => Helper_Pin::descriptionFix($pin['description']),
							'title' => Helper_Pin::descriptionFix(JO_Utf8::splitText($pin['description'], 60, '...')),
							'link' => WM_Router::create( $request->getBaseUrl() . '?controller=pin&pin_id=' . $pin['pin_id'] ),
							'author' => $pin['user']['fullname'],
							'pubDate' => WM_Date::format($pin['date_added'], JO_Date::RSS_FULL),
							'category' => $pin['board']
					);
				}
			}
		
		}
			
		echo $this->renderScript('rss');
		
	}
	
    public function accinvAction(){
        $this->noViewRenderer(true);	
        $request = $this->getRequest();
        if(!JO_Session::get('user[user_id]')) 
            $this->redirect(WM_Router::create( $request->getBaseUrl()));	
       
        
        if($request->getRequest('board_id') and $request->getRequest('ub_id') and $request->getRequest('type')) {
            $ubinfo = Model_Boards::getUsersBoard(array(
            'board_id' => $request->getRequest('board_id'),
            'ub_id'	=>    $request->getRequest('ub_id')
            ));
            if($ubinfo) {
                if($request->getRequest('type') == 'accept') {
                    Model_Boards::acceptUsersBoard($request->getRequest('ub_id'));
                }
                elseif($request->getRequest('type') == 'decline') {
                    Model_Boards::deleteUsersBoard($request->getRequest('ub_id'));
                }
            }
        } 
        
          $this->redirect(WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id='.JO_Session::get('user[user_id]') ));	
	}
	
	public function followersAction() {
                $request = $this->getRequest();
                $user_data = $this->profileHelp();

                $this->setViewChange('profile');

                $this->view->active = 'followers';
        
		$page = (int)$request->getRequest('page');
		if($page < 1) { $page = 1; }
		
		$users = Model_Users::getUsers(array(
			'filter_following_user_id' => $user_data['user_id'],
//			'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
//			'limit' => JO_Registry::get('config_front_limit')
		));
		
		$this->view->boards = '';
		if($users) {
			$view = JO_View::getInstance();
			$view->loged = JO_Session::get('user[user_id]');
			$model_images = new Helper_Images();
			foreach($users AS $key => $user) {
				
				$user['thumbs'] = array();
				
				for( $i = 0; $i < 8; $i ++) {
					$image = isset( $user['pins_array'][$i] ) ? $user['pins_array'][$i]['image'] : false;
					if($image) {
						$data_img = Helper_Uploadimages::pin($user['pins_array'][$i], '_A');
						if($data_img) {
							$user['thumbs'][] = array(
									'thumb' => $data_img['image'],
									'href' => WM_Router::create($request->getBaseUrl() . '?controller=pin&pin_id=' . $user['pins_array'][$i]['pin_id'] ),
									'title' => $user['pins_array'][$i]['title']
							);
						}
					}
				}
				////
				$avatar = Helper_Uploadimages::avatar($user, '_B');
				$user['avatar'] = $avatar['image'];
				
                                $user['userLikeIgnore'] = true;
				if($view->loged) {
					$user['userIsFollow'] = Model_Users::isFollowUser($user['user_id']);
					$user['userFollowIgnore'] = $user['user_id'] == JO_Session::get('user[user_id]');
				} else {
					$user['userFollowIgnore'] = true;
				}
				
				$user['href'] = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $user['user_id']);
				$user['pins_href'] = WM_Router::create($request->getBaseUrl() . '?controller=users&action=pins&user_id=' . $user['user_id']);
				$user['follow'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=follow&user_id=' . $user['user_id'] );
				
				$view->key = $key%2==0;
				$view->user = $user;
				$this->view->boards .= $view->render('box', 'users');
			}
		}

		$this->view->class_contaner = 'persons';
		
		if($request->isXmlHttpRequest()) {
			echo $this->view->boards;
			$this->noViewRenderer(true);
		} else {
			$this->view->children = array(
	        	'header_part' 	=> 'layout/header_part',
	        	'footer_part' 	=> 'layout/footer_part'
	        );
		}
		
	}
	
	public function followingAction(){
                $request = $this->getRequest();

                $user_data = $this->profileHelp();

                $this->setViewChange('profile');

                $this->view->active = 'following';
        
		$page = (int)$request->getRequest('page');
		if($page < 1) { $page = 1; }
		
		$users = Model_Users::getUsers(array(
			'filter_followers_user_id' => $user_data['user_id'],
//			'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
//			'limit' => JO_Registry::get('config_front_limit')
		));
		
		$this->view->boards = '';
		if($users) {
			$view = JO_View::getInstance();
			$view->loged = JO_Session::get('user[user_id]');
			$model_images = new Helper_Images();
			foreach($users AS $key => $user) {
				
				$user['thumbs'] = array();
				for( $i = 0; $i < 8; $i ++) {
					$image = isset( $user['pins_array'][$i] ) ? $user['pins_array'][$i]['image'] : false;
					if($image) {
						$data_img = call_user_func(array(Helper_Pin::formatUploadModule($user['pins_array'][$i]['store']), 'getPinImage'), $user['pins_array'][$i], '_A');
						if($data_img) {
							$user['thumbs'][] = array(
									'thumb' => $data_img['image'],
									'href' => WM_Router::create($request->getBaseUrl() . '?controller=pin&pin_id=' . $user['pins_array'][$i]['pin_id'] ),
									'title' => $user['pins_array'][$i]['title']
							);
						}
					}
				}
				$avatar = Helper_Uploadimages::avatar($user, '_B');
				$user['avatar'] = $avatar['image'];
	
                                $user['userLikeIgnore'] = true;
				if($view->loged) {
					$user['userIsFollow'] = Model_Users::isFollowUser($user['user_id']);
					$user['userFollowIgnore'] = $user['user_id'] == JO_Session::get('user[user_id]');
				} else {
					$user['userFollowIgnore'] = true;
				}
				
				
				$user['href'] = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $user['user_id']);
				$user['pins_href'] = WM_Router::create($request->getBaseUrl() . '?controller=users&action=pins&user_id=' . $user['user_id']);
				$user['follow'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=follow&user_id=' . $user['user_id'] );
				
				$view->key = $key%2==0;
				$view->user = $user;
				$this->view->boards .= $view->render('box', 'users');
			}
		}
		
		$this->view->class_contaner = 'persons';

		if($request->isXmlHttpRequest()) {
			echo $this->view->boards;
			$this->noViewRenderer(true);
		} else {
			$this->view->children = array(
	        	'header_part' 	=> 'layout/header_part',
	        	'footer_part' 	=> 'layout/footer_part'
	        );
		}
		
	}
	
       	public function likersAction() {
                $request = $this->getRequest();
               
                $user_data = $this->profileHelp();

                $this->setViewChange('profile');

                $this->view->active = 'likers';
        
		$page = (int)$request->getRequest('page');
		if($page < 1) { $page = 1; }
		
		$users = Model_Users::getUsers(array(
			'filter_liking_user_id' => $user_data['user_id'],
//			'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
//			'limit' => JO_Registry::get('config_front_limit')
		));
		
		$this->view->boards = '';
		if($users) {
			$view = JO_View::getInstance();
			$view->loged = JO_Session::get('user[user_id]');
			$model_images = new Helper_Images();
			foreach($users AS $key => $user) {
				
				$user['thumbs'] = array();
				
				for( $i = 0; $i < 8; $i ++) {
					$image = isset( $user['pins_array'][$i] ) ? $user['pins_array'][$i]['image'] : false;
					if($image) {
						//$data_img = Helper_Uploadimages::pin($user['pins_array'][$i], '_A');
                                                $data_img = call_user_func(array(Helper_Pin::formatUploadModule($user['pins_array'][$i]['store']), 'getPinImage'), $user['pins_array'][$i], '_A');
						if($data_img) {
							$user['thumbs'][] = array(
									'thumb' => $data_img['image'],
									'href' => WM_Router::create($request->getBaseUrl() . '?controller=pin&pin_id=' . $user['pins_array'][$i]['pin_id'] ),
									'title' => $user['pins_array'][$i]['title']
							);
						}
					}
				}
				////
				$avatar = Helper_Uploadimages::avatar($user, '_B');
				$user['avatar'] = $avatar['image'];
				
                                $user['userFollowIgnore'] = true;
				if($view->loged) {
					$user['userIsLike'] = Model_Users::isLikeUser($user['user_id']);
					$user['userLikeIgnore'] = $user['user_id'] == JO_Session::get('user[user_id]');
				} else {
					$user['userLikeIgnore'] = true;
				}
				
				$user['href'] = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $user['user_id']);
				$user['pins_href'] = WM_Router::create($request->getBaseUrl() . '?controller=users&action=pins&user_id=' . $user['user_id']);
				$user['likeUser'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=likeUser&user_id=' . $user['user_id'] );
				
				$view->key = $key%2==0;
				$view->user = $user;
				$this->view->boards .= $view->render('box', 'users');
			}
		}

		$this->view->class_contaner = 'persons';

		if($request->isXmlHttpRequest()) {
			echo $this->view->boards;
			$this->noViewRenderer(true);
		} else {
			$this->view->children = array(
	        	'header_part' 	=> 'layout/header_part',
	        	'footer_part' 	=> 'layout/footer_part'
	        );
		}
		
	}
	
	public function likingAction(){
                $request = $this->getRequest();

                $user_data = $this->profileHelp();

                $this->setViewChange('profile');

                $this->view->active = 'liking';
        
		$page = (int)$request->getRequest('page');
		if($page < 1) { $page = 1; }
		
		$users = Model_Users::getUsers(array(
			'filter_likers_user_id' => $user_data['user_id'],
//			'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
//			'limit' => JO_Registry::get('config_front_limit')
		));
		
		$this->view->boards = '';
		if($users) {
			$view = JO_View::getInstance();
			$view->loged = JO_Session::get('user[user_id]');
			$model_images = new Helper_Images();
			foreach($users AS $key => $user) {
				
				$user['thumbs'] = array();
				for( $i = 0; $i < 8; $i ++) {
					$image = isset( $user['pins_array'][$i] ) ? $user['pins_array'][$i]['image'] : false;
					if($image) {
						$data_img = call_user_func(array(Helper_Pin::formatUploadModule($user['pins_array'][$i]['store']), 'getPinImage'), $user['pins_array'][$i], '_A');
						if($data_img) {
							$user['thumbs'][] = array(
									'thumb' => $data_img['image'],
									'href' => WM_Router::create($request->getBaseUrl() . '?controller=pin&pin_id=' . $user['pins_array'][$i]['pin_id'] ),
									'title' => $user['pins_array'][$i]['title']
							);
						}
					}
				}
				$avatar = Helper_Uploadimages::avatar($user, '_B');
				$user['avatar'] = $avatar['image'];
	
                                $user['userFollowIgnore'] = true;
				if($view->loged) {
					$user['userIsLike'] = Model_Users::isLikeUser($user['user_id']);
					$user['userLikeIgnore'] = $user['user_id'] == JO_Session::get('user[user_id]');
				} else {
					$user['userLikeIgnore'] = true;
				}
				
				
				$user['href'] = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $user['user_id']);
				$user['pins_href'] = WM_Router::create($request->getBaseUrl() . '?controller=users&action=pins&user_id=' . $user['user_id']);
				$user['likeUser'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=likeUser&user_id=' . $user['user_id'] );
				
				$view->key = $key%2==0;
				$view->user = $user;
				$this->view->boards .= $view->render('box', 'users');
			}
		}
		
		$this->view->class_contaner = 'persons';

		if($request->isXmlHttpRequest()) {
			echo $this->view->boards;
			$this->noViewRenderer(true);
		} else {
			$this->view->children = array(
	        	'header_part' 	=> 'layout/header_part',
	        	'footer_part' 	=> 'layout/footer_part'
	        );
		}
		
	}
        
        
	public function activityAction(){
                $request = $this->getRequest();

                $user_data = $this->profileHelp();

                $this->setViewChange('profile');

                $this->view->active = 'activity';

		$page = (int)$request->getRequest('page');
		if($page < 1) { $page = 1; }
		
		$this->view->boards = '';
		
		$data = array(
			'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
			'limit' => JO_Registry::get('config_front_limit'),
			'sort' => 'DESC',
			'order' => 'history_id'
		);
		

		$history = Model_History::getHistory($data, 'from_user_id', $user_data['user_id']);
		
		if($history) { 
			$view = JO_View::getInstance();
			$view->loged = JO_Session::get('user[user_id]');
			$model_images = new Helper_Images();
			foreach($history AS $key => $data) {
				if($data['history_action'] == Model_History::REPIN) {
					$pin_data = Model_Pins::getPin($data['pin_id']);
					if($pin_data) {
						$pin_data['history_id'] = $data['history_id'];
						$pin_data['history_action'] = 'repin-box';
						$userdata = Model_Users::getUser($data['to_user_id']);
						$board_href = WM_Router::create($request->getBaseUrl() . '?controller=boards&action=view&user_id=' . $pin_data['user_id'] . '&board_id=' . $pin_data['board_id']);
						$via_href = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $userdata['user_id']);
						$view->set_activity_title = sprintf($this->translate('Repinned to %s via %s.'), '<a href="'.$board_href.'">'.$pin_data['board'].'</a>', '<a href="'.$via_href.'">'.$userdata['fullname'].'</a>');
						$view->date_dif = $data['date_dif'];
						$this->view->boards .= Helper_Pin::returnHtml( $pin_data );
					}
				} elseif($data['history_action'] == Model_History::ADDPIN) {
					$pin_data = Model_Pins::getPin($data['pin_id']);
					if($pin_data) {
						$pin_data['history_id'] = $data['history_id'];
						$pin_data['history_action'] = 'addpin-box';
						$board_href = WM_Router::create($request->getBaseUrl() . '?controller=boards&action=view&user_id=' . $pin_data['user_id'] . '&board_id=' . $pin_data['board_id']);
						$view->set_activity_title = sprintf($this->translate('Pinned to %s.'), '<a href="'.$board_href.'">'.$pin_data['board'].'</a>');
						$view->date_dif = $data['date_dif'];
						$this->view->boards .= Helper_Pin::returnHtml( $pin_data );
					}
				} elseif($data['history_action'] == Model_History::LIKEPIN) {
					$pin_data = Model_Pins::getPin($data['pin_id']);
					if($pin_data) {
						$pin_data['history_id'] = $data['history_id'];
						$pin_data['history_action'] = 'likepin-box';
						$userdata = Model_Users::getUser($pin_data['user_id']);
						$board_href = WM_Router::create($request->getBaseUrl() . '?controller=boards&action=view&user_id=' . $pin_data['user_id'] . '&board_id=' . $pin_data['board_id']);
						$via_href = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $userdata['user_id']);
						$view->set_activity_title = sprintf($this->translate("Liked %s's pin on %s."), '<a href="'.$via_href.'">'.$userdata['fullname'].'</a>', '<a href="'.$board_href.'">'.$pin_data['board'].'</a>');
						$view->date_dif = $data['date_dif'];
						$this->view->boards .= Helper_Pin::returnHtml( $pin_data );
					}
				} elseif($data['history_action'] == Model_History::UNLIKEPIN) {
					$pin_data = Model_Pins::getPin($data['pin_id']);
					if($pin_data) {
						$pin_data['history_id'] = $data['history_id'];
						$pin_data['history_action'] = 'unlikepin-box';
						$userdata = Model_Users::getUser($pin_data['user_id']);
						$board_href = WM_Router::create($request->getBaseUrl() . '?controller=boards&action=view&user_id=' . $pin_data['user_id'] . '&board_id=' . $pin_data['board_id']);
						$via_href = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $userdata['user_id']);
						$view->set_activity_title = sprintf($this->translate("Unliked %s's pin on %s."), '<a href="'.$via_href.'">'.$userdata['fullname'].'</a>', '<a href="'.$board_href.'">'.$pin_data['board'].'</a>');
						$view->date_dif = $data['date_dif'];
						$this->view->boards .= Helper_Pin::returnHtml( $pin_data );
					}
				} elseif($data['history_action'] == Model_History::COMMENTPIN) {
					$pin_data = Model_Pins::getPin($data['pin_id']);
					if($pin_data) {
						$pin_data['history_id'] = $data['history_id'];
						$pin_data['history_action'] = 'commentpin-box';
						$userdata = Model_Users::getUser($pin_data['user_id']);
						$board_href = WM_Router::create($request->getBaseUrl() . '?controller=boards&action=view&user_id=' . $pin_data['user_id'] . '&board_id=' . $pin_data['board_id']);
						$via_href = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $userdata['user_id']);
						$view->set_activity_title = sprintf($this->translate("Commented on %s's pin and said \"%s\"."), '<a href="'.$via_href.'">'.$userdata['fullname'].'</a>', JO_Utf8::splitText($data['comment'], 60, '...'));
						$view->date_dif = $data['date_dif'];
						$this->view->boards .= Helper_Pin::returnHtml( $pin_data );
					}
				} elseif($data['history_action'] == Model_History::ADDBOARD) {
					$board = Model_Boards::getBoard($data['board_id']);
					if($board) {
						$board['href'] = WM_Router::create($request->getBaseUrl() . '?controller=boards&action=view&user_id=' . $user_data['user_id'] . '&board_id=' . $board['board_id']);
						$board['thumbs'] = array();
						/*for( $i = 0; $i < min(9, count($board['pins_array'])); $i ++) {
							$image = isset( $board['pins_array'][$i] ) ? $board['pins_array'][$i]['image'] : false;
							$board['thumbs'][] = $model_images->resize($image, 60, 60, true);
						}*/
						
						$get_big = false;
						for( $i = 0; $i < 5; $i ++) {
							$image = isset( $board['pins_array'][$i] ) ? $board['pins_array'][$i]['image'] : false;
							if($image) {
								if($get_big) {
									$size = '_A';
								} else {
									$size = '_C';
									$get_big = true;
								}
								$data_img = Helper_Uploadimages::pin($board['pins_array'][$i], $size);
								if($data_img) {
									$board['thumbs'][] = $data_img['image'];
								} else {
									$board['thumbs'][] = false;
								}
							} else {
								$board['thumbs'][] = false;
							}
						}
						
						$board['edit'] = false;
						if($board['user_id'] == JO_Session::get('user[user_id]')) {
							$board['edit'] = WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=edit&user_id=' . $user_data['user_id'] . '&board_id=' . $board['board_id'] );
						}
						
						$board['boardIsFollow'] = Model_Users::isFollow(array(
							'board_id' => $board['board_id']
						));
						
						$board['userFollowIgnore'] = $board['user_id'] != JO_Session::get('user[user_id]');
						
						$board['follow'] = WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=follow&user_id=' . $user_data['user_id'] . '&board_id=' . $board['board_id'] );
						$board['history_action'] = 'addboard-box';
						$view->board = $board;
						$view->set_activity_title = $this->translate('Created');
						$this->view->boards .= $view->render('box', 'boards');
					}
					
				} else {
					$data['href'] = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $data['to_user_id']);
				
					$avatar = Helper_Uploadimages::avatar($data['user'], '_B');
					$data['thumb'] = $avatar['image'];
					$data['thumb_width'] = $avatar['width'];
					$data['thumb_height'] = $avatar['height'];
				
					
					if(!@getimagesize($data['thumb'])) {
						$data['thumb'] = $model_images->resize(JO_Registry::get('no_avatar'), 180, 180);
						$data['thumb_width'] = $model_images->getSizes('width');
						$data['thumb_height'] = $model_images->getSizes('height');
					}
					
					
					$view->history = $data;
					
					if($data['history_action'] == Model_History::FOLLOW_USER) {
						$view->history['userIsFollow'] = Model_Users::isFollowUser($view->history['to_user_id']);
						$view->history['follow_user'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=follow&user_id=' . $view->history['to_user_id'] );
                                                $view->history['fullname'] = $data['user']['fullname'];
                                                $view->history['avatar'] = $avatar['image'];
						$this->view->boards .= $view->render('history/follow_user', 'users');
					} elseif($data['history_action'] == Model_History::UNFOLLOW_USER) {
						$view->history['userIsFollow'] = Model_Users::isFollowUser($view->history['to_user_id']);
						$view->history['follow_user'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=follow&user_id=' . $view->history['to_user_id'] );
                                                $view->history['fullname'] = $data['user']['fullname'];
                                                $view->history['avatar'] = $avatar['image'];
						$this->view->boards .= $view->render('history/unfollow_user', 'users');
					} elseif($data['history_action'] == Model_History::FOLLOW) {
						$board_info = Model_Boards::getBoard($data['board_id']);
						if($board_info) {
							$board_info['boardIsFollow'] = Model_Users::isFollow(array(
								'board_id' => $board_info['board_id']
							));
							$board_info['follow'] = WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=follow&user_id=' . $board_info['user_id'] . '&board_id=' . $board_info['board_id'] );
							$board_info['href'] = WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=view&user_id=' . $board_info['user_id'] . '&board_id=' . $board_info['board_id'] );
                                                        $view->history['fullname'] = $data['user']['fullname'];
                                                        $view->history['avatar'] = $avatar['image'];
							$view->history['board'] = $board_info;
							$this->view->boards .= $view->render('history/follow_board', 'users');
						}
					} elseif($data['history_action'] == Model_History::UNFOLLOW) {
						$board_info = Model_Boards::getBoard($data['board_id']);
						if($board_info) {
							$board_info['boardIsFollow'] = Model_Users::isFollow(array(
								'board_id' => $board_info['board_id']
							));
							$board_info['follow'] = WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=follow&user_id=' . $board_info['user_id'] . '&board_id=' . $board_info['board_id'] );
							$board_info['href'] = WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=view&user_id=' . $board_info['user_id'] . '&board_id=' . $board_info['board_id'] );
                                                        $view->history['fullname'] = $data['user']['fullname'];
                                                        $view->history['avatar'] = $avatar['image'];
							$view->history['board'] = $board_info;
							$this->view->boards .= $view->render('history/unfollow_board', 'users');
						}
					} elseif($data['history_action'] == Model_History::LIKEUSER) {
                                                $view->history['fullname'] = $data['user']['fullname'];
                                                $view->history['avatar'] = $avatar['image'];
						$view->history['userIsLike'] = Model_Users::isLikeUser($view->history['to_user_id']);
						$view->history['like_user'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=likeUser&user_id=' . $view->history['to_user_id'] );
						$this->view->boards .= $view->render('history/like_user', 'users');
					} elseif($data['history_action'] == Model_History::UNLIKEUSER) {
                                                $view->history['fullname'] = $data['user']['fullname'];
                                                $view->history['avatar'] = $avatar['image'];
						$view->history['userIsLike'] = Model_Users::isLikeUser($view->history['to_user_id']);
						$view->history['like_user'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=likeUser&user_id=' . $view->history['to_user_id'] );
						$this->view->boards .= $view->render('history/unlike_user', 'users');
					} elseif($data['history_action'] == Model_History::COMMENTUSER) {
						$view->history['href'] = $data['href'];
                                                $view->history['avatar'] = $avatar['image'];
                                                $view->history['fullname'] = $data['user']['fullname'];
                                                $view->history['text_type'] = $data['text_type'];
                                                $view->history['comment'] = $data['comment'];
                                                $view->history['date_added'] = $data['date_added'];
                                                $view->history['value'] = $data['date_dif']['value'];
                                                $view->history['key'] = $data['date_dif']['key'];
                                                $this->view->boards .= $view->render('history/history', 'users');
						//$view->history['comment_user'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=likeUser&user_id=' . $view->history['to_user_id'] );
						//$this->view->boards .= $view->render('history/comment_user', 'users');
					} elseif($data['history_action'] == Model_History::MESSAGEUSER) {
						$view->history['href'] = $data['href'];
                                                $view->history['avatar'] = $avatar['image'];
                                                $view->history['fullname'] = $data['user']['fullname'];
                                                $view->history['text_type'] = $data['text_type'];
                                                $view->history['comment'] = $data['comment'];
                                                $view->history['date_added'] = $data['date_added'];
                                                $view->history['value'] = $data['date_dif']['value'];
                                                $view->history['key'] = $data['date_dif']['key'];
                                                $this->view->boards .= $view->render('history/history', 'users');
						//$view->history['messageUser'] = Model_Users::isLikeUser($view->history['to_user_id']);
						//$view->history['message_user'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=likeUser&user_id=' . $view->history['to_user_id'] );
						//$this->view->boards .= $view->render('history/message_user', 'users');
					} elseif($data['history_action'] == Model_History::UNMESSAGEUSER) {
						$view->history['href'] = $data['href'];
                                                $view->history['avatar'] = $avatar['image'];
                                                $view->history['fullname'] = $data['user']['fullname'];
                                                $view->history['text_type'] = $data['text_type'];
                                                $view->history['comment'] = $data['comment'];
                                                $view->history['date_added'] = $data['date_added'];
                                                $view->history['value'] = $data['date_dif']['value'];
                                                $view->history['key'] = $data['date_dif']['key'];
                                                $this->view->boards .= $view->render('history/history', 'users');
						//$view->history['unmessage_user'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=likeUser&user_id=' . $view->history['to_user_id'] );
						//$this->view->boards .= $view->render('history/unmessage_user', 'users');
					}
				}
				
			}
		}
		
		if($request->isXmlHttpRequest()) {
			echo $this->view->boards;
			$this->noViewRenderer(true);
		} else {
			$this->view->children = array(
	        	'header_part' 	=> 'layout/header_part',
	        	'footer_part' 	=> 'layout/footer_part'
	        );
		}
		
	}
	
	public function pinsAction() {
                $request = $this->getRequest();
                        $user_data = $this->profileHelp();

                $this->setViewChange('profile');

                $this->view->active = 'pins';

		$page = (int)$request->getRequest('page');
		if($page < 1) { $page = 1; }
		
		$this->view->boards = '';
		$data = array(
			'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
			'limit' => JO_Registry::get('config_front_limit'),
			'filter_marker' => $request->getRequest('marker'),
			'filter_user_id' => $user_data['user_id']
		);
		
		if( $request->getQuery('filter') == 'likes' ) {
			unset($data['filter_user_id']);
			$data['filter_likes'] = $user_data['user_id'];
			$this->view->active = 'likes';
		}

		$pins = Model_Pins::getPins($data);
		if($pins) {
			foreach($pins AS $pin) {
				$this->view->boards .= Helper_Pin::returnHtml($pin);
			}
// 			JO_Registry::set('marker', Model_Pins::getMaxPin($data));
		}
		
		if($request->isXmlHttpRequest()) {
			echo $this->view->boards;
			$this->noViewRenderer(true);
		} else {
			$this->view->children = array(
	        	'header_part' 	=> 'layout/header_part',
	        	'footer_part' 	=> 'layout/footer_part'
	        );
		}

	}
	
	public function registerAction() {
		
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
                $this->view->user_type =  array();
                $user_types = Model_Users::getUserType(array(
                        'filter_status' => 1
                ));

                foreach ($user_types as $user_type){
                        $user_type['subuser_types'] = Model_Users::getSubUserType($user_type['user_type_id']);
                        $this->view->user_types[] = $user_type;
                }
                
		if( JO_Session::get('user[user_id]') ) {
			$this->redirect( WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . JO_Session::get('user[user_id]') ) );
		}
		
		$shared_content = Model_Users::checkSharedContent( $request->getParam('key'), $request->getParam('user_id') );
		
		if(!JO_Registry::get('enable_free_registration')) {
			if(!$shared_content) {
				$this->redirect( WM_Router::create( $request->getBaseUrl() . '?controller=landing' ) );
			}
		} else {
			$this->view->fb_register = null;
			$fb_ses = JO_Registry::get('facebookapi');
			$session = $fb_ses->getUser();
			if( JO_Registry::get('oauth_fb_key') && JO_Registry::get('oauth_fb_secret') ) {
				$this->view->fb_register = $this->facebook->getLoginUrl(array(
						'redirect_uri' => WM_Router::create( $request->getBaseUrl() . '?controller=facebook&action=login' ),
						'req_perms' => 'email,user_birthday,status_update,user_videos,user_status,user_photos,offline_access,read_friendlists'
				));
			}
		}
		
		if(JO_Registry::get('oauth_in_key')) {
			$this->view->instagram_register = WM_Router::create($this->getRequest()->getBaseUrl() . '?controller=instagram&action=register&next=' . urlencode( WM_Router::create($request->getBaseUrl() . '?controller=instagram&action=register') ));
		}
		
		$this->view->error = false;
		if($request->isPost()) {
			
			$validate = new Helper_Validate();
			$validate->_set_rules($request->getPost('username'), $this->translate('Username'), 'not_empty;min_length[3];max_length[100];username');
			$validate->_set_rules($request->getPost('firstname'), $this->translate('First name'), 'not_empty;min_length[3];max_length[100]');
			$validate->_set_rules($request->getPost('lastname'), $this->translate('Last name'), 'not_empty;min_length[3];max_length[100]');
			$validate->_set_rules($request->getPost('email'), $this->translate('Email'), 'not_empty;min_length[5];max_length[100];email');
			$validate->_set_rules($request->getPost('password'), $this->translate('Password'), 'not_empty;min_length[4];max_length[30]');
			$validate->_set_rules($request->getPost('password2'), $this->translate('Confirm password'), 'not_empty;min_length[4];max_length[30]');
                        $validate->_set_rules($request->getPost('location'), $this->translate('Location'), 'not_empty;min_length[3];max_length[100]');
                        $validate->_set_rules($request->getPost('sport_category_1'), $this->translate('Category_id1'), 'not_empty;min_length[3];max_length[100]');
                        $validate->_set_rules($request->getPost('sport_category_2'), $this->translate('Category_id2'), 'not_empty;min_length[3];max_length[100]');
                        $validate->_set_rules($request->getPost('sport_category_3'), $this->translate('Category_id3'), 'not_empty;min_length[3];max_length[100]');
                        $validate->_set_rules($request->getPost('type_user'), $this->translate('User_type_id'), 'not_empty;min_length[1];max_length[100]');

			
			if($validate->_valid_form()) {
				if( md5($request->getPost('password')) != md5($request->getPost('password2')) ) {
					$validate->_set_form_errors( $this->translate('Password and Confirm Password should be the same') );
					$validate->_set_valid_form(false);
				}
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
					'username' => $request->getPost('username'),
					'firstname' => $request->getPost('firstname'),
					'lastname' => $request->getPost('lastname'),
					'email' => $request->getPost('email'),
					'password' => $request->getPost('password'),
					'delete_email' => isset($shared_content['email']) ? $shared_content['email'] : '',
					'delete_code' => isset($shared_content['if_id']) ? $shared_content['if_id'] : '',
					'following_user' => isset($shared_content['user_id']) ? $shared_content['user_id'] : '',
					'facebook_id' => isset($shared_content['facebook_id']) ? $shared_content['facebook_id'] : 0,
                                        'location' => $request->getPost('location'),
                                        'sport_category_1' => $request->getPost('sport_category_1'), 
                                        'sport_category_2' => $request->getPost('sport_category_2'), 
                                        'sport_category_3' => $request->getPost('sport_category_3'), 
                                        'type_user' => $request->getPost('type_user'), 
					'confirmed' => '0',
					'regkey'=>$reg_key
				));
				
				if($result) {
					
					if(self::sendMail($result)){
						self::loginInit($result);
					};
					
				} else {
					$this->view->error = $this->translate('There was a problem with the record. Please try again!');
				}
				
			} else {
				$this->view->error = $validate->_get_error_messages();
			}
		}
		

		$this->view->baseUrl = $request->getBaseUrl();
		
		if($request->issetPost('email')) {
			$this->view->email = $request->getPost('email');
		} else {
			if(isset($shared_content['email'])) {
				$this->view->email = $shared_content['email'];
			} else {
				$this->view->email = '';
			}
		}
		
		if($request->issetPost('firstname')) {
			$this->view->firstname = $request->getPost('firstname');
		} else {
			$this->view->firstname = '';
		}
		
		if($request->issetPost('lastname')) {
			$this->view->lastname = $request->getPost('lastname');
		} else {
			$this->view->lastname = '';
		}
		
		if($request->issetPost('username')) {
			$this->view->username = $request->getPost('username');
		} else {
			$this->view->username = '';
		}
		
		$this->view->password = $request->getPost('password');
		$this->view->password2 = $request->getPost('password2');
                
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
                            $this->view->cat_title1 = Model_Boards::getCategoryTitle($request->getPost('sport_category_1'));
                        }
		} else {
			$this->view->sport_category_1 = '';
		}
                $this->view->cat_title2 = '';
                $this->view->sport_category_2 = '';
		if($request->issetPost('sport_category_2')) {
			$this->view->sport_category_2 = $request->getPost('sport_category_2');
                        if ($request->getPost('sport_category_2') != "")
                        {
                            $this->view->cat_title2 = Model_Boards::getCategoryTitle($request->getPost('sport_category_2'));
                        }
		} else {
			$this->view->sport_category_2 = '';
		}
                $this->view->cat_title3 = '';
                $this->view->sport_category_3 = '';
		if($request->issetPost('sport_category_3')) {
			$this->view->sport_category_3 = $request->getPost('sport_category_3');
                        if ($request->getPost('sport_category_3') != "")
                        {
                            $this->view->cat_title3 = Model_Boards::getCategoryTitle($request->getPost('sport_category_3'));
                        }
		} else {
			$this->view->sport_category_3 = '';
		}
                $this->view->usertype_title = '';
                $this->view->type_user = '';
                if($request->issetPost('type_user')) {
                        $this->view->type_user = $request->getPost('type_user');
                        if ($request->getPost('type_user') != "")
                        {
                            $this->view->usertype_title = Model_Users::getUserTypeTitle($request->getPost('type_user'));
                        }
		} else {
			$this->view->type_user = '';
		}
                

                /*
                $this->view->ubication = $request->getPost('ubication');
                $this->view->category_id = $request->getPost('category_id');
                $this->view->type_user = $request->getPost('type_user');
                $this->edit = TRUE;
		*/
		
		
		$this->view->children = array(
        	'header_part' 	=> 'layout/header_part',
        	'footer_part' 	=> 'layout/footer_part'
        );
		
	}
	
	public function addInvateAction(){
		$request = $this->getRequest();
		
		if($request->isXmlHttpRequest()) {
			if(JO_Session::get('user[user_id]')) {
				
				$res = Model_Users::addInvateFacebook($request->getPost('user_id'));
				if($res) {
					echo 'success';
				} else {
					echo $this->translate('There was a problem with the record. Please try again!');
				}
				exit;
				
			} else {
				exit;
			}
		} else {
			$this->forward('error','error404');
		}
		
	}
	
	public function friendsAction(){
		$request = $this->getRequest();
		
		$this->view->users = array();
		
		if((int)JO_Session::get('user[user_id]') && $request->getPost('term')) {
			
			$friends = Model_Users::getUserFriends(array(
				'start' => 0,
				'limit' => 100,
				'filter_username' => $request->getPost('term')
			));
			
			if($friends) {
				$model_images = new Helper_Images();
				foreach($friends AS $friend) {
					if(!isset($friend['store'])) {
						continue;
					}
					$avatar = Helper_Uploadimages::avatar($friend, '_A');
		
					$this->view->users[] = array(
						'image' => $avatar['image'],
						'label' => $friend['fullname'],
						'value' => $friend['user_id'],
						'href' => WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $friend['user_id']),
						'username' => $friend['username']
					);
				}
			}
		}
		
		if($request->isXmlHttpRequest()) {
			echo $this->renderScript('json');
		} else {
			$this->forward('error', 'error404');
		}
		
	}
	
	public function followAction(){
	
		$this->noViewRenderer(true);
		
		$request = $this->getRequest();
		
		if((int)JO_Session::get('user[user_id]')) {
		
			$user_id = $request->getRequest('user_id');
			
			$board_info = Model_Users::getUser($user_id);
			
			if($board_info) {
				
				if($user_id) {
					if(Model_Users::isFollowUser($user_id)) {
						$result = Model_Users::UnFollowUser($user_id);
						if($result) {
							$this->view->ok = $this->translate('Follow');
							$this->view->classs = 'add';
							
							Model_History::addHistory($user_id, Model_History::UNFOLLOW_USER);
						} else {
							$this->view->error = true;
						}
					} else {
						$result = Model_Users::FollowUser($user_id);
						if($result) {
							$this->view->ok = $this->translate('Unfollow');
							$this->view->classs = 'remove';
							
							Model_History::addHistory($user_id, Model_History::FOLLOW_USER);
							
							if($board_info['email_interval'] == 1 && $board_info['follows_email']) {
								$this->view->user_info = $board_info;
								$this->view->profile_href = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . JO_Session::get('user[user_id]'));
								$this->view->full_name = JO_Session::get('user[firstname]') . ' ' . JO_Session::get('user[lastname]');
								$this->view->text_email = $this->translate('now follow you');

								Model_Email::send(
				    	        	$board_info['email'],
				    	        	JO_Registry::get('noreply_mail'),
				    	        	JO_Session::get('user[firstname]') . ' ' . JO_Session::get('user[lastname]') . ' ' . $this->translate('follow your'),
				    	        	$this->view->render('follow_user', 'mail')
				    	        );
							}
							
						} else {
							$this->view->error = true;
						}
					}
				} else {
					$this->view->error = true;
				}
				
			} else {
				$this->view->error = true;
			}
		
		} else {
			$this->view->location = WM_Router::create( $request->getBaseUrl() . '?controller=landing' );
		}
		
		if($request->isXmlHttpRequest()) {
			echo $this->renderScript('json');
		} else {
			$this->redirect( $request->getServer('HTTP_REFERER') );
		}
		
	}
	
        public function likeUserAction(){
	
		$this->noViewRenderer(true);
		
		$request = $this->getRequest();
		
		if((int)JO_Session::get('user[user_id]')) {
		
			$user_id = $request->getRequest('user_id');
			
			$board_info = Model_Users::getUser($user_id);
			
			if($board_info) {
				
				if($user_id) {
					if(Model_Users::isLikeUser($user_id)) {
						$result = Model_Users::UnLikeUser($user_id);
						if($result) {
							$this->view->ok = $this->translate('Like');
							$this->view->classs = 'add';
							
							Model_History::addHistory($user_id, Model_History::UNLIKEUSER);
						} else {
							$this->view->error = true;
						}
					} else {
						$result = Model_Users::LikeUser($user_id);
						if($result) {
							$this->view->ok = $this->translate('Unlike');
							$this->view->classs = 'remove';
							
							Model_History::addHistory($user_id, Model_History::LIKEUSER);
							
							if($board_info['email_interval'] == 1 && $board_info['likes_email']) {
								$this->view->user_info = $board_info;
								$this->view->profile_href = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . JO_Session::get('user[user_id]'));
								$this->view->full_name = JO_Session::get('user[firstname]') . ' ' . JO_Session::get('user[lastname]');
								$this->view->text_email = $this->translate('now like you');

								Model_Email::send(
				    	        	$board_info['email'],
				    	        	JO_Registry::get('noreply_mail'),
				    	        	JO_Session::get('user[firstname]') . ' ' . JO_Session::get('user[lastname]') . ' ' . $this->translate('like you'),
				    	        	$this->view->render('like_user', 'mail')
				    	        );
							}
							
						} else {
							$this->view->error = true;
						}
					}
				} else {
					$this->view->error = true;
				}
				
			} else {
				$this->view->error = true;
			}
		
		} else {
			$this->view->location = WM_Router::create( $request->getBaseUrl() . '?controller=landing' );
		}
		
		if($request->isXmlHttpRequest()) {
			echo $this->renderScript('json');
		} else {
			$this->redirect( $request->getServer('HTTP_REFERER') );
		}
		
	}
        
	public function loginAction() {
		
		$request = $this->getRequest();
		
		
		if($request->getQuery('verify')) {
			if( Model_Users::verifyEmailCheck($request->getQuery('verify'), $request->getParam('user_id')) ) {
    			JO_Session::set('successful', $this->translate('You verifying your email. Now you can access with the data from e-mail!'));
            	$this->redirect( WM_Router::create( $request->getBaseUrl() . '?controller=users&action=login' ) );
    		} else {
    			$this->view->error = $this->translate('There was a problem with the record. Please try again!');
    		}
		} else {
			if( JO_Session::get('user[user_id]') ) {
				$this->redirect( WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . JO_Session::get('user[user_id]') ) );
			}	
		}
		
		$this->view->successful = false;
		if( JO_Session::get('successful')) {
    		$this->view->successful = JO_Session::get('successful');
    		JO_Session::clear('successful'); 
    	}
		
    	$this->view->error = false;
    	
    	if( $request->getParam('user_id') && $request->getQuery('key') ) {
    		if( Model_Users::forgotPasswordCheck($request->getQuery('key'), $request->getParam('user_id')) ) {
    			JO_Session::set('successful', $this->translate('You verifying forgotten password. Now you can access with the data from e-mail!'));
            	$this->redirect( WM_Router::create( $request->getBaseUrl() . '?controller=users&action=login' ) );
    		} else {
    			$this->view->error = $this->translate('There was a problem with the record. Please try again!');
    		}
    	}
    	
		$referer = $request->getServer('HTTP_REFERER');
		$this->view->next = urlencode($request->getBaseUrl());
		if($referer) {
			$data = parse_url($referer);
			if(isset($data['host'])) {
				if( str_replace('www.', '', $data['host']) == $request->getDomain() ) {
					$this->view->next = urlencode($referer);
				}
			}
		}
		if($request->issetPost('next')) {
			$this->view->next = html_entity_decode($request->getPost('next'));
		} elseif($request->getQuery('popup') == 'true' && $request->issetQuery('next')) {
			$this->view->next = urlencode(html_entity_decode($request->getQuery('next')));
		}
		
		
		$this->view->is_forgot_password = (int)$request->getPost('forgot_password');
		
		if( $request->isPost() && $request->issetPost('login') ) {
			
			$validate = new Helper_Validate();
			$validate->_set_rules($request->getPost('email'), $this->translate('Email Address'), 'not_empty;min_length[5];max_length[100];email');
			if( $request->getPost('forgot_password') != 1 ) {
				$validate->_set_rules($request->getPost('password'), $this->translate('Password'), 'not_empty;min_length[4];max_length[30]');
			}

			if($validate->_valid_form()) {
				
				if( $request->getPost('forgot_password') == 1 ) {
					$result = Model_Users::forgotPassword($request->getPost('email'));
					if($result) {
						if($result['status']) {
							$new_password = Model_Users::generatePassword(8);
							
							$key_forgot = md5($result['user_id'] . md5($new_password));
							
							$add_new_pass = Model_Users::edit($result['user_id'], array(
								'new_password' => $new_password,
								'new_password_key' => $key_forgot
							));
							
							if($add_new_pass) {
								
								$this->view->new_password = $new_password;
				    			$this->view->user_info = $result;
				    			$this->view->forgot_password_href = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=login&user_id='.$result['user_id'].'&key=' . $key_forgot );
				    			$this->view->header_title = JO_Registry::get('site_name');
				    			$this->view->base_href = WM_Router::create( $request->getBaseUrl());
								
				    	        $result_send = Model_Email::send(
				    	        	$result['email'], 
				    	        	JO_Registry::get('noreply_mail'), 
				    	        	$this->translate('Request for forgotten password') . ' ' . JO_Registry::get('site_name'),
				    	        	$this->view->render('send_forgot_password_request', 'mail'));
				    	        
				    	        if($result_send) {
				    	        	JO_Session::set('successful', $this->translate('Was sent the e-mail with instructions for the new password!'));
				    	        	$this->redirect( WM_Router::create( $request->getBaseUrl() . '?controller=users&action=login' ) );
				    	        } else {
				    	        	$this->view->error = $this->translate('There was an error. Please try again later!');
				    	        }
				    	        
							} else {
								$this->view->error = $this->translate('There was a problem with the record. Please try again!');
							}
							
						} else {
							$this->view->error = $this->translate('This profile is not active.');
						}
					} else {
						$this->view->error = $this->translate('E-mail address was not found!');
					}
				} else {
					$result = Model_Users::checkLogin($request->getPost('email'), $request->getPost('password'));
					if($result) {
						if($result['status']) {
							@setcookie('csrftoken_', md5($result['user_id'] . $request->getDomain() . $result['date_added'] ), (time() + ((86400*366)*5)), '/', '.'.$request->getDomain());
							
							JO_Session::set(array('user' => $result));
							$this->redirect( urldecode($this->view->next) );
						} else {
							$this->view->error = $this->translate('This profile is not active.');
						}
					} else {
						$this->view->error = $this->translate('E-mail address and password do not match');
					}
				}
				
			} else {
				$this->view->error = $validate->_get_error_messages();
			}
		}
		
		$this->view->login_facebook = WM_Router::create( $request->getBaseUrl() . '?controller=facebook&next=' . $this->view->next );
		$this->view->login_twitter = WM_Router::create( $request->getBaseUrl() . '?controller=twitter&next=' . $this->view->next );
		$this->view->login_instagram = WM_Router::create( $request->getBaseUrl() . '?controller=instagram&next=' . $this->view->next );
		$this->view->login_login = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=login' );
		
		if($request->getQuery('popup') == 'true') {
			
			$this->view->site_name = JO_Registry::get('site_name');
			$this->view->meta_title = JO_Registry::get('meta_title');
			
			$this->view->popup = true;
			
			$this->view->baseUrl = $request->getBaseUrl();
			$this->view->site_logo = $request->getBaseUrl() . 'data/images/logo.png';
			if(JO_Registry::get('site_logo') && file_exists(BASE_PATH .'/uploads'.JO_Registry::get('site_logo'))) {
			    $this->view->site_logo = $request->getBaseUrl() . 'uploads' . JO_Registry::get('site_logo'); 
			}
			
			$this->setViewChange('loginPopup');
			
			$this->view->children = array(
	        	'header_part' 	=> 'layout/header_part',
	        	'footer_part' 	=> 'layout/footer_part'
	        );
			
		} else {
			
			$this->view->loginPopup = $this->view->render('loginPopup', 'users');
			
			$this->view->children = array(
	        	'header_part' 	=> 'layout/header_part',
	        	'footer_part' 	=> 'layout/footer_part'
	        );
		}
	}
	
	public function resendAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			if(JO_Session::get('user[user_id]')) {
				$user_data = JO_Session::get('user');
				$this->view->verify_email_href = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=login&user_id='.JO_Session::get('user[user_id]').'&verify=' . $user_data['new_email_key'] );
				$this->view->user_info = $user_data;
				$result = Model_Email::send(
    	        	$user_data['new_email'],
    	        	JO_Registry::get('noreply_mail'),
    	        	$this->translate('Please verify your email'),
    	        	$this->view->render('verify_email', 'mail')
    	        );
    	        if($result) {
    	        	$this->view->ok = $this->translate('Thanks! You should receive a verification email soon.');
    	        } else {
    	        	$this->view->error = $this->translate('There was a problem with the record. Please try again!');
    	        }
			} else {
				$this->view->redirect = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=login' );
			}
			echo $this->renderScript('json');
		} else {
			$this->forward('error', 'error404');
		}
	}
	
	public function logoutAction(){
		
//		$session = unserialize(JO_Session::get('user[facebook_session]'));
		
		@setcookie('csrftoken_', md5(JO_Session::get('user[user_id]') . $this->getRequest()->getDomain() . JO_Session::get('user[date_added]') ), (time() - 100 ), '/', '.'.$this->getRequest()->getDomain());
		
		JO_Session::set('user', array());
		
		$url_logout = $this->getRequest()->getBaseUrl();
//		if($session) {
//			$url_logout = 'https://www.facebook.com/logout.php?next='.urlencode($this->getRequest()->getServer('HTTP_REFERER')).'&access_token=' . $session['access_token'];
//		}
		
		$this->redirect( $url_logout );
	}
	
	public function deleteAction() {
		if(JO_Session::get('user[user_id]')) {
			Model_Users::edit(JO_Session::get('user[user_id]'), array(
				'delete_account' => '1',
				'delete_account_date' => date('Y-m-d H:i:s')
			));
			Model_Email::send(
            	JO_Session::get('user[email]'),
            	JO_Registry::get('noreply_mail'),
            	$this->translate('Delete Account Request'),
            	$this->view->render('delete_account', 'mail')
            );
		}
		$this->redirect( WM_Router::create( $this->getRequest()->getBaseUrl() , '?controller=settings' ) );
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
	
	

	
	
}

?>