<?php

class Helper_Pin {
	
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
	
	public static function descriptionFix($description) {
		//$description = html_entity_decode($description, ENT_QUOTES, 'utf-8');
                $description = str_replace("ƒÂ³", "ó", $description);
                $description = str_replace("í‚ª", "ª", $description);
                $description = str_replace("íƒÂº", "-ú", $description);
                $description = str_replace("Ãº", "ú", $description);
                $description = str_replace("Ã¡", "á", $description);
                $description = str_replace("Âª", "ª", $description);
                $description = str_replace("Ã©", "é", $description);
                $description = str_replace("Ã*", "í", $description);
                $description = str_replace("Ã³", "ó", $description);
                $description = str_replace("Ãº", "ú", $description);
                $description = str_replace("Ã", "Á", $description);
                $description = str_replace("Ã‰", "É", $description);
                $description = str_replace("Ã", "Í", $description);
                $description = str_replace("Ã“", "Ó", $description);
                $description = str_replace("Ãš", "Ú", $description);
                $description = str_replace("Ã±", "ñ", $description);
                $description = str_replace("Ã‘", "Ñ", $description);
                $description = str_replace("Ã", "í", $description);
		//$description = preg_replace('/(<!--|-->)/Uis','',$description);
		$description = self::parse_urls(str_replace('&amp;', '&', $description), 35, '_blank');
		return $description;
	}
	
	public static function getPinLikes($pin_id) {
		static $result = array(), $model_images = null, $request = null;
		if(isset($result[$pin_id])) return $result[$pin_id];
		if($model_images === null) { $model_images = new Helper_Images(); }
		if($request === null) { $request = JO_Request::getInstance(); }
		$users = Model_Users::getUsers(array(
			'filter_like_pin_id' => $pin_id,
			'start' => 0,
			'limit' => 20,
			'order' => 'pins_likes.like_id',
			'sort' => 'DESC'
		));
		$data = array();
		if($users) { 
			foreach($users AS $user) {
				
				$avatar = Helper_Uploadimages::avatar($user, '_A');
				$user['avatar'] = $avatar['image'];
				
				$data[] = array(
					'avatar' => $user['avatar'],
					'fullname' => $user['fullname'],
					'href' => WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $user['user_id'])
				);
			}
		}
		$result[$pin_id] = array(
			'data' => $data,
			'total' => (Model_Users::getTotalUsers(array(
				'filter_like_pin_id' => $pin_id
			)) - count($data))
		);
		return $result[$pin_id];
	}
	
	public static function getRePins($pin_id) {
		static $result = array(), $model_images = null, $request = null;
		if(isset($result[$pin_id])) return $result[$pin_id];
		if($model_images === null) { $model_images = new Helper_Images(); }
		if($request === null) { $request = JO_Request::getInstance(); }
		$pins = Model_Pins::getPins(array(
			'filter_repin_from' => $pin_id,
			'start' => 0,
			'limit' => 6
		)); 
		$data = array();
		
		if($pins) {
			foreach($pins AS $pin) {

				$img = Helper_Uploadimages::pin($pin, '_A');
				$image = $img['image'];
				
				$avatar = Helper_Uploadimages::avatar($pin['user'], '_A');
				$pin['user']['avatar'] = $avatar['image'];
				
				$pin['user']['profile'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $pin['user_id'] );
		
				$data[] = array(
					'board' => $pin['board'],
					'user' => $pin['user'],
					'onto_href' => WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=view&user_id=' . $pin['user_id'] . '&board_id=' . $pin['board_id'] ),
					'thumb' => $image,
					'href' => WM_Router::create($request->getBaseUrl() . '?controller=pin&pin_id=' . $pin['pin_id'] )
				);
			}
		}
		$result[$pin_id] = $data;
		return $data;
	}
	
	public static function getBoardPins($board_id, $limit = 12, $thumb = 75) {
		static $result = array(), $model_images = null, $request = null;
		if(isset($result[$board_id])) return $result[$board_id];
		if($model_images === null) { $model_images = new Helper_Images(); }
		if($request === null) { $request = JO_Request::getInstance(); }
		$pins = Model_Pins::getPins(array(
			'filter_board_id' => $board_id,
			'start' => 0,
			'limit' => $limit
		));
		$data = array();
		if($pins) {
			foreach($pins AS $pin) {
				$image = Helper_Uploadimages::pin($pin, '_A');
				if($image) {
					$data[] = array(
							'board' => Model_Boards::getBoardWithoutUser($board_id),
							'thumb' => $image['image'],
							'href' => WM_Router::create($request->getBaseUrl() . '?controller=boards&action=view&user_id=' . $pin['user_id'] . '&board_id=' . $pin['board_id'])
					);
				}
			}
		}
		$result[$board_id] = $data;
		return $data;
	}
	
	public static function getOriginallyPinned($user_id) {
		static $result = array(), $model_images = null, $request = null;
		if(isset($result[$user_id])) return $result[$user_id];
		if($model_images === null) { $model_images = new Helper_Images(); }
		if($request === null) { $request = JO_Request::getInstance(); }
		$pins = Model_Pins::getPins(array(
			'filter_user_id' => $user_id,
			'start' => 0,
			'limit' => 6
		));
		$data = array();
		if($pins) {
			foreach($pins AS $pin) {
				$image = Helper_Uploadimages::pin($pin, '_A');
				if($image) {
					$data[] = array(
							'user' => Model_Users::getUser($user_id),
							'thumb' => $image['image'],
							'href' => WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $pin['user_id'])
					);
				}
			}
		}
		$result[$user_id] = $data;
		return $data;
	}
	
	public static function getSourcePins($source_id, $limit = 5, $thumb = 75) {
		static $result = array(), $model_images = null, $request = null;
		if(isset($result[$source_id])) return $result[$source_id];
		if($model_images === null) { $model_images = new Helper_Images(); }
		if($request === null) { $request = JO_Request::getInstance(); }
		$pins = Model_Pins::getPins(array(
			'filter_source_id' => $source_id,
			'start' => 0,
			'limit' => $limit
		));
		$data = array();
		if($pins) {
			foreach($pins AS $pin) {
				$image = Helper_Uploadimages::pin($pin, '_A');
				if($image) {
					$data[] = array(
							'thumb' => $image['image'],
							'href' => WM_Router::create($request->getBaseUrl() . '?controller=source&source_id=' . $pin['source_id'])
					);
				}
			}
		}
		$result[$source_id] = $data;
		return $data;
	}

	public static function returnHtml($pin, $recache = false) { 
		static $view = null, $model_images = null, $request = null;
		if($view === null) { $view = JO_View::getInstance(); }
		if($model_images === null) { $model_images = new Helper_Images(); }
		if($request === null) { $request = JO_Request::getInstance(); }
		
		$view->image_no_cache = JO_Date::dateToUnix($pin['date_modified']);
		
		if(!JO_Registry::get('isMobile')) {
			$cache_file = Model_Pins::generateCachePatch($pin);
			if($cache_file && file_exists($cache_file)) {
				if(JO_Date::dateToUnix($pin['date_modified']) >= JO_Date::dateToUnix(filemtime($cache_file))) {
					$recache = true;
				}
			} 
			$content = false;
                        
			if(!$recache) {
			//CACHE OFF
			if($cache_file && file_exists($cache_file)) {
				$content = Model_Pins::getCache($cache_file);
				
				if($content && $content['html'] && $content['date_added'] > JO_Date::dateToUnix($pin['date_modified'])) {
					return $content['html'];
				}
			}
                         
			}
                         
		}
          //$image='';     
		//error_log("INICIO IMAGE thumb _B (): ".self::udate("Y-m-d H:i:s.u"));
		//$image = Helper_Uploadimages::pin($pin, '_B');
		//if($image) {
			//$pin['thumb'] = $image['image'];
			//$pin['thumb_width'] = $image['width'];
			//$pin['thumb_height'] = $image['height'];
			//$pin['original_image'] = $image['original'];
		//} else {
			//return '';
		//}
		//cogemos la extensión del fichero
		$extension= substr(strrchr($pin['image'], '.'), 1);
		//ahora la quitamos
		$nombreSextension=substr($pin['image'], 0,strlen($pin['image'])-strlen($extension)-1);
		if ($pin["store"]=="amazons3")
		{
			$host="http://images.amatteur.com/";
			$sufijo="_B.";
			
			//$img_size = @getimagesize($host.$nombreSextension."_B.".$extension);
			$pin['thumb'] = $host.$nombreSextension.$sufijo.$extension;
			if ($pin['width']!=0)
			{
				$pin['thumb_width'] = $pin['width'];
				$pin['thumb_height'] = $pin['height'];
			}else
			{
				$pin['thumb_width'] = 194;
				$pin['thumb_height'] = $pin['height'];
			}
			$pin['original_image'] = $host.$pin['image'];
		}else
		{
			$host="/uploads";
			$sufijo=".";
			$image = Helper_Uploadimages::pin($pin, '_B');
			if($image) {
				$pin['thumb'] = $image['image'];
				$pin['thumb_width'] = $image['width'];
				$pin['thumb_height'] = $image['height'];
				$pin['original_image'] = $image['original'];
			} else {
				return '';
			}
		}
		//error_log("FIN IMAGE thumb _B (): ".self::udate("Y-m-d H:i:s.u"));
		//error_log("INICIO IMAGE thumb _D (): ".self::udate("Y-m-d H:i:s.u"));
		//$image = Helper_Uploadimages::pin($pin, '_D');
		//if($image) {
			//$pin['popup'] = $image['image'];
			//$pin['popup_width'] = $image['width'];
			//$pin['popup_height'] = $image['height'];
			//$pin['original_image'] = $image['original'];
		//}else {
			//return '';
		//}
		
		if ($pin["store"]=="amazons3")
		{
			$host="http://images.amatteur.com/";
			$sufijo="_D.";
		}
		else
		{
			$host="/uploads";
			$sufijo=".";
		}
		$pin['popup'] = $host.$nombreSextension.$sufijo.$extension;
		//$pin['popup_width'] = $pin['width'];
		//$pin['popup_height'] = $pin['height'];
		$pin['original_image'] = $host.$pin['image'];
		//error_log("FIN IMAGE thumb _D (): ".self::udate("Y-m-d H:i:s.u"));
		$date_dif = array_shift( WM_Date::dateDiff($pin['date_added'], time()) );
		$pin['date_dif'] = $date_dif;
		
		
		$pin['description'] = self::descriptionFix($pin['description']);    
		
		//$pin['description'] = $pin['user'];
				
		$pin['href'] = WM_Router::create( $request->getBaseUrl() . '?controller=pin&pin_id=' . $pin['pin_id'] );
		
		if(JO_Session::get('user[user_id]')) {
			$pin['url_like'] = WM_Router::create( $request->getBaseUrl() . '?controller=pin&action=like&pin_id=' . $pin['pin_id'] );
			$pin['url_repin'] = WM_Router::create( $request->getBaseUrl() . '?controller=pin&action=repin&pin_id=' . $pin['pin_id'] );
			$pin['url_comment'] = WM_Router::create( $request->getBaseUrl() . '?controller=pin&pin_id=' . $pin['pin_id'] );
            $pin['comment'] = WM_Router::create( $request->getBaseUrl() . '?controller=pin&pin_id=' . $pin['pin_id'] );                        
			$pin['edit'] = JO_Session::get('user[user_id]') == $pin['user_id'] ? WM_Router::create( $request->getBaseUrl() . '?controller=pin&action=edit&pin_id=' . $pin['pin_id'] ) : false;
		} else {
			$pin['url_like'] = $pin['url_repin'] = $pin['url_comment'] = $pin['comment'] = WM_Router::create( $request->getBaseUrl() . '?controller=landing' );
			$pin['edit'] = false;
		}
		
		$pin['onto_href'] = WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=view&user_id=' . $pin['user_id'] . '&board_id=' . $pin['board_id'] );
		$pin['price_formated'] = WM_Currency::format($pin['price']);
		
		$view->author = $pin['user'];
		
		if ($pin["user"]["store"]=="amazons3")
		{
			$host='http://' . JO_Registry::get('bucklet') . '.' . trim(JO_Registry::get('awsDomain'),'.') . '/';
                        //$host="http://images.amatteur.com/";
			$sufijo="_A.";
		}else
		{
			$host="/uploads";
			$sufijo=".";
		}
		if($pin["user"]["avatar"]=="")
		{
			$imageUser="/uploads/cache/data/amatteur/amatteur_azul-50x50-crop.jpg";
		}else
		{
			//cogemos la extensión del fichero
			$extension= substr(strrchr($pin["user"]["avatar"], '.'), 1);
			//ahora la quitamos
			$nombreSextension=substr($pin["user"]["avatar"], 0,strlen($pin["user"]["avatar"])-strlen($extension)-1);
			$imageUser=$host.$nombreSextension.$sufijo.$extension;
		}
		$view->author['avatar'] =$imageUser;
		//$avatar = Helper_Uploadimages::avatar($pin['user'], '_A');
		//$avatar='';
		//$view->author['avatar'] = $avatar['image'];
		
		
		$view->author['profile'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $pin['user_id'] );
		
		if(JO_Session::get('user[user_id]')) {
			//error_log("entra");
			$imageProp=JO_Session::get('user');
			if ($imageProp["store"]=="amazons3")
				{
					//$host='http://' . JO_Registry::get('bucklet') . '.' . trim(JO_Registry::get('awsDomain'),'.') . '/';
                                        $host="http://images.amatteur.com/";
					$sufijo="_A.";
				}else
				{
					$host="/uploads";
					$sufijo=".";
				}
				if($imageProp["avatar"]=="")
				{
					$imageUser="/uploads/cache/data/amatteur/amatteur_azul-50x50-crop.jpg";
				}else
				{
					//cogemos la extensión del fichero
					$extension= substr(strrchr($imageProp["avatar"], '.'), 1);
					//ahora la quitamos
					$nombreSextension=substr($imageProp["avatar"], 0,strlen($imageProp["avatar"])-strlen($extension)-1);
					$imageUser=$host.$nombreSextension.$sufijo.$extension;
				}
				$view->author_self =$imageUser;
			//$avatar = Helper_Uploadimages::avatar(JO_Session::get('user'), '_A');
			//$avatar='';
			//$view->author_self = $avatar['image'];

			$view->profile_self = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . JO_Session::get('user[user_id]') );
		}
		
		if($pin['latest_comments']) {
			foreach($pin['latest_comments'] AS $key => $comment) {

				if(!isset($pin['latest_comments'][$key]['user']['store'])) {
					unset($pin['latest_comments'][$key]);
					continue;
				}
				if ($pin['latest_comments'][$key]['user']["store"]=="amazons3")
				{
					//$host='http://' . JO_Registry::get('bucklet') . '.' . trim(JO_Registry::get('awsDomain'),'.') . '/';
                                        $host="http://images.amatteur.com/";
					$sufijo="_A.";
				}else
				{
					$host="/uploads";
					$sufijo=".";
				}
				if($pin['latest_comments'][$key]['user']["avatar"]=="")
				{
					$imageUser="/uploads/cache/data/amatteur/amatteur_azul-50x50-crop.jpg";
				}else
				{
					//cogemos la extensión del fichero
					$extension= substr(strrchr($pin['latest_comments'][$key]['user']["avatar"], '.'), 1);
					//ahora la quitamos
					$nombreSextension=substr($pin['latest_comments'][$key]['user']["avatar"], 0,strlen($pin['latest_comments'][$key]['user']["avatar"])-strlen($extension)-1);
					$imageUser=$host.$nombreSextension.$sufijo.$extension;
				}
				$pin['latest_comments'][$key]['user']['avatar'] =$imageUser;
				//$avatar = Helper_Uploadimages::avatar($pin['latest_comments'][$key]['user'], '_A');
				//$avatar='';
				//$pin['latest_comments'][$key]['user']['avatar'] = $avatar['image'];
				
				
				$pin['latest_comments'][$key]['user']['profile'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $comment['user_id'] );
                $pin['latest_comments'][$key]['delete'] = '';
				if( JO_Session::get('user[user_id]') ) {
					if( JO_Session::get('user[is_admin]') || JO_Session::get('user[user_id]') == $comment['user_id'] ) {
						$pin['latest_comments'][$key]['delete'] = WM_Router::create( $request->getBaseUrl() . '?controller=pin&action=deleteComment&comment_id=' . $comment['comment_id'] );
					}
				}
				
			}
		}
		
		$view->via = array();
		if($pin['via']) {
			$view->via = array(
				'profile' => WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $pin['via'] ),
				'fullname' => $pin['user_via']['fullname']
			);
		}
		
		$view->loged = (int)JO_Session::get('user[user_id]');
		$view->site_name = JO_Registry::get('site_name');
		
		$view->history_id = isset($pin['history_id']) ? $pin['history_id'] : '';
		$view->history_action = isset($pin['history_action']) ? ' '.$pin['history_action'] : '';
		
		$view->pin = $pin;
		
		$response = $view->render('pinBox', 'pin');
		//CACHE OFF
		if(!JO_Registry::get('isMobile')) {
			if($cache_file && file_exists($cache_file)) {
				Model_Pins::generateCache($cache_file, $response);
			}
		}

		return $response;
	}
	
	public static function returnHtmlTop($pin, $recache = false) { 
	
		static $view = null, $model_images = null, $request = null;
		if($view === null) { $view = JO_View::getInstance(); }
		if($model_images === null) { $model_images = new Helper_Images(); }
		if($request === null) { $request = JO_Request::getInstance(); }
		
                
		$image = Helper_Uploadimages::pin($pin, '_D');
		if($image) {
			$pin['thumb'] = $image['image'];
			$pin['thumb_width'] = $image['width'];
			$pin['thumb_height'] = $image['height'];
			$pin['original_image'] = $image['original'];
		} else {
			return '';
		}
	
		
		$date_dif = array_shift( WM_Date::dateDiff($pin['date_added'], time()) );
		$pin['date_dif'] = $date_dif;
		
		
		$pin['description'] = self::descriptionFix($pin['description']);
                
		$pin['href'] = WM_Router::create( $request->getBaseUrl() . '?controller=pin&pin_id=' . $pin['pin_id'] );
		
		if(JO_Session::get('user[user_id]')) {
			$pin['url_like'] = WM_Router::create( $request->getBaseUrl() . '?controller=pin&action=like&pin_id=' . $pin['pin_id'] );
			$pin['url_repin'] = WM_Router::create( $request->getBaseUrl() . '?controller=pin&action=repin&pin_id=' . $pin['pin_id'] );
			$pin['url_comment'] = WM_Router::create( $request->getBaseUrl() . '?controller=pin&pin_id=' . $pin['pin_id'] );
                        $pin['comment'] = WM_Router::create( $request->getBaseUrl() . '?controller=pin&pin_id=' . $pin['pin_id'] );                        
			$pin['edit'] = JO_Session::get('user[user_id]') == $pin['user_id'] ? WM_Router::create( $request->getBaseUrl() . '?controller=pin&action=edit&pin_id=' . $pin['pin_id'] ) : false;
		} else {
			$pin['url_like'] = $pin['url_repin'] = $pin['url_comment'] = $pin['comment'] = WM_Router::create( $request->getBaseUrl() . '?controller=landing' );
			$pin['edit'] = false;
		}
		
		$pin['onto_href'] = WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=view&user_id=' . $pin['user_id'] . '&board_id=' . $pin['board_id'] );
		$pin['price_formated'] = WM_Currency::format($pin['price']);
		
		$view->author = $pin['user'];
		
		
		$avatar = Helper_Uploadimages::avatar($pin['user'], '_A');
		$view->author['avatar'] = $avatar['image'];
		
		$view->author['profile'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $pin['user_id'] );
		
		if(JO_Session::get('user[user_id]')) {
			
			$avatar = Helper_Uploadimages::avatar(JO_Session::get('user'), '_A');
			$view->author_self = $avatar['image'];

			$view->profile_self = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . JO_Session::get('user[user_id]') );
		}
		
		if($pin['latest_comments']) {
			foreach($pin['latest_comments'] AS $key => $comment) {

				if(!isset($pin['latest_comments'][$key]['user']['store'])) {
					unset($pin['latest_comments'][$key]);
					continue;
				}
				
				$avatar = Helper_Uploadimages::avatar($pin['latest_comments'][$key]['user'], '_A');
				$pin['latest_comments'][$key]['user']['avatar'] = $avatar['image'];
				
				
				$pin['latest_comments'][$key]['user']['profile'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $comment['user_id'] );
                                $pin['latest_comments'][$key]['delete'] = '';
				if( JO_Session::get('user[user_id]') ) {
					if( JO_Session::get('user[is_admin]') || JO_Session::get('user[user_id]') == $comment['user_id'] ) {
						$pin['latest_comments'][$key]['delete'] = WM_Router::create( $request->getBaseUrl() . '?controller=pin&action=deleteComment&comment_id=' . $comment['comment_id'] );
					}
				}
				
			}
		}
		
		$view->via = array();
		if($pin['via']) {
			$view->via = array(
				'profile' => WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $pin['via'] ),
				'fullname' => $pin['user_via']['fullname']
			);
		}
		
		$view->loged = (int)JO_Session::get('user[user_id]');
		$view->site_name = JO_Registry::get('site_name');
		
		$view->history_id = isset($pin['history_id']) ? $pin['history_id'] : '';
		$view->history_action = isset($pin['history_action']) ? ' '.$pin['history_action'] : '';
		
		$view->pin = $pin;
		
		$response = $view->render('pinBoxTop', 'pin');
		
		return $response;
	}
        
	public static function formatUploadModule($store) {
		static $front = null, $request = null, $upload_store = array();
		if($request === null) { $request = JO_Request::getInstance(); }
		if($front === null) { $front = JO_Front::getInstance(); }
		
		if($store == 'local' || $store == '') { $store = 'locale'; }
		
		if(isset($upload_store[$store])) {
			return $upload_store[$store];
		} else {
			$upload_model = 'model_upload_' . $store;
			$upload_model = $front->formatModuleName($upload_model);
			$upload_store[$store] = $upload_model;
			return $upload_model;
		}
	}
	

	public static function returnHtmlDetail($pin, $banners = false) {
		//XPER: función que carga PIN
		static $view = null, $model_images = null, $request = null;
		if($view === null) { $view = JO_View::getInstance(); }
		if($model_images === null) { $model_images = new Helper_Images(); }
		if($request === null) { $request = JO_Request::getInstance(); }
		
		$next_pin = Model_Pins::getNextPin($pin['pin_id']);
		if($next_pin) {
			$view->next_navigation_pin = WM_Router::create( $request->getBaseUrl() . '?controller=pin&pin_id=' . $next_pin['pin_id'] );
		}
		$prev_pin = Model_Pins::getPrevPin($pin['pin_id']);
		if($prev_pin) {
			$view->prev_navigation_pin = WM_Router::create( $request->getBaseUrl() . '?controller=pin&pin_id=' . $prev_pin['pin_id'] );
		}
		
		$image = call_user_func(array(self::formatUploadModule($pin['store']), 'getPinImage'), $pin, '_B');
		if($image) {
			$pin['thumb'] = $image['image'];
			$pin['thumb_width'] = $image['width'];
			$pin['thumb_height'] = $image['height'];
			$pin['original_image'] = $image['original'];
		} else {
			JO_Action::getInstance()->forward('error', 'error404');
		}
		
		$image = call_user_func(array(self::formatUploadModule($pin['store']), 'getPinImage'), $pin, '_D');
		if($image) {
			$pin['popup'] = $image['image'];
			$pin['popup_width'] = $image['width'];
			$pin['popup_height'] = $image['height'];
			$pin['original_image'] = $image['original'];
		} else {
			JO_Action::getInstance()->forward('error', 'error404');
		}
		
		
		$pin_description = self::descriptionFix($pin['description']);
		$pin['real_description'] = self::descriptionFix($pin['description']);
		$pin['description'] = self::descriptionFix($pin['description']);
		$pin['href'] = WM_Router::create( $request->getBaseUrl() . '?controller=pin&pin_id=' . $pin['pin_id'] );
	
		
		$pin['pinmarklet_href'] = WM_Router::create( $request->getBaseUrl() . '?controller=pages&action=read&page_id=' . JO_Registry::get('page_pinmarklet') );
		
		$pin['onto_href'] = WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=view&user_id=' . $pin['user_id'] . '&board_id=' . $pin['board_id'] );
		$pin['price_formated'] = WM_Currency::format($pin['price']);
		
		$view->author = $pin['user'];
		
		$avatar = Helper_Uploadimages::avatar($pin['user'], '_A');
		$view->author['avatar'] = $avatar['image'];
		
		$view->author['profile'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $pin['user_id'] );
		
		$view->comments = array();
		if($pin['latest_comments']) {
			foreach($pin['latest_comments'] AS $key => $comment) {
				
				if(!isset($comment['user']['store'])) {
					unset($pin['latest_comments'][$key]);
					continue;
				}
				
				$avatar = Helper_Uploadimages::avatar($comment['user'], '_A');
				$comment['user']['avatar'] = $avatar['image'];
				
				$comment['user']['profile'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $comment['user_id'] );
				
				$comment['delete'] = '';
				if(!Model_Pins::commentIsReported($comment['comment_id'])) {
					$comment['report'] = WM_Router::create( $request->getBaseUrl() . '?controller=pin&action=reportComment&comment_id=' . $comment['comment_id'] );
				} else {
					$comment['report'] = '';
				}
				
				if( JO_Session::get('user[user_id]') ) {
					
					if( JO_Session::get('user[is_admin]') || JO_Session::get('user[user_id]') == $comment['user_id'] || JO_Session::get('user[user_id]') == $pin['board_data']['user_id'] ) {
						$comment['delete'] = WM_Router::create( $request->getBaseUrl() . '?controller=pin&action=deleteComment&comment_id=' . $comment['comment_id'] );
					}
				}
				
				$view->comments[] = $comment;
			}
		}
		
		$view->via = array();
		$view->via_repin = array();
		if($pin['via'] && $pin['user_via']) {
			$view->via = array(
				'profile' => WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $pin['via'] ),
				'fullname' => $pin['user_via']['fullname']
			);
		}
		
		$view->replin_info = false;
		if($request->isXmlHttpRequest()) {
			$view->target_repin = false;
			if($pin['repin_from']) {
				$pin_repin = Model_Pins::getPin($pin['repin_from']);
				if($pin_repin) {
					$view->source = array();
					$view->source['source'] = $pin_repin['board'];
					$pin['from'] = WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=view&user_id=' . $pin_repin['user_id'] . '&board_id=' . $pin_repin['board_id'] );
					$view->target_repin = true;
				} else {
					$view->source = Model_Source::getSource($pin['source_id']);
				}
			} else {
				$view->source = Model_Source::getSource($pin['source_id']);
			}
		} else {
			$view->source = Model_Source::getSource($pin['source_id']);
			
			
			if($pin['repin_from']) {
				$repina = Model_Pins::getPin($pin['repin_from']);
				if($repina) {
					$view->replin_info = array(
						'pin_href' => WM_Router::create( $request->getBaseUrl() . '?controller=pin&pin_id=' . $pin['repin_from'] ),
						'profile' => WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $repina['user_id'] ),
						'fullname' => $repina['user']['fullname']
					);
				}
			}
			
		}
		if($pin['from'] && !preg_match('/^https?:\/\//',$pin['from'])) {
			$pin['from'] = 'http://' . $pin['from'];
		}
		if($pin['from_repin'] && !preg_match('/^https?:\/\//',$pin['from_repin'])) {
			$pin['from_repin'] = 'http://' . $pin['from_repin'];
		}
		
		$pin['onto_board'] = $pin['onto_board2'] = array();
		$pin['originally_pinned'] = $pin['originally_pinned2'] = array();
		if($request->isXmlHttpRequest()) {
			$pin['onto_board'] = self::getBoardPins($pin['board_id']);
			$pin['originally_pinned'] = self::getOriginallyPinned( $pin['via']?$pin['via']:$pin['user_id'] ); 
		} else {
			$pin['onto_board2'] = self::getBoardPins($pin['board_id']);
			$pin['originally_pinned2'] = self::getOriginallyPinned( $pin['via']?$pin['via']:$pin['user_id'] );
		}
		
		if($view->source && $request->isXmlHttpRequest()) {
			$pin['source_pins'] = self::getSourcePins($pin['source_id']);
		} else {
			$pin['source_pins'] = array();
		}
		
		$pin['boardIsFollow'] = Model_Users::isFollow(array(
			'board_id' => $pin['board_id']
		));
		
		$pin['userIsFollow'] = Model_Users::isFollowUser($pin['user_id']);
		if($pin['via']) {
			$pin['userViaIsFollow'] = Model_Users::isFollowUser($pin['via']);
		} else {
			$pin['userViaIsFollow'] = Model_Users::isFollowUser($pin['user_id']);
		}
		
		$pin['userFollowIgnore'] = $pin['user_id'] == JO_Session::get('user[user_id]');
		$pin['userViaFollowIgnore'] = ($pin['via']?$pin['via']:$pin['user_id']) == JO_Session::get('user[user_id]');
		
		$view->follow = WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=follow&user_id=' . $pin['user_id'] . '&board_id=' . $pin['board_id'] );
		$view->follow_user = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=follow&user_id=' . $pin['user_id'] );
		if($pin['via']) {
			$view->follow_user_via = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=follow&user_id=' . $pin['via'] );
		} else {
			$view->follow_user_via = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=follow&user_id=' . $pin['user_id'] );
		}
		
		$view->get_user_friends = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=friends' );
		
		if(JO_Session::get('user[user_id]')) {
			$view->enable_follow = $pin['user_id'] != JO_Session::get('user[user_id]');
		} else {
			$view->enable_follow = false;
		}
		
		if(JO_Session::get('user[user_id]')) {
			$pin['url_like'] = WM_Router::create( $request->getBaseUrl() . '?controller=pin&action=like&pin_id=' . $pin['pin_id'] );
			$pin['url_repin'] = WM_Router::create( $request->getBaseUrl() . '?controller=pin&action=repin&pin_id=' . $pin['pin_id'] );
			$pin['url_comment'] = WM_Router::create( $request->getBaseUrl() . '?controller=pin&action=comment&pin_id=' . $pin['pin_id'] );
                        $pin['comment'] = WM_Router::create( $request->getBaseUrl() . '?controller=pin&action=comment&pin_id=' . $pin['pin_id'] );
			$pin['edit'] = JO_Session::get('user[user_id]') == $pin['user_id'] ? WM_Router::create( $request->getBaseUrl() . '?controller=pin&action=edit&pin_id=' . $pin['pin_id'] ) : false;
		} else {
			$pin['url_like'] = $pin['url_repin'] = $pin['url_comment'] = $pin['comment'] = WM_Router::create( $request->getBaseUrl() . '?controller=landing' );
			$pin['edit'] = false;
		}
		
		$likes = self::getPinLikes($pin['pin_id']);
		$pin['likes'] = $likes['data'];
		$pin['likes_total'] = $likes['total'];
		
		$pin['repins'] = self::getRePins($pin['pin_id']);
		
		$pin['pinIsReported'] = Model_Pins::pinIsReported($pin['pin_id']);
		
		$date_dif = array_shift( WM_Date::dateDiff($pin['date_added'], time()) );
		$pin['date_dif'] = $date_dif;
		
		$view->loged = JO_Session::get('user[user_id]');
		$view->site_name = JO_Registry::get('site_name');
		
		if($view->loged) {
			$avatar = Helper_Uploadimages::avatar(JO_Session::get('user'), '_A');
			$view->self_avatar = $avatar['image'];
			$view->self_profile = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $view->loged );
			$view->self_fullname = JO_Session::get('user[firstname]') . ' ' . JO_Session::get('user[lastname]');
		}
		
		
		if($pin['is_video']) {
			$auto = new Helper_AutoEmbed();
			if( $pin['repin_from'] && $auto->parseUrl($pin['from_repin']) ) {
				$auto->setWidth('100%');
				$auto->setHeight('350');
				$pin['video_code'] = $auto->getEmbedCode();
				$attr = $auto->getObjectAttribs();
				$pin['thumb_width'] = $attr['width'];
				$pin['thumb_height'] = $attr['height'];
			} else {
				if( $auto->parseUrl($pin['from']) ) {
					$auto->setWidth('100%');
					$auto->setHeight('350');
					$pin['video_code'] = $auto->getEmbedCode();
					$attr = $auto->getObjectAttribs();
					$pin['thumb_width'] = $attr['width'];
					$pin['thumb_height'] = $attr['height'];
				} else {
					$pin['is_video'] = false;
				}
			}
		} else {
			$auto = new Helper_AutoEmbed();
			if( $pin['repin_from'] && $auto->parseUrl($pin['from_repin']) ) {
				$auto->setWidth('100%');
				$auto->setHeight('350');
				$pin['video_code'] = $auto->getEmbedCode();
				$attr = $auto->getObjectAttribs();
				$pin['thumb_width'] = $attr['width'];
				$pin['thumb_height'] = $attr['height'];
				$pin['is_video'] = true;
			} else if( $auto->parseUrl($pin['from']) ) {
				$auto->setWidth('100%');
				$auto->setHeight('350');
				$pin['video_code'] = $auto->getEmbedCode();
				$attr = $auto->getObjectAttribs();
				$pin['thumb_width'] = $attr['width'];
				$pin['thumb_height'] = $attr['height'];
				$pin['is_video'] = true;
			} else {
				$pin['is_video'] = false;
			}
		}
		
		$view->pin_url = WM_Router::create( $request->getBaseUrl() . '?controller=pin&pin_id=' . $pin['pin_id'] );
		
		$view->login_href = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=login&next=' . urlencode($pin['href']) );
		
		Model_Pins::updateViewed($pin['pin_id']);
		
		JO_Layout::getInstance()->meta_title = $pin['board'] . ' - ' . strip_tags( html_entity_decode($pin_description) );
		JO_Layout::getInstance()->placeholder('pin_url', ($view->replin_info ? $view->replin_info['pin_href'] : $view->pin_url ));
		JO_Layout::getInstance()->placeholder('pin_description', $pin_description);
		
		$params = array();
		$params['content'] = html_entity_decode($pin['description'] . ' ' . $pin['board'], ENT_QUOTES, 'UTF-8'); //page content
		$keywords = new WM_Keywords($params);
		$get_keywords = $keywords->get_keywords();
		if($get_keywords) {
			JO_Layout::getInstance()->placeholder('keywords', $get_keywords);
		}
		JO_Layout::getInstance()->placeholder('pin_image', $pin['thumb']);
		JO_Layout::getInstance()->placeholder('board_title', $pin['board']);
		
		$view->banners = array();
		if($banners) {
			foreach($banners AS $banner1) {
				foreach($banner1 AS $e) {
					$e['html'] = html_entity_decode($e['html']);
					$view->banners[] = $e;
				}
			}
		}
		
		$view->pin = $pin;
		return $view->render('pinBoxDetail', 'pin');
	}
	public static function udate($format, $utimestamp = null) {
	  if (is_null($utimestamp))
		$utimestamp = microtime(true);
	
	  $timestamp = floor($utimestamp);
	  $milliseconds = round(($utimestamp - $timestamp) * 1000000);
	
	  return date(preg_replace('`(?<!\\\\)u`', $milliseconds, $format), $timestamp);
	}
	
}

?>
