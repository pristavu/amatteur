<?php

class LayoutController extends JO_Action {	
	
	
	public function header_partAction() {

		
		$request=$this->getRequest();
		
		if(JO_Session::get('user[user_id]') && JO_Session::get('category_id')) {
			Model_Users::edit(JO_Session::get('user[user_id]'), array(
				'first_login' => '0'
			));
			JO_Session::clear('category_id');
			$this->view->user_info = JO_Session::get('user');
			Model_Email::send(
				JO_Session::get('user[email]'),
            	JO_Registry::get('noreply_mail'),
            	sprintf($this->translate('Welcome to %s!'), JO_Registry::get('site_name')),
            	$this->view->render('welcome', 'mail')
			);
		}
		
		$this->view->og_namespace = trim(JO_Registry::get('og_namespace'));
		$this->view->og_recipe = trim(JO_Registry::get('og_recipe'));
		if(!$this->view->og_recipe) {
			$this->view->og_namespace = '';
		}
		
		$this->view->show_landing = !JO_Registry::get('enable_free_registration');
		
		$to_title = '';
		if(JO_Session::get('user[user_id]')) {
			$to_title = JO_Session::get('user[fullname]') . ' / ';
		}
		
		if($this->getLayout()->meta_title) {
			$this->getLayout()->placeholder('title', ($this->getLayout()->meta_title . ' - ' . JO_Registry::get('meta_title')));
		} else {
			$this->getLayout()->placeholder('title', $to_title . JO_Registry::get('meta_title'));
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
		
		if(JO_Registry::get('favicon') && file_exists(BASE_PATH .'/uploads'.JO_Registry::get('favicon'))) {
		    $this->getLayout()->placeholder('favicon', $request->getBaseUrl() . 'uploads' . JO_Registry::get('favicon'));
		}
		
		$this->getLayout()->placeholder('site_logo', $this->view->site_logo);
		
		$this->view->show_header_invate = !JO_Session::get('user[user_id]');
		if(!JO_Session::get('user[user_id]')) {
			switch(true) {
				case $request->getAction() == 'login':
					$this->view->show_header_invate = true;
				case $request->getAction() == 'register':
					$this->view->show_header_invate = true;
				break;
			}	
		}
		
		$this->view->controller_open = $request->getController();
		
		$this->view->show_header_line = !in_array($request->getController(), array('pin'));
		$this->view->show_slider  = !in_array($request->getController(), array('users','pin','settings','prefs','password'));
		
		
		if($request->getController() == 'users') {
			$this->view->show_header_line = false;
		}
		
		//==== brand =====//
		$this->view->show_brand = true;
		if( JO_Registry::get('license_powered_check') == 'false' && JO_Registry::get('config_hide_brand') ) {
			$this->view->show_brand = false;
		}
		
		////////// CURRENCY
		//autoupdate currency if set
		if(JO_Registry::get('config_currency_auto_update')) {
			WM_Currency::updateCurrencies();
		}
		
		$currencies = WM_Currency::getCurrencies();
		$price_left = array();
		$price_right = array();
		if($currencies) {
			foreach($currencies AS $currency) {
				if(trim($currency['symbol_left'])) {
					$price_left[] = preg_quote(trim($currency['symbol_left']));
				}
				if(trim($currency['symbol_right'])) {
					$price_right[] = preg_quote(trim($currency['symbol_right']));
				}
			}
		}
		
		$this->view->price_left = implode('|', $price_left);
		$this->view->price_right = implode('|', $price_right);
		
		//////////// Categories ////////////
		$this->view->categories = array();
                $categories1 = array(
                    "0" => array(
                    "category_id" => "9999",
                    "title" => "TODO",
                    "image" => ""
                            ));
		$this->view->category_active = false;
		$categories2 = Model_Categories::getCategories(array(
			'filter_status' => 1
		));
                $categories = array_merge($categories1, $categories2);
                $x = 0;
		foreach($categories AS $category) {
                    if ($x== 0)
                    {
			$category['subcategories'] = Model_Categories::getSubcategories($category['category_id']);
			$category['href'] = WM_Router::create( $request->getBaseUrl() . '?controller=all&category_id=' . $category['category_id'] );
                        if ($request->getRequest('category_id') == 9999)
                        {
                            $category['active'] = TRUE;
                            $this->view->category_active = $category['title'];
                        }
                        else
                        {
                            $category['active'] = FALSE;
                        }   
                        
                        $this->view->categories[] = $category;
                    }
                    else
                    {
			$category['subcategories'] = Model_Categories::getSubcategories($category['category_id']);
			$category['href'] = WM_Router::create( $request->getBaseUrl() . '?controller=category&category_id=' . $category['category_id'] );
			$category['active'] = $category['category_id'] == $request->getRequest('category_id');
			if($category['active']) {
				$this->view->category_active = $category['title'];
			} else {
                            $i = 0;
                            foreach($category['subcategories'] AS $subcategory) {  
                                $category['subcategories'][$i]['active'] = $subcategory['category_id'] == $request->getRequest('category_id');
                            	if($category['subcategories'][$i]['active']) {
                                    $this->view->category_active = $subcategory['title'];
                                }
                                $i++;
                            }
			}
			
			$this->view->categories[] = $category;
                    }
                    $x = 1;
		}
		
		////////////////////////////// USER MENU ///////////////////////////
		$this->view->is_loged = JO_Session::get('user[user_id]');
		if($this->view->is_loged) {
			$model_images = new Helper_Images();
			
			$avatar = Helper_Uploadimages::avatar(JO_Session::get('user'), '_A');
			$this->view->self_avatar = $avatar['image'];
			
			
			$this->view->self_profile = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $this->view->is_loged );
                        $this->view->mails = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=mails');
			$this->view->self_firstname = JO_Session::get('user[firstname]');
			$this->view->logout = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=logout' );
			
			$this->view->invites = WM_Router::create( $request->getBaseUrl() . '?controller=invites' );
			$this->view->invites_fb = WM_Router::create( $request->getBaseUrl() . '?controller=invites&action=facebook' );
			$this->view->user_pins = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=pins&user_id=' . $this->view->is_loged  );
			$this->view->user_pins_likes = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=pins&user_id=' . $this->view->is_loged . '&filter=likes' );
			$this->view->settings = WM_Router::create( $request->getBaseUrl() . '?controller=settings' );
			
		}
		$this->view->login = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=login' );
		$this->view->landing = WM_Router::create( $request->getBaseUrl() . '?controller=landing' );
		$this->view->site_name = JO_Registry::get('site_name');
		
		$this->view->registration = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=register' );
		
                $category_id = null;
                if ($request->getRequest('category_id'))
                {
                    $category_id = $request->getRequest('category_id');
                    ////////////////////////////// GIFTS ///////////////////////////
                    $this->view->gifts = WM_Router::create( $request->getBaseUrl() . '?controller=gifts&category_id='.$category_id );
                    $this->view->gifts1 = WM_Router::create( $request->getBaseUrl() . '?controller=gifts&price_from=1&price_to=20&category_id='.$category_id );
                    $this->view->gifts2 = WM_Router::create( $request->getBaseUrl() . '?controller=gifts&price_from=20&price_to=50&category_id='.$category_id );
                    $this->view->gifts3 = WM_Router::create( $request->getBaseUrl() . '?controller=gifts&price_from=50&price_to=100&category_id='.$category_id );
                    $this->view->gifts4 = WM_Router::create( $request->getBaseUrl() . '?controller=gifts&price_from=100&price_to=200&category_id='.$category_id );
                    $this->view->gifts5 = WM_Router::create( $request->getBaseUrl() . '?controller=gifts&price_from=200&price_to=500&category_id='.$category_id );
                    $this->view->gifts6 = WM_Router::create( $request->getBaseUrl() . '?controller=gifts&price_from=500&category_id='.$category_id );

                    //////////// Video ////////////
                    $this->view->video_url = WM_Router::create( $request->getBaseUrl() . '?controller=videos&category_id='.$category_id );

                    /*
                    //////////// Popular ////////////
                    $this->view->popular_url = WM_Router::create( $request->getBaseUrl() . '?controller=popular&category_id='.$category_id );
*/

                    //////////// Articles ////////////
                    $this->view->article_url = WM_Router::create( $request->getBaseUrl() . '?controller=articles&category_id='.$category_id );

                    //////////// Ranking ////////////
                    $this->view->pinTop7_url = WM_Router::create( $request->getBaseUrl() . '?controller=toppins&index_id=1&category_id='.$category_id );
                    $this->view->pinTop_url = WM_Router::create( $request->getBaseUrl() . '?controller=toppins&index_id=2&category_id='.$category_id );
                    $this->view->profileTop7_url = WM_Router::create( $request->getBaseUrl() . '?controller=toppins&index_id=3&category_id='.$category_id );
                    $this->view->profileTop_url = WM_Router::create( $request->getBaseUrl() . '?controller=toppins&index_id=4&category_id='.$category_id );

                }
                else
                {
                    ////////////////////////////// GIFTS ///////////////////////////
                    $this->view->gifts = WM_Router::create( $request->getBaseUrl() . '?controller=gifts' );
                    $this->view->gifts1 = WM_Router::create( $request->getBaseUrl() . '?controller=gifts&price_from=1&price_to=20' );
                    $this->view->gifts2 = WM_Router::create( $request->getBaseUrl() . '?controller=gifts&price_from=20&price_to=50' );
                    $this->view->gifts3 = WM_Router::create( $request->getBaseUrl() . '?controller=gifts&price_from=50&price_to=100' );
                    $this->view->gifts4 = WM_Router::create( $request->getBaseUrl() . '?controller=gifts&price_from=100&price_to=200' );
                    $this->view->gifts5 = WM_Router::create( $request->getBaseUrl() . '?controller=gifts&price_from=200&price_to=500' );
                    $this->view->gifts6 = WM_Router::create( $request->getBaseUrl() . '?controller=gifts&price_from=500' );

                    //////////// Video ////////////
                    $this->view->video_url = WM_Router::create( $request->getBaseUrl() . '?controller=videos' );

                    /*
                    //////////// Popular ////////////
                    $this->view->popular_url = WM_Router::create( $request->getBaseUrl() . '?controller=popular' );
*/

                    //////////// Articles ////////////
                    $this->view->article_url = WM_Router::create( $request->getBaseUrl() . '?controller=articles' );

                    //////////// Ranking ////////////
                    $this->view->pinTop7_url = WM_Router::create( $request->getBaseUrl() . '?controller=toppins&index_id=1' );
                    $this->view->pinTop_url = WM_Router::create( $request->getBaseUrl() . '?controller=toppins&index_id=2' );
                    $this->view->profileTop7_url = WM_Router::create( $request->getBaseUrl() . '?controller=toppins&index_id=3' );
                    $this->view->profileTop_url = WM_Router::create( $request->getBaseUrl() . '?controller=toppins&index_id=4' );
        }
                //////////// Popular ////////////
                $this->view->popular_url = WM_Router::create( $request->getBaseUrl() . '?controller=premiostt' );

		//////////// ALL PINS ////////////
		$this->view->all_url = WM_Router::create( $request->getBaseUrl() . '?controller=all' );

		//////////// activate ////////////
		$this->view->activate_url = WM_Router::create( $request->getBaseUrl() . '?controller=index&action=indexActivate' );
                
                
		//////////// Eventtos ////////////
		$this->view->events_url = WM_Router::create( $request->getBaseUrl() . '?controller=events' );

		//////////// Volunttarios ////////////
                if ($this->view->is_loged)
                {
                    $this->view->voluntarios_url = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=voluntarioMenuPopup&user_id=' . $this->view->is_loged  );
                }
                else
                {
                    $this->view->voluntarios_url = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=voluntarioMenuPopup');
                }
                    
                
		////////////////////////////// SEARCH ///////////////////////////
		
		//$this->view->search_action = WM_Router::create($request->getBaseUrl() . '?controller=search');
		
                
		if(in_array($request->getAction(), array('advanced', 'page', 'view'))) {
			$with_action = $request->getAction();
			$this->view->search_action = WM_Router::create($request->getBaseUrl() . '?controller=search&action='.$request->getAction());
		} elseif( in_array($request->getAction(), array('advanced', 'advanced')) ) {
			$with_action = $request->getAction();
			$this->view->search_action = WM_Router::create($request->getBaseUrl() . '?controller=search&action='.$request->getAction());
                } else {
			$with_action = 0;
			$this->view->search_action = WM_Router::create($request->getBaseUrl() . '?controller=search');
		}
		
		//$this->view->search_autocomplete = WM_Router::create($request->getBaseUrl() . '?controller=search&action=autocomplete');
		if(strpos($this->view->search, '?') !== false) {
			$this->view->show_hidden = true;
			$this->view->with_action = $with_action;    
		}
		
		//$this->view->keywords = $request->issetQuery('q') ? $request->getQuery('q') : $this->translate('Search...');
		
                
                /*
		if(in_array($request->getAction(), array('amatteur'))) {
			$with_action = 0;
			$this->view->search_action_advanced = WM_Router::create($request->getBaseUrl() . '?controller=search');
		} elseif( in_array($request->getAction(), array('activate', 'services')) ) {
			$with_action = $request->getAction();
			$this->view->search_action_advanced = WM_Router::create($request->getBaseUrl() . '?controller=search&action='.$request->getAction());
                } else {
			$with_action = 0;
			$this->view->search_action_advanced = WM_Router::create($request->getBaseUrl() . '?controller=search');
		}
		
		$this->view->search_autocomplete_advanced = WM_Router::create($request->getBaseUrl() . '?controller=search&action=autocomplete');
		if(strpos($this->view->search_advanced, '?') !== false) {
			$this->view->show_hidden = true;
			$this->view->with_action = $with_action;    
		}
		
		$this->view->keywords = $request->issetQuery('q') ? $request->getQuery('q') : $this->translate('Search...');
                */
                
                $this->view->search_url = WM_Router::create($request->getBaseUrl() . '?controller=search&action=advanced?id=amatteur');
		////////////////////////////// ADD PIN ///////////////////////////
		
		$this->view->addPin = WM_Router::create($request->getBaseUrl() . '?controller=addpin');
		
		////////////////////////////// MAILS ///////////////////////////
		
		$this->view->addMail = WM_Router::create($request->getBaseUrl() . '?controller=mails&action=create');
		$this->view->stateMail = WM_Router::create($request->getBaseUrl() . '?controller=mails&action=state');
		$this->view->viewMail = WM_Router::create($request->getBaseUrl() . '?controller=mails&action=view');
		
		////////////////////////////// FULL URL ///////////////////////////
		$this->view->full_url_js = false;
		switch(true) {
			case 'index' == $request->getController():
				$this->view->full_url_js = WM_Router::create($request->getBaseUrl());
			break;
			case 'search' == $request->getController():
				if(in_array($request->getAction(), array('index', 'page', 'view'))) {
                                    $this->view->full_url_js = WM_Router::create($request->getBaseUrl() . '?controller=search&q=' . $request->getRequest('q'));
				} 
                                else 
                                {
                                    $parametros = "";
                                    
                                    $this->view->keywords = $request->issetQuery('location') ? $request->getQuery('location') : $this->translate('Search...');

                                    /*
                                    if ($request->issetPost('firstname'))
                                    {
                                        $parametros .= "&firstname=". $request->getPost('firstname');
                                    } 

                                    if ($request->issetPost('words'))
                                    {
                                        $parametros .= "&words=". $request->getPost('words');
                                    } 
                                    if ($request->issetPost('location'))
                                    {
                                        $parametros .= "&location=". $request->getPost('location');
                                    } 
                                    if ($request->issetPost('sport_category_1'))
                                    {
                                        $parametros .= "&sport_category_1=". $request->getPost('sport_category_1');
                                    }         
                                    if ($request->issetPost('sport_category_2'))
                                    {
                                        $parametros .= "&sport_category_2=". $request->getPost('sport_category_2');
                                    }
                                    if ($request->issetPost('sport_category_3'))
                                    {
                                        $parametros .= "&sport_category_3=". $request->getPost('sport_category_3');
                                    } 
                                    if ($request->issetPost('type_user'))
                                    {
                                        $parametros .= "&type_user=". $request->getPost('type_user');
                                    } 
                                    //gender
                                    if ($request->issetPost('gender'))
                                    {
                                        $parametros .= "&gender=". $request->getRequest('gender');
                                    } 

                                    //location		
                                    if ($request->issetPost('location'))
                                    {
                                        $parametros .= "&location=". $request->getPost('location');
                                    } 

                                    //sport category
                                    if ($request->issetPost('sport_category'))
                                    {
                                        $parametros .= "&sport_category=". $request->getPost('sport_category');
                                    } 

                                    //age
                                    if ($request->issetPost('age'))
                                    {
                                        $parametros .= "&age=". $request->getPost('age');
                                    } 

                                    //level
                                    if ($request->issetPost('level'))
                                    {
                                        $parametros .= "&level=". $request->getPost('level');
                                    } 

                                    //option1		
                                    if ($request->issetPost('option1'))
                                    {
                                        $parametros .= "&option1=". $request->getPost('option1');
                                    } 

                                    //option2		
                                    if ($request->issetPost('option2'))
                                    {
                                        $parametros .= "&option2=". $request->getPost('option2');
                                    } 

                                    //option3
                                    if ($request->issetPost('option3'))
                                    {
                                        $parametros .= "&option3=". $request->getPost('option3');
                                    } 

                                    //option4		
                                    if ($request->issetPost('option4'))
                                    {
                                        $parametros .= "&option4=". $request->getPost('option4');
                                    } 

                                    //option5		
                                    if ($request->issetPost('option5'))
                                    {
                                        $parametros .= "&option5=". $request->getPost('option5');
                                    } 

                                    //option6		
                                    if ($request->issetPost('option6'))
                                    {
                                        $parametros .= "&option6=". $request->getPost('option6');
                                    } 

                                    //option7		
                                    if ($request->issetPost('option7'))
                                    {
                                        $parametros .= "&option7=". $request->getPost('option7');
                                    } 

                                    //option8		
                                    if ($request->issetPost('option8'))
                                    {
                                        $parametros .= "&option8=". $request->getPost('option8');
                                    } 

                                    //option9		
                                    if ($request->issetPost('option9'))
                                    {
                                        $parametros .= "&option9=". $request->getPost('option9');
                                    } 

                                    //option10		
                                    if ($request->issetPost('option10'))
                                    {
                                        $parametros .= "&option10=". $request->getPost('option10');
                                    }
                                    
                                    //option11		
                                    if ($request->issetPost('option11'))
                                    {
                                        $parametros .= "&option11=". $request->getPost('option11');
                                    } 

                                    //option12		
                                    if ($request->issetPost('option12'))
                                    {
                                        $parametros .= "&option12=". $request->getPost('option12');
                                    } 

                                    //option13
                                    if ($request->issetPost('option13'))
                                    {
                                        $parametros .= "&option13=". $request->getPost('option13');
                                    } 

                                    //option14		
                                    if ($request->issetPost('option14'))
                                    {
                                        $parametros .= "&option14=". $request->getPost('option14');
                                    } 

                                    //option15		
                                    if ($request->issetPost('option15'))
                                    {
                                        $parametros .= "&option15=". $request->getPost('option15');
                                    } 

                                    //option16		
                                    if ($request->issetPost('option16'))
                                    {
                                        $parametros .= "&option16=". $request->getPost('option16');
                                    } 

                                    //option17		
                                    if ($request->issetPost('option17'))
                                    {
                                        $parametros .= "&option17=". $request->getPost('option17');
                                    } 

                                    //option18		
                                    if ($request->issetPost('option18'))
                                    {
                                        $parametros .= "&option18=". $request->getPost('option18');
                                    } 
                                     * 
                                     */
                                    if ($request->issetRequest('zoom'))
                                    {
                                        $parametros .= "&zoom=". $request->getRequest('zoom');
                                    } 
                                    
                                    if ($request->issetRequest('id'))
                                    {
                                        $parametros .= "&id=". $request->getRequest('id');
                                    } 
                                    
                                    if ($request->issetRequest('firstname'))
                                    {
                                        $parametros .= "&firstname=". $request->getRequest('firstname');
                                    } 

                                    if ($request->issetRequest('words'))
                                    {
                                        $parametros .= "&words=". $request->getRequest('words');
                                    } 
                                    if ($request->issetRequest('location'))
                                    {
                                        $parametros .= "&location=". $request->getRequest('location');
                                    } 
                                    if ($request->issetRequest('sport_category_1'))
                                    {
                                        $parametros .= "&sport_category_1=". $request->getRequest('sport_category_1');
                                    }         
                                    if ($request->issetRequest('sport_category_2'))
                                    {
                                        $parametros .= "&sport_category_2=". $request->getRequest('sport_category_2');
                                    }
                                    if ($request->issetRequest('sport_category_3'))
                                    {
                                        $parametros .= "&sport_category_3=". $request->getRequest('sport_category_3');
                                    } 
                                    if ($request->issetRequest('type_user'))
                                    {
                                        $parametros .= "&type_user=". $request->getRequest('type_user');
                                    } 
                                    //gender
                                    if ($request->issetRequest('gender'))
                                    {
                                        $parametros .= "&gender=". $request->getRequest('gender');
                                    } 

                                    //location		
                                    if ($request->issetRequest('location'))
                                    {
                                        $parametros .= "&location=". $request->getRequest('location');
                                    } 

                                    //sport category
                                    if ($request->issetRequest('sport_category'))
                                    {
                                        $parametros .= "&sport_category=". $request->getRequest('sport_category');
                                    } 

                                    //age
                                    if ($request->issetRequest('age'))
                                    {
                                        $parametros .= "&age=". $request->getRequest('age');
                                    } 

                                    //level
                                    if ($request->issetRequest('level'))
                                    {
                                        $parametros .= "&level=". $request->getRequest('level');
                                    } 

                                    //option1		
                                    if ($request->issetRequest('option1'))
                                    {
                                        $parametros .= "&option1=". $request->getRequest('option1');
                                    } 

                                    //option2		
                                    if ($request->issetRequest('option2'))
                                    {
                                        $parametros .= "&option2=". $request->getRequest('option2');
                                    } 

                                    //option3
                                    if ($request->issetRequest('option3'))
                                    {
                                        $parametros .= "&option3=". $request->getRequest('option3');
                                    } 

                                    //option4		
                                    if ($request->issetRequest('option4'))
                                    {
                                        $parametros .= "&option4=". $request->getRequest('option4');
                                    } 

                                    //option5		
                                    if ($request->issetRequest('option5'))
                                    {
                                        $parametros .= "&option5=". $request->getRequest('option5');
                                    } 

                                    //option6		
                                    if ($request->issetRequest('option6'))
                                    {
                                        $parametros .= "&option6=". $request->getRequest('option6');
                                    } 

                                    //option7		
                                    if ($request->issetRequest('option7'))
                                    {
                                        $parametros .= "&option7=". $request->getRequest('option7');
                                    } 

                                    //option8		
                                    if ($request->issetRequest('option8'))
                                    {
                                        $parametros .= "&option8=". $request->getRequest('option8');
                                    } 

                                    //option9		
                                    if ($request->issetRequest('option9'))
                                    {
                                        $parametros .= "&option9=". $request->getRequest('option9');
                                    } 

                                    //option10		
                                    if ($request->issetRequest('option10'))
                                    {
                                        $parametros .= "&option10=". $request->getRequest('option10');
                                    }
                                    
                                    //option11		
                                    if ($request->issetRequest('option11'))
                                    {
                                        $parametros .= "&option11=". $request->getRequest('option11');
                                    } 

                                    //option12		
                                    if ($request->issetRequest('option12'))
                                    {
                                        $parametros .= "&option12=". $request->getRequest('option12');
                                    } 

                                    //option13
                                    if ($request->issetRequest('option13'))
                                    {
                                        $parametros .= "&option13=". $request->getRequest('option13');
                                    } 

                                    //option14		
                                    if ($request->issetRequest('option14'))
                                    {
                                        $parametros .= "&option14=". $request->getRequest('option14');
                                    } 

                                    //option15		
                                    if ($request->issetRequest('option15'))
                                    {
                                        $parametros .= "&option15=". $request->getRequest('option15');
                                    } 

                                    //option16		
                                    if ($request->issetRequest('option16'))
                                    {
                                        $parametros .= "&option16=". $request->getRequest('option16');
                                    } 

                                    //option17		
                                    if ($request->issetRequest('option17'))
                                    {
                                        $parametros .= "&option17=". $request->getRequest('option17');
                                    } 

                                    //option18		
                                    if ($request->issetRequest('option18'))
                                    {
                                        $parametros .= "&option18=". $request->getRequest('option18');
                                    } 
                                    
                                    
                                    $parametros .= "&kk=kk";

                                    $this->view->full_url_js = WM_Router::create($request->getBaseUrl() . '?controller=search&action='.$request->getAction().'&q=' . $request->getRequest('q').'&id=' . $request->getRequest('id').$parametros);
				}
			break;
			case 'all' == $request->getController():
				$this->view->full_url_js = WM_Router::create($request->getBaseUrl() . '?controller=all');
			break;
			case 'videos' == $request->getController():
				$this->view->full_url_js = WM_Router::create($request->getBaseUrl() . '?controller=videos');
			break;
			case 'popular' == $request->getController():
				$this->view->full_url_js = WM_Router::create($request->getBaseUrl() . '?controller=popular');
			break;
			case 'category' == $request->getController():
				$this->view->full_url_js = WM_Router::create($request->getBaseUrl() . '?controller=category&category_id=' . $request->getRequest('category_id'));
			break;
			case 'source' == $request->getController():
				$this->view->full_url_js = WM_Router::create($request->getBaseUrl() . '?controller=source&source_id=' . $request->getRequest('source_id'));
			break;
			case 'boards' == $request->getController() && in_array($request->getAction(), array('index', 'page')):
				$url = '';
				if($request->getRequest('user_id')) {
					$url = '&user_id=' . $request->getRequest('user_id');
				}
				$this->view->full_url_js = WM_Router::create($request->getBaseUrl() . '?controller=boards&action=view' . $url . '&board_id=' . $request->getRequest('board_id'));
			break;
			case 'gifts' == $request->getController():
				$url = '';
				if($request->issetParam('price_from')) {
					$url .= (int)$request->getRequest('price_from');
				}
				if($request->issetParam('price_to')) {
					$url .= ':' . (int)$request->getRequest('price_to');
				}
				$this->view->full_url_js = WM_Router::create($request->getBaseUrl() . '?controller=gifts' . ($url ? '&action=' . $url : ''));
			break;
			case 'users' == $request->getController():
				if(in_array($request->getAction(), array(/*'index', 'profile', */'pins', 'followers', 'following', 'likers', 'liking', 'activity')) && $request->getRequest('user_id')) { 
					$this->view->full_url_js = WM_Router::create($request->getBaseUrl() . '?controller=users&action='.$request->getAction().'&user_id=' . $request->getRequest('user_id') . ($request->getQuery('filter') ? '&filter=' . $request->getQuery('filter') : '') );
				}
			break;
		}
		
		if($request->getRequest('user_id')) {
			$user_info = Model_Users::getUser($request->getRequest('user_id'));
			if($user_info && $user_info['dont_search_index']) {
				$this->getLayout()->placeholder('inhead', '<meta name="robots" content="noindex"/>');
			}
		}
		
		////////////////////////////// ABOUT MENU ///////////////////////////
		
		$this->view->about_menu = array();
		$has = false;
		if( is_array(JO_Registry::forceGet('about_menu')) ) {
			foreach(JO_Registry::forceGet('about_menu') AS $row => $page_id) {
				if($row==0) {
					$class = 'first';
				} else if( (count(JO_Registry::forceGet('about_menu'))-1) == $row ) {
					$class = 'last';
				} else {
					$class = '';
				}
//				$class = $row==0?' first':'';
				if($page_id == -1) {
					$has = true;
				} else {
					$pinfo = Model_Pages::getPage($page_id);
					if($pinfo && $pinfo['status']) {
						if($has) {
							$class .= " group";
							$has = false;
						}
						$this->view->about_menu[] = array(
							'class' => trim($class),
							'title' => $pinfo['title'],
							'href' => WM_Router::create($this->getRequest()->getBaseUrl() . '?controller=pages&action=read&page_id=' . $page_id)
						);
					}
				}
			}
		}
		
		////////////////////////////// NEW PASSWORD ///////////////////////////
		
		$this->view->show_new_password = false;
		if( JO_Session::get('user[user_id]') && JO_Session::get('user[email]') != JO_Session::get('user[new_email]') ) {
			switch(true) {
				case 'index' == $request->getController():
				case 'all' == $request->getController():
				case 'category' == $request->getController():
				case 'videos' == $request->getController():
				case 'popular' == $request->getController():
				case 'gifts' == $request->getController():
					$this->view->show_new_password = true;
				break;
			}
		}
		
		////////////////////////////// Board category /////////////////////////// 
		if( is_array($board_info = JO_Registry::forceGet('board_category_change')) ) {
			$this->view->board_category_change = array(
				'title' => 	$board_info['title'],
				'href' => WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=edit&user_id=' . $board_info['user_id'] . '&board_id=' . $board_info['board_id'] )	
			);
		}
		
	}	
	
    public function footer_partAction() {	
    	
	}
	
	public function left_partAction() {
		
	}
	
}

?>