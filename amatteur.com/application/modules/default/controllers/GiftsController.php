<?php

class GiftsController extends JO_Action {
	
	public function indexAction() {		
		
		$request = $this->getRequest();
		
		$page = (int)$request->getRequest('page');
		if($page < 1) { $page = 1; }
		
		$data = array(
			'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
			'limit' => JO_Registry::get('config_front_limit'),
			'filter_marker' => $request->getRequest('marker'),
			'filter_price_from' => (int)$request->getRequest('price_from'),
			'filter_price_to' => (int)$request->getRequest('price_to'),
			'allow_gifts' => true,
                        'filter_categoria_id' => $request->getRequest('category_id')
		);
		
//		if((int)JO_Session::get('user[user_id]')) {
//			$data['following_users_from_user_id'] = JO_Session::get('user[user_id]');
//		}
                $category_id = $request->getRequest('category_id');
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
			$category_info["title"]="Todo";
		}
		
		$this->view->category = $category_info;

                
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
		$this->forward('gifts', 'index');
	}
	
	public function viewAction(){
		$this->forward('gifts', 'index');
	}
	
}

?>