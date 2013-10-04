<?php

class WelcomeController extends JO_Action {

	public function helpas() {
		
		if(!JO_Session::get('user[user_id]')) {
			$this->forward('error', 'error404');
		}
		
		$request = $this->getRequest();
		if($this->getLayout()->meta_title) {
			$this->getLayout()->placeholder('title', ($this->getLayout()->meta_title . ' - ' . JO_Registry::get('meta_title')));
		} else {
			$this->getLayout()->placeholder('title', JO_Registry::get('meta_title'));
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
	}
	
	public function indexAction() {
		
/*	
		$request = $this->getRequest();
		
		if($request->isPost()) {
			if( !$request->getPost('category_id') || count($request->getPost('category_id')) < 1 ) {
				$this->view->error = true;
			} else {
				JO_Session::set('category_id', $request->getPost('category_id'));
				$this->redirect( WM_Router::create($request->getBaseUrl() . '?controller=welcome&action=second') );
			}
		}
		
		$this->helpas();
		
		//////////// Categories ////////////
		$this->view->categories = array();
		$categories = Model_Categories::getCategories(array(
			'filter_status' => 1
		));
		
		$model_images = new Helper_Images();
		
		foreach($categories AS $category) {
			if($category['image']) {
				$category['thumb'] = $model_images->resize($category['image'], 113, 113, true);
			} else {
				$category['thumb'] = $model_images->resize(JO_Registry::get('no_avatar'), 113, 113);
			}
			
			$this->view->categories[] = $category;
		}
		
		
	}
	
	function secondAction() {
*/		
		$request = $this->getRequest();
		
		$this->helpas();
		
		//$categories = JO_Session::get('category_id');
                $userSports = Model_Users::getUserSports(JO_Session::get('user[user_id]'));
		
                $categories = array();
		foreach($userSports AS $userSport) {
			
			$categories[] = $userSport["sport_category"];
		}

		
		$users = Model_Users::getUsers(array(
			'filter_welcome' => $categories,
			'start' => 0,
			'limit' => 20
		));
		
		if(!$users) {
			JO_Session::clear('category_id');
			$this->redirect( WM_Router::create($request->getBaseUrl() . '?controller=welcome') );
		}
		
		$this->view->boards = '';
		if($users) {
			$view = JO_View::getInstance();
			$view->loged = JO_Session::get('user[user_id]');
			$model_images = new Helper_Images();
			foreach($users AS $key => $user) {
				
				$user['thumbs'] = array();
				for( $i = 0; $i < min(8, count($user['pins_array'])); $i ++) {
					$image = isset( $user['pins_array'][$i] ) ? $user['pins_array'][$i]['image'] : false;
					
					if(isset($user['pins_array'][$i])) {
						$image = Helper_Uploadimages::pin($user['pins_array'][$i], '_A');
						if($image) {
							$user['thumbs'][] = array(
									'thumb' => $image['image'],
									'href' => WM_Router::create($request->getBaseUrl() . '?controller=pin&pin_id=' . $user['pins_array'][$i]['pin_id'] ),
									'title' => $user['pins_array'][$i]['title']
							);
						}
					
					}
				}
				$avatar = Helper_Uploadimages::avatar($user, '_B');
				$user['avatar'] = $avatar['image'];
		
				$user['userLikeIgnore'] = true;
				if(JO_Session::get('user[user_id]') == $user['user_id']) {
					$user['userIsFollow'] = 1;
					$user['userFollowIgnore'] = true;
				} else {
					$user['userIsFollow'] = Model_Users::isFollowUser($user['user_id']);
					if(!$user['userIsFollow']) {
						$user['userIsFollow'] =Model_Users::FollowUser($user['user_id']);
					}
					$user['userFollowIgnore'] = false;
				}
				
				$user['href'] = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $user['user_id']);
				$user['pins_href'] = WM_Router::create($request->getBaseUrl() . '?controller=users&action=pins&user_id=' . $user['user_id']);
				$user['follow'] = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=follow&user_id=' . $user['user_id'] );
				
				$view->key = $key%2==0;
				$view->user = $user;
				$this->view->boards .= $view->render('box', 'users');
			}
		}
		
		$this->view->pinmarklet_href = WM_Router::create( $request->getBaseUrl() . '?controller=pages&action=read&page_id=' . JO_Registry::get('page_pinmarklet') );
		//$this->view->direct_path = WM_Router::create( $request->getBaseUrl() . '?direct_path=true' );
                $this->view->direct_path = WM_Router::create( $request->getBaseUrl() . '?controller=guia-rapida' );
		
		
	}
	
	
	private function checkUserKey(){
		
		$user = Model_Users::getUser(JO_Session::get('user[user_id]'));
		return $user['confirmed'] ? true : false;
		
	}
	
	public function verificationRequiredAction(){
		
		if(!JO_Session::get('user[user_id]')){
			$this->forward('error', 'error404');
		}else{
			$this->view->children = array(
					'header_part' 	=> 'layout/header_part',
					'footer_part' 	=> 'layout/footer_part'
			);
			
						
		}
	
	
		
	}
	
	public function finishRegistrationAction(){
		$this->noViewRenderer(true);
		$this->noLayout(true);
		
		$request = $this->getRequest();

		$user_id = Model_Users::getUserByRegKey($request->getParam('key'));
		
		if($user_id){
			
			if(Model_Users::setKey('1',$user_id)){
				WM_Users::initSession($user_id);
				$this->redirect(WM_Router::create($request->getBaseUrl()."?controller=welcome"));
			}else{
				$this->forward('error','error404');
			}
		} else {
			$this->forward('error','error404');
		}
	}
	
}

?>