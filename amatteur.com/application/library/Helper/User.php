<?php

class Helper_User {
	
	public function parse_urls($text, $maxurl_len = 35, $target = '_self') {
	    if (preg_match_all('/((ht|f)tps?:\/\/([\w\.]+\.)?[\w-]+(\.[a-zA-Z]{2,4})?[^\s\r\n\(\)"\'<>\,]+)/si', $text, $urls)) {
	        $offset1 = ceil(0.65 * $maxurl_len) - 2;
	        $offset2 = ceil(0.30 * $maxurl_len) - 1;
	        
	        foreach (array_unique($urls[1]) AS $url) {
	            if ($maxurl_len AND strlen($url) > $maxurl_len) {
	                $urltext = substr($url, 0, $offset1) . '...' . substr($url, -$offset2);
	            } else {
	                $urltext = $url;
	            }
	            
	            $text = str_replace($url, '<a class="link" href="'. $url .'" onclick="target=\''. $target .'\'" title="'. $url .'">'. $urltext .'</a>', $text);
	        }
	    }
	
	    return $text;
	}  
	
	

	public static function returnHtmlTop($user, $recache = false) { 
	
		static $view = null, $model_images = null, $request = null;
		if($view === null) { $view = JO_View::getInstance(); }
		if($model_images === null) { $model_images = new Helper_Images(); }
		if($request === null) { $request = JO_Request::getInstance(); }

                /*$response = array();
                
		if($users) {
			$view = JO_View::getInstance();
			$view->loged = JO_Session::get('user[user_id]');
			$model_images = new Helper_Images();
			foreach($users AS $key => $user) {
			*/	
				$user['thumbs'] = array();
				
                                /*
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
                                 * 
                                 */
				////
				$avatar = Helper_Uploadimages::avatar($user, '_B');
				$user['avatar'] = $avatar['image'];
				
                                //$user['Likers'] = true;
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
				
				//$view->key = $key%2==0;
				$view->user = $user;
				$response = $view->render('boxTop', 'users');
                                /*
			}
		}
		*/
		return $response;
	}
	
        public static function returnHtmlDetail() {
		//XPER: función que carga PIN
		static $view = null, $model_images = null, $request = null;
		if($view === null) { $view = JO_View::getInstance(); }
		if($model_images === null) { $model_images = new Helper_Images(); }
		if($request === null) { $request = JO_Request::getInstance(); }
		
                //$request = $this->getRequest();
                
                //////////// Categories ////////////
                $view->categories =  array();
                $categories = Model_Categories::getCategories(array(
                        'filter_status' => 1
                ));

                foreach ($categories as $category){
                        $category['subcategories'] = Model_Categories::getSubcategories($category['category_id']);
                        $view->categories[] = $category;
                }
                
                //////////// Age ////////////
                $view->ages =  array();
                $ages = Model_Users::getAge();
                $view->ages = $ages;

                //////////// Level ////////////
                $view->levels =  array();
                $levels = Model_Users::getLevel();
                $view->levels = $levels;
                
                $user_data = Model_Users::getActivateUser( JO_Session::get('user[user_id]') );
                $view->user_data = $user_data;

                if(JO_Registry::get('isMobile'))
                {
                    $view->urlmensajes = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=mensajes&user_id=' . $request->getRequest('board_user')   );
                }

                //gender
                if($request->issetPost('gender')) {
                    $view->gender = $request->getRequest('gender');
                } elseif (isset ($user_data['gender'])) {
                    $view->gender = $user_data['gender'];
                }
                else
                {
                    $view->gender = "";
                }
                
                //location		
                if($request->issetPost('location')) {
			$view->location = $request->getPost('location');
		}  elseif (isset ($user_data['location'])) {
			$view->location = $user_data['location'];
		}
                else
                {
                        $view->location = '';
                }
                
                //sport category
		if($request->issetPost('sport_category')) {
			$view->sport_category = $request->getPost('sport_category');
                        if ($request->getPost('sport_category') != "")
                        {
                            $view->cat_title = Model_Boards::getCategoryTitle($request->getPost('sport_category'));
                        }
		} elseif (isset ($user_data['sport_category'])) {
			$view->sport_category = $user_data['sport_category'];
                        $view->cat_title = Model_Boards::getCategoryTitle($user_data['sport_category']);
		}
                else
                {
                        $view->cat_title = '';
                        $view->sport_category = '';
                }
                
                //age
		if($request->issetPost('age')) {
			$view->age = $request->getPost('age');
                        if ($request->getPost('age') != "")
                        {
                            $view->age_title = Model_Users::getAgeTitle($request->getPost('age'));
                        }
		} elseif (isset ($user_data['age'])) {
			$view->age = $user_data['age'];
                        $view->age_title = Model_Users::getAgeTitle($user_data['age']);
		}
                else
                {
                    $view->age_title = '';
                    $view->age = '';
                }
                
                //level
		if($request->issetPost('level')) {
			$view->level = $request->getPost('level');
                        if ($request->getPost('level') != "")
                        {
                            $view->level_title = Model_Users::getLevelTitle($request->getPost('level'));
                        }
		} elseif (isset ($user_data['level'])) {
			$view->level = $user_data['level'];
                        $view->level_title = Model_Users::getLevelTitle($user_data['level']);
		}
                else
                {
                    $view->level_title = '';
                    $view->level = '';
                }

                //comment		
                if($request->issetPost('comment')) {
			$view->comment = $request->getPost('comment');
		}  elseif (isset ($user_data['comment'])) {
			$view->comment = $user_data['comment'];
		}
                else
                {
                        $view->comment = '';
                }
                
	
		//$view->form_action = WM_Router::create( $request->getBaseUrl() . '?controller=addpin&action=get_images' );
                //$view->from_url = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=activatePopup' );
                $view->from_url = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=activateDetail' );
		
		//$view->popup_main_box = $view->render('activatePopup','users');
                $view->popup_main_box = $view->render('activateDetail','users');


		return $view->render('activateDetail', 'users');
	}

	
}

?>
