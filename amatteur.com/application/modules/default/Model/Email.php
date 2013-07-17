<?php

class Model_Email extends JO_Model {
	
	public static $error;

	public static function send($to, $from, $title, $body = '') {
		
		/*$is_mail_smtp = JO_Registry::forceGet('config_mail_smtp');			
    	$mail = new JO_Mail;
    	if($is_mail_smtp) {
    		$mail->setSMTPParams(JO_Registry::forceGet('config_mail_smtp_host'), JO_Registry::forceGet('config_mail_smtp_port'), JO_Registry::forceGet('config_mail_smtp_user'), JO_Registry::forceGet('config_mail_smtp_password'));
    	}
    	
    	$mail->setFrom( $from );
    	$mail->setReturnPath( $from );
    	$mail->setSubject( $title );
		$mail->setHTML( $body );
    	return (int)$mail->send(array( $to ), ($is_mail_smtp ? 'smtp' : 'mail'));*/
		
		
		$mail = new JO_Mailer_Base();
		if(JO_Registry::forceGet('config_mail_smtp')) {
			$mail->SMTPAuth = true;
			$mail->IsSMTP();
			$mail->Host = JO_Registry::forceGet('config_mail_smtp_host');
			$mail->Port = JO_Registry::forceGet('config_mail_smtp_port');
			$mail->Username = JO_Registry::forceGet('config_mail_smtp_user');
			$mail->Password = JO_Registry::forceGet('config_mail_smtp_password');
		}
		
		$mail->SetFrom($from, '');
		$mail->AddReplyTo($from,"");
		$mail->Subject    = $title;
		
		$mail->AltBody    = self::translate("To view the message, please use an HTML compatible email viewer!"); // optional, comment out and test
		
		$mail->MsgHTML($body, BASE_PATH);
		$mail->AddAddress($to, "");
		
    	$result = $mail->Send();
    	if($result) {
    		return true;
    	} else { 
    		self::$error = $mail->ErrorInfo;
    		return false;
    	}
		
	}
	
}

?>