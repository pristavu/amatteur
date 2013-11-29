<?php

class EventsController extends JO_Action {
	
	public static function config() {
		return array(
			'name' => self::translate('Events'),
			'has_permision' => true,
			'menu' => self::translate('Events'),
			'in_menu' => true,
			'permision_key' => 'events',
			'sort_order' => 30505
		);
	}
	
	/////////////////// end config

	private $session;
	
	public function init() {
		$this->session = JO_Session::getInstance();
	}
	
	public function indexAction() {
		
		if($this->session->get('successfu_edite')) 
                    {
    		$this->view->successfu_edite = true;
    		$this->session->clear('successfu_edite'); 
    	}
    	if($this->session->get('error_permision')) 
            {
    		$this->view->error_permision = $this->session->get('error_permision');
    		$this->session->clear('error_permision'); 
    	} 
        
    	$reques = $this->getRequest();
    	
    	$this->view->sort = $reques->getRequest('sort', 'DESC');
    	$this->view->order = $reques->getRequest('order', 'p.event_id');
    	$this->view->page_num = $page = $reques->getRequest('page', 1);
    	
    	$this->view->filter_event_id = $reques->getQuery('filter_event_id');
    	$this->view->filter_user_id = $reques->getQuery('filter_user_id');
    	$this->view->filter_fullname = $reques->getQuery('filter_fullname');
    	$this->view->filter_username = $reques->getQuery('filter_username');
    	$this->view->filter_description = $reques->getQuery('filter_description');
    	$this->view->filter_eventname = $reques->getQuery('filter_eventname');
    	
    	
    	$url = '';
    	if($this->view->filter_event_id) { $url .= '&filter_dic_id=' . $this->view->filter_event_id; }
    	if($this->view->filter_fullname) { $url .= '&filter_fullname=' . $this->view->filter_fullname; }
    	if($this->view->filter_username) { $url .= '&filter_username=' . $this->view->filter_username; }
    	if($this->view->filter_user_id) { $url .= '&filter_user_id=' . $this->view->filter_user_id; }
    	if($this->view->filter_description) { $url .= '&filter_description=' . $this->view->filter_description; }
    	if($this->view->filter_eventname) { $url .= '&filter_eventname=' . $this->view->filter_eventname; }
  
    	$url1 = '';
    	if($this->view->sort) {
    		$url1 .= '&sort=' . $this->view->sort;
    	}
    	if($this->view->order) {
    		$url1 .= '&order=' . $this->view->order;
    	}
    	
    	$url2 = '&page=' . $page;
    	
    	
    	$data = array(
    		'start' => ($page * JO_Registry::get('config_admin_limit')) - JO_Registry::get('config_admin_limit'),
			'limit' => JO_Registry::get('config_admin_limit'),
    		'sort' => $this->view->sort,
    		'order' => $this->view->order,
    		'filter_event_id' => $this->view->filter_event_id,
    		'filter_fullname' => trim($this->view->filter_fullname),
    		'filter_username' => trim($this->view->filter_username),
    		'filter_description' => trim($this->view->filter_description),
    		'filter_eventname' => trim($this->view->filter_eventname),
    		'filter_user_id' => $this->view->filter_user_id
    	);
    	
        $this->view->events = array();
        $events = Model_Events::getEvents($data);
        
        
        if($events) {
            
            foreach($events AS $event) {
            	$event['event_href'] = WM_Router::create( $reques->getBaseUrl() . '?controller=events&event_id=' . $event['event_id'] );
            	$event['user_href'] = WM_Router::create($reques->getBaseUrl() . '?controller=users&action=profile&user_id=' . $event['user_id']);
                $event['likes'] = Model_Events::getCountLike($event['event_id']);
                $event['follow'] = Model_Events::getCountFollow($event['event_id']);
                $event['comments'] = Model_Events::getCountComments($event['event_id']);
                $event['event_href'] = WM_Router::create( $reques->getBaseUrl() . '?controller=events&action=indexeventBoxDetail&event_id=' . $event['event_id'] );
                $this->view->events[] = $event;
            }
        } 
        
        $this->view->sort = strtolower($this->view->sort);
    	
    	$this->view->sort_event_id = $reques->getModule() . '/events/?order=p.event_id&sort=' . ($this->view->sort == 'asc' ? 'DESC' : 'ASC') . $url . $url2;
    	$this->view->sort_fullname = $reques->getModule() . '/events/?order=fullname&sort=' . ($this->view->sort == 'asc' ? 'DESC' : 'ASC') . $url . $url2;
    	$this->view->sort_username = $reques->getModule() . '/events/?order=u.username&sort=' . ($this->view->sort == 'asc' ? 'DESC' : 'ASC') . $url . $url2;
    	$this->view->sort_price = $reques->getModule() . '/events/?order=p.price&sort=' . ($this->view->sort == 'asc' ? 'DESC' : 'ASC') . $url . $url2;
    	$this->view->sort_likes = $reques->getModule() . '/events/?order=p.likes&sort=' . ($this->view->sort == 'asc' ? 'DESC' : 'ASC') . $url . $url2;
    	$this->view->sort_comments = $reques->getModule() . '/events/?order=p.comments&sort=' . ($this->view->sort == 'asc' ? 'DESC' : 'ASC') . $url . $url2;
    	$this->view->sort_eventname = $reques->getModule() . '/events/?order=p.eventname&sort=' . ($this->view->sort == 'asc' ? 'DESC' : 'ASC') . $url . $url2;
    	 
        $total_records = Model_Events::getTotalEvents($data);
		
		$this->view->total_pages = ceil($total_records / JO_Registry::get('config_admin_limit'));
		$this->view->total_rows = $total_records;
		
		$pagination = new Model_Pagination;
		$pagination->setLimit(JO_Registry::get('config_admin_limit'));
		$pagination->setPage($page);
		$pagination->setTotal($total_records);
		$pagination->setUrl($this->getRequest()->getModule() . '/events/?page={page}' . $url . $url1);
		$this->view->pagination = $pagination->render();
        
	}
	
	public function changeStatusAction() {
		$this->noViewRenderer(true);
		if( !WM_Users::allow('edit',  $this->getRequest()->getController()) ) {
			echo $this->translate('You do not have permission to this action');
		} else {
			if(Model_Events::changeStatus($this->getRequest()->getPost('id'))) {
				echo 'ok';
			} else {
				echo 'error';
			}
		}
	}
	
	public function editAction() {
	if( !WM_Users::allow('edit',  $this->getRequest()->getController()) ) {
			$this->session->set('error_permision', $this->translate('You do not have permission to this action'));
			$this->redirect($this->getRequest()->getBaseUrl() . $this->getRequest()->getModule() . '/events/');
		}
		$this->setViewChange('form');
		
		if($this->getRequest()->isPost()) {
    		Model_Events::edit($this->getRequest()->getQuery('id'), $this->getRequest()->getParams());
    		$this->session->set('successfu_edite', true);
			$this->redirect($this->getRequest()->getBaseUrl() . $this->getRequest()->getModule() . '/events/');
    	}
		
		$this->getForm();
	}
	
	public function deleteAction() {
		$this->setInvokeArg('noViewRenderer',true);
		if( !WM_Users::allow('delete',  $this->getRequest()->getController()) ) {
			echo $this->translate('You do not have permission to this action');
		} else {
		Model_Events::delete($this->getRequest()->getPost('id'));
		}
	}
	
	public function deleteMultiAction() {
		$this->setInvokeArg('noViewRenderer',true);
		if( !WM_Users::allow('delete',  $this->getRequest()->getController()) ) {
			echo $this->translate('You do not have permission to this action');
		} else {
		$action_check = $this->getRequest()->getPost('action_check');
		if($action_check && is_array($action_check)) {
			foreach($action_check AS $record_id) {
				Model_Events::delete($record_id);
			}
		}
		}
	}
	
	/***************************************** HELP FUNCTIONS ********************************************/
	private function getForm() {
		$request = $this->getRequest();
    	
    	$event_id = $request->getRequest('id');
		$event_info = Model_Events::getEvent($event_id);
		if(!$event_info) {
			$this->redirect($this->getRequest()->getBaseUrl() . $this->getRequest()->getModule() . '/events/');
		}
    	
		if($request->issetPost('description')) {
			$this->view->description = $request->issetPost('description');
		} else {
			$this->view->description = $event_info['description'];
		}
    	
		if($request->issetPost('website')) {
			$this->view->website = $request->issetPost('website');
		} else {
			$this->view->website = $event_info['website'];
		}
    	
		/*if($request->issetPost('board_id')) {
			$this->view->board_id = $request->issetPost('board_id');
		} else {
			$this->view->board_id = $event_info['board_id'];
		}
		*/
		$this->view->event_id = $event_id;
		$this->view->event_href = WM_Router::create( $request->getBaseUrl() . '?controller=events&action=indexeventBoxDetail&event_id=' . $event_id );
		/*
		$this->view->boards = Model_Boards::getBoards(array(
			'filter_user_id' => $event_info['user_id'],
			'sort' => 'asc',
			'order' => 'p.title'
		));
		*/
	}
	
	/***************************************** COMMENTS FUNCTIONS ********************************************/
	
	public function commentsAction() {
		
		$request = $this->getRequest();
		
		$event_id = $request->getRequest('filter_id');
		$event_info = Model_Events::getEvent($event_id);
		if(!$event_info) {
			$this->redirect($this->getRequest()->getBaseUrl() . $this->getRequest()->getModule() . '/events/');
		}
		
		$this->view->comments = Model_Events::getComments(array(
			'filter_event_id' => $event_id,
			'sort' => 'ASC',
			'order' => 'events_comments.comment_id'
		));
	}
	
	public function deleteCommentAction() {
		$this->setInvokeArg('noViewRenderer',true);
		if( !WM_Users::allow('delete',  $this->getRequest()->getController()) ) {
			echo $this->translate('You do not have permission to this action');
		} else {
		Model_Events::deleteComment($this->getRequest()->getPost('id'));
		}
	}
	
	public function deleteMultiCommentsAction() {
		$this->setInvokeArg('noViewRenderer',true);
		if( !WM_Users::allow('delete',  $this->getRequest()->getController()) ) {
			echo $this->translate('You do not have permission to this action');
		} else {
		$action_check = $this->getRequest()->getPost('action_check');
		if($action_check && is_array($action_check)) {
			foreach($action_check AS $record_id) {
				Model_Events::deleteComment($record_id);
			}
		}
		}
	}
	
	
}

?>