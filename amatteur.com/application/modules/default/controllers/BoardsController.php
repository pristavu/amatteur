<?php

class BoardsController extends JO_Action {
	
	public function indexAction() {	
		
		$request = $this->getRequest();
		
		$board_id = $request->getRequest('board_id');
		$user_id = $request->getRequest('user_id');
		
		$board_info = Model_Boards::getBoard($board_id/*, $user_id*/, true);
		if(!$board_info) {
			$this->forward('error', 'error404');
		}
		
		if(!$board_info['category_id'] && JO_Session::get('user[user_id]') == $board_info['user_id']) {
			JO_Registry::set('board_category_change', $board_info);
		}
		
		$user_info = Model_Users::getUserByBoard($board_info['user_id'], $board_id);
		$model_images = new Helper_Images();
		if($user_info) {
			$avatar = Helper_Uploadimages::avatar($user_info, '_A');
			$user_info['avatar'] = $avatar['image'];
			$user_info['profile'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $user_info['user_id'] );
			
			$this->view->user_info = $user_info;
			$user_id = $user_info['user_id'];
		}
		
		Model_Boards::updateViewed($board_id);
		
		if(!$board_info['public'] && $user_id != JO_Registry::get('user[user_id]')) {
			$this->forward('error', 'error404');
		}
		
		$board_info['isFollow'] = Model_Users::isFollow(array(
			'board_id' => $board_info['board_id']
		));
		
		$this->view->follow = WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=follow' . ($board_info['user_id'] ? '&user_id=' . $board_info['user_id'] : '') . '&board_id=' . $board_info['board_id'] );
		
		if( Model_Boards::allowEdit($board_id) ) {
			$this->view->is_enable_follow = false;
			$board_info['edit'] = WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=edit&user_id=' . $board_info['user_id'] . '&board_id=' . $board_info['board_id'] );
		} else {
			$board_info['edit'] = false;
			if(JO_Session::get('user[user_id]')) {
				if(JO_Session::get('user[user_id]') != $board_info['user_id']) {
					$this->view->is_enable_follow = true;
				} else {
					$this->view->is_enable_follow = false;
					$board_info['edit'] = WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=edit&user_id=' . $board_info['user_id'] . '&board_id=' . $board_info['board_id'] );
				}
			} else {
				$this->view->is_enable_follow = false;
			}
		}
		
		$this->view->board_url = WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=view&user_id=' . $board_info['user_id'] . '&board_id=' . $board_info['board_id'] );
		
		
		$this->view->board_users = array();
		if($board_info['board_users']) {
			foreach($board_info['board_users'] AS $usr) {
				$avatar = Helper_Uploadimages::avatar($usr, '_A');
				$usr['avatar'] = $avatar['image'];
				$usr['profile'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $usr['user_id'] );
				$this->view->board_users[] = $usr;
			}
		} 
		
		$this->view->board = $board_info;
		
		$page = (int)$request->getRequest('page');
		if($page < 1) { $page = 1; }
		
		$data = array(
			'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
			'limit' => JO_Registry::get('config_front_limit'),
			'filter_board_id' => $board_info['board_id'],
			'filter_marker' => $request->getRequest('marker')
		);
		
//		if((int)JO_Session::get('user[user_id]')) {
//			$data['following_users_from_user_id'] = JO_Session::get('user[user_id]');
//		}
		
		$this->view->pins = '';
		
		$pins = Model_Pins::getPins($data); 
		
		//==== FEED ====//
		
		JO_Registry::set('rss_feed', array(
				'title' => $board_info['title'] . ' ' . sprintf($this->translate('on %s'), JO_Registry::get('site_name')),
				'href' => WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=view&user_id=' . $board_info['user_id'] . '&board_id=' . $board_info['board_id'] . '&feed' )
		));
		
		$_route_ = $request->getParam('_route_');
		$_route_parts = explode('/', $_route_);
	
		if( isset($_route_parts[2]) && $_route_parts[2] == 'feed' ) {
			$this->forward('boards', 'feed', array(
				'pins' => $pins,
				'view' => $this->view
			));
		}
		//==== FEED ====//
		$image = '';
		if($pins) {
			foreach($pins AS $pin) {
				$this->view->pins .= Helper_Pin::returnHtml($pin);
				
				if(!$image) {
					$img = Helper_Uploadimages::pin($pin, '_D');
					if($img) {
						$image = $img['image'];
					}
				}
				
			}
// 			JO_Registry::set('marker', Model_Pins::getMaxPin($data));
		} 

		$this->getLayout()->placeholder('pin_url', $this->view->board_url);
		if($image) {
		$this->getLayout()->placeholder('pin_image', $image);
		}
		$this->getLayout()->placeholder('pin_description', 'null');
		
		if($request->isXmlHttpRequest()) {
			echo $this->view->pins;
			$this->noViewRenderer(true);
		} else {
			$this->view->children = array(
	        	'header_part' 	=> 'layout/header_part',
	        	'footer_part' 	=> 'layout/footer_part'
	        );
		}
		
		JO_Layout::getInstance()->meta_title = $board_info['title'];
		
	}
	
	public function viewAction() {
		$this->forward('boards', 'index');
	}
	
	public function pageAction() {
		$this->forward('boards', 'index');
	}
	
	public function feedAction($data = array()) {
		
		$request = $this->getRequest();
		
		if(!$data) {
			$this->forward('error', 'error404');
		} else {
			
			$this->view->item = array();
			$model_images = new Helper_Images();
			foreach($data['pins'] AS $pin) {
				
				$image = call_user_func(array(Helper_Pin::formatUploadModule($pin['store']), 'getPinImage'), $pin, '_D');
				if($image) {
					$enclosure = $image['image'];
				} else {
					continue;
				}
				
				
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
			
			echo $this->renderScript('rss');
		}
	}
	
	public function followAction() {	
		
		$this->noViewRenderer(true);
		
		$request = $this->getRequest();
		
		if((int)JO_Session::get('user[user_id]')) {
		
			$board_id = $request->getRequest('board_id');
			$user_id = $request->getRequest('user_id');
			if(!$user_id) {
				$user_info = Model_Users::getUserByBoard($user_id, $board_id);
				if($user_info) {
					$user_id = $user_info['user_id'];
				}
			}
			
			$board_info = Model_Boards::getBoard($board_id, $user_id);
			
			if($board_info) {
				
				if($user_id) {
					if(Model_Users::isFollow(array('user_id' => $user_id, 'board_id' => $board_info['board_id']))) {
						
						$result = Model_Users::UnFollowBoard($user_id, $board_id);
						if($result) {
							$this->view->ok = $this->translate('Follow');
							$this->view->classs = 'add';
							
							Model_History::addHistory($user_id, Model_History::UNFOLLOW, 0, $board_id);
						} else {
							$this->view->error = true;
						}
					} else {
						$result = Model_Users::FollowBoard($user_id, $board_id);
						if($result) {
							$this->view->ok = $this->translate('Unfollow');
							$this->view->classs = 'remove';
							
							Model_History::addHistory($user_id, Model_History::FOLLOW, 0, $board_id);
							
						} else {
							$this->view->error = true;
						}
					}
				} else {
					$this->view->error = true;
				}
				
			} else {
				$this->view->error = true;
			}
		
		} else {
			$this->view->location = WM_Router::create( $request->getBaseUrl() . '?controller=landing' );
		}
		
		if($request->isXmlHttpRequest()) {
			echo $this->renderScript('json');
		} else {
			$this->redirect( $request->getServer('HTTP_REFERER') );
		}
		
	}
	
	public function create_mobileAction(){
		$this->noLayout(true);
		$this->noViewRenderer(true);
		$request = $this->getRequest();
	
		if($request->isXmlHttpRequest()){
				
			if(trim($request->getPost('title'))){
				$data = Model_Boards::createBoard(array(
						'title' => trim($request->getPost('title'))
	
				));
	
				if($data){
					echo JO_Json::encode($data);
				}else{
					echo JO_Json::encode(array('error'=>$this->translate("Oooops..! For some reason we couldn't create a new board")));
				}
			}
		}
	}
	
	public function createAction(){
		
		$request = $this->getRequest();
		
//		if(!$request->isXmlHttpRequest()) {
//			$this->forward('error', 'error404');
//		}
		
		if( $request->isPost() ) {
			
			if( JO_Session::get('user[user_id]') ) {
			
				if( trim($request->getPost('newboard')) ) {
					if(( trim($request->getPost('newboard')) != $this->translate('Nombre de la carpeta') ) && ( trim($request->getPost('newboard')) != $this->translate('Create New Board') )) {
						if( trim($request->getPost('category_id')) ) {
							$data = Model_Boards::createBoard(array(
								'title' => trim($request->getPost('newboard')),
								'category_id' => $request->getPost('category_id'),
								'friends' => $request->getPost('friends')
							));
							if($data) {
								if(is_array($request->getPost('friends'))) {
									foreach($request->getPost('friends') as $fr) {
										$this->view->uinfo = Model_Users::getUser($fr);
										$this->view->board_href = $data['href'] = WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=view&user_id=' . JO_Session::get('user[user_id]') . '&board_id=' . $data['board_id'] );
										$this->view->board_name = trim($request->getPost('newboard'));
										$this->view->author_href = $data['href'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . JO_Session::get('user[user_id]') );
										$this->view->author_name = JO_Session::get('firstname') .' '.JO_Session::get('lastname');
		//                                print_R(JO_Session::getAll());
										$result = Model_Email::send(
											$this->view->uinfo['email'],
											JO_Registry::get('noreply_mail'),
											$this->translate('You have been invited to pin on '.trim($request->getPost('newboard'))),
											$this->view->render('board_invite', 'mail')
										);
									}
								}
								Model_History::addHistory(0, Model_History::ADDBOARD, 0, $data['board_id']);
								$data['href'] = WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=view&user_id=' . JO_Session::get('user[user_id]') . '&board_id=' . $data['board_id'] );
								$this->view->data = $data;
							} else {
								$this->view->error = $this->translate('There was a problem with the record. Please try again!');
							}
						} else {
							$this->view->error = $this->translate('Debe seleccionar una categoría para la carpeta');
						}
					} else {
						$this->view->error = $this->translate('Board name must not be empty!');
					}
				} else {
					$this->view->error = $this->translate('Board name must not be empty!');
				}
			
			} else {
				$this->view->error = 'error login';
			}
			
			echo $this->renderScript('json');
			
		} else {
			
			$avatar = Helper_Uploadimages::avatar(JO_Session::get('user'), '_A');
			$this->view->avatar = $avatar['image'];

			$this->view->fullname = JO_Session::get('user[fullname]');
			$this->view->userhref = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . JO_Session::get('user[user_id]'));
			$this->view->friends_href = WM_Router::create($request->getBaseUrl() . '?controller=users&action=friends');
			
			$this->view->form_action = WM_Router::create($request->getBaseUrl() . '?controller=boards&action=create');
			
			//////////// Categories ////////////
			$this->view->categories =  array();
			$categories = Model_Categories::getCategories(array(
				'filter_status' => 1
			));
			
			foreach ($categories as $category){
				$category['subcategories'] = Model_Categories::getSubcategories($category['category_id']);
				$this->view->categories[] = $category;
			}
			
			
			
			$this->view->popup_main_box = $this->view->render('popup_form','boards');
			$this->setViewChange('form');
			
			if($request->isXmlHttpRequest()) {
				$this->view->popup = true;
				echo $this->view->popup_main_box;
				$this->noViewRenderer(true);
			} else {
				$this->view->children = array(
		        	'header_part' 	=> 'layout/header_part',
		        	'footer_part' 	=> 'layout/footer_part',
		        	'left_part' 	=> 'layout/left_part'
		        );
			}
			
		}
	}
	
	public function editAction() {
		
		$request = $this->getRequest();
		
		$board_id = $request->getRequest('board_id');

		
		$board_info = Model_Boards::getBoard($board_id);
		
		if(!$board_info) {
			$this->forward('error', 'error404');
		} 
		
		$shared = Model_Boards::allowEdit($board_id);
		
		if( $board_info['user_id'] != JO_Session::get('user[user_id]') ) {
			if(!$shared) {
				$this->forward('error', 'error404');
			}
		}
		
		$this->view->shared = $shared;
		
		if($shared) {
			$_POST['newboard'] = $board_info['title'];
			$_POST['category_id'] = $board_info['category_id'];
		}
		
		$this->view->is_edit = true;
		
		if( $request->isPost() ) {
		
			if( JO_Session::get('user[user_id]') ) {
				
				if( trim($request->getPost('newboard')) ) {
				
					$data = Model_Boards::editBoard($board_id, array(
						'title' => trim($request->getPost('newboard')),
						'category_id' => $request->getPost('category_id'),
						'friends' => $request->getPost('friends')
					));
					if($data) {
						$data = array();
						$data['href'] = WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=view&user_id=' . JO_Session::get('user[user_id]') . '&board_id=' . $board_id );
						$this->view->data = $data;
					} else {
						$this->view->error = $this->translate('There was a problem with the record. Please try again!');
					}
				} else {
					$this->view->error = $this->translate('Board name must not be empty!');
				}
			
			} else {
				$this->view->error = 'error login';
			}
			
			echo $this->renderScript('json');
			
		} else {
			$this->view->cat_title = Model_Boards::getCategoryTitle($board_info['category_id']);
			
			$this->view->title = $board_info['title'];
			
			$this->view->category_id = $board_info['category_id'];
			$this->view->another_users = array();
			
			$this->view->board_id = $board_id;
			
			$model_images = new Helper_Images();
			foreach($board_info['board_users'] AS $u) {
				$avatar = Helper_Uploadimages::avatar($u, '_A');
				$u['avatar'] = $avatar['image'];
				$u['href'] = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $u['user_id']);
				
				$this->view->another_users[] = $u;
				
			}
			
			$uin = Model_Users::getUser($board_info['user_id']);
			
			$avatar = Helper_Uploadimages::avatar($uin, '_A');
			$this->view->avatar = $avatar['image'];
			
			$this->view->fullname = $uin['fullname'];
			$this->view->userhref = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $uin['user_id']);
			$this->view->friends_href = WM_Router::create($request->getBaseUrl() . '?controller=users&action=friends');
			
			$this->view->form_action = WM_Router::create($request->getBaseUrl() . '?controller=boards&action=edit&user_id=' . $uin['user_id'].'&board_id=' . $board_id);
			$this->view->board_href = WM_Router::create($request->getBaseUrl() . '?controller=boards&user_id=' . $uin['user_id'].'&board_id=' . $board_id);
			$this->view->board_delete = WM_Router::create($request->getBaseUrl() . '?controller=boards&action=delete&user_id=' . $uin['user_id'].'&board_id=' . $board_id);
			
			//////////// Categories ////////////
			$this->view->categories =  array();
			$categories = Model_Categories::getCategories(array(
				'filter_status' => 1
			));
			
			foreach ($categories as $category){
				$category['subcategories'] = Model_Categories::getSubcategories($category['category_id']);
				$this->view->categories[] = $category;
			}
			
			$this->view->popup_main_box = $this->view->render('popup_form','boards');
			$this->setViewChange('form');
			
			if($request->isXmlHttpRequest()) {
				$this->view->popup = true;
				echo $this->view->popup_main_box;
				$this->noViewRenderer(true);
			} else {
				$this->view->children = array(
		        	'header_part' 	=> 'layout/header_part',
		        	'footer_part' 	=> 'layout/footer_part'
		        );
			}
			
		}
		
	}
	
	public function deleteAction(){
		
		$request = $this->getRequest();
		
		$board_id = $request->getRequest('board_id');
		
		$board_info = Model_Boards::getBoard($board_id/*, $user_id*/);
		
		if(!$board_info) {
			$this->forward('error','error404');
		}
		
		if($board_info['user_id'] != JO_Session::get('user[user_id]')) {
			$this->redirect( WM_Router::create($request->getBaseUrl() . '?controller=boards&user_id=' . $board_info['user_id'].'&board_id=' . $board_info['board_id']) );
		} else {
			if(Model_Boards::delete($board_id)) {
				$this->redirect( WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $board_info['user_id']) );
			} else {
				$this->redirect( WM_Router::create($request->getBaseUrl() . '?controller=boards&action=edit&user_id=' . $board_info['user_id'].'&board_id=' . $board_info['board_id']) );
			}
		}
	}
	
	public function sort_orderAction(){
		
		$request = $this->getRequest();

		$page = (int)$request->getRequest('page');
		if($page < 1) { $page = 1; }
		
		$plus = (int)( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit');
		
		if($request->isXmlHttpRequest()) {
			if(JO_Session::get('user[user_id]')) {
				$ids = $request->getPost('ids');
				if($ids) {
					foreach($ids AS $sort_order => $id) {
						Model_Boards::sort_order($id, ($sort_order + $plus));
					}
					$this->view->ok = $this->translate('The arrangement is saved!');
				}
			}
		} else {
			$this->forward('error', 'error404');
		}
		
		echo $this->renderScript('json');
		
	}
	
}

?>