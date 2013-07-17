<?php

class Model_Settings {

	public static function deleteGroup($group) {
		$db = JO_Db::getDefaultAdapter();
		return $db->delete('system', array('`group` = ?' => $group));
	} 

	public static function updateAll($data) {
		$db = JO_Db::getDefaultAdapter();
		if(is_array($data)) {
			foreach($data AS $group => $value) {
				$db->delete('system', array('`group` = ?' => $group)); 
				if(is_array($value)) {
					foreach($value AS $key => $val) {
						$serialize = false;
						if(is_array($val)) {
							$serialize = true;
							$val = serialize($val);
						} 
						$db->insert('system', array(
							'group' => $group,
							'key' => $key,
							'value' => $val,
							'system' => (int) ($group == 'config'),
							'serialize' => $serialize
						));
					}
				}
			}
		}
	} 
	
	public static function getSettingsPairs($data = array()) {
		$db = JO_Db::getDefaultAdapter();
		$query = $db->select()
					->from('system');
		
		if(isset($data['filter_group']) && !is_null($data['filter_group'])) {
			$query->where('`group` = ?', $data['filter_group']);
		}
		
		if(isset($data['filter_domain_id']) && !is_null($data['filter_domain_id'])) {
			$query->where('`domain_id` = ?', $data['filter_domain_id']);
		}
		
		$response = array();
		$results = $db->fetchAll($query);
		if($results) {
			foreach($results AS $result) {
				if($result['serialize']) {
					$response[$result['key']] = self::mb_unserialize($result['value']);
				} else {
					$response[$result['key']] = $result['value'];
				}
			}
		}
		
		return $response;
	}
  	
	public function mb_unserialize($serial_str) {
		$out = preg_replace('!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'", $serial_str );
		return unserialize($out);
	} 
	
}

?>