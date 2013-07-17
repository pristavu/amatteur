<?php

class PinController extends JO_Action {

	public function indexAction() {
//		var_dump( htmlspecialchars('⚐') );exit;
		$request = $this->getRequest();
		
		$pin_id = $request->getRequest('pin_id');
		
		$pin_info = Model_Pins::getPin($pin_id);
		
		if(!$pin_info) {
			$this->forward('error', 'error404');
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
					$result = Model_Pins::addComment($data, $pin_info['latest_comments'], Model_Users::$allowed_fields);
					$this->view = JO_View::getInstance()->reset();
					if($result) {
						$avatar = Helper_Uploadimages::avatar($result['user'], '_A');
						$result['user']['avatar'] = $avatar['image'];
						$result['user']['profile'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $result['user_id'] );
						
						$this->view->ok = true;
						$result['pin'] = self::getPinStat($pin_id);
						
						if( JO_Session::get('user[user_id]') ) {
							if( JO_Session::get('user[is_admin]') || JO_Session::get('user[user_id]') == $result['user_id'] ) {
								$result['delete_comment'] = WM_Router::create( $request->getBaseUrl() . '?controller=pin&action=deleteComment&comment_id=' . $result['comment_id'] );
							}
						}
						
						if($request) {
							Model_History::addHistory($pin_info['user_id'], Model_History::COMMENTPIN, $pin_id, 0, $request->getPost('write_comment'));
						
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
					$result = Model_Pins::addComment($data, $pin_info['latest_comments']);
					$this->redirect(WM_Router::create( $request->getBaseUrl() . '?controller=pin&pin_id=' . $pin_id ));
				} else {
					$this->redirect(WM_Router::create( $request->getBaseUrl() . '?controller=landing' ));
				}
			}
		}
		
		
		$this->view->show_buttonswrapper = true;
		
		$this->view->url_like = WM_Router::create( $request->getBaseUrl() . '?controller=pin&action=like&pin_id=' . $pin_id );
		$this->view->url_tweet = WM_Router::create( $request->getBaseUrl() . '?controller=pin&pin_id=' . $pin_id );
		$this->view->url_embed = WM_Router::create( $request->getBaseUrl() . '?controller=pin&action=embed&pin_id=' . $pin_id );
		$this->view->url_report = WM_Router::create( $request->getBaseUrl() . '?controller=pin&action=report&pin_id=' . $pin_id );
		$this->view->url_email = WM_Router::create( $request->getBaseUrl() . '?controller=pin&action=email&pin_id=' . $pin_id );
		$this->view->url_repin = WM_Router::create( $request->getBaseUrl() . '?controller=pin&action=repin&pin_id=' . $pin_id );
		$this->view->url_comment = WM_Router::create( $request->getBaseUrl() . '?controller=pin&action=comment&pin_id=' . $pin_id );
		
		$banners = Model_Banners::getBanners(
			new JO_Db_Expr("`controller` = '".$request->getController()."'")
		);
		
		if($request->isXmlHttpRequest()) {
			$this->view->popup = true;
			echo Helper_Externallinks::fixExternallinks(Helper_Pin::returnHtmlDetail($pin_info, $banners));
			$this->noViewRenderer(true);
		} else {
			$this->view->pins_details = Helper_Pin::returnHtmlDetail($pin_info, $banners);
			JO_Registry::set('pin_info', $pin_info);
			
			$this->view->children = array(
	        	'header_part' 	=> 'layout/header_part',
	        	'footer_part' 	=> 'layout/footer_part',
				'left_part'		=> 'pin/left_part'
	        );
		}
		
	}
	
	public function editAction(){
//		var_dump( htmlspecialchars('⚐') );exit;
		$request = $this->getRequest();
		
		$pin_id = $request->getRequest('pin_id');
		
		$pin_info = Model_Pins::getPin($pin_id);
		
		if(!$pin_info || $pin_info['user_id'] != JO_Session::get('user[user_id]')) {
			$this->forward('error', 'error404');
		}
		
		if( $request->isPost() ) {
			
			$validate = new Helper_Validate();
			if($pin_info['from']) {
				$validate->_set_rules($request->getPost('from'), $this->translate('Link'), 'not_empty;min_length[3];domain');
			}
			
			$data = $request->getPost();
			
			if($validate->_valid_form()) {
				
				Model_Pins::editPin($pin_id, $request->getPost());
				$this->redirect( WM_Router::create( $request->getBaseUrl() . '?controller=pin&pin_id=' . $pin_info['pin_id'] ) );
				
			} else {
				$this->view->error = $validate->_get_error_messages();
			}
			
			foreach($data AS $k=>$v) {
				if(isset($pin_info[$k])) {
					$pin_info[$k] = $v;
				}
			}
			
		}
		
		$image = Helper_Uploadimages::pin($pin_info, '_B');
		if($image) {
			$pin_info['thumb'] = $image['image'];
			$pin_info['thumb_width'] = $image['width'];
			$pin_info['thumb_height'] = $image['height'];
		} else {
			$pin_info['thumb'] = '';
			$pin_info['thumb_width'] = 0;
			$pin_info['thumb_height'] = 0;
		}
		
		
		if($pin_info['gift']) {
			$pin_info['price_formated'] = WM_Currency::format($pin_info['price']);	
		} else {
			$pin_info['price_formated'] = '';
			$pin_info['price'] = 0;
		}
		
		$pin_info['href'] = WM_Router::create( $request->getBaseUrl() . '?controller=pin&pin_id=' . $pin_info['pin_id'] );
		
		$this->view->pin_info = $pin_info;
		
		$view->get_user_friends = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=friends' );
		
		$boards = Model_Boards::getBoards(array(
			'filter_user_id' => JO_Session::get('user[user_id]'),
			'order' => 'boards.sort_order',
			'sort' => 'ASC',
			'friendly' => JO_Session::get('user[user_id]')
		));
		
		$this->view->boards = array();
		if($boards) {
			foreach($boards AS $board) {
				$this->view->boards[] = array(
					'board_id' => $board['board_id'],
					'title' => $board['title']
				);
			}
		}
		
		$this->view->pin_delete = WM_Router::create($request->getBaseUrl() . '?controller=pin&action=delete&pin_id=' . $pin_id);
		
		$this->view->children = array(
        	'header_part' 	=> 'layout/header_part',
        	'footer_part' 	=> 'layout/footer_part',
			'left_part'		=> 'pin/left_part'
        );
	}
	
	public function left_partAction(){
		$request = $this->getRequest();
		
		$this->view->pin = JO_Registry::get('pin_info');
		
//		$this->view->onto_board = Helper_Pin::getBoardPins( JO_Registry::getArray('pin_info[board_id]'), 9, 60 );
		
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
			$pin_repin = Model_Pins::getPin(JO_Registry::getArray('pin_info[repin_from]'));
			if($pin_repin) {
				$this->view->source['source'] = $pin_repin['board'];
				$this->view->pin['from'] = WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=view&user_id=' . $pin_repin['user_id'] . '&board_id=' . $pin_repin['board_id'] );
				$this->view->source_pins = Helper_Pin::getBoardPins( $pin_repin['board_id'], 9, 75 );
			}
		}
		
		$this->view->boardIsFollow = Model_Users::isFollow(array(
			'board_id' => JO_Registry::getArray('pin_info[board_id]')
		));
		
		$this->view->follow = WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=follow&user_id=' . $this->view->pin['user_id'] . '&board_id=' . $this->view->pin['board_id'] );
		
		$this->view->loged = JO_Session::get('user[user_id]');
		
		$this->view->pin['userFollowIgnore'] = ($this->view->pin['via'] ? $this->view->pin['via'] : $this->view->pin['user_id']) == JO_Session::get('user[user_id]');
		
//		var_dump($this->view->onto_board);
		
		JO_Registry::set('pin_info', array());
	}
	
	public function embedAction() {
		
		$request = $this->getRequest();
		
		$pin_id = $request->getRequest('pin_id');
		
		$pin = Model_Pins::getPin($pin_id);
		
		if(!$pin) {
			$this->forward('error', 'error404');
		}
		
		$image = Helper_Uploadimages::pin($pin, '_B');
		$image2 = Helper_Uploadimages::pin($pin, '_D');
		if($image && $image2) {
			$pin['thumb'] = $image2['image'];
			$pin['thumb_width'] = $image['width'];
			$pin['thumb_height'] = $image['height'];
			$pin['original'] = $image['original'];
		} else {
			$pin['thumb'] = '';
			$pin['thumb_width'] = 0;
			$pin['thumb_height'] = 0;
		}

		
		$pin['href'] = WM_Router::create( $request->getBaseUrl() . '?controller=pin&pin_id=' . $pin['pin_id'] );
		$pin['onto_href'] = WM_Router::create( $request->getBaseUrl() );
		$pin['onto_title'] = JO_Registry::get('site_name');
		$pin['profile'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $pin['user_id'] );
		
		$this->view->pin = $pin;
		
		$this->view->pins_details = $this->view->render('embed','pin');
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
	
	public function emailAction() {
		
		$request = $this->getRequest();
		
		$pin_id = $request->getRequest('pin_id');
		
		$pin_info = Model_Pins::getPin($pin_id);
		
		if(!$pin_info) {
			$this->forward('error', 'error404');
		}
		
		$this->view->pin_id = $pin_id;
	
		$this->view->pin_href = WM_Router::create( $request->getBaseUrl() . '?controller=pin&pin_id=' . $pin_id );
		$this->view->url_form = WM_Router::create( $request->getBaseUrl() . '?controller=pin&action=email&pin_id=' . $pin_id );
		
		
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
		
		$this->view->pins_details = $this->view->render('email','pin');
		
		
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
    			
    			$this->view->pin_info = $pin_info;
    			$this->view->self_profile = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . JO_Session::get('user[user_id]') );
				$this->view->self_fullname = JO_Session::get('user[firstname]') . ' ' . JO_Session::get('user[lastname]');
    			$this->view->self_firstname = JO_Session::get('user[firstname]');
    			$this->view->header_title = JO_Registry::get('site_name');
    			
    	        $result = Model_Email::send(
    	        	$request->getPost('email'),
    	        	JO_Registry::get('noreply_mail'),
    	        	$this->translate('Shared content from') . ' ' . JO_Session::get('user[firstname]') . ' ' . JO_Session::get('user[lastname]'),
    	        	$this->view->render('send_pin', 'mail')
    	        );
    	        
    	        if($result) {
					$this->view->pins_details = $this->view->render('message_email','pin');
    			} else {
    				$this->view->error = $this->translate('There was an error. Please try again later!');
    			}
				
			
			} else {
				$this->view->error = $validate->_get_error_messages();
			}
			
			$this->view->pin_oppener = $request->getPost('pin_oppener');
			
		}
		
		if($this->view->error) {
			$this->view->pins_details = $this->view->render('email','pin');
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
	
	public function reportAction() {
		
		$request = $this->getRequest();
		
		$pin_id = $request->getRequest('pin_id');
		
		$pin_info = Model_Pins::getPin($pin_id);
		
		if(!$pin_info) {
			$this->forward('error', 'error404');
		}
		
		$this->view->reportcategories = Model_Pins::getPinReportCategories();
		
		$this->view->url_form = WM_Router::create( $request->getBaseUrl() . '?controller=pin&action=report&pin_id=' . $pin_id );
		$this->view->intellectual_property = WM_Router::create( $request->getBaseUrl() . '?controller=about&action=copyright&pin_id=' . $pin_id );
		$this->view->pin_id = $pin_id;
	
		$this->view->pin_href = WM_Router::create( $request->getBaseUrl() . '?controller=pin&pin_id=' . $pin_id );
		
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
		
		$this->view->pins_details = $this->view->render('report','pin');
		
		if($request->isPost()) {
			$this->view->is_posted = true;
			
			if(Model_Pins::pinIsReported($request->getRequest('pin_id'))) {
				$this->view->error = $this->translate('You are already reported this pin!');
				$this->view->pins_details = $this->view->render('report','pin');
			} else {
			
				$result = Model_Pins::reportPin( $request->getRequest('pin_id'), $request->getPost('report_category'), $request->getPost('report_message') );
				if(!$result) {
					$this->view->error = $this->translate('Error reporting experience. Try again!');
					$this->view->pins_details = $this->view->render('report','pin');
				} else {
				    if(JO_Registry::get('not_rp')) {
    		    			Model_Email::send(
    				    	  	JO_Registry::get('report_mail'),
    				    	 	JO_Registry::get('noreply_mail'),
    				    	   	$this->translate('New reported pin'),
    				    	  	$this->translate('Hello, there is new reported pin in ').' '.JO_Registry::get('site_name')
    				    	 );
		    			}
					$terms = Model_Pages::getPage( JO_Registry::get('page_terms') );
					if($terms) {
						$this->view->terms = $terms['title'];
					}
					
					$this->view->pin_oppener = $request->getRequest('pin_oppener');
					$this->view->terms_href = WM_Router::create( $request->getBaseUrl() . '?controller=about&action=terms' );
					
					$this->view->pins_details = $this->view->render('message_report','pin');
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
	
	public function likeAction() {
		
		$request = $this->getRequest();
		
		$pin_id = $request->getRequest('pin_id');
		
		$pin = Model_Pins::getPin($pin_id, Model_Users::$allowed_fields);
		
		if(!$pin) {
			$this->forward('error', 'error404');
		}
		
		if($request->isXmlHttpRequest()) {
			if(!(int)JO_Session::get('user[user_id]')) {
				$this->view->location = WM_Router::create( $request->getBaseUrl() . '?controller=landing' );
			} else {
				if(Model_Pins::pinIsLiked($pin_id)) {
					$result = Model_Pins::unlikePin($pin_id);
					$this->view = JO_View::getInstance()->reset();
					if($result) {
						Model_History::addHistory($pin['user_id'], Model_History::UNLIKEPIN, $pin_id);
						$this->view->pin = self::getPinStat($pin_id);
						$this->view->ok = $this->translate('Like');
						$this->view->classs = 'remove';
					} else {
						$this->view->error = true;
					} 
				} else {
					$result = Model_Pins::likePin($pin_id);
					$this->view = JO_View::getInstance()->reset();
					if($result) {
						Model_History::addHistory($pin['user_id'], Model_History::LIKEPIN, $pin_id);
						
						if($pin['user']['email_interval'] == 1 && $pin['user']['likes_email']) {
							$this->view->user_info = $pin['user'];
							$this->view->profile_href = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . JO_Session::get('user[user_id]'));
							$this->view->full_name = JO_Session::get('user[firstname]') . ' ' . JO_Session::get('user[lastname]');
							$this->view->text_email = $this->translate('like your');
							$this->view->pin_href = WM_Router::create($request->getBaseUrl() . '?controller=pin&pin_id=' . $pin_id );
							$result = Model_Email::send(
			    	        	$pin['user']['email'],
			    	        	JO_Registry::get('noreply_mail'),
			    	        	JO_Session::get('user[firstname]') . ' ' . JO_Session::get('user[lastname]') . ' ' . $this->translate('like your pin'),
			    	        	$this->view->render('like_pin', 'mail')
			    	        );
						}
						
						$this->view->pin = self::getPinStat($pin_id);
						$this->view->ok = $this->translate('Unlike');
						$this->view->classs = 'add';
					} else {
						$this->view->error = true;
					}
				}
				
			}
			
			echo $this->renderScript('json');
			
		} else {
			if(!(int)JO_Session::get('user[user_id]')) {
				$this->redirect(WM_Router::create( $request->getBaseUrl() . '?controller=landing' ));
			}
		}
		
	}
	
	public function reportCommentAction(){
		$request = $this->getRequest();
		$comment_id = $request->getRequest('comment_id');
		$comment_info = Model_Pins::getComment($comment_id);
		
		if(!$comment_info) {
			$this->forward('error', 'error404');
		}
		
		
		$this->view->reportcategories = Model_Pins::getPinReportCategories();
		
		$this->view->url_form = WM_Router::create( $request->getBaseUrl() . '?controller=pin&action=reportComment&comment_id=' . $comment_id );
		$this->view->comment_id = $comment_id;
	
		$this->view->pin_href = WM_Router::create( $request->getBaseUrl() . '?controller=pin&pin_id=' . $comment_info['pin_id'] );
		
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
			
			if(Model_Pins::commentIsReported($comment_id)) {
				$this->view->error = $this->translate('You are already reported this comment!');
				$this->view->pins_details = $this->view->render('report','pin');
			} else {
			
				$result = Model_Pins::reportComment( $comment_id, $request->getPost('report_category'), $request->getPost('report_message') );
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
					
					$this->view->pins_details = $this->view->render('message_report','pin');
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
		$comment_info = Model_Pins::getComment($comment_id);
		if($comment_info) {
			$pin = Model_Pins::getPin($comment_info['pin_id']);
			if($comment_info['user_id'] == JO_Session::get('user[user_id]') || JO_Session::get('user[is_admin]') || JO_Session::get('user[user_id]') == $pin['board_data']['user_id']) {
				if(Model_Pins::deleteComment($comment_id)) {
					$this->view->ok = true;
					$this->view->stats = self::getPinStat($comment_info['pin_id']);
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
	
	private function getPinStat($pin_id) {
		$result = Model_Pins::getPin($pin_id, Model_Users::$allowed_fields);
		if(!$result) {
			return false;
		}
		
		$request = $this->getRequest();
		
		$result['stats'] = array();
		if($result['likes']) {
			$result['stats']['likes'] = sprintf( $this->translate('%d like' . ($result['likes'] == 1 ? '' : 's')), $result['likes'] );
		} else {
			$result['stats']['likes'] = '';
		}
		if($result['comments']) {
			$result['stats']['comments'] = sprintf( $this->translate('%d comment' . ($result['comments'] == 1 ? '' : 's')), $result['comments'] );
			if($result['comments'] > 5) {
				$result['stats']['all_comments'] = sprintf($this->translate('All %d comments...'), $result['comments']);
				$result['stats']['all_comments_href'] = WM_Router::create( $request->getBaseUrl() . '?controller=pin&pin_id=' . $pin_id );
			}
		} else {
			$result['stats']['comments'] = '';
		}
		if($result['repins']) {
			$result['stats']['repins'] = sprintf( $this->translate('%d repin' . ($result['repins'] == 1 ? '' : 's')), $result['repins'] );
		} else {
			$result['stats']['repins'] = '';
		}
		return $result;
	}
	
	public function repinAction(){
		
		$request = $this->getRequest();
		
		$pin_id = $request->getRequest('pin_id');
		
		$pin_info = Model_Pins::getPin($pin_id);
		
		
		if(!$pin_info) {
			$this->forward('error', 'error404');
		}
		
		$model_images = new Helper_Images();
		
		$this->view->title = $pin_info['title'];
		$this->view->price = $pin_info['price'];
		
		$image = Helper_Uploadimages::pin($pin_info, '_B');
		if($image) {
			$this->view->media = $image['original'];
		} else {
			$this->view->media = false;
		}
		
		$this->view->is_video = $pin_info['is_video'] ? 'true' : 'false';
		$this->view->from = $pin_info['from'];
		$this->view->description = $pin_info['description'];
		
		$this->view->from_url = WM_Router::create( $request->getBaseUrl() . '?controller=pin&action=repin&pin_id=' . $pin_id );
				
		$this->view->createBoard = WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=create' );
		
		$boards = Model_Boards::getBoards(array(
			'filter_user_id' => JO_Session::get('user[user_id]'),
			'order' => 'boards.sort_order',
			'sort' => 'ASC',
			'friendly' => JO_Session::get('user[user_id]')
		));
		
		$this->view->boards = array();
		if($boards) {
			foreach($boards AS $board) {
				$this->view->boards[] = array(
					'board_id' => $board['board_id'],
					'title' => $board['title']
				);
			}
		}
		
		$this->view->popup_main_box = $this->view->render('repin','pin');
                
                $this->view->is_article = $pin_info['is_article'] ? 'true' : 'false';
		
		if( $request->isPost() ) {
			
			$result = Model_Pins::create(array(
				'title' => $pin_info['title'],
				'from' => $pin_info['from'],
				'image' => $this->view->media,
				'is_video' => $pin_info['is_video'] ? 'true' : 'false',
                                'is_article' => $pin_info['is_article'] ? 'true' : 'false',
				'description' => $request->getPost('message'),
				'price' => $request->getPost('price'),
				'board_id' => $request->getPost('board_id'),
				'via' => $pin_info['user_id'],
				'repin_from' => $pin_info['pin_id'],
				'from_repin'=> $pin_info['from']
			));
			if($result) {
				$this->view->pin_url = WM_Router::create( $request->getBaseUrl() . '?controller=pin&pin_id=' . $result );
				$this->view->popup_main_box = $this->view->render('success','addpin');
				//add history
				Model_History::addHistory($pin_info['user_id'], Model_History::REPIN, $result);
				
				if($pin_info['user']['email_interval'] == 1 && $pin_info['user']['repins_email']) {
					$this->view->user_info = $pin_info['user'];
					$this->view->profile_href = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . JO_Session::get('user[user_id]'));
					$this->view->full_name = JO_Session::get('user[firstname]') . ' ' . JO_Session::get('user[lastname]');
					$this->view->text_email = $this->translate('repin your');
					$this->view->pin_href = WM_Router::create($request->getBaseUrl() . '?controller=pin&pin_id=' . $pin_id );
					Model_Email::send(
	    	        	$pin_info['user']['email'],
	    	        	JO_Registry::get('noreply_mail'),
	    	        	JO_Session::get('user[firstname]') . ' ' . JO_Session::get('user[lastname]') . ' ' . $this->translate('repin your pin'),
	    	        	$this->view->render('repin_pin', 'mail')
	    	        );
				}
				
			}
			
		}
		
		$this->setViewChange('index');
		if($request->isXmlHttpRequest()) {
			$this->noViewRenderer(true);
			echo $this->view->popup_main_box;
			$this->view->is_popup = true;
		} else {
			$this->view->pins_details = $this->view->popup_main_box;
			$this->view->is_popup = false;
			$this->view->children = array(
	        	'header_part' 	=> 'layout/header_part',
	        	'footer_part' 	=> 'layout/footer_part',
	        	'left_part' 	=> 'pin/left_part'
	        );
		}
		
		
	}
	
	public function deleteAction(){
		
		$request = $this->getRequest();
		
		$pin_id = $request->getRequest('pin_id');
		
		$pin_info = Model_Pins::getPin($pin_id);
		
		if(!$pin_info) {
			$this->forward('error','error404');
		}
		
		if($pin_info['user_id'] != JO_Session::get('user[user_id]')) {
			$this->redirect( WM_Router::create($request->getBaseUrl() . '?controller=pin&pin_id=' . $pin_info['pin_id']) );
		} else {
			if(Model_Pins::delete($pin_id)) {
				$this->redirect( WM_Router::create($request->getBaseUrl() . '?controller=boards&user_id=' . $pin_info['user_id'].'&board_id=' . $pin_info['board_id']) );
			} else {
				$this->redirect( WM_Router::create($request->getBaseUrl() . '?controller=pin&action=edit&pin_id=' . $pin_info['pin_id']) );
			}
		}
	}
	
}

?>