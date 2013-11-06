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
            if($request->issetPost('cancel')) {
                    $this->view->cancel = $request->getPost('cancel');
            }       
            else if ($event_data)
            {
                    if (isset($event_data['cancel'])) 
                    {
                        $this->view->cancel  = $event_data['cancel'];
                    }
            }
            
            $this->view->cancelReason = '';
            if($request->issetPost('cancelReason')) {
                    $this->view->cancelReason = $request->getPost('cancelReason');
            }       
            else if ($event_data)
            {
                    if (isset($event_data['cancelReason'])) 
                    {
                        $this->view->cancelReason  = $event_data['cancelReason'];
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
                        $validate->_set_rules($request->getPost('description'), $this->translate('Detalles'), 'not_empty;min_length[3];max_length[100]');
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
                                
				if(Model_Events::createEvent( JO_Session::get('user[user_id]'), $event_id, $data )) {
					JO_Session::set('successfu_edite', true);
					$upload->getFileInfo(true);

                $event_data = Model_Events::getEvent(array( 'filter_event_id' => $event_id));

                foreach($event_data AS $k=>$v) {
                        if(isset($event_data[$k])) {
                                $event_data[$k] = $v;
                        }
                }

					
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
                    error_log("Image " . $avatar['image']  . " ". $event_data['avatar']);
                    $this->view->has_avatar = @getimagesize($event_data['avatar']) ? true : false;
                    error_log ("avatar " . $this->view->avatar . " ". $this->view->has_avatar);
                }
            //}
            $this->view->form_action = WM_Router::create( $request->getBaseUrl() . '?controller=events&action=upload_avatar' );
            $this->view->search_json = WM_Router::create( $request->getBaseUrl() . '?controller=events&action=search2' );
            


            
		
            $this->view->children = array(
                'header_part' 	=> 'layout/header_part',
                'footer_part' 	=> 'layout/footer_part'
            );
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
                    'filter_compartir' => $request->getPost('compartir')
                );

                $events = Model_Events::getEvents($dataEvents);

                if ($events)
                {
                    foreach ($events AS $key => $event)
                    {
                        $href = "";
                        $view = JO_View::getInstance();
                        $view->loged = JO_Session::get('user[user_id]');
                        $model_images = new Helper_Images();

                        $avatar = Helper_Uploadimages::avatar($event, '_B');
                        $event['avatar'] = $avatar['image'];

                        $event["sport_category"] = Model_Boards::getCategoryTitle($event["sport_category"]);
                        
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
                        $view->boxeventdetail = WM_Router::create($request->getBaseUrl() . '?controller=events&action=eventBoxDetail&event_id=' . $event['event_id']);
                        
                        $view->event = $event;
                        $this->view->events .= $view->render('boxEvent', 'events');
                        
                    }
                    $this->view->eventos = $events;
                    $this->view->class_contaner = 'persons';
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
                    'filter_compartir' => $request->getPost('compartir')
                );

                $events = Model_Events::getEvents($dataEvents);

                if ($events)
                {
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
                        $view->boxeventdetail = WM_Router::create($request->getBaseUrl() . '?controller=events&action=eventBoxDetail&event_id=' . $event['event_id']);
                        $this->view->from_url = WM_Router::create( $request->getBaseUrl() . '?controller=addpin&action=fromurl' );
                        
                        $view->event = $event;
                        $this->view->eventsBox .= $view->render('boxEvent', 'events');
                        
                    }
                    $this->view->eventos = $events;
                    $this->view->class_contaner = 'persons';
                }
                else
                {
                    $this->view->error = $this->translate('La búsqueda no ha devuelto resultados');
                }
            }
            else
            {
                $this->view->all = false;
                
                $dataEvents = array(
                    'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                    'limit' => JO_Registry::get('config_front_limit')
                );

                $events = Model_Events::getEvents($dataEvents);

                if ($events)
                {
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
                        $view->boxeventdetail = WM_Router::create($request->getBaseUrl() . '?controller=events&action=evenBoxDetail&event_id=' . $event['event_id']);
                        
                        $view->event = $event;
                        $this->view->eventsBox .= $view->render('boxEvent', 'events');
                        
                    }
                    $this->view->eventos = $events;
                    $this->view->class_contaner = 'persons';
                }
                else
                {
                    $this->view->error = $this->translate('La búsqueda no ha devuelto resultados');
                }
            }
              
            $this->view->children = array(
                'header_part' => 'layout/header_part',
                'footer_part' => 'layout/footer_part'
            );
            
      
        }  
        
        
	public function eventBoxDetailAction() {
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

                $events = Model_Events::getEvents($dataEvents);

		if(!$events) {
			$this->forward('error', 'error404');
		}
                
                
                if ($events)
                {
                    foreach ($events AS $key => $event)
                    {
                        $event_id = $event['event_id'];
                        
                        $href = "";
                        $view = JO_View::getInstance();
                        $view->loged = JO_Session::get('user[user_id]');
                        $model_images = new Helper_Images();

                        $avatar = Helper_Uploadimages::avatar($event, '_D');
                        $event['thumb'] = $avatar['image'];
                        $event['avatar'] = $avatar['image'];
			$event['popup'] = $avatar['image'];
			$event['popup_width'] = $avatar['width'];
			$event['popup_height'] = $avatar['height'];
			$event['original_image'] = $avatar['original'];
                        

                        $event["sport_category"] = Model_Boards::getCategoryTitle($event["sport_category"]);
                        
                        $data = array(
                            'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                            'limit' => JO_Registry::get('config_front_limit'),
                            'filter_user_id' => $event["user_id"]
                        );

                        $users = Model_Users::getUsers($data);
                        if ($users)
                        {
                            $event['fullname'] = $users[0]["fullname"];
                            $event['description'] = $users[0]["description"];
                            $avataruser = Helper_Uploadimages::avatar($users[0], '_B');
                            $event['avataruser'] = $avataruser['image'];
                           
                            
                            $event['href'] = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $event['user_id']);
                            $href = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $event['user_id']);
                        }
                        
                        $view->event = $event;
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
					$result = Model_Events::addComment($data, $pin_info['latest_comments'], Model_Users::$allowed_fields);
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
							Model_History::addHistory($pin_info['user_id'], Model_History::COMMENTPIN, $event_id, 0, $request->getPost('write_comment'));
						
							if($pin_info['user']['email_interval'] == 1 && $pin_info['user']['comments_email']) {
								$this->view->user_info = $pin_info['user'];
								$this->view->text_email = $this->translate('comment your');
								$this->view->profile_href = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . JO_Session::get('user[user_id]'));
								$this->view->full_name = JO_Session::get('user[firstname]') . ' ' . JO_Session::get('user[lastname]');
								$this->view->pin_href = WM_Router::create($request->getBaseUrl() . '?controller=pin&pin_id=' . $pin_id );
								Model_Email::send(
				    	        	$pin_info['user']['email'],
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
					$result = Model_Events::addComment($data, $pin_info['latest_comments']);
					//$this->redirect(WM_Router::create( $request->getBaseUrl() . '?controller=pin&pin_id=' . $pin_id ));
				} else {
					$this->redirect(WM_Router::create( $request->getBaseUrl() . '?controller=landing' ));
				}
			}
		}
		
		
		$this->view->show_buttonswrapper = true;
		
		$this->view->url_like = WM_Router::create( $request->getBaseUrl() . '?controller=event&action=like&event_id=' . $event_id );
		$this->view->url_tweet = WM_Router::create( $request->getBaseUrl() . '?controller=event&action=eventboxdetail&event_id=' . $event_id );
		$this->view->url_embed = WM_Router::create( $request->getBaseUrl() . '?controller=event&action=embed&event_id=' . $event_id );
		$this->view->url_report = WM_Router::create( $request->getBaseUrl() . '?controller=event&action=report&event_id=' . $event_id );
		$this->view->url_email = WM_Router::create( $request->getBaseUrl() . '?controller=event&action=email&event_id=' . $event_id );
		$this->view->url_comment = WM_Router::create( $request->getBaseUrl() . '?controller=event&action=comment&event_id=' . $event_id );
		
		$banners = Model_Banners::getBanners(
			new JO_Db_Expr("`controller` = '".$request->getController()."'")
		);
		
		if($request->isXmlHttpRequest()) {
			$this->view->popup = true;
			echo Helper_Externallinks::fixExternallinks(Helper_Events::returnHtmlDetail($events[0], $banners));
			$this->noViewRenderer(true);
		} else {
			$this->view->pins_details = Helper_Events::returnHtmlDetail($events[0], $banners);
			JO_Registry::set('events_info', $events[0]);
			
			$this->view->children = array(
	        	'header_part' 	=> 'layout/header_part',
	        	'footer_part' 	=> 'layout/footer_part',
			'left_part'		=> 'pin/left_part'
	        );
		}
		
	}        
        
        public function boxeventdetailActionNOSEUSA(){
            
            
		
		$request = $this->getRequest();
		
		$this->view->popup_main_box = $this->view->render('boxeventdetail','events');
		
		
            



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

                $events = Model_Events::getEvents($dataEvents);

                if ($events)
                {
                    foreach ($events AS $key => $event)
                    {
                        $href = "";
                        $view = JO_View::getInstance();
                        $view->loged = JO_Session::get('user[user_id]');
                        $model_images = new Helper_Images();

                        $avatar = Helper_Uploadimages::avatar($event, '_D');
                        $event['thumb'] = $avatar['image'];
                        $event['avatar'] = $avatar['image'];
			$event['popup'] = $avatar['image'];
			$event['popup_width'] = $avatar['width'];
			$event['popup_height'] = $avatar['height'];
			$event['original_image'] = $avatar['original'];
                        

                        $event["sport_category"] = Model_Boards::getCategoryTitle($event["sport_category"]);
                        
                        $data = array(
                            'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                            'limit' => JO_Registry::get('config_front_limit'),
                            'filter_user_id' => $event["user_id"]
                        );

                        $users = Model_Users::getUsers($data);
                        if ($users)
                        {
                            $event['fullname'] = $users[0]["fullname"];
                            $event['desctiption'] = $users[0]["description"];
                            $avataruser = Helper_Uploadimages::avatar($users[0], '_B');
                            $event['avataruser'] = $avataruser['image'];
                           
                            
                            $event['href'] = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $event['user_id']);
                            $href = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $event['user_id']);
                        }
                        
                        $view->event = $event;
                        //$this->view->events .= $view->render('boxEventDetail', 'events');
                        //$this->view->events .= $view->render('pinboxdetail', 'events');
                        
                    }
                    $this->view->eventos = $events;
                    $this->view->class_contaner = 'persons';
                }
         
		if($request->isXmlHttpRequest()) {
			$this->noViewRenderer(true);
			echo $this->view->popup_main_box;
		} else {
			$this->view->children = array(
	        	'header_part' 	=> 'layout/header_part',
	        	'footer_part' 	=> 'layout/footer_part',
	        	'left_part' 	=> 'layout/left_part'
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
	
			$pins = Model_Events::getEvents($data);
					
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
	
		$this->view->pin_href = WM_Router::create( $request->getBaseUrl() . '?controller=events&event_id=' . $comment_info['event_id'] );
		
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
		
		$this->view->pins_details = $this->view->render('report','pin');
		
		if($request->isPost()) {
			$this->view->is_posted = true;
			
			if(Model_Events::commentIsReported($comment_id)) {
				$this->view->error = $this->translate('¡Ya has denunciado este comentario!');
				$this->view->pins_details = $this->view->render('report','pin');
			} else {
			
				$result = Model_Events::reportComment( $comment_id, $request->getPost('report_category'), $request->getPost('report_message') );
				if(!$result) {
					$this->view->error = $this->translate('Error reporting experience. Try again!');
					$this->view->pins_details = $this->view->render('report','pin');
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
					
					$this->view->pins_details = $this->view->render('message_report','event');
				}
			
			}
		}
		
		
		
		$this->setViewChange('index');
		if($request->isXmlHttpRequest()) {
			$this->view->popup = true;
			echo $this->view->pins_details;
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
			$pin = Model_Events::getEvent($comment_info['event_id']);
			if($comment_info['user_id'] == JO_Session::get('user[user_id]') || JO_Session::get('user[is_admin]') || JO_Session::get('user[user_id]') == $pin['board_data']['user_id']) {
				if(Model_Events::deleteComment($comment_id)) {
					$this->view->ok = true;
					$this->view->stats = self::getPinStat($comment_info['event_id']);
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
	

	
	
}

?>