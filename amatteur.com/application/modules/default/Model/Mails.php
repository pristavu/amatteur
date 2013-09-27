<?php

class Model_Mails {
	
	public static function getMails($data = array()) {
		$db = JO_Db::getDefaultAdapter();
    
			
		$query = $db
					->select()
					->from(array('m' => 'users_mails'))
					->joinLeft(array('u' => 'users'), 'm.from_user_id = u.user_id', array('fullname' => "CONCAT(firstname,' ', lastname)",'u.username','u.avatar','u.store'))
					->where('to_user_id = ?', $data['user_id'])
					->order('date_mail DESC');
		
		return $db->fetchAll($query);
	}
	
	public static function getTotalMails($data = array()) {
		
		$db = JO_Db::getDefaultAdapter();
        
			$query = $db
					->select()
					->from(array('m' => 'users_mails'), 'COUNT(m.mail_id)')
					->where('to_user_id = ? AND read_mail=0', $data['user_id']);
		
		return $db->fetchOne($query);
	}
	
	public static function getMail($mail_id) {
		$db = JO_Db::getDefaultAdapter();
        
		$query = $db
					->select()
					->from('users_mails')
					->where('mail_id = ?', $mail_id)
					->limit(1);
		
		return $db->fetchRow($query);
		
	}
}

?>