<?php

class Model_Upload_Amazons3 extends Model_Upload_Abstract {
	
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
		
		//$img = 'http://' . JO_Registry::get('bucklet') . '.' . trim(JO_Registry::get('awsDomain'),'.') . '/' . $img;
		$img = 'http://images.amatteur.com/' . $img;
		if( ( $img_size = @getimagesize($img) ) !== false ) {
			return array(
				'image' => $img,
				//'original' => 'http://' . JO_Registry::get('bucklet') . '.' . trim(JO_Registry::get('awsDomain'),'.') . '/' . $pin['image'],
				'original' => 'http://images.amatteur.com/' . $pin['image'],
				'width' => $img_size[0],
				'height' => $img_size[1],
				'mime' => $img_size['mime']
			);
		}
		return false;
	}
	
	public static function deletePinImage($pin_info) {
		try {
			$pin_info['image'] = $pin_info['image'];
			$ext = strtolower(strrchr($pin_info['image'],"."));
			$thumbs = array( $pin_info['image'] );
			$sizes = self::pinThumbSizes();
			if($sizes) {
				foreach($sizes AS $size => $key) {
					$thumbs[] = preg_replace('/'.$ext.'$/i',$key.$ext,$pin_info['image']);
				}
			}
			foreach($thumbs AS $thumb) {
				self::deleteFromServer($thumb);
			}
		} catch (JO_Exception $e) { }
	}
	
	public static function deleteFromServer($image) {
		$s3 = new JO_Api_Amazon(JO_Registry::get('awsAccessKey'), JO_Registry::get('awsSecretKey'));
		$s3->putBucket(JO_Registry::get('bucklet'), JO_Api_Amazon::ACL_PUBLIC_READ);
		if($s3->getBucketLogging(JO_Registry::get('bucklet'))) {
			$s3->deleteObject(JO_Registry::get('bucklet'), $image);
		}
	}
	
	public static function uploadPin($image, $title = '', $id = 0) {
		
		try {
			if($title && mb_strlen($title, 'utf-8') > 60) {
				$title = JO_Utf8::splitText($title, 60, '');
			}
			
			$user_agent = ini_get('user_agent');
			ini_set('user_agent', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.9) Gecko/20071025 Firefox/2.0.0.9');
			
			$date_added = WM_Date::format(time(), 'yy-mm-dd H:i:s');
		
			$s3 = new JO_Api_Amazon(JO_Registry::get('awsAccessKey'), JO_Registry::get('awsSecretKey'));
			$s3->putBucket(JO_Registry::get('bucklet'), JO_Api_Amazon::ACL_PUBLIC_READ);
				
			if($s3->getBucketLogging(JO_Registry::get('bucklet'))) {
				if( ( $imageinfo = getimagesize($image) ) !== false ) {
                                    
                                        $pos = strpos($image, "?");

                                        // Nótese el uso de ===. Puesto que == simple no funcionará como se espera
                                        // porque la posición de 'a' está en el 1° (primer) caracter.
                                        if ($pos === false) 
                                        {
                                            // no tiene ? en el nombre de la imagen
                                        }                            
                                        else
                                        {
                                            $pieces = explode("?", $image);
                                            $image = $pieces[0];    
                                        }

					$ext = strtolower(strrchr($image,"."));
					if(!$ext) {
						$mime_ext = explode('/', $imageinfo['mime']);
						if(isset($mime_ext[1])) {
							$ext = '.' . $mime_ext[1];
						}
					}
						
					if( $title ) {
						$name = self::translateImage($title) . '_' . $id . $ext;
					} else {
						$name = md5($image) . '_' . $id . $ext;
					}
						
					$image_path = 'pins/' . WM_Date::format($date_added, 'yy/mm/');
					//					$name = self::rename_if_exists_amazon($image_path, $name);
						
					if(!file_exists(BASE_PATH . '/uploads/cache_pins/' . $image_path) || !is_dir(BASE_PATH . '/uploads/cache_pins/' . $image_path)) {
						@mkdir(BASE_PATH . '/uploads/cache_pins/' . $image_path, 0777, true);
					}
						
					// 					Helper_Images::copyFromUrl($image, BASE_PATH . '/uploads/cache_pins/' . $image_path . $name);
						
					@copy($image, BASE_PATH . '/uploads/cache_pins/' . $image_path . $name);
						
					ini_set('user_agent', $user_agent);
						
					//if ( self::uploatToServer(BASE_PATH . '/uploads/cache_pins/' . $image_path . $name, $image_path . $name) ) {
                    if ( self::uploatToServer(BASE_PATH . '/uploads/cache_pins/' . $image_path . $name, $image_path .$name) ) {                                        
			
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
									$thumb_a = $model_images->resize('/cache_pins/' . $image_path . $name, $width, $height, true);
								} else if($width && !$height) {
									$thumb_a = $model_images->resizeWidth('/cache_pins/' . $image_path . $name, $width);
								} else if($height && !$width) {
									$thumb_a = $model_images->resizeHeight('/cache_pins/' . $image_path . $name, $height);
								}
								if($prefix == '_B') {
									$temp_width = $model_images->getSizes('width');
									$temp_height = $model_images->getSizes('height');
								}
								$thumb_a1 = explode('/uploads/', $thumb_a);
								if($thumb_a1 && isset($thumb_a1[1])) {
									if( !self::uploatToServer(BASE_PATH . '/uploads/' . $thumb_a1[1], $image_path . $name_pref )) {
										return false;
									}
								}
							}
						}
			
						$model_images->deleteImages('/cache_pins/' . $image_path . $name);
						if($temp_width) {
							return array(
									'store' 	=> 'amazons3',
									'image' => $image_path . $name,
									'width'	=> $temp_width,
									'height' => $temp_height
							);
						} else {
							return false;
						}
					} else {
						return false;
					}
				} else {
					return false;
				}
			}
			
		} catch (JO_Exception $e) {
			return false;
		}
		return false;
	}
	
	private static function uploatToServer($image, $image_server) {
		$s3 = new JO_Api_Amazon(JO_Registry::get('awsAccessKey'), JO_Registry::get('awsSecretKey'));
		$s3->putBucket(JO_Registry::get('bucklet'), JO_Api_Amazon::ACL_PUBLIC_READ);
		if($s3->getBucketLogging(JO_Registry::get('bucklet'))) {
			if ( $s3->putObjectFile($image, JO_Registry::get('bucklet'), $image_server, JO_Api_Amazon::ACL_PUBLIC_READ, array(), JO_File_Ext::getMimeFromFile($image)) ) {
				return true;
			} else {
				return false;
			}
		}
		return false;
	}
	
	
	/////////// user avatar
	
	public static function uploadUserAvatar($avatar, $user_id = 0) {
		try {
			
			$added_date = time();
			if( is_array($user_info = Model_Users::getUser($user_id)) ) {
				$added_date = $user_info['date_added'];
			}
				
			$date_added = WM_Date::format($added_date, 'yy-mm-dd H:i:s');
			
			$s3 = new JO_Api_Amazon(JO_Registry::get('awsAccessKey'), JO_Registry::get('awsSecretKey'));
			$s3->putBucket(JO_Registry::get('bucklet'), JO_Api_Amazon::ACL_PUBLIC_READ);
				
			if($s3->getBucketLogging(JO_Registry::get('bucklet'))) {
				if( ( $imageinfo = @getimagesize($avatar) ) !== false ) {
						
					$ext = strtolower(strrchr($avatar,"."));
						
					$name = $user_id . $ext;
						
					$image_path = 'avatars/' . WM_Date::format($date_added, 'yy/mm/');
			
					if(!file_exists(BASE_PATH . '/uploads/cache_avatars/' . $image_path) || !is_dir(BASE_PATH . '/uploads/cache_avatars/' . $image_path)) {
						@mkdir(BASE_PATH . '/uploads/cache_avatars/' . $image_path, 0777, true);
					}
					@copy($avatar, BASE_PATH . '/uploads/cache_avatars/' . $image_path . $name);
						
					if ( self::uploatToServer(BASE_PATH . '/uploads/cache_avatars/' . $image_path . $name, $image_path . $name) ) {
			
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
									$thumb_a = $model_images->resize('/cache_avatars/' . $image_path . $name, $width, $height, true);
								} else if($width && !$height) {
									$thumb_a = $model_images->resizeWidth('/cache_avatars/' . $image_path . $name, $width);
								} else if($height && !$width) {
									$thumb_a = $model_images->resizeHeight('/cache_avatars/' . $image_path . $name, $height);
								}
								if($prefix == '_B') {
									$temp_width = $model_images->getSizes('width');
									$temp_height = $model_images->getSizes('height');
								}
								$thumb_a1 = explode('/uploads/', $thumb_a);
								if($thumb_a1 && isset($thumb_a1[1])) {
									if( !self::uploatToServer(BASE_PATH . '/uploads/' . $thumb_a1[1], $image_path . $name_pref )) {
			
									}
								}
							}
						}
			
						$model_images->deleteImages('/cache_avatars/' . $image_path . $name);
						if($temp_width) {
							return array(
									'store' 	=> 'amazons3',
									'image' => $image_path . $name,
									'width'	=> $temp_width,
									'height' => $temp_height
							);
						} else {
							return false;
						}
					} else {
						return false;
					}
				} else {
					return false;
				}
			}
			
		} catch (JO_Exception $e) {
			return false;
		}
		return false;
	}
        
        
	
	public static function deleteUserImage($user_info) {
		try {
			$$user_info['avatar'] = $user_info['avatar'];
			$ext = strtolower(strrchr($user_info['avatar'],"."));
			$thumbs = array( $user_info['avatar'] );
			$sizes = self::userThumbSizes();
			if($sizes) {
				foreach($sizes AS $size => $key) {
					$thumbs[] = preg_replace('/'.$ext.'$/i',$key.$ext,$user_info['avatar']);
				}
			}
			foreach($thumbs AS $thumb) {
				self::deleteFromServer($thumb);
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
		
		$img = 'http://' . JO_Registry::get('bucklet') . '.' . trim(JO_Registry::get('awsDomain'),'.') . '/' . $img;
		//$img = 'http://images.amatteur.com/' . $img;
		//error_log("Buscamos: ".$img);
		if( ( $img_size = @getimagesize($img) ) !== false ) {
			//error_log("Encontramos imagen");
			return array(
				'image' => $img,
				'original' => 'http://' . JO_Registry::get('bucklet') . '.' . trim(JO_Registry::get('awsDomain'),'.') . '/' . $user['avatar'],
				//'original' => 'http://images.amatteur.com/' . $user['avatar'],
				'width' => $img_size[0],
				'height' => $img_size[1],
				'mime' => $img_size['mime']
			);
		}
		return false;
	}
	
        
	/////////// event image
	
	public static function uploadEventImage($image, $title = '', $user_id = 0) {
		
		try {
			if($title && mb_strlen($title, 'utf-8') > 60) {
				$title = JO_Utf8::splitText($title, 60, '');
			}
			
			$user_agent = ini_get('user_agent');
			ini_set('user_agent', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.9) Gecko/20071025 Firefox/2.0.0.9');
			
			$date_added = WM_Date::format(time(), 'yy-mm-dd H:i:s');
		
			$s3 = new JO_Api_Amazon(JO_Registry::get('awsAccessKey'), JO_Registry::get('awsSecretKey'));
			$s3->putBucket(JO_Registry::get('bucklet'), JO_Api_Amazon::ACL_PUBLIC_READ);
				
			if($s3->getBucketLogging(JO_Registry::get('bucklet'))) {
				if( ( $imageinfo = getimagesize($image) ) !== false ) {
                                    
                                        $pos = strpos($image, "?");

                                        // Nótese el uso de ===. Puesto que == simple no funcionará como se espera
                                        // porque la posición de 'a' está en el 1° (primer) caracter.
                                        if ($pos === false) 
                                        {
                                            // no tiene ? en el nombre de la imagen
                                        }                            
                                        else
                                        {
                                            $pieces = explode("?", $image);
                                            $image = $pieces[0];    
                                        }

					$ext = strtolower(strrchr($image,"."));
					if(!$ext) {
						$mime_ext = explode('/', $imageinfo['mime']);
						if(isset($mime_ext[1])) {
							$ext = '.' . $mime_ext[1];
						}
					}
						
					if( $title ) {
						$name = self::translateImage($title) . '_' . $user_id . $ext;
					} else {
						$name = md5($image) . '_' . $user_id . $ext;
					}
						
					$image_path = 'events/' . WM_Date::format($date_added, 'yy/mm/');
					//					$name = self::rename_if_exists_amazon($image_path, $name);
						
					if(!file_exists(BASE_PATH . '/uploads/cache_events/' . $image_path) || !is_dir(BASE_PATH . '/uploads/cache_events/' . $image_path)) {
						@mkdir(BASE_PATH . '/uploads/cache_events/' . $image_path, 0777, true);
					}
						
					// 					Helper_Images::copyFromUrl($image, BASE_PATH . '/uploads/cache_events/' . $image_path . $name);
						
					@copy($image, BASE_PATH . '/uploads/cache_events/' . $image_path . $name);
						
					ini_set('user_agent', $user_agent);
						
					//if ( self::uploatToServer(BASE_PATH . '/uploads/cache_events/' . $image_path . $name, $image_path . $name) ) {
                                        if ( self::uploatToServer(BASE_PATH . '/uploads/cache_events/' . $image_path . $name, $image_path .$name) ) {                                        
			
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
									$thumb_a = $model_images->resize('/cache_events/' . $image_path . $name, $width, $height, true);
								} else if($width && !$height) {
									$thumb_a = $model_images->resizeWidth('/cache_events/' . $image_path . $name, $width);
								} else if($height && !$width) {
									$thumb_a = $model_images->resizeHeight('/cache_events/' . $image_path . $name, $height);
								}
								if($prefix == '_B') {
									$temp_width = $model_images->getSizes('width');
									$temp_height = $model_images->getSizes('height');
								}
								$thumb_a1 = explode('/uploads/', $thumb_a);
								if($thumb_a1 && isset($thumb_a1[1])) {
									if( !self::uploatToServer(BASE_PATH . '/uploads/' . $thumb_a1[1], $image_path . $name_pref )) {
                error_log("error al subir imagen al servidor");
										return false;
									}
								}
							}
						}
			
						$model_images->deleteImages('/cache_events/' . $image_path . $name);
						if($temp_width) {
							return array(
									'store' => 'amazons3',
									'image' => $image_path . $name,
									'width'	=> $temp_width,
									'height' => $temp_height
							);
						} else {
							return false;
						}
					} else {
						return false;
					}
				} else {
					return false;
				}
			}
			
		} catch (JO_Exception $e) {
			return false;
		}
		return false;
	}        
        
	public static function uploadEventImage1($avatar, $user_id = 0) {
		try {
			
			$added_date = time();
			if( is_array($user_info = Model_Users::getUser($user_id)) ) {
				$added_date = $user_info['date_added'];
			}
				
			$date_added = WM_Date::format($added_date, 'yy-mm-dd H:i:s');
			
			$s3 = new JO_Api_Amazon(JO_Registry::get('awsAccessKey'), JO_Registry::get('awsSecretKey'));
			$s3->putBucket(JO_Registry::get('bucklet'), JO_Api_Amazon::ACL_PUBLIC_READ);
				
			if($s3->getBucketLogging(JO_Registry::get('bucklet'))) {
				if( ( $imageinfo = @getimagesize($avatar) ) !== false ) {
						
					$ext = strtolower(strrchr($avatar,"."));
						
					$name = $user_id . $ext;
						
					$image_path = 'events/' . WM_Date::format($date_added, 'yy/mm/');
			
					if(!file_exists(BASE_PATH . '/uploads/cache_events/' . $image_path) || !is_dir(BASE_PATH . '/uploads/cache_events/' . $image_path)) {
						@mkdir(BASE_PATH . '/uploads/cache_events/' . $image_path, 0777, true);
					}
					@copy($avatar, BASE_PATH . '/uploads/cache_events/' . $image_path . $name);
						
					if ( self::uploatToServer(BASE_PATH . '/uploads/cache_events/' . $image_path . $name, $image_path . $name) ) {
			
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
									$thumb_a = $model_images->resize('/cache_events/' . $image_path . $name, $width, $height, true);
								} else if($width && !$height) {
									$thumb_a = $model_images->resizeWidth('/cache_events/' . $image_path . $name, $width);
								} else if($height && !$width) {
									$thumb_a = $model_images->resizeHeight('/cache_events/' . $image_path . $name, $height);
								}
								if($prefix == '_B') {
									$temp_width = $model_images->getSizes('width');
									$temp_height = $model_images->getSizes('height');
								}
								$thumb_a1 = explode('/uploads/', $thumb_a);
								if($thumb_a1 && isset($thumb_a1[1])) {
									if( !self::uploatToServer(BASE_PATH . '/uploads/' . $thumb_a1[1], $image_path . $name_pref )) {
			
									}
								}
							}
						}
			
						$model_images->deleteImages('/cache_events/' . $image_path . $name);
						if($temp_width) {
							return array(
									'store' 	=> 'amazons3',
									'image' => $image_path . $name,
									'width'	=> $temp_width,
									'height' => $temp_height
							);
						} else {
							return false;
						}
					} else {
						return false;
					}
				} else {
					return false;
				}
			}
			
		} catch (JO_Exception $e) {
			return false;
		}
		return false;
	}
        
        
	
	public static function deleteEventImage($user_info) {
		try {
			$$user_info['avatar'] = $user_info['avatar'];
			$ext = strtolower(strrchr($user_info['avatar'],"."));
			$thumbs = array( $user_info['avatar'] );
			$sizes = self::userThumbSizes();
			if($sizes) {
				foreach($sizes AS $size => $key) {
					$thumbs[] = preg_replace('/'.$ext.'$/i',$key.$ext,$user_info['avatar']);
				}
			}
			foreach($thumbs AS $thumb) {
				self::deleteFromServer($thumb);
			}
		} catch (JO_Exception $e) {
		}
	}
	
	public static function getEventImage($user, $prefix = null) {
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
		
		$img = 'http://' . JO_Registry::get('bucklet') . '.' . trim(JO_Registry::get('awsDomain'),'.') . '/' . $img;
		//$img = 'http://images.amatteur.com/' . $img;
		//error_log("Buscamos: ".$img);
		if( ( $img_size = @getimagesize($img) ) !== false ) {
			//error_log("Encontramos imagen");
			return array(
				'image' => $img,
				//'original' => 'http://' . JO_Registry::get('bucklet') . '.' . trim(JO_Registry::get('awsDomain'),'.') . '/' . $user['avatar'],
				'original' => 'http://images.amatteur.com/' . $user['avatar'],
				'width' => $img_size[0],
				'height' => $img_size[1],
				'mime' => $img_size['mime']
			);
		}
		return false;
	}        
        
        
	private function rename_if_exists_amazon($s3, $dir, $filename) {
	    $ext = strtolower(strrchr($filename, '.'));
	    $prefix = substr($filename, 0, -strlen($ext));
	    $i = 0;
	    while($s3->getObject(JO_Registry::get('bucklet'), $dir . $filename)) { // If file exists, add a number to it.
	        $filename = $prefix . '[' .++$i . ']' . $ext;
	    }
	    return $filename;
	}

}

?>