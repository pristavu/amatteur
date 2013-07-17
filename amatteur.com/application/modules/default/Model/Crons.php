<?php

class Model_Crons {

	public function stats() {
		$db = JO_Db::getDefaultAdapter();
		
		$db->delete('statistics');
		
		$db->query("INSERT INTO `statistics`(`id`, `total`, `type`) SELECT DATE_FORMAT(`date_added`, '%Y%m'),COUNT(pin_id),1 FROM pins GROUP BY DATE_FORMAT(`date_added`, '%Y%m');");
		$db->query("INSERT INTO `statistics`(`id`, `total`, `type`) SELECT DATE_FORMAT(`date_added`, '%Y%m'),COUNT(user_id),2 FROM users GROUP BY DATE_FORMAT(`date_added`, '%Y%m');");
		$db->query("INSERT INTO `statistics`(`id`, `total`, `type`) SELECT DATE_FORMAT(`date_added`, '%Y%m'),COUNT(board_id),3 FROM boards GROUP BY DATE_FORMAT(`date_added`, '%Y%m');");
		
	}
	
	public static function updateStats() {
		$db = JO_Db::getDefaultAdapter();
		
		$db->delete('users_following_user', array(
			'user_id NOT IN (SELECT user_id FROM users)' => 1
		));
		
		$db->delete('users_following_user', array(
			'following_id NOT IN (SELECT user_id FROM users)' => 1
		));
		
		$db->update('users', array(
			'pins' => new JO_Db_Expr('(SELECT COUNT(DISTINCT pin_id) FROM pins WHERE user_id = users.user_id)'),
			'boards' => new JO_Db_Expr('(SELECT COUNT(DISTINCT board_id) FROM boards WHERE user_id = users.user_id)'),
			'likes' => new JO_Db_Expr('(SELECT COUNT(DISTINCT pin_id) FROM pins_likes WHERE user_id = users.user_id)'),
			'following' => new JO_Db_Expr('(SELECT COUNT(DISTINCT following_id) FROM users_following_user WHERE user_id = users.user_id AND following_id != users.user_id)'),
			'followers' => new JO_Db_Expr('(SELECT COUNT(DISTINCT user_id) FROM users_following_user WHERE following_id = users.user_id AND user_id != users.user_id)')
		));
		
		$db->update('boards', array(
			'pins' => new JO_Db_Expr('(SELECT COUNT(DISTINCT pin_id) FROM pins WHERE board_id = boards.board_id)'),
			'followers' => new JO_Db_Expr('(SELECT COUNT(DISTINCT users_following_id) FROM users_following WHERE board_id = boards.board_id)')
		));
		
		$db->update('pins', array(
			'likes' => new JO_Db_Expr('(SELECT COUNT(DISTINCT user_id) FROM pins_likes WHERE pin_id = pins.pin_id)'),
			'comments' => new JO_Db_Expr('(SELECT COUNT(DISTINCT comment_id) FROM pins_comments WHERE pin_id = pins.pin_id)'),
//			'repins' => new JO_Db_Expr('(SELECT COUNT(DISTINCT pin_id) FROM pins WHERE repin_from = pins.pin_id)')
		));

	}
	
}

?>