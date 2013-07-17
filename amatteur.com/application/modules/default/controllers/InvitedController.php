<?php

class InvitedController extends JO_Action {
	
	/**
	 * @var WM_Facebook
	 */
	protected $facebook;
	
	public function init() {
		$this->facebook = JO_Registry::get('facebookapi');
	}
	
	public function indexAction() {
		
		$request = $this->getRequest();
		
		$invate = Model_Users::checkInvateFacebook($request->getQuery('code'));
		
		if( !$invate ) {
			
			$this->setViewChange('../facebook/no_account');
					
			$page_login_trouble = Model_Pages::getPage( JO_Registry::get('page_login_trouble') );
			if($page_login_trouble) {
				$this->view->page_login_trouble = array(
					'title' => $page_login_trouble['title'],
					'href' => WM_Router::create( $request->getBaseUrl() . '?controller=pages&action=read&page_id=' . $page_login_trouble['page_id'] )
				);
			}
			
		} else {
			
// 			$this->facebook->setNextUrl( WM_Router::create( $request->getBaseUrl() . '?controller=facebook&action=login&next='.urlencode('/').'&code=' . $request->getQuery('code') ) );
		
// 			$this->view->facebook_login_url = $this->facebook->getLoginUrl();
			
			$this->view->facebook_login_url = $this->facebook->getLoginUrl(array(
					'redirect_uri' => WM_Router::create( $request->getBaseUrl() . '?controller=facebook&action=login&next='.urlencode('/').'&code=' . $request->getQuery('code') ),
					'req_perms' => 'email,user_birthday,status_update,user_videos,user_status,user_photos,offline_access,read_friendlists'
			));
			
		}
		
		$this->view->children = array(
        	'header_part' 	=> 'layout/header_part',
        	'footer_part' 	=> 'layout/footer_part'
        );
		
	}
	
}

?>