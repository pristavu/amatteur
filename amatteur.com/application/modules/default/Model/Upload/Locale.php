<?php

class Model_Upload_Locale extends Model_Upload_Abstract {

	public static function getPinImage($pin, $size = null) {
		$sizes = self::pinThumbSizes();
		$format_size = false;
		if($sizes) {
			foreach($sizes AS $val => $key) {
				if($key == $size) {
					$format_size = $val;
					break;
				}
			}
		}
		if(!$format_size) {
			return false;
		}
		
		$model_images = new Helper_Images();
		
		$sizes = explode('x', $format_size);
		$width = (int)isset($sizes[0])?$sizes[0]:0;
		$height = (int)isset($sizes[1])?$sizes[1]:0;
		
		if($width && $height) {
			$img = $model_images->resize($pin['image'], $width, $height, true);
		} else if($width && !$height) {
			$img = $model_images->resizeWidth($pin['image'], $width);
		} else if($height && !$width) {
			$img = $model_images->resizeHeight($pin['image'], $height);
		}
		
		if( $img ) {
			return array(
					'image' => $img,
					'original' => $model_images->original($pin['image']),
					'width' => $model_images->getSizes('width'),
					'height' => $model_images->getSizes('height'),
					'mime' => JO_File_Ext::getMimeFromFile($img)
			);
		}
		
		return false;

	}
	
	public static function deletePinImage($pin_info) {
		$model_image = new Helper_Images();
		$model_image->deleteImages($pin_info['image'], true);
	}
	
	public static function uploadPin($image, $title = '', $id = 0) {
		try {
			
			if($title && mb_strlen($title, 'utf-8') > 60) {
				$title = JO_Utf8::splitText($title, 60, '');
			}
			
			$date_added = WM_Date::format(time(), 'yy-mm-dd H:i:s');
			$user_agent = ini_get('user_agent');
			ini_set('user_agent', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.9) Gecko/20071025 Firefox/2.0.0.9');
			
			if( ( $imageinfo = getimagesize($image) ) !== false ) {
				$ext = JO_File_Ext::getExtFromMime($imageinfo['mime']);
				$ext = '.'.$ext;
				if( $title ) {
					$name = self::translateImage($title) . '_' . $id . $ext;
				} else {
					$name = md5($image) . '_' . $id . $ext;
				}
				
				$image_path = '/pins/' . WM_Date::format($date_added, 'yy/mm/');
				if(!file_exists( BASE_PATH . '/uploads' . $image_path ) || !is_dir(BASE_PATH . '/uploads' . $image_path)) {
					@mkdir(BASE_PATH . '/uploads' . $image_path, 0777, true);
				}
				
				$name = self::rename_if_exists($image_path, $name);
			
				// 				Helper_Images::copyFromUrl($image, BASE_PATH . '/uploads' . $image_path . $name);
				if(@copy($image, BASE_PATH . '/uploads' . $image_path . $name )) {
			
					ini_set('user_agent', $user_agent);
				
					if( file_exists( BASE_PATH . '/uploads' . $image_path . $name ) ) {
						return array(
								'store' 	=> 'locale',
								'image' => $image_path . $name,
								'width'	=> 0,
								'height' => 0
						);
					} else {
						return false;
					}
				}
			} else {
				return false;
			}
		} catch (JO_Exception $e) {
			return false;
		}
		return false;
	}
	
	private function rename_if_exists($dir, $filename) {
	    $ext = strtolower(strrchr($filename, '.'));
	    $prefix = substr($filename, 0, -strlen($ext));
	    $i = 0;
	    while(file_exists($dir . $filename)) { // If file exists, add a number to it.
	        $filename = $prefix . '[' .++$i . ']' . $ext;
	    }
	    return $filename;
	}
	
	
	/////////// user avatar
	
	public static function uploadUserAvatar($avatar, $user_id = 0) {
		try {
			$added_date = time();
			if( is_array($user_info = Model_Users::getUser($user_id)) ) {
				$added_date = $user_info['date_added'];
			}
			
			$date_added = WM_Date::format($added_date, 'yy-mm-dd H:i:s');
			if( ( $imageinfo = @getimagesize($avatar) ) !== false ) {
				$ext = JO_File_Ext::getExtFromMime($imageinfo['mime']);
				$ext = '.'.$ext;
				$name = $user_id . $ext;
			
				$image_path = '/users/' . WM_Date::format($added_date, 'yy/mm/');
				//$name = self::rename_if_exists($image_path, $name);
			
				if(!file_exists( BASE_PATH . '/uploads' . $image_path ) || !is_dir(BASE_PATH . '/uploads' . $image_path)) {
					@mkdir(BASE_PATH . '/uploads' . $image_path, 0777, true);
				}
			
				if(@copy($avatar, BASE_PATH . '/uploads' . $image_path . $name )) {
					if( file_exists( BASE_PATH . '/uploads' . $image_path . $name ) ) {
						return array(
								'store' 	=> 'locale',
								'image' => $image_path . $name,
								'width'	=> 0,
								'height' => 0
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
		} catch (JO_Exception $e) {
			return false;
		}
		return false;
	}
	
	public static function deleteUserImage($user_info) {
		$model_image = new Helper_Images();
		$model_image->deleteImages($user_info['avatar'], true);
	}

	public static function getUserImage($user, $prefix = null) {
		$sizes = self::userThumbSizes();
		$format_size = false;
		if($sizes) {
			foreach($sizes AS $val => $key) {
				if($key == $prefix) {
					$format_size = $val;
					break;
				}
			}
		}
		if(!$format_size) {
			return false;
		}
		
		$model_images = new Helper_Images();
		
		$sizes = explode('x', $format_size);
		$width = (int)isset($sizes[0])?$sizes[0]:0;
		$height = (int)isset($sizes[1])?$sizes[1]:0;
		
		if($width && $height) {
			$img = $model_images->resize($user['avatar'], $width, $height, true);
		} else if($width && !$height) {
			$img = $model_images->resizeWidth($user['avatar'], $width);
		} else if($height && !$width) {
			$img = $model_images->resizeHeight($user['avatar'], $height);
		}
		
		if( $img ) {
			return array(
					'image' => $img,
					'original' => $model_images->original($user['avatar']),
					'width' => $model_images->getSizes('width'),
					'height' => $model_images->getSizes('height'),
					'mime' => JO_File_Ext::getMimeFromFile($img)
			);
		}
		
		return false;

	}
	
}

?>