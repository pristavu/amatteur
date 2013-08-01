<?php

class IndexController extends JO_Action {
	
	public function indexAction() {	

		$request = $this->getRequest();
		
		if($request->getParam('direct_path') == 'true') {
			if(JO_Session::get('user[user_id]') && JO_Session::get('category_id')) {
				Model_Users::edit(JO_Session::get('user[user_id]'), array(
						'first_login' => '0'
				));
				JO_Session::clear('category_id');
				$this->view->user_info = JO_Session::get('user');
				Model_Email::send(
						JO_Session::get('user[email]'),
						JO_Registry::get('noreply_mail'),
						sprintf($this->translate('Welcome to %s!'), JO_Registry::get('site_name')),
						$this->view->render('welcome', 'mail')
				);
			}
			$this->redirect( $request->getBaseUrl() );
		}
		
		/*$img = JO_Phpthumb::getInstance();
		var_dump($img->isValidImplementation('imagick')); exit;
		
		$img = JO_Phpthumb_Factory::create('http://www.desiredanimations.com/wp-content/uploads/2011/08/Cars-2.jpg');
		
		$img->adaptiveResize(250, 250)->createReflection(40, 40, 80, true, '#a4a4a4');
		$img->show();
		
		exit;*/
		
		$page = (int)$request->getRequest('page');
		if($page < 1) { $page = 1; }
		
		
		$data = array(
			'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
			'limit' => JO_Registry::get('config_front_limit')
		);
		
		if(JO_Session::get('user[user_id]')) {
			$data['following_users_from_user_id'] = JO_Session::get('user[user_id]');
			$data['filter_marker'] = $request->getRequest('marker');
		} else {
			$data['filter_rand'] = true;
		}
		
		$this->view->pins = '';
		
	
		$pins = Model_Pins::getPins($data);
		
		if($pins) {
			$banners = Model_Banners::getBanners(
				new JO_Db_Expr("`controller` = '".$request->getController()."' AND position BETWEEN '".(int)$data['start']."' AND '".(int)$data['limit']."'")
			);
			$pp = JO_Registry::get('config_front_limit');
			foreach($pins AS $row => $pin) {
				///banners
				$key = $row + (($pp*$page)-$pp);
				if(isset($banners[$key])) {
					$this->view->pins .= Helper_Banners::returnHtml($banners[$key]);	
				}
				//pins
				$this->view->pins .= Helper_Pin::returnHtml($pin);
			}
			if(JO_Session::get('user[user_id]')) {
// 				JO_Registry::set('marker', Model_Pins::getMaxPin($data));
			}
		}
		
		if(!$request->isXmlHttpRequest() && JO_Session::get('user[user_id]')) {
			$history = Model_History::getHistory(array(
				'start' => 0,
				'limit' => 10,
				'sort' => 'DESC',
				'order' => 'history_id'
			));
			$model_images = new Helper_Images();
			foreach($history AS $key => $data) {
				if(!isset($data['user']['store'])) {
					continue;
				}
				$avatar = Helper_Uploadimages::avatar($data['user'], '_A');
				$history[$key]['user']['avatar'] = $avatar['image'];

				if($data['history_action'] == Model_History::REPIN) 
                                {
					$history[$key]['href'] = WM_Router::create($request->getBaseUrl() . '?controller=pin&pin_id=' . $data['pin_id']);
				} 
                                else 
                                {
					$history[$key]['href'] = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $data['from_user_id']);
				}
			}
			$this->view->history = $history;
		}
		

		if($request->isXmlHttpRequest()) {
			echo $this->view->pins;
//			echo $this->renderScript('json');
			$this->noViewRenderer(true);
		} else {
			$this->view->children = array(
	        	'header_part' 	=> 'layout/header_part',
	        	'footer_part' 	=> 'layout/footer_part'
	        );
		}
		
//		if((int)JO_Registry::get('config_cache_live')) {
//			$http = new JO_Http();
//			$http->setTimeout(1);
//			$http->execute($request->getBaseUrl() . '?action=generateCache');
//		}
		
	}
	
	
	
}

?>