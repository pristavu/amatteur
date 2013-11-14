<?php

class BookmarkletController extends JO_Action {

	public function indexAction() {
		
		$request = $this->getRequest();
		
		if(!JO_Session::get('user[user_id]')) {
			$this->redirect( WM_Router::create( $request->getBaseUrl() . '?controller=users&action=login&popup=true&next=' . urlencode($request->getFullUrl()) ) );
		}
		
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
		
		//////////// Categories ////////////
			$this->view->categories =  array();
			$categories = Model_Categories::getCategories(array(
				'filter_status' => 1
			));
			
			foreach ($categories as $category){
				$category['subcategories'] = Model_Categories::getSubcategories($category['category_id']);
				$this->view->categories[] = $category;
			}
		$this->view->title = JO_Utf8::convertToUtf8( $request->getQuery('title') );
		$this->view->url = JO_Utf8::convertToUtf8( urldecode($request->getQuery('url')) );
		$this->view->media = JO_Utf8::convertToUtf8( $request->getQuery('media') );
		$this->view->is_video = JO_Utf8::convertToUtf8( $request->getQuery('is_video') );
		$this->view->description = JO_Utf8::convertToUtf8( $request->getQuery('description') );
		$this->view->charset = JO_Utf8::convertToUtf8( $request->getQuery('charset') );
		
		if(!trim($this->view->description)) {
			$this->view->description = $this->view->title;
		}
		
		if(JO_Session::get('success_added')) {
			$this->view->pin_url = WM_Router::create( $request->getBaseUrl() . '?controller=pin&pin_id=' . JO_Session::get('success_added') );
			$this->setViewChange('success');
			JO_Session::clear('success_added');
		} else if( $request->isPost() ) {
			
			$result = Model_Pins::create(array(
				'title' => $this->view->title,
				'from' => $this->view->url,
				'image' => $this->view->media,
				'is_video' => $this->view->is_video,
				'description' => $request->getPost('message'),
				'price' => $request->getPost('price'),
				'board_id' => $request->getPost('board_id'),
				'pinmarklet' => 1
			));
			if($result) {
				Model_History::addHistory(0, Model_History::ADDPIN, $result);
				
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
				
				JO_Session::set('success_added', $result);
				$this->redirect( $request->getBaseUrl() . '?controller=bookmarklet' );
			}
		}
	}
	
	public function urlinfoAction() {
		
		$request = $this->getRequest();
		$response = $this->getResponse();
		
		$array['url'] = $request->getQuery('url');
		$array['status'] = 'success';
		$array['pinnable'] = 'true';
		
		$this->noViewRenderer(true);
		
		$response->addHeader('Cache-Control: no-cache, must-revalidate');
		$response->addHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		
		if( $request->getQuery('callback') ) {
			unset($array['status']);
			$response->addHeader('Content-type: application/javascript');
			echo $request->getQuery('callback') . '(' . JO_Json::encode($array) . ')';
		} else {
			$response->addHeader('Content-type: application/json');
			echo JO_Json::encode($array);
		}
	}
	
}

?>