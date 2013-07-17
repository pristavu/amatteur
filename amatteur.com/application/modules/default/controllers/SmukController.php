<?php

class SmukController extends JO_Action {

	public function smukAction() {
		
		exit;
		
		set_time_limit(0);
		$urls = array(
// 			'Africa' => 'http://www.php.net/manual/en/timezones.africa.php',
// 			'America' => 'http://www.php.net/manual/en/timezones.america.php',
// 			'Antarctica' => 'http://www.php.net/manual/en/timezones.antarctica.php',
// 			'Arctic' => 'http://www.php.net/manual/en/timezones.arctic.php',
// 			'Asia' => 'http://www.php.net/manual/en/timezones.asia.php',
// 			'Atlantic' => 'http://www.php.net/manual/en/timezones.atlantic.php',
// 			'Australia' => 'http://www.php.net/manual/en/timezones.australia.php',
// 			'Europe' => 'http://www.php.net/manual/en/timezones.europe.php',
// 			'Indian' => 'http://www.php.net/manual/en/timezones.indian.php',
// 			'Pacific' => 'http://www.php.net/manual/en/timezones.pacific.php',
			'Others' => 'http://www.php.net/manual/en/timezones.others.php'
		);
		
		$string = 'private static $timezones = array(' . "\n";
		foreach($urls AS $key => $url) {
			$html = @file_get_contents($url);
			$dom = new JO_Html_Dom();
			$dom->load($html);

			$string .= "\t".'"'.$key.'" => array(' . "\n";
			
			$hrefs = $dom->find('table tr td');
			foreach($hrefs AS $href) {
				$string .= "\t\t".'"'.$href->innertext.'",'."\n";
			}
			
			$string .= "),\n";
		}
		$string .= ");\n";
		
		echo($string);
		
		
		/*$html = @file_get_contents('http://www.php.net/manual/en/timezones.africa.php');
		$dom = new JO_Html_Dom();
		$dom->load($html);
		 
		$hrefs = $dom->find('table tr td');
		var_dump(count($hrefs));
		foreach($hrefs AS $href) {
			var_dump($href->innertext);
		}*/
		exit;
	}
	
	public function indexAction() {
		
		ini_set('memory_limit','4200M');
		
		$this->noViewRenderer(true);
		ignore_user_abort(true);
		
		for($i=1; $i<5; $i++) {
			
			$html = @file_get_contents('http://pinterest.com/?page=' . $i);
			
			if( $html ) {
		
			
				$dom = new JO_Html_Dom();
				$dom->load($html);
		    	
		    	$hrefs = $dom->find('.PinImage');
				
		    	if($hrefs) {
			    	foreach($hrefs AS $href) {
			    		$price = 0;
			    		
			    		$url = JO_Url_Relativetoabsolute::toAbsolute('http://pinterest.com/?page=' . $i, $href->href);
			    		
						$html2 = @file_get_contents($url);
						
						if( $html2 ) {
							$dom = new JO_Html_Dom();
			    			$dom->load( $html2 );
			    		
			    			$board = $dom->find('h3.serif a', 0)->innertext;
			    			$image = $dom->find('#pinCloseupImage', 0)->src;
			    			$description = $dom->find('#PinCaption', 0)->innertext;
			    			$description = explode('<', $description);
			    			$description = $description[0];
			    			$from = $dom->find('#PinSource a', 0)->href;
			    			$usernames = $dom->find('#PinnerName a', 0)->innertext;
			    			$avatar = $dom->find('#PinnerImage img', 0)->src;
			    			$username = trim($dom->find('#PinnerName a', 0)->href, '/');
			    			$price_o = $dom->find('.buyable', 0);
							if($price_o) {
	    						$price = $price_o->innertext;
	    					}
	    					
	    					$user_id = Model_Users::getUserByName($username, $usernames, $avatar);
	    					if(!$user_id) {
	    						continue;
	    					}
	    					
	    					WM_Users::initSession($user_id);
	    					
	    					
	    					$board_id = Model_Boards::getBoardId( trim($board) );
	    					
				    		
				    		$price_f = 0;
				    		if(preg_match('/([0-9.]{1,})/',$price,$m)) {
				    			$price_f = $m[1];
				    		}
				    		
				    		$pin_id = Model_Pins::create(array(
				    			'board_id' => $board_id,
				    			'description' => htmlspecialchars($description, ENT_QUOTES, 'utf-8'),
				    			'image' => (string)$image,
				    			'price' => (float)$price,
				    			'from' => urldecode($from),
				    			'public' => '1'
				    		));
				    		
				    		$commm = $dom->find('.PinComments .comment');
				    		if($commm) {
				    			foreach($commm AS $com) {
				    				$avatar = $com->find('.CommenterImage img', 0)->src;
				    				$usernames = $com->find('.CommenterName', 0)->innertext;
					    			$username = trim($com->find('.CommenterName', 0)->href, '/');
					    			$text = explode('<br />', $com->find('.CommenterMeta', 0)->innertext);
					    			$text = isset($text[1]) ? $text[1] : '';
					    			if($text) {
					    				$user_id = Model_Users::getUserByName($username, $usernames, $avatar);
				    					if(!$user_id) {
				    						continue;
				    					}
				    					
				    					WM_Users::initSession($user_id);
				    					$pin_info = Model_Pins::getPin($pin_id);
				    					Model_Pins::addComment(array(
				    						'write_comment' => $text,
				    						'pin_id' => $pin_id
				    					), $pin_info['latest_comments']);
					    			}
			    			
				    			}
				    			
				    			sleep(1);
				    		}
				    		
				    		sleep(1);
				    		
			    			
						}
			    		
			    	}
		    	}
	    	
	    	
	    	
			}
	    	
			
		}
		
		
	}
	
}

?>