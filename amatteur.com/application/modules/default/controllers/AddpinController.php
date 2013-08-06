<?php

class AddpinController extends JO_Action {

	public function indexAction() {
		
		$request = $this->getRequest();
		
		$this->view->from_url = WM_Router::create( $request->getBaseUrl() . '?controller=addpin&action=fromurl' );
		$this->view->from_file = WM_Router::create( $request->getBaseUrl() . '?controller=addpin&action=fromfile' );
		$this->view->add_board = WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=create' );
		
		$goodies = Model_Pages::getPage( JO_Registry::get('page_goodies') );
		
		$pin_text = $this->translate('Pin images from any website as you browse the web with the %s"Pin It" button.%s');
		if($goodies) {
			$this->view->pin_text = sprintf($pin_text, '<a href="'.WM_Router::create($this->getRequest()->getBaseUrl() . '?controller=pages&action=read&page_id=' . JO_Registry::get('page_goodies')).'">', '</a>');
		}
		
		$this->view->popup_main_box = $this->view->render('popup_main','addpin');
		
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
	
	public function fromurlAction() {
		
		$request = $this->getRequest();
	
		$this->view->form_action = WM_Router::create( $request->getBaseUrl() . '?controller=addpin&action=get_images' );
		
		$this->view->popup_main_box = $this->view->render('fromurl','addpin');
		
		if( $request->isPost() ) {
			
			$url_m = $request->getPost('media');
			if(strpos($url_m, '.jpg?')) {
			$url_m = explode('?', $url_m);
			$url_m = $url_m[0];
			}
			
			$result = Model_Pins::create(array(
				'title' => $request->getPost('title'),
				'from' => $request->getPost('from'),
				'image' => $url_m,
				'is_video' => $request->getPost('is_video'),
				'is_article' => $request->getPost('is_article'),
				'description' => $request->getPost('message'),
				'price' => $request->getPost('price'),
				'board_id' => $request->getPost('board_id')
			));
			if($result) {
				Model_History::addHistory(JO_Session::get('user[user_id]'), Model_History::ADDPIN, $result);
				
			
				$session_user = JO_Session::get('user[user_id]');
				
				$group = Model_Boards::isGroupBoard($request->getPost('board_id'));
				if($group) {
					$users = explode(',',$group);
					foreach($users AS $user_id) {
						if($user_id != $session_user) {
							$user_data = Model_Users::getUser($user_id);

							if($user_data && $user_data['email_interval'] == 1 && $user_data['groups_pin_email']) {
								$this->view->user_info = $user_data;
								$this->view->profile_href = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . JO_Session::get('user[user_id]'));
								$this->view->full_name = JO_Session::get('user[firstname]') . ' ' . JO_Session::get('user[lastname]');
								$this->view->pin_href = WM_Router::create( $request->getBaseUrl() . '?controller=pin&pin_id=' . $result );
								$board_info = Model_Boards::getBoard($request->getPost('board_id'));
								if($board_info) {
									$this->view->board_title = $board_info['title'];
									$this->view->board_href = WM_Router::create($request->getBaseUrl() . '?controller=boards&action=view&user_id=' . $board_info['user_id'] . '&board_id=' . $board_info['board_id']);
								}
								Model_Email::send(
				    	        	$user_data['email'],
				    	        	JO_Registry::get('noreply_mail'),
				    	        	JO_Session::get('user[firstname]') . ' ' . JO_Session::get('user[lastname]') . ' ' . $this->translate('added new pin to a group board'),
				    	        	$this->view->render('group_board', 'mail')
				    	        );
							}

						}
					}
				}
				
				$this->view->pin_url = WM_Router::create( $request->getBaseUrl() . '?controller=pin&pin_id=' . $result );
				$this->view->popup_main_box = $this->view->render('success','addpin');
			}
			
		}
		
		
		$this->setViewChange('index');
		if($request->isXmlHttpRequest()) {
			$this->noViewRenderer(true);
			echo $this->view->popup_main_box;
			$this->view->is_popup = true;
		} else {
			$this->view->is_popup = false;
			$this->view->children = array(
	        	'header_part' 	=> 'layout/header_part',
	        	'footer_part' 	=> 'layout/footer_part',
	        	'left_part' 	=> 'layout/left_part'
	        );
		}
	}
	
	public function get_imagesAction() {
		
		$request = $this->getRequest();
		$this->view->form_action = WM_Router::create( $request->getBaseUrl() . '?controller=addpin&action=get_images' );
		$this->view->total_images = 0;
		if($request->isGet() && $request->getQuery('url')) {
			
			$http = new JO_Http();
			$http->setUseragent('Amatteur bot v' . JO_Registry::get('system_version'));
			$http->useCurl(true);
			$http->execute($request->getQuery('url'), $request->getBaseUrl(), 'GET');
			
			$video_url = $request->getQuery('url');
			
			if(isset($http->headers['location']) && $http->headers['location']) {
				$new_url = $http->headers['location'];
				$http = new JO_Http();
				$http->setUseragent('Amatteur bot v' . JO_Registry::get('system_version'));
				$http->useCurl(true);
				$http->execute($new_url, $request->getBaseUrl(), 'GET');
				$video_url = $new_url;
			}
			
			$video_url = trim($video_url);
			if(strpos($video_url,'http') === false) {
				$video_url = 'http://' . $video_url;
			}
			
			$this->view->from = $video_url;
			
			
			if($http->error) {
				$this->view->error = $http->error;
			} elseif( ($imagesize = $this->getimagesize2($video_url) ) !== false ) {
				
				$this->view->images[] = array(
					'src' => $video_url,
					'width' => $imagesize[0],
					'height' => $imagesize[1],
				);
				
				$this->view->total_images = 1;
				
				$this->view->from_url = WM_Router::create( $request->getBaseUrl() . '?controller=addpin&action=fromurl' );
				
				$this->view->createBoard = WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=create' );
				
				$boards = Model_Boards::getBoards(array(
					'filter_user_id' => JO_Session::get('user[user_id]'),
					'order' => 'boards.sort_order',
					'sort' => 'ASC',
					'friendly' => JO_Session::get('user[user_id]')
				));
				
				$this->view->boards = array();
				if($boards) {
					foreach($boards AS $board) {
						$this->view->boards[] = array(
							'board_id' => $board['board_id'],
							'title' => $board['title']
						);
					}
				}
				
				$this->view->is_video = 'false';
				
				$this->view->title = basename($video_url);
				
				$this->view->popup_main_box = $this->view->render('from_url', 'addpin');
				
			} else {
			
				$html = JO_Utf8::convertToUtf8($http->result);
				
				$dom = new JO_Dom_Query($html);
				
				$title = $dom->query('title');
				
				$this->view->title = '';
				if($title->innerHtml()) {
					$this->view->title = trim($title->innerHtml());
				}
				
				
				$this->view->images = array();
				
				$meta_image = $dom->query('meta[property="og:image"]');
// 				$meta_image_src_dom = $meta_image->rewind();//->getAttribute('content');
				
				$meta_image_src = null;
				if($meta_image->count()) {
					$meta_image_src = $meta_image->rewind()->getAttribute('content');
				}
				
				if($meta_image_src) {
					
					if( ($imagesize = $this->getimagesize2($meta_image_src)) !== false ) {
						if($imagesize && $imagesize[0] >=80 && $imagesize[1] >= 80) {
							$this->view->images[] = array(
								'src' => $meta_image_src,
								'width' => $imagesize[0],
								'height' => $imagesize[1]
							);
						}
					}
				}
				
				$images = $dom->query('img');

				if($images->count() > 0) {
				
					
					$images_array = array();
					for($i=0; $i<$images->count(); $i++) {
						$src = $images->getItem($i)->getAttribute('src');
						$image_full = JO_Url_Relativetoabsolute::toAbsolute($request->getQuery('url'), $src);
						$images_array[$image_full] = $image_full;
					}
					foreach($images_array AS $image_full) {
	
						$imagesize = $this->getimagesize2($image_full);
						
						if($imagesize && $imagesize[0] >=80 && $imagesize[1] >= 80) {
							
							$this->view->images[] = array(
								'src' => $image_full,
								'width' => $imagesize[0],
								'height' => $imagesize[1],
//								'parent' => $parent_href,
//								'is_video' => $parent_href ? $help_video->parseUrl($parent_href) : false
							);
						}
					}
				}
			
			
				$this->view->total_images = count($this->view->images);
				
				$this->view->from_url = WM_Router::create( $request->getBaseUrl() . '?controller=addpin&action=fromurl' );
				
				$this->view->createBoard = WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=create' );
				
				$boards = Model_Boards::getBoards(array(
					'filter_user_id' => JO_Session::get('user[user_id]'),
					'order' => 'boards.sort_order',
					'sort' => 'ASC',
					'friendly' => JO_Session::get('user[user_id]')
				));
				
				$this->view->boards = array();
				if($boards) {
					foreach($boards AS $board) {
						$this->view->boards[] = array(
							'board_id' => $board['board_id'],
							'title' => $board['title']
						);
					}
				}
			
				$this->view->is_video = 'false';
				$help_video = new Helper_AutoEmbed();
				if($help_video->parseUrl($video_url)) {
					$this->view->is_video = 'true';
					if(!count($this->view->images)) {
						$img = $help_video->getImageURL();
						
						$image_full = null;
						if($img) {
							$image_full = $img;
						} elseif( preg_match('~http://(?:www\.)?vimeo\.com/([0-9]{1,12})~imu', $video_url, $match) ) {
							$url = 'http://vimeo.com/api/v2/video/'.$match[1].'.json?callback=';
							
							$http = new JO_Http();
							$http->setUseragent('Amatteur bot v' . JO_Registry::get('system_version'));
							$http->useCurl(true);
							$http->execute($url, $request->getBaseUrl(), 'GET');
							
							if($http->error) {
								$this->view->error = $http->error;
							} else {
								
								$meta_image = $dom->query('meta[property="og:image"]');
								$meta_image_src = $meta_image->rewind()->getAttribute('content');
								if($meta_image_src && @getimagesize($meta_image->content)) {
									$image_full = $meta_image_src;
								} else {
									$data = JO_Json::decode($http->result , true);
									if(isset($data[0]['thumbnail_large'])) {
										$image_full = $data[0]['thumbnail_large'];
									} elseif(isset($data[0]['thumbnail_medium'])) {
										$image_full = $data[0]['thumbnail_medium'];
									} elseif(isset($data[0]['thumbnail_small'])) {
										$image_full = $data[0]['thumbnail_small'];
									}
								}
							}
						}
						if($image_full && $imagesize = @getimagesize($image_full)) {
							if($imagesize && $imagesize[0] >=80 && $imagesize[1] >= 80) {
								$this->view->images[] = array(
									'src' => $image_full,
									'width' => $imagesize[0],
									'height' => $imagesize[1]
								);
							}
						}
						$this->view->total_images = count($this->view->images);
					}
				}
			
				$this->view->popup_main_box = $this->view->render('from_url', 'addpin');
				
			}
			
			if(count($this->view->images) == 0) {
				$this->view->error_total_images = 1;
				$this->view->popup_main_box = $this->view->render('fromurl', 'addpin');
			}
			
			
			$this->setViewChange('index');
			if($request->isXmlHttpRequest()) {
				$this->noViewRenderer(true);
				echo $this->view->popup_main_box;//$this->renderScript('json');
			} else {
				$this->view->is_popup = false;
				$this->view->children = array(
		        	'header_part' 	=> 'layout/header_part',
		        	'footer_part' 	=> 'layout/footer_part',
		        	'left_part' 	=> 'layout/left_part'
		        );
			}
		} else {
			$this->forward('error', 'error404');
		}
	}
	
	public function getimagesize2($image_url){
		
		$image_url = urldecode($image_url);
		
		$user_agent = ini_get('user_agent');
		ini_set('user_agent', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.9) Gecko/20071025 Firefox/2.0.0.9');
		
		$handle = @fopen ($image_url, "rb");
		$contents = "";
		$count = 0;
		if ($handle) {
			do {
				$count += 1;
				$data = fread($handle, 8192);
				if (strlen($data) == 0) {
					break;
				}
				$contents .= $data;
			} while(true);
		} else { 
			return false;
		}
		fclose ($handle);
		
		$im = @ImageCreateFromString($contents);
		if (!$im) {
			return false;
		}
		$gis[0] = ImageSX($im);
		$gis[1] = ImageSY($im);
		// array member 3 is used below to keep with current getimagesize standards
		$gis[3] = "width={$gis[0]} height={$gis[1]}";
		ImageDestroy($im);
		
		ini_set('user_agent', $user_agent);
		
		return $gis;
	}
	
	/* from file */
	
	public function fromfileAction(){
		
		$request = $this->getRequest();
		
		$this->view->form_action = WM_Router::create( $request->getBaseUrl() . '?controller=addpin&action=upload_images' );
		
		$this->view->upload_action = WM_Router::create( $request->getBaseUrl() . '?controller=addpin&action=upload_imagesView' );
		
	
		
		$this->view->popup_main_box = $this->view->render('fromfile','addpin');
		
		
		if( $request->isPost() ) {
			
			$result = Model_Pins::create(array(
				'title' => $request->getPost('title'),
				'from' => '',
				'image' => BASE_PATH . JO_Session::get('upload_from_file'),
				'is_video' => $request->getPost('is_video'),
                                'is_article' => $request->getPost('is_article'),
				'description' => $request->getPost('message'),
				'price' => $request->getPost('price'),
				'board_id' => $request->getPost('board_id')
			));
			if($result) {
				
				Model_History::addHistory(0, Model_History::ADDPIN, $result);
				
				if(JO_Registry::get('isMobile')){
					$this->redirect('/');
				}
				
				$session_user = JO_Session::get('user[user_id]');
				
				$group = Model_Boards::isGroupBoard($request->getPost('board_id'));
				if($group) {
					$users = explode(',',$group);
					foreach($users AS $user_id) {
						if($user_id != $session_user) {
							$user_data = Model_Users::getUser($user_id);

							if($user_data && $user_data['email_interval'] == 1 && $user_data['groups_pin_email']) {
								$this->view->user_info = $user_data;
								$this->view->profile_href = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . JO_Session::get('user[user_id]'));
								$this->view->full_name = JO_Session::get('user[firstname]') . ' ' . JO_Session::get('user[lastname]');
								$this->view->pin_href = WM_Router::create( $request->getBaseUrl() . '?controller=pin&pin_id=' . $result );
								$board_info = Model_Boards::getBoard($request->getPost('board_id'));
								if($board_info) {
									$this->view->board_title = $board_info['title'];
									$this->view->board_href = WM_Router::create($request->getBaseUrl() . '?controller=boards&action=view&user_id=' . $board_info['user_id'] . '&board_id=' . $board_info['board_id']);
								}
								Model_Email::send(
				    	        	$user_data['email'],
				    	        	JO_Registry::get('noreply_mail'),
				    	        	JO_Session::get('user[firstname]') . ' ' . JO_Session::get('user[lastname]') . ' ' . $this->translate('added new pin to a group board'),
				    	        	$this->view->render('group_board', 'mail')
				    	        );
							}

						}
					}
				}
				
				$this->view->pin_url = WM_Router::create( $request->getBaseUrl() . '?controller=pin&pin_id=' . $result );
				$this->view->popup_main_box = $this->view->render('success','addpin');
				if(JO_Session::get('upload_from_file')) {
					@unlink( BASE_PATH . JO_Session::get('upload_from_file') );
					JO_Session::clear('upload_from_file');
					JO_Session::clear('upload_from_file_name');
				}
			}
			
		}
		
		
		
		$this->setViewChange('index');
		if($request->isXmlHttpRequest()) {
			$this->view->popup = true;
			echo $this->view->popup_main_box;
			$this->noViewRenderer(true);
		} else {
			$this->view->children = array(
	        	'header_part' 	=> 'layout/header_part',
	        	'footer_part' 	=> 'layout/footer_part',
	        	'left_part' 	=> 'layout/left_part'
	        );
		}
		
	}
	
	public function upload_imagesAction() {
		
		$request = $this->getRequest();
		
		if(JO_Session::get('upload_from_file')) {
			@unlink( BASE_PATH . JO_Session::get('upload_from_file') );
			JO_Session::clear('upload_from_file');
			JO_Session::clear('upload_from_file_name');
		}
		
		$image = $request->getFile('file');
		if(!$image) {
			 $this->view->error = $this->translate('There is no file selected');
		} else {

			$temporary = '/cache/review/';
			$upload_folder = BASE_PATH . $temporary;
			$upload = new Helper_Upload;
			
			$upload->setFile($image)
				->setExtension(array('.jpg','.jpeg','.png','.gif'))
				->setUploadDir($upload_folder);
				$new_name = md5(time() . serialize($image)); 
				if($upload->upload($new_name)) {
					$info = $upload->getFileInfo();
					if($info) {
						
						$this->view->from_url = WM_Router::create( $request->getBaseUrl() . '?controller=addpin&action=fromfile' );
		
//						$this->view->file = $image['name'];
//						$this->view->full_path = $temporary . $info['name'];
						$this->view->success = 1;//$this->view->render('upload_images', 'addpin');
						JO_Session::set('upload_from_file', $temporary . $info['name']);
						JO_Session::set('upload_from_file_name', $image['name']);
						
					} else {
						$this->view->error = $this->translate('An unknown error');
					}
				} else {
					$this->view->error = $upload->getError();
				}
		}
		
		$this->noViewRenderer(true);
		echo $this->renderScript('json');
	}
	
	public function upload_imagesViewAction() {
		
		$request = $this->getRequest();
		if($request->isXmlHttpRequest() && JO_Session::get('upload_from_file') && file_exists(BASE_PATH . JO_Session::get('upload_from_file'))) {
			
			$this->view->createBoard = WM_Router::create( $request->getBaseUrl() . '?controller=boards&action=create' );
			
			$temporary = '/cache/review/';
			$upload_folder = BASE_PATH . $temporary;
			
			$boards = Model_Boards::getBoards(array(
				'filter_user_id' => JO_Session::get('user[user_id]'),
				'order' => 'boards.sort_order',
				'sort' => 'ASC',
				'friendly' => JO_Session::get('user[user_id]')
			));
			
			$this->view->boards = array();
			if($boards) {
				foreach($boards AS $board) {
					$this->view->boards[] = array(
						'board_id' => $board['board_id'],
						'title' => $board['title']
					);
				}
			}
			
			
			$this->view->form_action = WM_Router::create( $request->getBaseUrl() . '?controller=addpin&action=upload_images' );
			
			$this->view->upload_action = WM_Router::create( $request->getBaseUrl() . '?controller=addpin&action=upload_imagesView' );
		
			
			$this->view->from_url = WM_Router::create( $request->getBaseUrl() . '?controller=addpin&action=fromfile' );
		
			$this->view->file = JO_Session::get('upload_from_file_name');
			$this->view->full_path = WM_Router::create( $request->getBaseUrl().JO_Session::get('upload_from_file'));
			$this->view->success = $this->view->render('upload_images', 'addpin');
			
		} else {
			$this->forward('addpin', 'fromfile');
		}
		
		$this->noViewRenderer(true);
		echo $this->view->success;
		
	}
	
	public function upload_mobileAction(){
		$this->noViewRenderer(true);
	
		$this->noLayout(true);
		$request = $this->getRequest();
		if(JO_Registry::get('isMobile') && JO_Session::get('upload_from_file') && file_exists(BASE_PATH . JO_Session::get('upload_from_file'))){
			$image =  "<img src='".JO_Session::get('upload_from_file')."'>";
				
			$boards = Model_Boards::getBoards(array(
					'filter_user_id' => JO_Session::get('user[user_id]'),
					'order' => 'boards.sort_order',
					'sort' => 'ASC',
					'friendly' => JO_Session::get('user[user_id]')
			));
				
			$data['boards'] = $boards;
			$data['image'] = $image;
			$data['phrases']= array('create_board'=>$this->translate("Create New Board"),
					'upload_button'=>$this->translate("Upload"),
					'select_board'=>$this->translate("Select Board"),
					'textarea_validation'=>$this->translate("Please add a description"),
					'board_validation'=>$this->translate("Please add a board title"),
					'notEmptyMsg'=>$this->translate("Please choose board"));
				
			echo JO_Json::encode((object)$data);
				
		}
	}
	
	
}

?>