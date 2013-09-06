<?php

class CategoryController extends JO_Action {
	
	public function indexAction() {
		
		$request = $this->getRequest();
		
		$category_id = $request->getRequest('category_id');
		
                JO_Session::set('categoria_id', $category_id);
                
		$category_info = Model_Categories::getCategory($category_id);
		
		if($category_info && !$category_info['parent_id']){
			$subCats = Model_Categories::getSubcategories($category_id);
			if($subCats){
				
				$category_id = '';
				foreach($subCats as $sc){
						$category_id.= $sc['category_id'].",";
				}
				
				$category_id = substr($category_id,0,-1);
			}
			
		}
		
		if(!$category_info) {
			$this->forward('error', 'error404');
		}
		
		$this->view->category = $category_info;
		
		$this->getLayout()->meta_title = ($category_info['meta_title'] ? $category_info['meta_title'] : $category_info['title']);
		$this->getLayout()->meta_description = $category_info['meta_description'];
		$this->getLayout()->meta_keywords = $category_info['meta_keywords'];
		
		$page = (int)$request->getRequest('page');
		if($page < 1) { $page = 1; }
		
		$data = array(
			'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
			'limit' => JO_Registry::get('config_front_limit'),
			'filter_category_id' => $category_id,
			//'filter_is_video' => 0,
			'filter_marker' => $request->getRequest('marker')
		);
		
//		if((int)JO_Session::get('user[user_id]')) {
//			$data['following_users_from_user_id'] = JO_Session::get('user[user_id]');
//		}
		
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
		$this->forward('category', 'index');
	}
	
	public function viewAction(){
		$this->forward('category', 'index');
	}
	
}

?>