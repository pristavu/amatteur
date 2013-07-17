<?php

class Model_Upload_Rackspace extends Model_Upload_Abstract {
	
	public static function getPinImage($pin, $prefix = null) {
		if(!$pin['image']) {
			return false;
		}
		if($prefix) {
			$img = self::formatImageSize($pin['image'], $prefix);
			if(!$img) {
				return false;
			}
		} else {
			$img = $pin['image'];
		}
		
		if( ( $img_size = @getimagesize($img) ) !== false ) {
			return array(
				'image' => $img,
				'original' => $pin['image'],
				'width' => $img_size[0],
				'height' => $img_size[1],
				'mime' => $img_size['mime']
			);
		}
		return false;
	}

	public static function deletePinImage($pin_info) {
		try {
			$pin_info['image'] = basename($pin_info['image']);
			$ext = strtolower(strrchr($pin_info['image'],"."));
			$thumbs = array( $pin_info['image'] );
			$sizes = self::pinThumbSizes();
			if($sizes) {
				foreach($sizes AS $size => $key) {
					$thumbs[] = preg_replace('/'.$ext.'$/i',$key.$ext,$pin_info['image']);
				}
			}
			$auth = new JO_Api_Rackspace_Authentication(JO_Registry::get('rsUsername'), JO_Registry::get('rsApiKey'));
			if($auth->authenticate()) {
				$container = 'pins_' . WM_Date::format($pin_info['date_added'], 'yy_mm');
				$conn = new JO_Api_Rackspace_Connection($auth);
				$contaners = $conn->list_public_containers();
				if($contaners && in_array($container, $contaners)) {
					$images = $conn->get_container($container);
					foreach($thumbs AS $thumb) {
						$get = $images->exists_object($thumb);
						if($get && $get->content_length) {
							$images->delete_object($thumb);
						}
					}
				}
			}
		} catch (JO_Exception $e) {}
	}
	
	public static function uploadPin($image, $title = '', $id = 0) {
		try {
			if( ( $imageinfo = getimagesize($image) ) !== false ) {
				
				if(!file_exists(BASE_PATH . '/uploads/cache_pins/' . $id) || !is_dir(BASE_PATH . '/uploads/cache_pins/' . $id)) {
					@mkdir(BASE_PATH . '/uploads/cache_pins/' . $id, 0777, true);
				}
				
				$ext = strtolower(strrchr($image,"."));
				if(!$ext) {
					$mime_ext = explode('/', $imageinfo['mime']);
					if(isset($mime_ext[1])) {
						$ext = '.' . $mime_ext[1];
					}
				}
			
				if(trim($title) && mb_strlen($title, 'utf-8') > 60) {
					$title = JO_Utf8::splitText($title, 60, '');
				}
				
				if( trim($title) ) {
					$name = self::translateImage($title) . '_' . $id . $ext;
				} else {
					$name = md5($image) . '_' . $id . $ext;
				}
				
				if(@copy($image, BASE_PATH . '/uploads/cache_pins/' . $id . '/' . $name)) {
				
					$user_agent = ini_get('user_agent');
					ini_set('user_agent', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.9) Gecko/20071025 Firefox/2.0.0.9');
					
					$container = 'amatteur_pins';
					
					$auth = new JO_Api_Rackspace_Authentication(JO_Registry::get('rsUsername'), JO_Registry::get('rsApiKey'));
					if($auth->authenticate()) {
						$conn = new JO_Api_Rackspace_Connection($auth);
						$contaners = $conn->list_public_containers();
						if(!in_array($container, $contaners)) {
							$conn->create_container($container);
							$contaners[] = $container;
						}
						if($contaners && in_array($container, $contaners)) {
							$images = $conn->get_container($container);
							$images->make_public(86400*365);
							$images = $conn->get_container($container);
							if(!$images->cdn_uri) {
								return false;
							}
							$object = $images->create_object($name);
							$object->load_from_filename(BASE_PATH . '/uploads/cache_pins/' . $id . '/' . $name);
							$image_info = $images->get_object($name);
							if(!$image_info->name) {
								return false;
							}
							
							$model_images = new Helper_Images();
							
							$temp_width = 0;
							$temp_height = 0;
							$sizes = self::pinThumbSizes();
							if($sizes) {
								foreach($sizes AS $size => $prefix) {
									$sizes = explode('x', $size);
									$width = (int)isset($sizes[0])?$sizes[0]:0;
									$height = (int)isset($sizes[1])?$sizes[1]:0;
									$name_pref = basename($name, $ext) . $prefix . $ext;
									if($width && $height) {
										$thumb_a = $model_images->resize('/cache_pins/' . $id . '/' . $name, $width, $height, true);
									} else if($width && !$height) {
										$thumb_a = $model_images->resizeWidth('/cache_pins/' . $id . '/' . $name, $width);
									} else if($height && !$width) {
										$thumb_a = $model_images->resizeHeight('/cache_pins/' . $id . '/' . $name, $height);
									}
									if($prefix == '_B') {
										$temp_width = $model_images->getSizes('width');
										$temp_height = $model_images->getSizes('height');
									} 
									
									$thumb_a1 = explode('/uploads/', $thumb_a);
									if($thumb_a1 && isset($thumb_a1[1])) {
										$object = $images->create_object($name_pref);
										$object->load_from_filename(BASE_PATH . '/uploads/' . $thumb_a1[1]);
									}
								}
							}
							self::recursiveDelete(BASE_PATH . '/uploads/cache_pins/' . $id . '/');
							self::recursiveDelete(BASE_PATH . '/uploads/cache/cache_pins/' . $id . '/');
							if($temp_width) {
								return array(
									'store' 	=> 'rackspace',
									'image' => $images->cdn_uri . '/' . $image_info->name,
									'width'	=> $temp_width,
									'height' => $temp_height
								);
							}
						}
					}
				
				}
			}
			return false;
		} catch (JO_Exception $e) {
			return false;
		}
		return false;
	}
	
	
	/////////// user avatar
	
	public static function uploadUserAvatar($avatar, $user_id = 0) {
		//try {
		
			if( ( $imageinfo = getimagesize($avatar) ) !== false ) {
				
				if(!file_exists(BASE_PATH . '/uploads/cache_avatars/' . $user_id) || !is_dir(BASE_PATH . '/uploads/cache_avatars/' . $user_id)) {
					@mkdir(BASE_PATH . '/uploads/cache_avatars/' . $user_id, 0777, true);
				}
				
				$ext = strtolower(strrchr($avatar,"."));
				if(!$ext) {
					$mime_ext = explode('/', $imageinfo['mime']);
					if(isset($mime_ext[1])) {
						$ext = '.' . $mime_ext[1];
					}
				}
				
				$name = md5(time() . mt_rand()) . '_'. $user_id . $ext;
				
				if(@copy($avatar, BASE_PATH . '/uploads/cache_avatars/' . $user_id . '/' . $name)) {
					
					$added_date = time();
					if( is_array($user_info = Model_Users::getUser($user_id)) ) {
						$added_date = $user_info['date_added'];
						self::deleteUserImage($user_info);
					}
					
					$user_agent = ini_get('user_agent');
					ini_set('user_agent', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.9) Gecko/20071025 Firefox/2.0.0.9');
					
					$container = 'amatteur_users';
					
					$auth = new JO_Api_Rackspace_Authentication(JO_Registry::get('rsUsername'), JO_Registry::get('rsApiKey'));
					
					if($auth->authenticate()) {
						$conn = new JO_Api_Rackspace_Connection($auth);
						$contaners = $conn->list_public_containers();
						if(!in_array($container, $contaners)) {
							$conn->create_container($container);
							$contaners[] = $container;
						}
						
						if($contaners && in_array($container, $contaners)) {
							$images = $conn->get_container($container);
							$images->make_public(86400*365);
							$images = $conn->get_container($container);
							if(!$images->cdn_uri) {
								return false;
							}
							$object = $images->create_object($name);
							$object->load_from_filename(BASE_PATH . '/uploads/cache_avatars/' . $user_id . '/' . $name);
							$image_info = $images->get_object($name);
							if(!$image_info->name) {
								return false;
							}
							
							$model_images = new Helper_Images();
							
							$temp_width = 0;
							$temp_height = 0;
							$sizes = self::userThumbSizes();
							if($sizes) {
								foreach($sizes AS $size => $prefix) {
									$sizes = explode('x', $size);
									$width = (int)isset($sizes[0])?$sizes[0]:0;
									$height = (int)isset($sizes[1])?$sizes[1]:0;
									$name_pref = basename($name, $ext) . $prefix . $ext;
									if($width && $height) {
										$thumb_a = $model_images->resize('/cache_avatars/' . $user_id . '/' . $name, $width, $height, true);
									} else if($width && !$height) {
										$thumb_a = $model_images->resizeWidth('/cache_avatars/' . $user_id . '/' . $name, $width);
									} else if($height && !$width) {
										$thumb_a = $model_images->resizeHeight('/cache_avatars/' . $user_id . '/' . $name, $height);
									}
									if($prefix == '_B') {
										$temp_width = $model_images->getSizes('width');
										$temp_height = $model_images->getSizes('height');
									} 
									
									$thumb_a1 = explode('/uploads/', $thumb_a);
									if($thumb_a1 && isset($thumb_a1[1])) {
										$object = $images->create_object($name_pref);
										$object->load_from_filename(BASE_PATH . '/uploads/' . $thumb_a1[1]);
									}
								}
							}
							self::recursiveDelete(BASE_PATH . '/uploads/cache_avatars/' . $user_id . '/');
							self::recursiveDelete(BASE_PATH . '/uploads/cache/cache_avatars/' . $user_id . '/');
							if($temp_width) {
								return array(
									'store' 	=> 'rackspace',
									'image' => $images->cdn_uri . '/' . $image_info->name,
									'width'	=> $temp_width,
									'height' => $temp_height
								);
							}
						}
					}
				
				}
			}
			return false;
		/*} catch (JO_Exception $e) {
			return false;
		}*/
		return false;
	}
	
	public static function deleteUserImage($user_info) {
		try {
			$user_info['avatar'] = basename($user_info['avatar']);
			$ext = strtolower(strrchr($user_info['avatar'],"."));
			$thumbs = array( $user_info['avatar'] );
			$sizes = self::pinThumbSizes();
			if($sizes) {
				foreach($sizes AS $size => $key) {
					$thumbs[] = preg_replace('/'.$ext.'$/i',$key.$ext,$user_info['avatar']);
				}
			}
			$auth = new JO_Api_Rackspace_Authentication(JO_Registry::get('rsUsername'), JO_Registry::get('rsApiKey'));
			if($auth->authenticate()) {
				$container = 'users_' . WM_Date::format($user_info['date_added'], 'yy_mm');
				$conn = new JO_Api_Rackspace_Connection($auth);
				$contaners = $conn->list_public_containers();
				if($contaners && in_array($container, $contaners)) {
					$images = $conn->get_container($container);
					foreach($thumbs AS $thumb) {
						$get = $images->exists_object($thumb);
						if($get && $get->content_length) {
							$images->delete_object($thumb);
						}
					}
				}
			}
		} catch (JO_Exception $e) {
		}
	}
	
	public static function getUserImage($user, $prefix = null) {
		if(!$user['avatar']) {
			return false;
		}
		if($prefix) {
			$img = self::formatImageSize($user['avatar'], $prefix);
			if(!$img) {
				return false;
			}
		} else {
			$img = $user['avatar'];
		}
		
		if( ( $img_size = @getimagesize($img) ) !== false ) {
			return array(
					'image' => $img,
					'original' => $user['avatar'],
					'width' => $img_size[0],
					'height' => $img_size[1],
					'mime' => $img_size['mime']
			);
		}
		return false;
	}
	
}

?>