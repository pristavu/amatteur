<?php

class LandingController extends JO_Action {

	public function indexAction() {        

		$request=$this->getRequest();

		if(JO_Session::get('user[user_id]')) {
			$this->redirect( WM_Router::create( $this->getRequest()->getBaseUrl() ) );
		}
		
		if(JO_Registry::get('enable_free_registration')) {
			$this->redirect( WM_Router::create( $request->getBaseUrl() . '?controller=users&action=register' ) );
		}
		
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
		
		$this->view->login = WM_Router::create( $request->getBaseUrl() . '?controller=users&action=login' );
		
		if(JO_Session::get('successfu_edite')) {
    		$this->view->successfu_edite = true;
    		JO_Session::clear('successfu_edite'); 
    	}
		
		if($request->isPost()) {
			
			$validate = new Helper_Validate();
			$validate->_set_rules($request->getPost('email'), $this->translate('Email'), 'not_empty;min_length[5];max_length[100];email');
			
			if($validate->_valid_form()) {
				$shared_content = Model_Users::sharedContentInvate($request->getPost('email'));
	    		if($shared_content == 1) {
	    			$this->view->error = $this->translate('This e-mail address is already registered');
	    		} else if($shared_content == 2) {
	    			$this->view->error = $this->translate('This e-mail address is already registered');
	    		} else {
	    			if(($key = Model_Users::addSharedContent($request->getPost('email'))) !== false) {
		    			JO_Session::set('successfu_edite', true);
		    			if(JO_Registry::get('not_ri')) {
    		    			Model_Email::send(
    				    	  	JO_Registry::get('report_mail'),
    				    	 	JO_Registry::get('noreply_mail'),
    				    	   	$this->translate('New invitation request'),
    				    	  	$this->translate('Hello, there is new invitation request in ').' '.JO_Registry::get('site_name')
    				    	 );
		    			}
						$this->redirect( WM_Router::create( $request->getBaseUrl() . '?controller=landing' ) );
	    			} else {
	    				$this->view->error = $this->translate('There was an error. Please try again later!');
	    			}
	    		}
			} else {
				$this->view->error = $validate->_get_error_messages();
			}
			
		}
		
		
//        $this->view->children = array(
//            'header_part'     => 'layout/header_part',
//            'footer_part'     => 'layout/footer_part'
//        );
		
	}
	
}

?>