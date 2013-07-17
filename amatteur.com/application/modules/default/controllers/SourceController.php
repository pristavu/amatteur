<?php

class SourceController extends JO_Action {

	public function indexAction() {
		
		$request = $this->getRequest();
		
		$source_id = $request->getRequest('source_id');
		
		$source_info = Model_Source::getSource($source_id);
		if(!$source_info) {
			$this->forward('error', 'error404');
		}
		
		$this->getLayout()->meta_title = $source_info['source'];
		
		$this->view->source = $source_info;
		
		$page = (int)$request->getRequest('page');
		if($page < 1) { $page = 1; }
		
		$data = array(
			'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
			'limit' => JO_Registry::get('config_front_limit'),
			'filter_source_id' => $request->getRequest('source_id'),
			'filter_marker' => $request->getRequest('marker')
		);
		
//		if((int)JO_Session::get('user[user_id]')) {
//			$data['following_users_from_user_id'] = JO_Session::get('user[user_id]');
//		}
		
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
		$this->forward('source', 'index');
	}
	
	public function viewAction(){
		$this->forward('source', 'index');
	}
	
}

?>