<?php

class JsController extends JO_Action {

	public function indexAction() {
		$this->noLayout(true);
		
		$response = $this->getResponse();
		$response->addHeader('Cache-Control: no-cache, must-revalidate');
		$response->addHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$response->addHeader('Content-type: application/javascript');
	}
	
}

?>