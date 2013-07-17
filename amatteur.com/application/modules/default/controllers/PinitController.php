<?php

class PinitController extends JO_Action {

	public  function indexAction() {
		$request = $this->getRequest();
		$this->noLayout(true);
		
		$this->view->baseUrl = $request->getBaseUrl();
		
		$this->view->bookmarklet = WM_Router::create( $request->getBaseUrl() . '?controller=bookmarklet' );
		
	}
	
}

?>