<?php

class Model_Mails {
	
	public static function getMails($data = array()) {
		$db = JO_Db::getDefaultAdapter();
    
			
		$query = $db
					->select()
					->from(array('m' => 'users_mails'))
					->joinLeft(array('u' => 'users'), 'm.from_user_id = u.user_id', array('fullname' => "CONCAT(firstname,' ', lastname)",'u.username','u.avatar','u.store'))
					->joinLeft(array('t' => 'users_mails_to'), 'm.mail_id = t.mail_id','t.read_mail')
					->where('t.user_id = ?', (string)JO_Session::get('user[user_id]'))
					->order('date_mail DESC');
		
		return $db->fetchAll($query);
	}
	
	public static function getTotalMails($data = array()) {
		
		$db = JO_Db::getDefaultAdapter();
        
			$query = $db
					->select()
					->from(array('m' => 'users_mails'), 'COUNT(m.mail_id)')
					->joinLeft(array('t' => 'users_mails_to'), 'm.mail_id = t.mail_id')
					->where('t.user_id = ? AND t.read_mail=0', (string)JO_Session::get('user[user_id]'));
		
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
	
	public static function createMail($data) {
		
		
		$db = JO_Db::getDefaultAdapter();
		
		$db->insert('users_mails', array(
			'from_user_id' => isset($data['user_id'])?(string)$data['user_id']:JO_Session::get('user[user_id]'),
			'date_mail' => new JO_Db_Expr('NOW()'),
			'parent_mail_id' => isset($data['parent_mail_id'])?(string)$data['parent_mail_id']:0
		));
		
		$mail_id = $db->lastInsertId();
		
		if(!$mail_id) {
			return false;
		}
		
		if(isset($data['toUsers'])) {
			foreach($data['toUsers'] AS $fr) {
				$db->insert('users_mails_to', array(
					'user_id' => $fr,
					'mail_id' => $mail_id
				));
			}
		}
		return array(
			'status' => "OK"
		);
		
	}
	
	public static function updateMail($data) {
		
		
		$db = JO_Db::getDefaultAdapter();
		
		$db->update('users_mails_to', array(
			'read_mail' => $data['read_mail']),
			array('mail_id = ?' => $data['mail_id'],'user_id = ?' => (string)JO_Session::get('user[user_id]')));
		
		$totalmails=self::getTotalMails(array('user_id' => (string)JO_Session::get('user[user_id]')));
		
		return array(
			'status' => "OK",
			'unread' => $totalmails
		);
		
	}
}

?>