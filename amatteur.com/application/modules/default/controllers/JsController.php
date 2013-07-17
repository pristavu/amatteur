<?php

class JsController extends JO_Action {

	public function pinmarkletAction() {
		$this->noLayout(true);
		
		$response = $this->getResponse();
		$response->addHeader('Cache-Control: no-cache, must-revalidate');
		$response->addHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$response->addHeader('Content-type: application/javascript');
		
		$request = $this->getRequest();
		
		$this->view->checkpoint = WM_Router::create( $request->getBaseUrl() . '?controller=bookmarklet&action=urlinfo' );
		$this->view->bookmarklet = WM_Router::create( $request->getBaseUrl() . '?controller=bookmarklet' );
		
		$this->view->imagefolder = $request->getBaseUrl() . 'data/images/';
		$this->view->baseUrl = $request->getBaseUrl();
		
		$this->view->domain = str_replace('.','\.',$request->getDomain(true));
		
		$this->view->site_logo = $request->getBaseUrl() . 'data/images/logo.png';
		if(JO_Registry::get('site_logo') && file_exists(BASE_PATH .'/uploads'.JO_Registry::get('site_logo'))) {
		    $this->view->site_logo = $request->getBaseUrl() . 'uploads' . JO_Registry::get('site_logo'); 
		}
		
	}

	public function i18nAction() {
		
		$translate = new WM_Gettranslate();
		$results = $translate->getTranslateJs();
		if($results) {
			foreach($results AS $key => $data) {
				$this->view->{$key} = $data;
			}
		}
		
		$response = $this->getResponse();
		$response->addHeader('Cache-Control: no-cache, must-revalidate');
		$response->addHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$response->addHeader('Content-type: application/json');
		
		echo 'var lang = ' . $this->renderScript('json') . ';';
	}
	
	public function pinitAction() {
		
		$request = $this->getRequest();
		$this->noLayout(true);
		
		$this->view->baseUrl = $request->getBaseUrl();
		
		$this->view->bookmarklet = WM_Router::create( $request->getBaseUrl() . '?controller=bookmarklet' );
		
		$response = $this->getResponse();
		$response->addHeader('Cache-Control: no-cache, must-revalidate');
		$response->addHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$response->addHeader('Content-type: application/javascript');	
	}
	
}

?>