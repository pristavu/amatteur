<?php

class EventsController extends JO_Action {
	
	/**
	 * @var WM_Facebook
	 */
	protected $facebook;
	
	private function loginInit($id) {
		$event_data = WM_Users::initSession($id);
		if($event_data) { 
			JO_Session::set(array('user' => $event_data));
		}
		$this->redirect( WM_Router::create( $this->getRequest()->getBaseUrl() ) );
	}
	
	public function init() {
		$this->facebook = JO_Registry::get('facebookapi');
	}
	
	public function indexAction() {
		
		$request = $this->getRequest();
		
                
                
                
                $this->view->register_url = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=register' );
                $this->view->search_url = WM_Router::create( $request->getBaseUrl() . '?controller=events&action=search' );
                $this->view->addEvent_url = WM_Router::create( $request->getBaseUrl() . '?controller=events&action=add' );
                
		$event_data = Model_Users::getUser( JO_Session::get('user[user_id]') );
		
		$upload = new JO_Upload_SessionStore();
		$upload->setName('upload_avatar');
		$info = $upload->getFileInfo();
		
		if(JO_Session::get('successfu_edite')) {
                    $this->view->successfu_edite = true;
                    JO_Session::clear('successfu_edite'); 
                }
		
        
                $this->view->user_data = $event_data;
		
		
		$this->view->children = array(
        	'header_part' 	=> 'layout/header_part',
        	'footer_part' 	=> 'layout/footer_part'
        );
	}
	
	public function addAction(){
		
            $request = $this->getRequest();
                
                
            //////////// Categories ////////////
            $this->view->categories = array();
            $categories = Model_Categories::getCategories(array(
                        'filter_status' => 1
                    ));

            foreach ($categories as $category)
            {
                $category['subcategories'] = Model_Categories::getSubcategories($category['category_id']);
                $this->view->categories[] = $category;
            }

            $this->view->user_id = JO_Session::get('user[user_id]');           
            
            $event_id = $request->getRequest('event_id');
            
            $this->view->edited = $event_id;
            
            $event_data = "";
            if ($event_id)
            {
            
                $event_data = Model_Events::getEvent(array( 'filter_event_id' => $event_id));

                foreach($event_data AS $k=>$v) {
                        if(isset($event_data[$k])) {
                                $event_data[$k] = $v;
                        }
                }
                
                $event_data['date_event'] = Model_Events::cambiafyh_espanol($event_data['date_event']);
                
                if ($event_data['user_id'] != "") 
                {
                    $this->view->owner = (JO_Session::get('user[user_id]') == $event_data['user_id']);  
                }

            }
            else
            {
                $this->view->owner = true;
            }

            
            $this->view->eventname = '';
            if($request->issetPost('eventname')) {
                    $this->view->eventname = $request->getPost('eventname');
            }       
            else if ($event_data)
            {
                    if ($event_data['eventname'] != "") 
                    {
                        $this->view->eventname  = $event_data['eventname'];
                    }
            }

            $this->view->organiza = '';
            if($request->issetPost('organiza')) {
                    $this->view->organiza = $request->getPost('organiza');
            }       
            else if ($event_data)
            {
                    if ($event_data['organiza'] != "") 
                    {
                        $this->view->organiza  = $event_data['organiza'];
                    }
            }

            $this->view->date_event = '';
            if($request->issetPost('date_event')) {
                    $this->view->date_event = $request->getPost('date_event');
            }       
            else if ($event_data)
            {
                    if ($event_data['date_event'] != "") 
                    {
                        $this->view->date_event  = $event_data['date_event'];
                    }
            }

            $this->view->website = '';
            if($request->issetPost('website')) {
                    $this->view->website = $request->getPost('website');
            }       
            else if ($event_data)
            {
                    if ($event_data['website'] != "") 
                    {
                        $this->view->website  = $event_data['website'];
                    }
            }

            $this->view->website = '';
            if($request->issetPost('website')) {
                    $this->view->website = $request->getPost('website');
            }       
            else if ($event_data)
            {
                    if ($event_data['website'] != "") 
                    {
                        $this->view->website  = $event_data['website'];
                    }
            }

            $this->view->description = '';
            if($request->issetPost('description')) {
                    $this->view->description = $request->getPost('description');
            }       
            else if ($event_data)
            {
                    if ($event_data['description'] != "") 
                    {
                        $this->view->description  = $event_data['description'];
                    }
            }

            $this->view->compartir = '';
            if($request->issetPost('compartir')) {
                    $this->view->compartir = $request->getPost('compartir');
            }       
            else if ($event_data)
            {
                    if (isset($event_data['compartir'])) 
                    {
                        $this->view->compartir  = $event_data['compartir'];
                    }
            }

            
            $this->view->cancel = '';
            if($request->issetPost('delete_event')) {
                    $this->view->cancel = $request->getPost('delete_event');
            }       
            else if ($event_data)
            {
                    if (isset($event_data['delete_event'])) 
                    {
                        $this->view->cancel  = $event_data['delete_event'];
                    }
            }
            
            $this->view->cancelReason = '';
            if($request->issetPost('delete_reason')) {
                    $this->view->cancelReason = $request->getPost('delete_reason');
            }       
            else if ($event_data)
            {
                    if (isset($event_data['delete_reason'])) 
                    {
                        $this->view->cancelReason  = $event_data['delete_reason'];
                    }
            }
            
            //////////// User location ////////////
            $this->view->location = '';
            if($request->issetPost('location')) {
                    $this->view->location = $request->getPost('location');
            }
            else if ($event_data)
            {
                    if ($event_data['location'] != "") 
                    {
                        $this->view->location  = $event_data['location'];
                    }
            }
            
            $this->view->cat_title = '';
            $this->view->sport_category = '';
            if($request->issetPost('sport_category')) {
                if ($request->getPost('sport_category') != "")
                {
                    $this->view->sport_category = $request->getPost('sport_category');
                    if ($request->getPost('sport_category') != "")
                    {
                        if ($request->getPost('sport_category') == 1)
                        {
                            $this->view->cat_title = "Todo";
                        }
                        else
                        {
                            $this->view->cat_title = Model_Boards::getCategoryTitle($request->getPost('sport_category'));
                        }
                    }
                }
                else if ($event_data)
                {
                        if ($event_data['sport_category'] != "") 
                        {
                            $this->view->sport_category = $event_data['sport_category'];
                            if ($event_data['sport_category'] == 1)
                            {
                                $this->view->cat_title = "Todo";
                            }
                            else
                            {
                                $this->view->cat_title = Model_Boards::getCategoryTitle($event_data['sport_category']);
                            }
                        }
                }                
            } 
            else if ($event_data)
            {
                    if ($event_data['sport_category'] != "") 
                    {
                        $this->view->sport_category = $event_data['sport_category'];
                        if ($event_data['sport_category'] == 1)
                        {
                            $this->view->cat_title = "Todo";
                        }
                        else
                        {
                            $this->view->cat_title = Model_Boards::getCategoryTitle($event_data['sport_category']);
                        }
                    }
            }
             
            
            $upload = new JO_Upload_SessionStore();
            $upload->setName('upload_avatar');
            $info = $upload->getFileInfo();
            
		if(JO_Session::get('successfu_edite')) {
                    $this->view->successfu_edite = true;
                    JO_Session::clear('successfu_edite'); 
                }
            
            
		if( $request->isPost() ) {

                        $validate = new Helper_Validate();
                        $validate->_set_rules($request->getPost('user_id'), $this->translate('user_id del evento'), 'not_empty;min_length[3];max_length[100];username');
			$validate->_set_rules($request->getPost('eventname'), $this->translate('Nombre del evento'), 'not_empty;min_length[3];max_length[100]');
			$validate->_set_rules($request->getPost('organiza'), $this->translate('Organiza'), 'not_empty;min_length[3];max_length[100]');
			$validate->_set_rules($request->getPost('date_event'), $this->translate('Fecha y Hora'), 'not_empty;min_length[5];max_length[100]');
                        $validate->_set_rules($request->getPost('location'), $this->translate('Location'), 'not_empty;min_length[3];max_length[100]');                                        
                        $validate->_set_rules($request->getPost('description'), $this->translate('Detalles'), 'not_empty;min_length[3];max_length[200]');
                        $validate->_set_rules($request->getPost('sport_category'), $this->translate('Category_id1'), 'not_empty;min_length[3];max_length[100]');

			
			$data = $request->getPost();
		
			if($validate->_valid_form()) {
                            
				/*if( Model_Events::isExistEventname($request->getPost('eventname')) ) {
					$validate->_set_form_errors( $this->translate('El nombre del evento ya se ha utilizado') );
					$validate->_set_valid_form(false);
				}*/
			}
			
			if($validate->_valid_form()) {
				
				$data['dont_search_index'] = (int)$request->issetPost('dont_search_index');
				$data['facebook_timeline'] = (int)$request->issetPost('facebook_timeline');
				
				if($info) {
					if(!@file_exists(BASE_PATH . '/cache/event/') || !is_dir(BASE_PATH . '/cache/event/')) {
						mkdir(BASE_PATH . '/cache/event/');
					}
					$filename = BASE_PATH . '/cache/event/' . md5(mt_rand().time()) . $upload->get_extension($info['name']);
					if( file_put_contents( $filename, $info['data'] ) ) {
						$data['avatar'] = $filename;
					}
				}
				
				
                                $lat = $data['lat'];
                                $len = $data['len'];

                                while(Model_Events::getEventsLatLen($lat,$len))
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
                                
				if(Model_Events::createEvent( JO_Session::get('user[user_id]'), $event_id, $data )) 
                                {
                                    //JO_Session::set('successfu_edite', true);
                                    $upload->getFileInfo(true);

                                    $event_data = Model_Events::getEvent(array( 'filter_event_id' => $event_id));

                                    foreach($event_data AS $k=>$v) {
                                            if(isset($event_data[$k])) {
                                                    $event_data[$k] = $v;
                                            }
                                    }

                                    if ($this->view->cancel)
                                    {
                                        self::sendMail($event_id);
                                    }
                                    
                                    $this->view->successfu_edite = true;
                                    $this->view->user_events = WM_Router::create($request->getBaseUrl() . '?controller=users&action=events&user_id=' . JO_Session::get('user[user_id]'));

					//$this->redirect( WM_Router::create( $request->getBaseUrl() . '?controller=events' ) );
				} else {
					$this->view->error = $this->translate('There was a problem with the record. Please try again!');
				}
			} else {
				$this->view->error = $validate->_get_error_messages();
			}
			
		}             
            /*
            if($info) {
                    $this->view->avatar = WM_Router::create( $request->getBaseUrl() . '?controller=events&action=temporary_avatar&s=' . microtime(true) );
                    $this->view->has_avatar = true;
            } else {
                */
                if ($event_data)
                {
                    $avatar = Helper_Uploadimages::avatar($event_data, '_B');
                    $this->view->avatar = $avatar['image'] . '?s=' . microtime(true);
                    $this->view->has_avatar = @getimagesize($event_data['avatar']) ? true : false;
                }
            //}
            $this->view->form_action = WM_Router::create( $request->getBaseUrl() . '?controller=events&action=upload_avatar' );
            $this->view->search_json = WM_Router::create( $request->getBaseUrl() . '?controller=events&action=search2' );
            


            
		
            $this->view->children = array(
                'header_part' 	=> 'layout/header_part',
                'footer_part' 	=> 'layout/footer_part'
            );
	}
	
        private function sendMail($eventId)
        {
            //$this->noViewRenderer(true);
            //$this->noLayout(true);
            $dataEvents = array(
                'filter_event_id' => $eventId
            );

            $events = Model_Events::getEvents($dataEvents);
            if ($events)
            {
                foreach ($events AS $key => $event)
                {
                    $dataFollowEvents = array(
                        'filter_event_id' => $eventId
                    );
                    
                    $followEvents = Model_Events::getFollowers($dataFollowEvents);
                    if ($followEvents)
                    {
                        foreach ($followEvents AS $key => $followEvent)
                        {

                            $user = Model_Users::getUser($followEvent['user_id']);

                            $body = "Hola " . $user['username'] . "! <br /> El evento " . $event['eventname'] . " ha sido cancelado por la razón: " . $event['delete_reason'] . ".<br/>";
                            //var_dump($user);
                            $to = $user['email'];
                            $from = JO_Registry::forceGet('noreply_mail');
                            $title = "amatteur - Evento " . $event['eventname'] . " cancelado ";


                            if (Model_Email::send($to, $from, $title, $body))
                            {
                                //$this->redirect(WM_Router::create(JO_Request::getInstance()->getBaseUrl()."?controller=users&action=verificationRequired"));
                                //return true;
                            }
                        }
                    }
                }
            }

        }
        
        public function search2Action(){
            
            $request = $this->getRequest();



            $page = (int) $request->getRequest('page');
            if ($page < 1)
            {
                $page = 1;
            }

                $dataEvents = array(
                    'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                    'limit' => JO_Registry::get('config_front_limit'),
                    'filter_eventname' => $request->getPost('eventname'),
                    'filter_organiza' => $request->getPost('organiza'),
                    'filter_location' => $request->getPost('location'),
                    'filter_sport_category' => $request->getPost('sport_category'),
                    'filter_event_date' => $request->getPost('date_event'),
                    'filter_compartir' => $request->getPost('compartir'),
                    'filter_delete_event' => '1'
                );

                $events = Model_Events::getEvents($dataEvents);

                $numeroEventos = 0;
                if ($events)
                {
                    $this->view->events = "<div id='boxero'>";
                    foreach ($events AS $key => $event)
                    {
                        $href = "";
                        $view = JO_View::getInstance();
                        $view->loged = JO_Session::get('user[user_id]');
                        $model_images = new Helper_Images();

                        $avatar = Helper_Uploadimages::avatar($event, '_B');
                        $event['avatar'] = $avatar['image'];

                        $event["sport_category"] = Model_Boards::getCategoryTitle($event["sport_category"]);
                        
                        $event['date_event'] = Model_Events::cambiafyh_espanol($event['date_event']);
                        
                        $data = array(
                            'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                            'limit' => JO_Registry::get('config_front_limit'),
                            'filter_user_id' => $event["user_id"]
                        );

                        $users = Model_Users::getUsers($data);
                        if ($users)
                        {
                            $event['fullname'] = $users[0]["fullname"];
                            $event['href'] = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $event['user_id']);
                            $href = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $event['user_id']);
                        }
                        
                        //$view->boxeventdetail = WM_Router::create($request->getBaseUrl() . '?controller=events&action=boxeventdetail&event_id=' . $event['event_id']);
                        $view->boxeventdetail = WM_Router::create($request->getBaseUrl() . '?controller=events&action=indexeventBoxDetail&event_id=' . $event['event_id']);
                        
                        $view->event = $event;
                        $this->view->events .= $view->render('boxEvent', 'events');
                        
                        $numeroEventos++;
                    }
                    $this->view->events .= "</div>";
                    $this->view->search_add = true;
                    $this->view->eventos = $events;
                    $this->view->class_contaner = 'persons';
                    $this->view->numeroEventos = $numeroEventos;
                }
         

            if ($request->isXmlHttpRequest())
            {
                echo $this->renderScript('json');
            } else
            {
                $this->forward('error', 'error404');
            }
                
        }
        
        public function searchAction(){
            
            $request = $this->getRequest();
            
            $this->view->search_action = WM_Router::create( $request->getBaseUrl() . '?controller=events&action=search' );            

            $event_data = Model_Users::getUser( JO_Session::get('user[user_id]') );

            $this->view->user_data = $event_data;
            
            
        //////////// Categories ////////////
        $this->view->categories = array();
        $categories = Model_Categories::getCategories(array(
                    'filter_status' => 1
                ));

        foreach ($categories as $category)
        {
            $category['subcategories'] = Model_Categories::getSubcategories($category['category_id']);
            $this->view->categories[] = $category;
        }

        if ($request->issetPost('eventname'))
        {
            $this->view->eventname = $request->getPost('eventname');
        } else
        {
            $this->view->eventname = '';
        }

        if ($request->issetPost('organiza'))
        {
            $this->view->organiza = $request->getPost('organiza');
        } else
        {
            $this->view->organiza = '';
        }
        
        //date_event
        if ($request->issetPost('date_event1'))
        {
            $this->view->date_event1 = $request->getRequest('date_event1');
        } elseif (isset($user_data['date_event1']))
        {
            $this->view->date_event1 = $user_data['date_event1'];
        } else
        {
            $this->view->date_event1 = "";
        }

        //date_event
        if ($request->issetPost('date_event2'))
        {
            $this->view->date_event2 = $request->getRequest('date_event2');
        } elseif (isset($user_data['date_event2']))
        {
            $this->view->date_event2 = $user_data['date_event2'];
        } else
        {
            $this->view->date_event2 = "";
        }

        //location		
        if ($request->issetPost('location'))
        {
            $this->view->location = $request->getPost('location');
        } elseif (isset($user_data['location']))
        {
            $this->view->location = $user_data['location'];
        } else
        {
            $this->view->location = '';
        }

        //sport category
        if ($request->issetPost('sport_category'))
        {
            $this->view->sport_category = $request->getPost('sport_category');
            if ($request->getPost('sport_category') != "")
            {
                $this->view->cat_title = Model_Boards::getCategoryTitle($request->getPost('sport_category'));
            }
        } elseif (isset($user_data['sport_category']))
        {
            $this->view->sport_category = $user_data['sport_category'];
            $this->view->cat_title = Model_Boards::getCategoryTitle($user_data['sport_category']);
        } else
        {
            $this->view->cat_title = '';
            $this->view->sport_category = '';
        }

        //compartir		
        if ($request->issetPost('compartir'))
        {
            $this->view->compartir = $request->getPost('compartir');
        } elseif (isset($user_data['compartir']))
        {
            $this->view->compartir = $user_data['compartir'];
        } else
        {
            $this->view->compartir = '';
        }


            $page = (int) $request->getRequest('page');
            if ($page < 1)
            {
                $page = 1;
            }
            $this->view->error = "";
            
            if( $request->isPost() ) {
                
                $this->view->all = true;                                    
                
                $dataEvents = array(
                    'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                    'limit' => JO_Registry::get('config_front_limit'),
                    'filter_eventname' => $request->getPost('eventname'),
                    'filter_organiza' => $request->getPost('organiza'),
                    'filter_location' => $request->getPost('location'),
                    'filter_sport_category' => $request->getPost('sport_category'),
                    'filter_event_date1' => $request->getPost('date_event1'),
                    'filter_event_date2' => $request->getPost('date_event2'),
                    'filter_compartir' => $request->getPost('compartir'),
                    'filter_delete_event' => '1'
                );

                $events = Model_Events::getEvents($dataEvents);

                if ($events)
                {
                    $i=0;
                    foreach ($events AS $key => $event)
                    {
                        $this->view->all = false;
                        $href = "";
                        $view = JO_View::getInstance();
                        $view->loged = JO_Session::get('user[user_id]');
                        $model_images = new Helper_Images();

                        $avatar = Helper_Uploadimages::avatar($event, '_B');
                        $event['avatar'] = $avatar['image'];

                        $event["sport_category"] = Model_Boards::getCategoryTitle($event["sport_category"]);
                        
                        $event['date_event'] = Model_Events::cambiafyh_espanol($event['date_event']);
                        
                        $event['href'] = WM_Router::create($request->getBaseUrl() . '?controller=events&action=indexeventBoxDetail&event_id=' . $event['event_id']);
                        $href = WM_Router::create($request->getBaseUrl() . '?controller=events&action=indexeventBoxDetail&event_id=' . $event['event_id']);
                        
                        $data = array(
                            'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                            'limit' => JO_Registry::get('config_front_limit'),
                            'filter_user_id' => $event["user_id"]
                        );

                        $users = Model_Users::getUsers($data);
                        if ($users)
                        {
                            $event['fullname'] = $users[0]["fullname"];
                            $event['hrefuser'] = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $event['user_id']);
                            //$href = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $event['user_id']);
                        }
                        
                        //$view->boxeventdetail = WM_Router::create($request->getBaseUrl() . '?controller=events&action=boxeventdetail&event_id=' . $event['event_id']);
                        $view->boxeventdetail = WM_Router::create($request->getBaseUrl() . '?controller=events&action=indexeventBoxDetail&event_id=' . $event['event_id']);
                        $this->view->from_url = WM_Router::create( $request->getBaseUrl() . '?controller=addpin&action=fromurl' );
                        
                        $this->view->successfu_edite = false;
                        
                        $view->event = $event;
                        $this->view->eventsBox .= $view->render('boxEvent', 'events');
                        
                        //$events[$i]["href"] = $href;
                        //$eventsTot[] = $events[$i];
                        $eventsTot[] = $event;
                        $i++;
                    }

                    $this->view->eventos = $eventsTot;
                    $this->view->class_contaner = 'persons';
                }
                else
                {
                    $this->view->error = $this->translate('La búsqueda no ha devuelto resultados');
                }
            }
            
            else
            {
                $this->view->all = true;
                /*
                
                $this->view->all = false;

                $dataEvents = array(
                    'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                    'limit' => JO_Registry::get('config_front_limit')
                );

                $events = Model_Events::getEvents($dataEvents);

                if ($events)
                {
                    $i=0;
                    foreach ($events AS $key => $event)
                    {
                        $this->view->all = true;                        
                        $href = "";
                        $view = JO_View::getInstance();
                        $view->loged = JO_Session::get('user[user_id]');
                        $model_images = new Helper_Images();

                        $avatar = Helper_Uploadimages::avatar($event, '_B');
                        $event['avatar'] = $avatar['image'];

                        $event["sport_category"] = Model_Boards::getCategoryTitle($event["sport_category"]);
                        
                        $event['date_event'] = Model_Events::cambiafyh_espanol($event['date_event']);                        
                        
                        $event['href'] = WM_Router::create($request->getBaseUrl() . '?controller=events&action=indexeventBoxDetail&event_id=' . $event['event_id']);
                        $href = WM_Router::create($request->getBaseUrl() . '?controller=events&action=indexeventBoxDetail&event_id=' . $event['event_id']);
                        
                        $data = array(
                            'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                            'limit' => JO_Registry::get('config_front_limit'),
                            'filter_user_id' => $event["user_id"]
                        );

                        $users = Model_Users::getUsers($data);
                        if ($users)
                        {
                            $event['fullname'] = $users[0]["fullname"];
                            $event['hrefuser'] = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $event['user_id']);
                            //$href = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $event['user_id']);
                        }
                        
                        //$view->boxeventdetail = WM_Router::create($request->getBaseUrl() . '?controller=events&action=boxeventdetail&event_id=' . $event['event_id']);
                        $view->boxeventdetail = WM_Router::create($request->getBaseUrl() . '?controller=events&action=indexeventBoxDetail&event_id=' . $event['event_id']);
                        
                        $view->event = $event;
                        $this->view->eventsBox .= $view->render('boxEvent', 'events');
                        
                        //$events[$i]["href"] = $href;
                        //$eventsTot[] = $events[$i];
                        $eventsTot[] = $event;
                        $i++;
                    }

                        
                    $this->view->successfu_edite = false;                    
                    $this->view->eventos = $eventsTot;
                    $this->view->class_contaner = 'persons';
                }
                else
                {
                    $this->view->error = $this->translate('La búsqueda no ha devuelto resultados');
                }
                 * 
                 */
            }

            $this->view->children = array(
                'header_part' => 'layout/header_part',
                'footer_part' => 'layout/footer_part'
            );
            
      
        }  
        
        
	public function indexeventBoxDetailAction() {
//		var_dump( htmlspecialchars('⚐') );exit;
		$request = $this->getRequest();
		
                $page = (int) $request->getRequest('page');
                if ($page < 1)
                {
                    $page = 1;
                }

		
                $dataEvents = array(
                    'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                    'limit' => JO_Registry::get('config_front_limit'),
                    'filter_event_id' => $request->getRequest('event_id')
                );

                $events = Model_Events::getEvent($dataEvents);

		if(!$events) {
			$this->forward('error', 'error404');
		}
                
                 if ($request->getRequest('comment'))
                 {
                     $this->view->comment = true;
                 }
                
                if ($events)
                {
                    //foreach ($events AS $key => $event)
                    {
                        $event_id = $events['event_id'];
                        $user_id = $events['user_id'];
                        
                        $href = "";
                        $view = JO_View::getInstance();
                        $view->loged = JO_Session::get('user[user_id]');
                        $model_images = new Helper_Images();

                        $avatar = Helper_Uploadimages::avatar($events, '_D');
                        $events['thumb'] = $avatar['image'];
                        //$events['avatar'] = $avatar['image'];
			$events['popup'] = $avatar['image'];
			$events['popup_width'] = $avatar['width'];
			$events['popup_height'] = $avatar['height'];
			$events['original_image'] = $avatar['original'];
                        
                        $events['date_event'] = Model_Events::cambiafyh_espanol($events['date_event']);
                        

                        //$events["sport_category"] = Model_Boards::getCategoryTitle($events["sport_category"]);
                        
                        $data = array(
                            'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                            'limit' => JO_Registry::get('config_front_limit'),
                            'filter_user_id' => $events["user_id"]
                        );

                        $users = Model_Users::getUsers($data);
                        if ($users)
                        {
                            $events['fullname'] = $users[0]["fullname"];
                            $events['descriptionUser'] = $users[0]["description"];
                            $avataruser = Helper_Uploadimages::avatar($users[0], '_B');
                            $events['avataruser'] = $avataruser['image'];
                           
                            
                            $events['href'] = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $events['user_id']);
                            $href = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $events['user_id']);
                        }
                        
                        
                        if(JO_Session::get('user[user_id]')) {
                                $events['url_like'] = WM_Router::create( $request->getBaseUrl() . '?controller=events&action=like&event_id=' . $event_id );
                                $events['url_repin'] = WM_Router::create( $request->getBaseUrl() . '?controller=events&action=repin&event_id=' . $event_id );
                                $events['url_comment'] = WM_Router::create( $request->getBaseUrl() . '?controller=events&action=comment&event_id=' . $event_id );
                                $events['comment'] = WM_Router::create( $request->getBaseUrl() . '?controller=events&action=comment&event_id=' . $event_id );
                                $events['edit'] = JO_Session::get('user[user_id]') == $user_id ? WM_Router::create( $request->getBaseUrl() . '?controller=events&action=events&event_id=' . $event_id ) : false;
                        } else {
                                $events['url_like'] = $events['url_repin'] = $events['url_comment'] = $events['comment'] = WM_Router::create( $request->getBaseUrl() . '?controller=landing' );
                                $events['edit'] = false;
                        }
                        
                        
                        $view->event = $events;
                        //$this->view->events .= $view->render('boxEventDetail', 'events');
                        //$this->view->events .= $view->render('pinboxdetail', 'events');
                        
                    }
                    $this->view->eventos = $events;
                    $this->view->class_contaner = 'persons';
                }
                
                
		if($request->isPost()) {
			
			$data = $request->getParams();
			$write_comment = $request->getPost('write_comment');
			if(JO_Session::get('user[user_id]') && $request->issetPost('friends') && is_array($request->getPost('friends'))) {
				foreach($request->getPost('friends') AS $user_id => $fullname) {
					if( Model_Users::isFriendUser($user_id, JO_Session::get('user[user_id]')) ) {
						$profile = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $user_id );
						$write_comment = preg_replace('/\@'.$fullname.'/i',' <a class="link comment-user-profile" href="'.$profile.'">@'.$fullname.'</a> ',$write_comment);
					}
				}
			}
			$data['write_comment'] = $write_comment;
			
			if($request->isXmlHttpRequest()) {
				if(JO_Session::get('user[user_id]')) {
					$result = Model_Events::addComment($data, $events['latest_comments'], Model_Users::$allowed_fields);
					$this->view = JO_View::getInstance()->reset();
					if($result) {
						$avatar = Helper_Uploadimages::avatar($result['user'], '_A');
						$result['user']['avatar'] = $avatar['image'];
						$result['user']['profile'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $result['user_id'] );
						
						$this->view->ok = true;
						//$result['pin'] = self::getPinStat($event_id);
						
						if( JO_Session::get('user[user_id]') ) {
							if( JO_Session::get('user[is_admin]') || JO_Session::get('user[user_id]') == $result['user_id'] ) {
								$result['delete_comment'] = WM_Router::create( $request->getBaseUrl() . '?controller=events&action=deleteComment&comment_id=' . $result['comment_id'] );
							}
						}
						
                                                /*
						if($request) {
							Model_History::addHistory($event_info['user_id'], Model_History::COMMENTPIN, $event_id, 0, $request->getPost('write_comment'));
						
							if($event_info['user']['email_interval'] == 1 && $event_info['user']['comments_email']) {
								$this->view->user_info = $event_info['user'];
								$this->view->text_email = $this->translate('comment your');
								$this->view->profile_href = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . JO_Session::get('user[user_id]'));
								$this->view->full_name = JO_Session::get('user[firstname]') . ' ' . JO_Session::get('user[lastname]');
								$this->view->event_href = WM_Router::create($request->getBaseUrl() . '?controller=pin&pin_id=' . $event_id );
								Model_Email::send(
				    	        	$event_info['user']['email'],
				    	        	JO_Registry::get('noreply_mail'),
				    	        	JO_Session::get('user[firstname]') . ' ' . JO_Session::get('user[lastname]') . ' ' . $this->translate('comment your pin'),
				    	        	$this->view->render('comment_pin', 'mail')
				    	        );
							}
							
						}
						*/
						$this->view->comment = $result;
					} else {
						$this->view->error = $this->translate('There was a problem with the record. Please try again!');
					}
				} else {
					$this->view->location = WM_Router::create( $request->getBaseUrl() . '?controller=landing' );
				}
				echo $this->renderScript('json');
				exit;
			} else {
				if(JO_Session::get('user[user_id]')) {
					$result = Model_Events::addComment($data, $event_info['latest_comments']);
					$this->redirect(WM_Router::create( $request->getBaseUrl() . '?controller=events&action=indexeventBoxDetail&event_id=' . $event_id ));
				} else {
					$this->redirect(WM_Router::create( $request->getBaseUrl() . '?controller=landing' ));
				}
			}
		}
		
		
		if(!$request->isXmlHttpRequest() && JO_Session::get('user[user_id]')) {
			$history = Model_Events::getTotalFollow(array(
				'sort' => 'ASC',
				'order' => 'date_added',
                                'filter_event_id' => $event_id
			));
			$model_images = new Helper_Images();
			foreach($history AS $key => $data) {
				if(!isset($data['user']['store'])) {
					continue;
				}
				$avatar = Helper_Uploadimages::avatar($data['user'], '_A');
				$history[$key]['user']['avatar'] = $avatar['image'];

                                $history[$key]['href'] = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $data['user_id']);
			}
			$this->view->history = $history;
		}
		
                
                
		$this->view->show_buttonswrapper = true;

                $this->view->url_like = WM_Router::create( $request->getBaseUrl() . '?controller=events&action=like&event_id=' . $event_id );
		$this->view->url_tweet = WM_Router::create( $request->getBaseUrl() . '?controller=events&action=eventboxdetail&event_id=' . $event_id );
		$this->view->url_embed = WM_Router::create( $request->getBaseUrl() . '?controller=events&action=embed&event_id=' . $event_id );
		$this->view->url_report = WM_Router::create( $request->getBaseUrl() . '?controller=events&action=report&event_id=' . $event_id );
		$this->view->url_email = WM_Router::create( $request->getBaseUrl() . '?controller=events&action=email&event_id=' . $event_id );
		$this->view->url_comment = WM_Router::create( $request->getBaseUrl() . '?controller=events&action=comment&event_id=' . $event_id );

                $view->event_url = WM_Router::create( $request->getBaseUrl() . '?controller=events&event_id=' . $event_id );
		
		//$view->login_href = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=login&next=' . urlencode($event['href']) );
                
                $view->like_event = WM_Router::create( $request->getBaseUrl() . '?controller=events&action=like&event_id=' . $event_id . '&userio_id=' . $user_id); 
                if (JO_Session::get('user[user_id]'))
                {
                    $view->eventIsLike = Model_Events::isLikeEvent($event_id, JO_Session::get('user[user_id]'));
                }
                
                $view->editEvent_url = WM_Router::create( $request->getBaseUrl() . '?controller=events&action=add?event_id=' . $event_id );
                
                $view->follow_event = WM_Router::create($request->getBaseUrl() . '?controller=events&action=follow&event_id=' . $event_id . '&userio_id=' . $user_id); 
                if (JO_Session::get('user[user_id]'))
                {
                    $view->eventIsFollow = Model_Events::isFollowEvent($event_id, JO_Session::get('user[user_id]'));
                }


                
		
		$banners = Model_Banners::getBanners(
			new JO_Db_Expr("`controller` = '".$request->getController()."'")
		);
		
		if($request->isXmlHttpRequest()) {
			$this->view->popup = true;
			echo Helper_Externallinks::fixExternallinks(Helper_Events::returnHtmlDetail($events, $banners));
			$this->noViewRenderer(true);
		} else {
			$this->view->events_details = Helper_Events::returnHtmlDetail($events, $banners);
			JO_Registry::set('events_info', $events);
			
			$this->view->children = array(
	        	'header_part' 	=> 'layout/header_part',
	        	'footer_part' 	=> 'layout/footer_part',
			'left_part'	=> 'events/left_part'
	        );
		}
		
	}        
        
       
	public function upload_avatarAction(){
		
		$request = $this->getRequest();
		
		$upload = new JO_Upload_SessionStore($request->getFile('file'));
		$upload->setName('upload_avatar');
		if( $upload->upload(true) ) {
			$info = $upload->getFileInfo();
			$this->view->success = WM_Router::create( $request->getBaseUrl() . '?controller=events&action=temporary_avatar&hash=' . microtime(true) );//'data:'.$info['type'].';base64,'.base64_encode($info['data']);
		} else {
			$this->view->error = $upload->getError();
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
		
		$event_data = Model_Users::getUser($request->getRequest('user_id'));
		if($event_data) {
			
			JO_Registry::set('meta_title', $event_data['fullname'] . ' - ' . JO_Registry::get('meta_title'));
	
			$events = Model_Events::getEvents($data);
					
			$this->view->item = array();
			if($events) {
				$model_images = new Helper_Images();
				foreach($events AS $event) {
					$data_img = Helper_Uploadimages::event($event, '_D');
					if(!$data_img) {
						continue;
					}
					$enclosure = $data_img['image'];
			
					$category_info = Model_Categories::getCategory($event['category_id']);
					if($category_info) {
						$event['sport_category'] = $category_info['title'] . ' >> ' . $event['sport_category'];
					}
			
					$this->view->item[] = array(
							'guid' => $event['event_id'],
							'enclosure' => $enclosure,
							'description' => Helper_Pin::descriptionFix($event['description']),
							'title' => Helper_Pin::descriptionFix(JO_Utf8::splitText($event['description'], 60, '...')),
							'link' => WM_Router::create( $request->getBaseUrl() . '?controller=events&action=indexeventBoxDetail&event_id=' . $event['event_id'] ),
							'author' => $event['user']['fullname'],
							'pubDate' => WM_Date::format($event['date_added'], JO_Date::RSS_FULL),
							'category' => $event['sport_category']
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
	
	public function left_partAction(){
		$request = $this->getRequest();
		
		$this->view->pin = JO_Registry::get('events_info');
		
//		$this->view->onto_board = Helper_Pin::getBoardPins( JO_Registry::getArray('pin_info[board_id]'), 9, 60 );
		/*
		$boards = Model_Boards::getBoards(array(
			'start' => 0,
			'limit' => 1,
//			'filter_user_id' => $user_data['user_id']
			'filter_id_in' => JO_Registry::getArray('pin_info[board_id]')
		));
		
		$this->view->has_edit_boards = true;
		$this->view->enable_sort = true;
		
		$this->view->onto_board = '';
		if($boards) {
			$view = JO_View::getInstance();
			$view->loged = JO_Session::get('user[user_id]');
			$view->enable_sort = false;
			$model_images = new Helper_Images();
			foreach($boards AS $board) {
				$board['href'] = WM_Router::create($request->getBaseUrl() . '?controller=boards&action=view&user_id=' . $board['user_id'] . '&board_id=' . $board['board_id']);
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
				
				$board['follow'] = WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=follow&user_id=' . $board['user_id'] . '&board_id=' . $board['board_id'] );
				
				$board['edit'] = false;
				if($board['user_id'] == JO_Session::get('user[user_id]')) {
					$board['edit'] = WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=edit&user_id=' . $board['user_id'] . '&board_id=' . $board['board_id'] );
				}
				
				$view->board = $board;
				$this->view->onto_board .= $view->render('box', 'boards');
			}
		}
		
		$this->view->source = Model_Source::getSource(JO_Registry::getArray('pin_info[source_id]'));
		
		if($this->view->source) {
			$this->view->source_pins = Helper_Pin::getSourcePins(JO_Registry::getArray('pin_info[source_id]'), 6, 75);
			$this->view->pin['from'] = WM_Router::create($request->getBaseUrl() . '?controller=source&source_id=' . $this->view->pin['source_id']);
		} else if(JO_Registry::getArray('pin_info[repin_from]')) {
			$event_repin = Model_Pins::getPin(JO_Registry::getArray('pin_info[repin_from]'));
			if($event_repin) {
				$this->view->source['source'] = $event_repin['board'];
				$this->view->pin['from'] = WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=view&user_id=' . $event_repin['user_id'] . '&board_id=' . $event_repin['board_id'] );
				$this->view->source_pins = Helper_Pin::getBoardPins( $event_repin['board_id'], 9, 75 );
			}
		}
		
		$this->view->boardIsFollow = Model_Users::isFollow(array(
			'board_id' => JO_Registry::getArray('pin_info[board_id]')
		));
		
		$this->view->follow = WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=follow&user_id=' . $this->view->pin['user_id'] . '&board_id=' . $this->view->pin['board_id'] );
		*/
		$this->view->loged = JO_Session::get('user[user_id]');
		
		$this->view->pin['userFollowIgnore'] =  JO_Session::get('user[user_id]');//($this->view->pin['via'] ? $this->view->pin['via'] : $this->view->pin['user_id']) == JO_Session::get('user[user_id]');
		
//		var_dump($this->view->onto_board);
		
		JO_Registry::set('events_info', array());
	}
        
        
	public function reportCommentAction(){
		$request = $this->getRequest();
		$comment_id = $request->getRequest('comment_id');
		$comment_info = Model_Events::getComment($comment_id);
		
		if(!$comment_info) {
			$this->forward('error', 'error404');
		}
		
		
		$this->view->reportcategories = Model_Events::getEventReportCategories();
		
		$this->view->url_form = WM_Router::create( $request->getBaseUrl() . '?controller=events&action=reportComment&comment_id=' . $comment_id );
		$this->view->comment_id = $comment_id;
	
		$this->view->event_href = WM_Router::create( $request->getBaseUrl() . '?controller=events&event_id=' . $comment_info['event_id'] );
		
		if($request->issetPost('report_category')) {
			$this->view->report_category = $request->getPost('report_category');
		} else {
			if($this->view->reportcategories) {
				list($firstKey) = array_keys($this->view->reportcategories);
				$this->view->report_category = $firstKey;
			} else {
				$this->view->report_category = 0;
			}
		}
		
		$this->view->comment_is = true;
		
		$this->view->events_details = $this->view->render('report','events');
		
		if($request->isPost()) {
			$this->view->is_posted = true;
			
			if(Model_Events::commentIsReported($comment_id)) {
				$this->view->error = $this->translate('¡Ya has denunciado este comentario!');
				$this->view->events_details = $this->view->render('report','events');
			} else {
			
				$result = Model_Events::reportComment( $comment_id, $request->getPost('report_category'), $request->getPost('report_message') );
				if(!$result) {
					$this->view->error = $this->translate('Error reporting experience. Try again!');
					$this->view->events_details = $this->view->render('report','events');
				} else {
    				if(JO_Registry::get('not_rc')) {
    		    			Model_Email::send(
    				    	  	JO_Registry::get('report_mail'),
    				    	 	JO_Registry::get('noreply_mail'),
    				    	   	$this->translate('New reported comment'),
    				    	  	$this->translate('Hello, there is new reported comment in ').' '.JO_Registry::get('site_name')
    				    	 );
		    			}
					$terms = Model_Pages::getPage( JO_Registry::get('page_terms') );
					if($terms) {
						$this->view->terms = $terms['title'];
					}
					
					$this->view->pin_oppener = $request->getRequest('pin_oppener');
					$this->view->terms_href = WM_Router::create( $request->getBaseUrl() . '?controller=about&action=terms' );
					
					$this->view->events_details = $this->view->render('message_report','events');
				}
			
			}
		}
		
		
		
		$this->setViewChange('index');
		if($request->isXmlHttpRequest()) {
			$this->view->popup = true;
			echo $this->view->events_details;
			$this->noViewRenderer(true);
		} else {
			$this->view->children = array(
	        	'header_part' 	=> 'layout/header_part',
	        	'footer_part' 	=> 'layout/footer_part',
	        	'left_part' 	=> 'layout/left_part'
	        );
		}
	}
	
	public function deleteCommentAction() {
		$request = $this->getRequest();
		$comment_id = $request->getRequest('comment_id');
		$comment_info = Model_Events::getComment($comment_id);
		if($comment_info) {
			$event = Model_Events::getEvent($comment_info['event_id']);
			if($comment_info['user_id'] == JO_Session::get('user[user_id]') || JO_Session::get('user[is_admin]') ) {
				if(Model_Events::deleteComment($comment_id)) {
					$this->view->ok = true;
					$this->view->stats = self::getEventStat($comment_info['event_id']);
				} else {
					$this->view->error = $this->translate('An error occurred while deleting. Please try again');
				}
			} else {
				$this->view->error = $this->translate('You are not authorized to delete this comment');
			}
		} else {
			$this->view->error = $this->translate('Comment not found');
		}
		
		if($request->isXmlHttpRequest()) {
			echo $this->renderScript('json');
		} else {
			$this->redirect( $request->getServer('HTTP_REFERER') );
		}
	}	
        
	private function getEventStat($event_id) {
		$result = Model_Events::getEvent($event_id, Model_Users::$allowed_fields);
		if(!$result) {
			return false;
		}
		
		$request = $this->getRequest();
		
		$result['stats'] = array();
                $result['stats']['likes'] = '';
                $result['stats']['comments'] = '';
                $result['stats']['repins'] = '';
		return $result;
	}
        
	public function reportAction() {
		
		$request = $this->getRequest();
		
		$event_id = $request->getRequest('event_id');
		
		$event_info = Model_Events::getEventSolo($event_id);
		
		if(!$event_info) {
			$this->forward('error', 'error404');
		}
		
		$this->view->reportcategories = Model_Events::getEventReportCategories();
		
		$this->view->url_form = WM_Router::create( $request->getBaseUrl() . '?controller=events&action=report&event_id=' . $event_id );
		$this->view->intellectual_property = WM_Router::create( $request->getBaseUrl() . '?controller=about&action=copyright&event_id=' . $event_id );
		$this->view->event_id = $event_id;
	
		$this->view->event_href = WM_Router::create( $request->getBaseUrl() . '?controller=events&action=indexeventBoxDetail?event_id=' . $event_id );
		
		if($request->issetPost('report_category')) {
			$this->view->report_category = $request->getPost('report_category');
		} else {
			if($this->view->reportcategories) {
				list($firstKey) = array_keys($this->view->reportcategories);
				$this->view->report_category = $firstKey;
			} else {
				$this->view->report_category = 0;
			}
		}
		
		$this->view->events_details = $this->view->render('report','events');
		
		if($request->isPost()) {
			$this->view->is_posted = true;
			
			if(Model_Events::eventIsReported($request->getRequest('event_id'))) {
				$this->view->error = $this->translate('Ya has denunciado este evento!');
				$this->view->events_details = $this->view->render('report','events');
			} else {
			
				$result = Model_Events::reportEvent( $request->getRequest('event_id'), $request->getPost('report_category'), $request->getPost('report_message') );
				if(!$result) {
					$this->view->error = $this->translate('Error denunciando el evento. Intentalo de nuevo!');
					$this->view->events_details = $this->view->render('report','events');
				} else {
				    if(JO_Registry::get('not_rp')) {
    		    			Model_Email::send(
    				    	  	JO_Registry::get('report_mail'),
    				    	 	JO_Registry::get('noreply_mail'),
    				    	   	$this->translate('Nuevo evento denunciado'),
    				    	  	$this->translate('Hola, existe un nuevo evento denunciado en ').' '.JO_Registry::get('site_name')
    				    	 );
		    			}
					$terms = Model_Pages::getPage( JO_Registry::get('page_terms') );
					if($terms) {
						$this->view->terms = $terms['title'];
					}
					
					$this->view->pin_oppener = $request->getRequest('pin_oppener');
					$this->view->terms_href = WM_Router::create( $request->getBaseUrl() . '?controller=about&action=terms' );
					
					$this->view->events_details = $this->view->render('message_report','events');
				}
			
			}
		}
		
		
		$this->setViewChange('indexeventBoxDetail');
		if($request->isXmlHttpRequest()) {
			$this->view->popup = true;
			echo $this->view->events_details;
			$this->noViewRenderer(true);
		} else {
			$this->view->children = array(
	        	'header_part' 	=> 'layout/header_part',
	        	'footer_part' 	=> 'layout/footer_part',
	        	'left_part' 	=> 'layout/left_part'
	        );
		}
	}

        public function totalLikeAction() {
		$request = $this->getRequest();
                
                $event_id = $request->getRequest('event_id');
	
                if($request->isXmlHttpRequest() && JO_Session::get('user[user_id]')) {
			$history = Model_Events::getTotalLike(array(
				'sort' => 'ASC',
				'order' => 'date_added',
                                'filter_event_id' => $event_id
			));
			$model_images = new Helper_Images();
			foreach($history AS $key => $data) {
				if(!isset($data['user']['store'])) {
					continue;
				}
				$avatar = Helper_Uploadimages::avatar($data['user'], '_A');
				$history[$key]['user']['avatar'] = $avatar['image'];

                                $history[$key]['href'] = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $data['user_id']);
			}
			$this->view->history = $history;
		}
		if($request->isXmlHttpRequest()) {
			echo $this->renderScript('json');
		} else {
			$this->redirect( $request->getServer('HTTP_REFERER') );
		}
	}	
        
        
        public function totalFollowAction() {
		$request = $this->getRequest();
                
                $event_id = $request->getRequest('event_id');
	
                if($request->isXmlHttpRequest() && JO_Session::get('user[user_id]')) {
			$history = Model_Events::getTotalFollow(array(
				'sort' => 'ASC',
				'order' => 'date_added',
                                'filter_event_id' => $event_id
			));
			$model_images = new Helper_Images();
			foreach($history AS $key => $data) {
				if(!isset($data['user']['store'])) {
					continue;
				}
				$avatar = Helper_Uploadimages::avatar($data['user'], '_A');
				$history[$key]['user']['avatar'] = $avatar['image'];

                                $history[$key]['href'] = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $data['user_id']);
			}
			$this->view->history = $history;
		}
		if($request->isXmlHttpRequest()) {
			echo $this->renderScript('json');
		} else {
			$this->redirect( $request->getServer('HTTP_REFERER') );
		}
	}	
        
	public function embedAction() {
		
		$request = $this->getRequest();
		
		$event_id = $request->getRequest('event_id');
		
		$event = Model_Events::getEventSolo($event_id);
		
		if(!$event) {
			$this->forward('error', 'error404');
		}
		
		$image = Helper_Uploadimages::event($event, '_B');
		$image2 = Helper_Uploadimages::event($event, '_D');
		if($image && $image2) {
			$event['thumb'] = $image2['image'];
			$event['thumb_width'] = $image['width'];
			$event['thumb_height'] = $image['height'];
			$event['original'] = $image['original'];
		} else {
			$event['thumb'] = '';
			$event['thumb_width'] = 0;
			$event['thumb_height'] = 0;
		}

		
		$event['href'] = WM_Router::create( $request->getBaseUrl() . '?controller=events&action=indexeventBoxDetail&event_id=' . $event['event_id'] );
		$event['onto_href'] = WM_Router::create( $request->getBaseUrl() );
		$event['onto_title'] = JO_Registry::get('site_name');
		$event['profile'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $event['user_id'] );
		
		$this->view->event = $event;
		
		$this->view->events_details = $this->view->render('embed','events');
		$this->setViewChange('indexeventBoxDetail');
		if($request->isXmlHttpRequest()) {
			$this->view->popup = true;
			echo $this->view->events_details;
			$this->noViewRenderer(true);
		} else {
			$this->view->children = array(
	        	'header_part' 	=> 'layout/header_part',
	        	'footer_part' 	=> 'layout/footer_part',
	        	'left_part' 	=> 'layout/left_part'
	        );
		}
	}
	
        public function emailAction() {
		
		$request = $this->getRequest();
		
		$event_id = $request->getRequest('event_id');
		
		$event_info = Model_Events::getEventSolo($event_id);
		
		if(!$event_info) {
			$this->forward('error', 'error404');
		}
		
		$this->view->event_id = $event_id;
	
		$this->view->event_href = WM_Router::create( $request->getBaseUrl() . '?controller=events&action=indexeventBoxDetail?event_id=' . $event_id );
		$this->view->url_form = WM_Router::create( $request->getBaseUrl() . '?controller=events&action=email&event_id=' . $event_id );
		
		
		if($request->issetPost('name')) {
			$this->view->Recipient_name = $request->getPost('name');
		} else {
			$this->view->Recipient_name = $this->translate('Recipient Name');
		}
		if($request->issetPost('email')) {
			$this->view->Recipient_email = $request->getPost('email');
		} else {
			$this->view->Recipient_email = $this->translate('Recipient Email');
		}
		if($request->issetPost('message')) {
			$this->view->Recipient_message = $request->getPost('message');
		} else {
			$this->view->Recipient_message = $this->translate('Message');
		}
		
		$this->view->events_details = $this->view->render('email','events');
		
		
		$this->view->error = '';
		if($request->isPost()) {
			
			$validate = new Helper_Validate(); 
			
			$validate->_set_rules($request->getPost('name'), $this->translate('Recipient Name'), 'not_empty;min_length[3];max_length[100]');
			$validate->_set_rules($request->getPost('email'), $this->translate('Recipient Email'), 'not_empty;min_length[5];max_length[100];email');
//			$validate->_set_rules($request->getPost('message'), $this->translate('Message'), 'not_empty;min_length[15]');
			
			if($validate->_valid_form()) {
			
				$this->view->is_posted = true;
				
				
    			$shared_content = Model_Users::sharedContent($request->getPost('email'));
    			if( $shared_content != -1 ) {
    				$this->view->shared_content = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=register&user_id=' . JO_Session::get('user[user_id]') . '&key=' . $shared_content);
    			}
    			
    			$this->view->event_info = $event_info;
    			$this->view->self_profile = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . JO_Session::get('user[user_id]') );
                        $this->view->self_fullname = JO_Session::get('user[firstname]') . ' ' . JO_Session::get('user[lastname]');
    			$this->view->self_firstname = JO_Session::get('user[firstname]');
    			$this->view->header_title = JO_Registry::get('site_name');
    			
    	        $result = Model_Email::send(
    	        	$request->getPost('email'),
    	        	JO_Registry::get('noreply_mail'),
    	        	$this->translate('Shared content from') . ' ' . JO_Session::get('user[firstname]') . ' ' . JO_Session::get('user[lastname]'),
    	        	$this->view->render('send_event', 'mail')
    	        );
    	        
    	        if($result) {
                                $this->view->events_details = $this->view->render('message_email','events');
    			} else {
    				$this->view->error = $this->translate('There was an error. Please try again later!');
    			}
				
			
			} else {
				$this->view->error = $validate->_get_error_messages();
			}
			
			$this->view->pin_oppener = $request->getPost('pin_oppener');
			
		}
		
		if($this->view->error) {
			$this->view->events_details = $this->view->render('email','events');
		}
		
		
		$this->setViewChange('indexeventBoxDetail');
		if($request->isXmlHttpRequest()) {
			$this->view->popup = true;
			echo $this->view->events_details;
			$this->noViewRenderer(true);
		} else {
			$this->view->children = array(
	        	'header_part' 	=> 'layout/header_part',
	        	'footer_part' 	=> 'layout/footer_part',
	        	'left_part' 	=> 'layout/left_part'
	        );
		}
	}
        
    public function followAction()
    {

        $this->noViewRenderer(true);

        $request = $this->getRequest();

        if ((int) JO_Session::get('user[user_id]'))
        {

            $history_user_id = $request->getRequest('userio_id');
            $user_id = JO_Session::get('user[user_id]');
            $event_id = $request->getRequest('event_id');


                if ($user_id)
                {
                    if (Model_Events::isFollowEvent($event_id, $user_id))
                    {
                        $result = Model_Events::UnFollowEvent($event_id, $user_id);
                        if ($result)
                        {
                            $this->view->ok = $this->translate('Me apunto!');
                            $this->view->classs = 'add';

                            Model_History::addHistory($history_user_id, Model_History::UNFOLLOW_EVENT, $event_id);
                        } else
                        {
                            $this->view->error = true;
                        }
                    } 
                    else
                    {
                        $result = Model_Events::FollowEvent($event_id, $user_id);
                        if ($result)
                        {
                            $this->view->ok = $this->translate('Ya no me apunto');
                            $this->view->classs = 'remove';

                            Model_History::addHistory($history_user_id, Model_History::FOLLOW_EVENT, $event_id);

                            /*
                            if ($board_info['email_interval'] == 1 && $board_info['follows_email'])
                            {
                                $this->view->user_info = $board_info;
                                $this->view->profile_href = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . JO_Session::get('user[user_id]'));
                                $this->view->full_name = JO_Session::get('user[firstname]') . ' ' . JO_Session::get('user[lastname]');
                                $this->view->text_email = $this->translate('now follow you');

                                Model_Email::send(
                                        $board_info['email'], JO_Registry::get('noreply_mail'), JO_Session::get('user[firstname]') . ' ' . JO_Session::get('user[lastname]') . ' ' . $this->translate('follow your'), $this->view->render('follow_user', 'mail')
                                );
                            }
                             * 
                             */
                        } else
                        {
                            $this->view->error = true;
                        }
                    }
                } else
                {
                    $this->view->error = true;
                }
        } else
        {
            $this->view->location = WM_Router::create($request->getBaseUrl() . '?controller=landing');
        }

        if ($request->isXmlHttpRequest())
        {
            echo $this->renderScript('json');
        } else
        {
            $this->redirect($request->getServer('HTTP_REFERER'));
        }
    }
        
    public function likeAction()
    {

        $this->noViewRenderer(true);

        $request = $this->getRequest();

        if ((int) JO_Session::get('user[user_id]'))
        {

            $history_user_id = $request->getRequest('userio_id');
            $user_id = JO_Session::get('user[user_id]');
            $event_id = $request->getRequest('event_id');


                if ($user_id)
                {
                    if (Model_Events::isLikeEvent($event_id, $user_id))
                    {
                        $result = Model_Events::UnLikeEvent($event_id, $user_id);
                        if ($result)
                        {
                            $this->view->ok = $this->translate('Me gusta');
                            $this->view->classs = 'add';

                            Model_History::addHistory($history_user_id, Model_History::UNLIKE_EVENT, $event_id);
                        } else
                        {
                            $this->view->error = true;
                        }
                    } 
                    else
                    {
                        $result = Model_Events::LikeEvent($event_id, $user_id);
                        if ($result)
                        {
                            $this->view->ok = $this->translate('No me gusta');
                            $this->view->classs = 'remove';

                            Model_History::addHistory($history_user_id, Model_History::LIKE_EVENT, $event_id);

                            /*
                            if ($board_info['email_interval'] == 1 && $board_info['follows_email'])
                            {
                                $this->view->user_info = $board_info;
                                $this->view->profile_href = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . JO_Session::get('user[user_id]'));
                                $this->view->full_name = JO_Session::get('user[firstname]') . ' ' . JO_Session::get('user[lastname]');
                                $this->view->text_email = $this->translate('now follow you');

                                Model_Email::send(
                                        $board_info['email'], JO_Registry::get('noreply_mail'), JO_Session::get('user[firstname]') . ' ' . JO_Session::get('user[lastname]') . ' ' . $this->translate('follow your'), $this->view->render('follow_user', 'mail')
                                );
                            }
                             * 
                             */
                        } else
                        {
                            $this->view->error = true;
                        }
                    }
                } else
                {
                    $this->view->error = true;
                }
        } else
        {
            $this->view->location = WM_Router::create($request->getBaseUrl() . '?controller=landing');
        }

        if ($request->isXmlHttpRequest())
        {
            echo $this->renderScript('json');
        } else
        {
            $this->redirect($request->getServer('HTTP_REFERER'));
        }
    }
    
    public function cambiafyh_espanol($fechaH)
    {
        $traducir_fecha = explode("-",$fechaH);
        $separaHoras=explode(" ",$traducir_fecha[2]);
        $fecha_espana = $separaHoras[0]."/".$traducir_fecha[1]."/".$traducir_fecha[0]." ".$separaHoras[1]; 
        return $fecha_espana;
    }
    public function cambiafyh_espanolAFecha($fechaH)
    {
        $traducir_fecha = explode("-",$fechaH);
        $separaHoras=explode(" ",$traducir_fecha[2]);
        $fecha_espana = $separaHoras[0]."/".$traducir_fecha[1]."/".$traducir_fecha[0]; 
        return $fecha_espana;
    }
    public function cambiaf_a_mysql($fecha)
    { 
        $fecha_espana = $fecha; 
        $traducir_fecha = explode("/",$fecha_espana); 
        $fecha_mysql = $traducir_fecha[2]."-".$traducir_fecha[1]."-".$traducir_fecha[0]; 
        return $fecha_mysql;
    } 
    public function cambiafyh_a_mysql($fecha)
    { 
        $fecha_espana = $fecha; 
        $separaHora=explode(" ",$fecha_espana);
        $traducir_fecha = explode("/",$separaHora[0]); 
        $fecha_mysql = $traducir_fecha[2]."-".$traducir_fecha[1]."-".$traducir_fecha[0]." ".$separaHora[1]; 
        return $fecha_mysql;
    }
}

?>