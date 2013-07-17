<?php

class SearchController extends JO_Action {
	
	private function searchMenu($query) {
		$request = $this->getRequest();
		return array(
			array(
					'title' => $this->translate('Pins'),
					'active' => in_array($request->getAction(), array('index', 'page', 'view')),
					'href' => WM_Router::create($request->getBaseUrl() . '?controller=search&q=' . $query)
					),
			array(
					'title' => $this->translate('Boards'),
					'active' => in_array($request->getAction(), array('boards')),
					'href' => WM_Router::create($request->getBaseUrl() . '?controller=search&action=boards&q=' . $query)
					),
			array(
					'title' => $this->translate('People'),
					'active' => in_array($request->getAction(), array('people')),
					'href' => WM_Router::create($request->getBaseUrl() . '?controller=search&action=people&q=' . $query)
					),
			
		);
	}
	
	public function indexAction() {		
		
		$request = $this->getRequest();
		
		$page = (int)$request->getRequest('page');
		if($page < 1) { $page = 1; }
		
		$query = $request->getRequest('q');
		
		$this->view->query = $query;
		
		$this->view->menuSearch = $this->searchMenu($query);
		
		$data = array(
			'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
			'limit' => JO_Registry::get('config_front_limit'),
			'filter_description' => $query,
			'filter_marker' => $request->getRequest('marker')
		);
		
		$this->view->pins = '';
		
		$pins = Model_Pins::getPins($data);
		if($pins) {
			foreach($pins AS $pin) {
				$this->view->pins .= Helper_Pin::returnHtml($pin);
			}
// 			JO_Registry::set('marker', Model_Pins::getMaxPin($data));
		}

		if($request->isXmlHttpRequest()) {
			echo $this->view->pins;
			$this->noViewRenderer(true);
		} else {
			$this->view->children = array(
	        	'header_part' 	=> 'layout/header_part',
	        	'footer_part' 	=> 'layout/footer_part'
	        );
		}	
	}
	
	public function pageAction(){
		$this->forward('search', 'index');
	}
	
	public function viewAction(){
		$this->forward('search', 'index');
	}
	
	public function peopleAction() {

		$this->setViewChange('index');
		
		$request = $this->getRequest();
		
		$page = (int)$request->getRequest('page');
		if($page < 1) {
			$page = 1;
		}
		
		$query = $request->getRequest('q');
		
		$this->view->query = $query;
		
		$this->view->menuSearch = $this->searchMenu($query);
		
		$data = array(
				'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
				'limit' => JO_Registry::get('config_front_limit'),
				'filter_username' => $query
		);
		
		$this->view->pins = '';
		
		$users = Model_Users::getUsers($data);
		if($users) {
			$this->view->follow_user = true;
			$view = JO_View::getInstance();
			$view->loged = JO_Session::get('user[user_id]');
			$model_images = new Helper_Images();
			foreach($users AS $key => $user) {
				$avatar = Helper_Uploadimages::avatar($user, '_B');
				$user['avatar'] = $avatar['image'];
				
				if($view->loged) {
					$user['userIsFollow'] = Model_Users::isFollowUser($user['user_id']);
					$user['userFollowIgnore'] = $user['user_id'] == JO_Session::get('user[user_id]');
				} else {
					$user['userFollowIgnore'] = true;
				}
				
				$user['href'] = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $user['user_id']);
				$user['follow'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=follow&user_id=' . $user['user_id'] );
				
				$view->key = $key%2==0;
				$view->user = $user;
				$this->view->pins .= $view->render('boxSearch', 'users');
				
			}
		}
		
		if($request->isXmlHttpRequest()) {
			echo $this->view->pins;
			$this->noViewRenderer(true);
		} else {
			$this->view->children = array(
					'header_part' 	=> 'layout/header_part',
					'footer_part' 	=> 'layout/footer_part'
			);
		}
	}
	
	public function boardsAction() {	

		$this->setViewChange('index');
		
		$request = $this->getRequest();
		
		$page = (int)$request->getRequest('page');
		if($page < 1) { $page = 1; }
		
		$query = $request->getRequest('q');
		
		$this->view->query = $query;
		
		$this->view->menuSearch = $this->searchMenu($query);
		
		$data = array(
			'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
			'limit' => JO_Registry::get('config_front_limit'),
			'filter_title' => $query
		);
		
		$this->view->pins = '';
		
		$boards = Model_Boards::getBoards($data);
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
				if($board['user_id'] == JO_Session::get('user[user_id]') || Model_Boards::allowEdit($board['board_id'])) {
					$board['userFollowIgnore'] = false;
					$board['edit'] = WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=edit&user_id=' . $board['user_id'] . '&board_id=' . $board['board_id'] );
				}
				
				
				$view->board = $board;
				$this->view->pins .= $view->render('box', 'boards');
			}
		}

		if($request->isXmlHttpRequest()) {
			echo $this->view->pins;
			$this->noViewRenderer(true);
		} else {
			$this->view->children = array(
	        	'header_part' 	=> 'layout/header_part',
	        	'footer_part' 	=> 'layout/footer_part'
	        );
		}	
		
	}
	
	public function autocompleteAction() {
		$request = $this->getRequest();
		
		$this->view->items = array();
		
		if(JO_Session::get('user[user_id]') && $request->getPost('value')) {
			
			$friends = Model_Users::getUserFriends(array(
				'filter_username' => $request->getPost('value')
			));
			
			if($friends) {
				$model_images = new Helper_Images();
				foreach($friends AS $friend) {
					if(!isset($friend['store'])) {
						continue;
					}
					$avatar = Helper_Uploadimages::avatar($friend, '_A');
					$this->view->items[] = array(
						'image' => $avatar['image'],
						'label' => $friend['fullname'],
						'value' => $friend['user_id'],
						'href' => WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $friend['user_id']),
						'username' => $friend['username']
					);
				}
			}
			
			$boards = Model_Boards::getBoards(array(
				'filter_user_id' => JO_Session::get('user[user_id]'),
				'friendly' => JO_Session::get('user[user_id]'),
				'filter_title' => $request->getPost('value'),
				'sort' => 'asc',
				'order' => 'boards.title'
			));
			
			if($boards) {
				foreach($boards AS $board) {
					$this->view->items[] = array(
							'image' => $request->getBaseUrl() . 'data/images/typeahead_board.png',
							'label' => $board['title'],
							'value' => $board['board_id'],
							'href' => WM_Router::create($request->getBaseUrl() . '?controller=boards&action=view&user_id=' . $board['user_id'] . '&board_id=' . $board['board_id']),
							'username' => $board['title']
					);
				}
			}
			
		}
			
		$this->view->items[] = array(
				'search_for' => 1,
				'label' => sprintf($this->translate('Search for %s'), $request->getPost('value')),
				'href' => WM_Router::create($request->getBaseUrl() . '?controller=search&q=' . $request->getPost('value'))
		);
		
		if($request->isXmlHttpRequest()) {
			echo $this->renderScript('json');
		} else {
			$this->forward('error', 'error404');
		}
		
	}
	
}

?>