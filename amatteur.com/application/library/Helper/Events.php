<?php

class Helper_Events {
	
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
                $description = nl2br($description);
                //$description = str_replace ("<br />", " ", $description);
		//$description = preg_replace('/(<!--|-->)/Uis','',$description);
		$description = self::parse_urls(str_replace('&amp;', '&', $description), 35, '_blank');
		return $description;
	}
	
	public static function returnHtmlDetail($event, $banners = false) {
		//XPER: función que carga PIN
		static $view = null, $model_images = null, $request = null;
		if($view === null) { $view = JO_View::getInstance(); }
		if($model_images === null) { $model_images = new Helper_Images(); }
		if($request === null) { $request = JO_Request::getInstance(); }
		/*
		$next_pin = Model_Pins::getNextPin($event['event_id']);
		if($next_pin) {
			$view->next_navigation_pin = WM_Router::create( $request->getBaseUrl() . '?controller=pin&event_id=' . $next_pin['event_id'] );
		}
		$prev_pin = Model_Pins::getPrevPin($event['event_id']);
		if($prev_pin) {
			$view->prev_navigation_pin = WM_Router::create( $request->getBaseUrl() . '?controller=pin&event_id=' . $prev_pin['event_id'] );
		}
		*/
		$image = call_user_func(array(self::formatUploadModule($event['store']), 'getEventImage'), $event, '_B');
		if($image) {
			$event['thumb'] = $image['image'];
			$event['thumb_width'] = $image['width'];
			$event['thumb_height'] = $image['height'];
			$event['original_image'] = $image['original'];
		} else {
			JO_Action::getInstance()->forward('error', 'error404');
		}
		
		$image = call_user_func(array(self::formatUploadModule($event['store']), 'getEventImage'), $event, '_D');
		if($image) {
			$event['popup'] = $image['image'];
			$event['popup_width'] = $image['width'];
			$event['popup_height'] = $image['height'];
			$event['original_image'] = $image['original'];
		} else {
			//JO_Action::getInstance()->forward('error', 'error404');
                        $image = call_user_func(array(self::formatUploadModule($event['store']), 'getEventImage'), $event, '_B');
                        if($image) {
                            $event['popup'] = $image['image'];
                            $event['popup_width'] = $image['width'];
                            $event['popup_height'] = $image['height'];
                            $event['original_image'] = $image['original'];
                        }

		}
		
		
		$event_description = self::descriptionFix($event['description']);
		$event['real_description'] = self::descriptionFix($event['description']);
		$event['description'] = self::descriptionFix($event['description']);
		$event['href'] = WM_Router::create( $request->getBaseUrl() . '?controller=events&action=indexeventBoxDetail&event_id=' . $event['event_id'] );
	
                //$event['date_event'] = Model_Events::cambiafyh_espanol($event['date_event']);
                        
                $event["sport_category"] = Model_Boards::getCategoryTitle($event["sport_category"]);

                $page = (int) $request->getRequest('page');
                if ($page < 1)
                {
                    $page = 1;
                }
                
                $data = array(
                    'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                    'limit' => JO_Registry::get('config_front_limit'),
                    'filter_user_id' => $event["user_id"]
                );

                $users = Model_Users::getUsers($data);
                if ($users)
                {
                    $event['fullname'] = $users[0]["fullname"];
                    $event['descriptionUser'] = $users[0]["description"];
                    $avataruser = Helper_Uploadimages::avatar($users[0], '_B');
                    $event['avataruser'] = $avataruser['image'];


                    $event['userHref'] = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $event['user_id']);
                    $href = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $event['user_id']);
                }
                        

		
		$event['pinmarklet_href'] = WM_Router::create( $request->getBaseUrl() . '?controller=pages&action=read&page_id=' . JO_Registry::get('page_pinmarklet') );
		
		//$event['onto_href'] = WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=view&user_id=' . $event['user_id'] . '&board_id=' . $event['board_id'] );
		//$event['price_formated'] = WM_Currency::format($event['price']);
                
                
                // esto es del autor del evento
                /*
		
		$view->author = $event['user_id'];
		
		$avatar = Helper_Uploadimages::avatar($event['user_id'], '_A');
		$view->author['avatar'] = $avatar['image'];
		
		$view->author['profile'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $event['user_id'] );
		
                 * 
                 * 
                 */
                
                
                //comentarios
                
		$view->comments = array();

		if($event['latest_comments']) {
			foreach($event['latest_comments'] AS $key => $comment) {
				
				if(!isset($comment['user']['store'])) {
					unset($event['latest_comments'][$key]);
					continue;
				}
				
				$avatar = Helper_Uploadimages::avatar($comment['user'], '_A');
				$comment['user']['avatar'] = $avatar['image'];
				
				$comment['user']['profile'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $comment['user_id'] );
				
				$comment['delete'] = '';
				if(!Model_Events::commentIsReported($comment['comment_id'])) {
					$comment['report'] = WM_Router::create( $request->getBaseUrl() . '?controller=events&action=reportComment&comment_id=' . $comment['comment_id'] );
				} else {
					$comment['report'] = '';
				}
				
				if( JO_Session::get('user[user_id]') ) {
					
					if( JO_Session::get('user[is_admin]') || JO_Session::get('user[user_id]') == $comment['user_id']  ) {
						$comment['delete'] = WM_Router::create( $request->getBaseUrl() . '?controller=events&action=deleteComment&comment_id=' . $comment['comment_id'] );
					}
				}
				
				$view->comments[] = $comment;
			}
		}
		
                $view->get_user_friends = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=friends' );
                $view->totalFollow = WM_Router::create( $request->getBaseUrl() . '?controller=events&action=totalFollow' );
                
                // no se pa que vale
                /*
		$view->via = array();
		$view->via_repin = array();
		if($event['via'] && $event['user_via']) {
			$view->via = array(
				'profile' => WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $event['via'] ),
				'fullname' => $event['user_via']['fullname']
			);
		}
		
		$view->replin_info = false;
		if($request->isXmlHttpRequest()) {
			$view->target_repin = false;
			if($event['repin_from']) {
				$event_repin = Model_Pins::getPin($event['repin_from']);
				if($event_repin) {
					$view->source = array();
					$view->source['source'] = $event_repin['board'];
					$event['from'] = WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=view&user_id=' . $event_repin['user_id'] . '&board_id=' . $event_repin['board_id'] );
					$view->target_repin = true;
				} else {
					$view->source = Model_Source::getSource($event['source_id']);
				}
			} else {
				$view->source = Model_Source::getSource($event['source_id']);
			}
		} else {
			$view->source = Model_Source::getSource($event['source_id']);
			
			
			if($event['repin_from']) {
				$repina = Model_Pins::getPin($event['repin_from']);
				if($repina) {
					$view->replin_info = array(
						'pin_href' => WM_Router::create( $request->getBaseUrl() . '?controller=pin&event_id=' . $event['repin_from'] ),
						'profile' => WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $repina['user_id'] ),
						'fullname' => $repina['user']['fullname']
					);
				}
			}
			
		}
		if($event['from'] && !preg_match('/^https?:\/\//',$event['from'])) {
			$event['from'] = 'http://' . $event['from'];
		}
		if($event['from_repin'] && !preg_match('/^https?:\/\//',$event['from_repin'])) {
			$event['from_repin'] = 'http://' . $event['from_repin'];
		}
		
		$event['onto_board'] = $event['onto_board2'] = array();
		$event['originally_pinned'] = $event['originally_pinned2'] = array();
                */
                
                
                
                /*
		if($request->isXmlHttpRequest()) {
			$event['onto_board'] = self::getBoardPins($event['board_id']);
			$event['originally_pinned'] = self::getOriginallyPinned( $event['via']?$event['via']:$event['user_id'] ); 
		} else {
			$event['onto_board2'] = self::getBoardPins($event['board_id']);
			$event['originally_pinned2'] = self::getOriginallyPinned( $event['via']?$event['via']:$event['user_id'] );
		}
		
		if($view->source && $request->isXmlHttpRequest()) {
			$event['source_pins'] = self::getSourcePins($event['source_id']);
		} else {
			$event['source_pins'] = array();
		}
		
		$event['boardIsFollow'] = Model_Users::isFollow(array(
			'board_id' => $event['board_id']
		));
		
		$event['userIsFollow'] = Model_Users::isFollowUser($event['user_id']);
		if($event['via']) {
			$event['userViaIsFollow'] = Model_Users::isFollowUser($event['via']);
		} else {
			$event['userViaIsFollow'] = Model_Users::isFollowUser($event['user_id']);
		}
		
		$event['userFollowIgnore'] = $event['user_id'] == JO_Session::get('user[user_id]');
		$event['userViaFollowIgnore'] = ($event['via']?$event['via']:$event['user_id']) == JO_Session::get('user[user_id]');
		
		$view->follow = WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=follow&user_id=' . $event['user_id'] . '&board_id=' . $event['board_id'] );
		$view->follow_user = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=follow&user_id=' . $event['user_id'] );
		if($event['via']) {
			$view->follow_user_via = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=follow&user_id=' . $event['via'] );
		} else {
			$view->follow_user_via = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=follow&user_id=' . $event['user_id'] );
		}
		
		$view->get_user_friends = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=friends' );
		
		if(JO_Session::get('user[user_id]')) {
			$view->enable_follow = $event['user_id'] != JO_Session::get('user[user_id]');
		} else {
			$view->enable_follow = false;
		}
		*/
		if(JO_Session::get('user[user_id]')) {
			$event['url_like'] = WM_Router::create( $request->getBaseUrl() . '?controller=events&action=like&event_id=' . $event['event_id'] );
			$event['url_repin'] = WM_Router::create( $request->getBaseUrl() . '?controller=events&action=repin&event_id=' . $event['event_id'] );
			$event['url_comment'] = WM_Router::create( $request->getBaseUrl() . '?controller=events&action=comment&event_id=' . $event['event_id'] );
                        $event['comment'] = WM_Router::create( $request->getBaseUrl() . '?controller=events&action=comment&event_id=' . $event['event_id'] );
			$event['edit'] = JO_Session::get('user[user_id]') == $event['user_id'] ? WM_Router::create( $request->getBaseUrl() . '?controller=events&action=events&event_id=' . $event['event_id'] ) : false;
		} else {
			$event['url_like'] = $event['url_repin'] = $event['url_comment'] = $event['comment'] = WM_Router::create( $request->getBaseUrl() . '?controller=landing' );
			$event['edit'] = false;
		}
		/*
		$likes = self::getPinLikes($event['event_id']);
		$event['likes'] = $likes['data'];
		$event['likes_total'] = $likes['total'];
		
		$event['repins'] = self::getRePins($event['event_id']);
		
		$event['pinIsReported'] = Model_Pins::pinIsReported($event['event_id']);
		
		$date_dif = array_shift( WM_Date::dateDiff($event['date_added'], time()) );
		$event['date_dif'] = $date_dif;
		*/
		$view->loged = JO_Session::get('user[user_id]');
                $view->owner = (JO_Session::get('user[user_id]') == $event["user_id"]);
		$view->site_name = JO_Registry::get('site_name');
		
		if($view->loged) {
			$avatar = Helper_Uploadimages::avatar(JO_Session::get('user'), '_A');
			$view->self_avatar = $avatar['image'];
			$view->self_profile = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $view->loged );
			$view->self_fullname = JO_Session::get('user[firstname]') . ' ' . JO_Session::get('user[lastname]');
		}
		
		
                /*
		if($event['is_video']) {
			$auto = new Helper_AutoEmbed();
			if( $event['repin_from'] && $auto->parseUrl($event['from_repin']) ) {
				$auto->setWidth('100%');
				$auto->setHeight('350');
				$event['video_code'] = $auto->getEmbedCode();
				$attr = $auto->getObjectAttribs();
				$event['thumb_width'] = $attr['width'];
				$event['thumb_height'] = $attr['height'];
			} else {
				if( $auto->parseUrl($event['from']) ) {
					$auto->setWidth('100%');
					$auto->setHeight('350');
					$event['video_code'] = $auto->getEmbedCode();
					$attr = $auto->getObjectAttribs();
					$event['thumb_width'] = $attr['width'];
					$event['thumb_height'] = $attr['height'];
				} else {
					$event['is_video'] = false;
				}
			}
		} else 
                  {

			$auto = new Helper_AutoEmbed();
			if( $event['repin_from'] && $auto->parseUrl($event['from_repin']) ) {
				$auto->setWidth('100%');
				$auto->setHeight('350');
				$event['video_code'] = $auto->getEmbedCode();
				$attr = $auto->getObjectAttribs();
				$event['thumb_width'] = $attr['width'];
				$event['thumb_height'] = $attr['height'];
				$event['is_video'] = true;
			} else if( $auto->parseUrl($event['from']) ) {
				$auto->setWidth('100%');
				$auto->setHeight('350');
				$event['video_code'] = $auto->getEmbedCode();
				$attr = $auto->getObjectAttribs();
				$event['thumb_width'] = $attr['width'];
				$event['thumb_height'] = $attr['height'];
				$event['is_video'] = true;
			} else {
				$event['is_video'] = false;
			}
		}
		*/
		$view->event_url = WM_Router::create( $request->getBaseUrl() . '?controller=events&action=indexeventBoxDetail&event_id=' . $event['event_id'] ); 
                //WM_Router::create( $request->getBaseUrl() . '?controller=events&event_id=' . $event['event_id'] );
                
		$view->login_href = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=login&next=' . urlencode($event['href']) );
                
                $view->like_event = WM_Router::create( $request->getBaseUrl() . '?controller=events&action=like&event_id=' . $event['event_id'] . '&userio_id=' . $event['user_id'] ); 
                $view->eventIsLike = Model_Events::isLikeEvent($event['event_id'], JO_Session::get('user[user_id]'));
                
                $view->editEvent_url = WM_Router::create( $request->getBaseUrl() . '?controller=events&action=add?event_id=' . $event['event_id']  );
                
                $view->follow_event = WM_Router::create($request->getBaseUrl() . '?controller=events&action=follow&event_id=' . $event['event_id'] . '&userio_id=' . $event['user_id'] ); 
                $view->eventIsFollow = Model_Events::isFollowEvent($event['event_id'], JO_Session::get('user[user_id]'));

		
		//Model_Pins::updateViewed($event['event_id']);
                
		JO_Layout::getInstance()->meta_title = $event['eventname']. ' - ' . strip_tags( html_entity_decode($event_description) );
		JO_Layout::getInstance()->placeholder('pin_url', $view->event_url);
		JO_Layout::getInstance()->placeholder('pin_description', $event_description);
		
		$params = array();
		$params['content'] = html_entity_decode($event_description . ' ' . $event['eventname'], ENT_QUOTES, 'UTF-8'); //page content
		$keywords = new WM_Keywords($params);
		$get_keywords = $keywords->get_keywords();
		if($get_keywords) {
			JO_Layout::getInstance()->placeholder('keywords', $get_keywords);
		}
		JO_Layout::getInstance()->placeholder('pin_image', $event['thumb']);
		JO_Layout::getInstance()->placeholder('board_title', $event['eventname']);
		/*
		$view->banners = array();
		if($banners) {
			foreach($banners AS $banner1) {
				foreach($banner1 AS $e) {
					$e['html'] = html_entity_decode($e['html']);
					$view->banners[] = $e;
				}
			}
		}
		*/
		$view->event = $event;
		return $view->render('eventBoxDetail', 'events');
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
}

?>
