<?php

class MailsController extends JO_Action {

	public function indexAction() {
		
		$request = $this->getRequest();
		
		$this->view->popup_main_box = $this->view->render('popup_form','mails');
		$this->view->friends_href = WM_Router::create($request->getBaseUrl() . '?controller=users&action=mailfriends');
		
		if($request->isXmlHttpRequest()) {
			$this->noViewRenderer(true);
			echo $this->view->popup_main_box;
		} else {
			$this->view->children = array(
	        	'header_part' 	=> 'layout/header_part',
	        	'footer_part' 	=> 'layout/footer_part',
	        	'left_part' 	=> 'layout/left_part'
	        );
		}
	}
	
	public function createAction() {
		
		$request = $this->getRequest();
		$this->view->form_action = WM_Router::create( $request->getBaseUrl() . '?controller=mails&action=create' );
		$this->view->friends_href = WM_Router::create($request->getBaseUrl() . '?controller=users&action=mailfriends');
		$this->view->popup_main_box = $this->view->render('popup_form','mails');
		
		
		if( $request->isPost() ) {
			if( JO_Session::get('user[user_id]') ) {
						$data = Model_Mails::createMail(array(
							'text' => preg_replace("/\n/","<br/>",$request->getPost('text')),
							'toUsers' => $request->getPost('friends')
						));
						if($data) {
							$this->view->data = $data;
						} else {
							$this->view->error = $this->translate('There was a problem with the record. Please try again!');
						}
			}
			echo $this->renderScript('json');
		}else
		{
			if($request->isXmlHttpRequest()) {
				$this->noViewRenderer(true);
				echo $this->view->popup_main_box;
			} else {
				$this->view->children = array(
					'header_part' 	=> 'layout/header_part',
					'footer_part' 	=> 'layout/footer_part',
					'left_part' 	=> 'layout/left_part'
				);
			}
		}
		
	}
	
	public function stateAction() {
		
		$request = $this->getRequest();
		
		
		if( $request->isPost() ) {
			if( JO_Session::get('user[user_id]') ) {
						$data = Model_Mails::updateMail(array(
							'mail_id' => $request->getPost('id'),
							'read_mail' => $request->getPost('read')
						));
						if($data) {
							$this->view->data = $data;
						} else {
							$this->view->error = $this->translate('There was a problem with the record. Please try again!');
						}
			}
			echo $this->renderScript('json');
		}
	}
	
	public function viewAction() {
		
		$request = $this->getRequest();
		
		$this->view->form_action = WM_Router::create( $request->getBaseUrl() . '?controller=mails&action=reply' );
		$this->view->base_url = $request->getBaseUrl();
		$this->view->popup_main_box = $this->view->render('popup_mail','mails');
		
		
		
		
			if($request->isXmlHttpRequest()) {
				$this->noViewRenderer(true);
				echo $this->view->popup_main_box;
			} else {
				$this->view->children = array(
					'header_part' 	=> 'layout/header_part',
					'footer_part' 	=> 'layout/footer_part',
					'left_part' 	=> 'layout/left_part'
				);
			}
		
	}
	
	public function replyAction() {
		
		$request = $this->getRequest();
		
		
		if( $request->isPost() ) {
			if( JO_Session::get('user[user_id]') ) {
						$data = Model_Mails::replyMail(array(
							'text' => preg_replace("/\n/","<br/>",$request->getPost('text')),
							'parent' => $request->getPost('parent'),
							'replies' => $request->getPost('replies')
						));
						if($data) {
							$this->view->data = $data;
						} else {
							$this->view->error = $this->translate('There was a problem with the record. Please try again!');
						}
			}
			echo $this->renderScript('json');
		}else
		{
			if($request->isXmlHttpRequest()) {
				$this->noViewRenderer(true);
				echo $this->view->popup_main_box;
			} else {
				$this->view->children = array(
					'header_part' 	=> 'layout/header_part',
					'footer_part' 	=> 'layout/footer_part',
					'left_part' 	=> 'layout/left_part'
				);
			}
		}
		
	}
}