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
	
	

	public static function returnHtml($user, $recache = false) { 
	
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
	
	
}

?>
