<?php

class ToppinsController extends JO_Action {
	
	public function indexAction() {
		
		$request = $this->getRequest();
		
		$page = (int)$request->getRequest('page');
		if($page < 1) { $page = 1; }
                if($page > 1) { exit; }
                
                $index_id = $request->getRequest('index_id');
		
               
                if ($index_id == 1)
                {
                    $this->view->title = 'Top 10 fotos - Últimos 7 días';
                    $data = array(
                            'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                            'order' => 'pins.likes',
                            'sort' => 'DESC',
                            'limit' => 10,
    //			'filter_marker' => $request->getRequest('marker'),
                            'filter_pin_top_10_7' => '7',
                            'filter_categoria_id' => $request->getRequest('category_id')
                    );
                }
                else if ($index_id == 2)
                {
                    $this->view->title = 'Top 10 fotos - Absoluto';
                    $data = array(
                            'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                            'order' => 'pins.likes',
                            'sort' => 'DESC',
                            'limit' => 10,
    //			'filter_marker' => $request->getRequest('marker'),
                            'filter_pin_top_10' => true,
                            'filter_categoria_id' => $request->getRequest('category_id')
                    );
                }
                else if ($index_id == 3)
                {
                    $this->view->title = 'Top 10 perfiles - Últimos 7 días';
                    $data = array(
                            'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                            'order' => 'users.likers',
                            'sort' => 'DESC',
                            'limit' => 10,
    //			'filter_marker' => $request->getRequest('marker'),
                            'filter_profile_top_10_7' => '7',
                            'filter_categoria_id' => $request->getRequest('category_id')
                    );
                }
                else if ($index_id == 4)
                {
                    $this->view->title = 'Top 10 perfiles - Absoluto';
                    $data = array(
                            'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                            'order' => 'users.likers',
                            'sort' => 'DESC',
                            'limit' => 10,
    //			'filter_marker' => $request->getRequest('marker'),
                            'filter_profile_top_10' => true,
                            'filter_categoria_id' => $request->getRequest('category_id')
                    );
                }

                    
                
//		if((int)JO_Session::get('user[user_id]')) {
//			$data['following_users_from_user_id'] = JO_Session::get('user[user_id]');
//		}
		
		
		$this->view->pins = '';
                if ($index_id == 1 || $index_id == 2)                
                {
                    $pins = Model_Pins::getPins($data);
                }
                else if ($index_id == 3 || $index_id == 4)                
                {
                    //$pins = Model_Users::getUsers($data);
                    $pins = Model_Users::getUsers($data);
                }
                    
		
		if($pins) {
			/*$banners = Model_Banners::getBanners(
				new JO_Db_Expr("`controller` = '".$request->getController()."' AND position BETWEEN '".(int)$data['start']."' AND '".(int)$data['limit']."'")
			);
			$pp = JO_Registry::get('config_front_limit');
                         * 
                         */
                        $total = 0;
			foreach($pins AS $row => $pin) {
                            $total++;
                            $this->view->position = $total;
				///banners
                            /*
				$key = $row + (($pp*$page)-$pp);
				if(isset($banners[$key])) {
					$this->view->pins .= Helper_Banners::returnHtml($banners[$key]);	
				}
                 */
                                if ($index_id == 1 || $index_id == 2)                
                                {
        				//pins
                        		$this->view->pins .= Helper_Pin::returnHtmlTop($pin);
                                }
                                else  if ($index_id == 3 || $index_id == 4)
                                {
        				//users
                        		$this->view->pins .= Helper_User::returnHtmlTop($pin);
                                    //$this->view->users .= $this->returnHtml($pin);
                                }
                                if ($total == 10)
                                {
                                    break;
                                }
			}
			//JO_Registry::set('marker', Model_Pins::getMaxPin($data));
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
		$this->forward('toppins', 'index');
	}
	
	public function viewAction(){
		$this->forward('toppins', 'index');
	}

        
        
}

?>