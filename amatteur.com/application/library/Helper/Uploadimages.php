<?php

class Helper_Uploadimages {
	
	public static function pin($pin, $size = null) {
		return call_user_func(array(Helper_Pin::formatUploadModule($pin['store']), 'getPinImage'), $pin, $size);
	}

	public static function event($event, $size = null) {
		return call_user_func(array(Helper_Events::formatUploadModule($event['store']), 'getEventImage'), $event, $size);
	}
        
        
	public static function avatar($user, $size = null) {
		$image = call_user_func(array(Helper_Pin::formatUploadModule($user['store']), 'getUserImage'), $user, $size);
		if(!$image) {
			$user['avatar'] = JO_Registry::get('no_avatar');
			$image = call_user_func(array(Helper_Pin::formatUploadModule('locale'), 'getUserImage'), $user, $size);
		}
		return $image;
	}

}

?>