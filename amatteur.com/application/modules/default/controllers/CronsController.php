<?php

class CronsController extends JO_Action {
	
	private $now = null;
	
	public function init() {
		$request = $this->getRequest();
		$request->setBaseUrl( JO_Registry::get('config_base_domain') );
		$this->noViewRenderer();
		ignore_user_abort(true);
		$this->now = time();
	}

	public function generateCacheAction() {
		Model_Pins::Cmd();
		if(in_array( 'mobile', WM_Modules::getTemplates() )) {
			JO_Registry::set('config_front_limit', 5);
			Model_Pins::Cmd();
		}
	}

	public function generatePopularCacheAction() {
		Model_Pins::CmdPopular();
		if(in_array( 'mobile', WM_Modules::getTemplates() )) {
			JO_Registry::set('config_front_limit', 5);
			Model_Pins::CmdPopular();
		}
	}

	public function generateStatAction() {
		Model_Crons::stats();
	}

	public function updateStatAction() {
		Model_Crons::updateStats();
	}
	
	public function sendDailyAction() {
		
		$request = $this->getRequest();

		$this->view->base_href = $request->getBaseUrl();
		$this->view->site_name = JO_Registry::get('site_name');
		$this->view->on_facebook = JO_Registry::get('config_on_facebook');
		
		$this->view->site_logo = $this->view->base_href . 'data/images/logo.png';
		if(JO_Registry::get('site_logo') && file_exists(BASE_PATH .'/uploads'.JO_Registry::get('site_logo'))) {
		    $this->view->site_logo = $this->view->base_href . 'uploads' . JO_Registry::get('site_logo'); 
		}

		$this->view->settings = WM_Router::create( $this->view->base_href . '?controller=prefs' );
		
		$goodies = Model_Pages::getPage( JO_Registry::get('page_goodies') );
		if($goodies) {
			$this->view->pin_it = WM_Router::create($this->view->base_href . '?controller=pages&action=read&page_id=' . JO_Registry::get('page_goodies'));
		}
		
		$this->view->pages = array();
		$page = Model_Pages::getPage( JO_Registry::get('page_privacy_policy') );
		if($page) {
			$this->view->pages[] = array(
				'title' => $page['title'],
				'href' => WM_Router::create($this->view->base_href . '?controller=pages&action=read&page_id=' . JO_Registry::get('page_privacy_policy'))
			);
		}
		
		$page = Model_Pages::getPage( JO_Registry::get('page_terms') );
		if($page) {
			$this->view->pages[] = array(
				'title' => $page['title'],
				'href' => WM_Router::create($this->view->base_href . '?controller=pages&action=read&page_id=' . JO_Registry::get('page_privacy_policy'))
			);
		}
		
		
		$histories = Model_History::getHistoryToday(array(
			'today' => WM_Date::format($this->now, JO_Date::ATOM)
		));
		
		$no_avatar = JO_Registry::get('no_avatar');
		
		if($histories) {
			$model_images = new Helper_Images();
			foreach($histories AS $history) {
				$avatar = Helper_Uploadimages::avatar($history, '_B');
				$history['avatar'] = $avatar['image'];
				
				$history['user_followers'] = WM_Router::create( $this->view->base_href . '?controller=users&action=followers&user_id=' . $history['user_id']  );
				
				$history['profile'] = WM_Router::create( $this->view->base_href . '?controller=users&action=profile&user_id=' . $history['user_id'] );
				
				$history['history_comments_total'] = count($history['history_comments']);
				$history['history_follow_total'] = count($history['history_follow']);
				$history['history_like_total'] = count($history['history_like']);
				$history['history_repin_total'] = count($history['history_repin']);
				
				/////comments
				if($history['history_comments_total']) {
					foreach($history['history_comments'] AS $k => $v) {
						if(!isset($v['store'])) {
							continue;
						}
						$avatar = Helper_Uploadimages::avatar($v, '_A');
						$history['history_comments'][$k]['avatar'] = $avatar['image'];
						$history['history_comments'][$k]['profile'] = WM_Router::create( $this->view->base_href . '?controller=users&action=profile&user_id=' . $v['user_id'] );
					}
				}
				/////follow
				if($history['history_follow_total']) {
					foreach($history['history_follow'] AS $k => $v) {
						if(!isset($v['store'])) {
							continue;
						}
						$avatar = Helper_Uploadimages::avatar($v, '_A');
						$history['history_follow'][$k]['avatar'] = $avatar['image'];
						$history['history_follow'][$k]['profile'] = WM_Router::create( $this->view->base_href . '?controller=users&action=profile&user_id=' . $v['user_id'] );
					}
				}
				/////like
				if($history['history_like_total']) {
					foreach($history['history_like'] AS $k => $v) {
						if(!isset($v['store'])) {
							continue;
						}
						$avatar = Helper_Uploadimages::avatar($v, '_A');
						$history['history_like'][$k]['avatar'] = $avatar['image'];
						$history['history_like'][$k]['profile'] = WM_Router::create( $this->view->base_href . '?controller=users&action=profile&user_id=' . $v['user_id'] );
					}
				}
				/////repin
				if($history['history_repin_total']) {
					foreach($history['history_repin'] AS $k => $v) {
						if(!isset($v['store'])) {
							continue;
						}
						$avatar = Helper_Uploadimages::avatar($v, '_A');
						$history['history_repin'][$k]['avatar'] = $avatar['image'];
						$history['history_repin'][$k]['profile'] = WM_Router::create( $this->view->base_href . '?controller=users&action=profile&user_id=' . $v['user_id'] );
					}
				}
				
				$this->view->history = $history;
				
				$html = $this->view->render('sendDaily','crons');
				
				Model_Email::send(
					$history['email'],
					JO_Registry::get('noreply_mail'),
					sprintf($this->translate('Daily %s'), $this->view->site_name),
					$html
				);
				
			}
		}
		
		
		
	}
	
	public function sendWeeklyAction() {
		
		$request = $this->getRequest();
		
		$this->view->base_href = $request->getBaseUrl();
		$this->view->site_name = JO_Registry::get('site_name');
		$this->view->on_facebook = JO_Registry::get('config_on_facebook');
		
		$this->view->site_logo = $this->view->base_href . 'data/images/logo.png';
		if(JO_Registry::get('site_logo') && file_exists(BASE_PATH .'/uploads'.JO_Registry::get('site_logo'))) {
		    $this->view->site_logo = $this->view->base_href . 'uploads' . JO_Registry::get('site_logo'); 
		}

		$this->view->settings = WM_Router::create( $this->view->base_href . '?controller=prefs' );
		
		$goodies = Model_Pages::getPage( JO_Registry::get('page_goodies') );
		if($goodies) {
			//$this->view->pin_it = WM_Router::create($this->view->base_href . '?controller=pages&action=read&page_id=' . JO_Registry::get('page_goodies'));
                    $this->view->pin_it = 'http://amatteur.com/apps';
		}
		
		$this->view->pages = array();
		$page = Model_Pages::getPage( JO_Registry::get('page_privacy_policy') );
		if($page) {
			$this->view->pages[] = array(
				'title' => $page['title'],
				'href' => WM_Router::create($this->view->base_href . '?controller=pages&action=read&page_id=' . JO_Registry::get('page_privacy_policy'))
			);
		}
		
		$page = Model_Pages::getPage( JO_Registry::get('page_terms') );
		if($page) {
			$this->view->pages[] = array(
				'title' => $page['title'],
				'href' => WM_Router::create($this->view->base_href . '?controller=pages&action=read&page_id=' . JO_Registry::get('page_privacy_policy'))
			);
		}
		
		$histories = Model_History::getHistoryToday(array(
			'week_range' => WM_Date::x_week_range($this->now)
		));
		
		$no_avatar = JO_Registry::get('no_avatar');
		
		if($histories) {
			$model_images = new Helper_Images();
			
			/* BOARDS */
			$this->view->popular_bards = array();
			$populars = Model_Boards::getBoards(array(
				'start' => 0,
				'limit' => 6,
				'sort' => 'DESC',
				'order' => 'boards.total_views',
				'where' => new JO_Db_Expr('pins > 4')
			));
			
			if($populars) {
				foreach($populars AS $board) {
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
							$data_img = call_user_func(array(Helper_Pin::formatUploadModule($board['pins_array'][$i]['store']), 'getPinImage'), $board['pins_array'][$i], $size);
							if($data_img) {
								$board['thumbs'][] = $data_img['image'];
							} else {
								$board['thumbs'][] = false;
							}
						} else {
							$board['thumbs'][] = false;
						}
					}
					
					$board['user'] = Model_Users::getUser($board['user_id']);
					$board['user']['profile'] = WM_Router::create( $this->view->base_href . '?controller=users&action=profile&user_id=' . $board['user_id'] );
					
					$avatar = Helper_Uploadimages::avatar($board['user'], '_A');
					$board['user']['avatar'] = $avatar['image'];
					
					$this->view->popular_bards[] = $board;
					
				}
			}
		
			/* VIDEO */
//			$this->view->video = array();
//			$video = Model_Pins::getPins(array(
//				'start' => 0,
//				'limit' => 1,
//				'filter_is_video' => 1
//			));
//			
//			if($video) {
//				foreach($video AS $pin) {
//					$pin['thumb'] = $model_images->resizeWidth($pin['image'], 194);
//					$pin['thumb_width'] = $model_images->getSizes('width');
//					$pin['thumb_height'] = $model_images->getSizes('height');
//					$pin['description'] = Helper_Pin::descriptionFix($pin['description']);
//					$pin['href'] = WM_Router::create( $request->getBaseUrl() . '?controller=pin&pin_id=' . $pin['pin_id'] );
//								
//				}
//			}
			
			
			/* HISTORY */
			foreach($histories AS $history) {
				if(!isset($history['store'])) {
					continue;
				}
				$avatar = Helper_Uploadimages::avatar($history, '_B');
				$history['avatar'] = $avatar['image'];

				
				$history['user_followers'] = WM_Router::create( $this->view->base_href . '?controller=users&action=followers&user_id=' . $history['user_id']  );
				
				$history['profile'] = WM_Router::create( $this->view->base_href . '?controller=users&action=profile&user_id=' . $history['user_id'] );
				
				$history['history_comments_total'] = count($history['history_comments']);
				$history['history_follow_total'] = count($history['history_follow']);
				$history['history_like_total'] = count($history['history_like']);
				$history['history_repin_total'] = count($history['history_repin']);
                                $history['history_event_total'] = count($history['history_event']);
				
				/////comments
				if($history['history_comments_total']) {
					foreach($history['history_comments'] AS $k => $v) {
						if(!isset($v['store'])) {
							continue;
						}
						$avatar = Helper_Uploadimages::avatar($v, '_A');
						$history['history_comments'][$k]['avatar'] = $avatar['image'];
						$history['history_comments'][$k]['profile'] = WM_Router::create( $this->view->base_href . '?controller=users&action=profile&user_id=' . $v['user_id'] );
					}
				}
				/////follow
				if($history['history_follow_total']) {
					foreach($history['history_follow'] AS $k => $v) {
						if(!isset($v['store'])) {
							continue;
						}
						$avatar = Helper_Uploadimages::avatar($v, '_A');
						$history['history_follow'][$k]['avatar'] = $avatar['image'];
						$history['history_follow'][$k]['profile'] = WM_Router::create( $this->view->base_href . '?controller=users&action=profile&user_id=' . $v['user_id'] );
					}
				}
				/////like
				if($history['history_like_total']) {
					foreach($history['history_like'] AS $k => $v) {
						if(!isset($v['store'])) {
							continue;
						}
						$avatar = Helper_Uploadimages::avatar($v, '_A');
						$history['history_like'][$k]['avatar'] = $avatar['image'];
						$history['history_like'][$k]['profile'] = WM_Router::create( $this->view->base_href . '?controller=users&action=profile&user_id=' . $v['user_id'] );
					}
				}
				/////repin
				if($history['history_repin_total']) {
					foreach($history['history_repin'] AS $k => $v) {
						if(!isset($v['store'])) {
							continue;
						}
						$avatar = Helper_Uploadimages::avatar($v, '_A');
						$history['history_repin'][$k]['avatar'] = $avatar['image'];
						$history['history_repin'][$k]['profile'] = WM_Router::create( $this->view->base_href . '?controller=users&action=profile&user_id=' . $v['user_id'] );
					}
				}
                                ////events
				if($history['history_event_total']) {
					foreach($history['history_event'] AS $k => $v) {
						if(!isset($v['store'])) {
							continue;
						}
						$avatar = Helper_Uploadimages::event($v, '_A');
						$history['history_event'][$k]['avatar'] = $avatar['image'];
						$history['history_event'][$k]['profile'] = WM_Router::create( $this->view->base_href . '?controller=users&action=profile&user_id=' . $v['user_id'] );
					}
				}
                                
                                $this->view->events = array();
                                $this->view->event = array();
                                
                                $dataEvents = array(
                                    'filter_cron' => $history['user_id']
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
                                        $event['thumbs'] = $avatar['image'];

                                        $event["sport_category"] = Model_Boards::getCategoryTitle($event["sport_category"]);

                                        $data = array(
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

                                        $this->view->event[] = $event;
                                        $view->event = $event;
                                        $this->view->events[] = $view->render('boxEvent', 'events');

                                    }
                                }
                                
			
				/* PINS */
				$likes = Model_History::getHistory(array(
					'history_action' => Model_History::LIKEPIN,
					'start' => 0,
					'limit' => 30
				), 'from_user_id', $history['user_id']);
				
				$history['pins_likes'] = array();
				if($likes) {
					$temp = array();
					foreach($likes AS $like) {
						$temp[$like['pin_id']] = $like['pin_id'];
					}
					
					if($temp) { 
						$pins = Model_Pins::getPins(array(
							'start' => 0,
							'limit' => 9,
							'filter_id_in' => implode(',', $temp)
						));
						if($pins) {
							foreach($pins AS $pin) {
								
								$image = call_user_func(array(Helper_Pin::formatUploadModule($pin['store']), 'getPinImage'), $pin, '_B');
								if($image) {
									$pin['thumb'] = $image['image'];
									$pin['thumb_width'] = $image['width'];
									$pin['thumb_height'] = $image['height'];
								} else {
									continue;
								}
								
								
								$pin['description'] = Helper_Pin::descriptionFix($pin['description']);
								$pin['href'] = WM_Router::create( $request->getBaseUrl() . '?controller=pin&pin_id=' . $pin['pin_id'] );
								$pin['onto_href'] = WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=view&user_id=' . $pin['user_id'] . '&board_id=' . $pin['board_id'] );
								$pin['price_formated'] = WM_Currency::format($pin['price']);
								
								$avatar = Helper_Uploadimages::avatar($pin['user'], '_A');
								$pin['user']['avatar'] = $avatar['image'];
								$pin['user']['profile'] = WM_Router::create( $this->view->base_href . '?controller=users&action=profile&user_id=' . $pin['user_id'] );
								
								$pin['via_profile'] = array();
								if($pin['via'] && $pin['user_via']) {
									$pin['via_profile'] = array(
										'profile' => WM_Router::create( $this->view->base_href . '?controller=users&action=profile&user_id=' . $pin['via'] ),
										'fullname' => $pin['user_via']['fullname']
									);
								}
								$history['pins_likes'][] = $pin;
							}
						}
					}
				}
				
				$this->view->history = $history;
				
				$html = $this->view->render('sendWeekly','crons');
				
				Model_Email::send(
					$history['email'],
					JO_Registry::get('noreply_mail'),
					sprintf($this->translate('Weekly %s'), $this->view->site_name),
					$html
				);
				
			}
		}
	}
	
}

?>