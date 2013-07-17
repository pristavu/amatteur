<?php

	class SlidesController extends JO_Action{
		
		function indexAction(){
			$this->noLayout(true);
			$this->noViewRenderer(true);

			$files = scandir("data/slider/");
			$images = array();
			foreach($files as $key => $f){
				if($key > 1){
					$images[mt_rand(000000,999999)]['image'] = JO_Request::getInstance()->getBaseUrl()."/data/slider/".$f;
				}
			}
			
			ksort($images);
			
			echo JO_Json::encode($images);
		}
		
		
		function videoImageAction(){
			$this->noLayout(true);
			$this->noViewRenderer(true);
			$files = scandir("data/videoImage/");
			
			echo JO_Json::encode(array('image'=>JO_Request::getInstance()->getBaseUrl()."data/videoImage/".$files['2']));
		}
		
		
		
		
	}

?>