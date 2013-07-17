<?php

class PruebaController extends JO_Action {
	
	private $error = false;
	
	public function indexAction() {
                $variable = Model_Prueba::masPrueba();
                echo $variable;
		//$this->forward('prueba', 'index');
                //$this->forward('users', 'profile');            
	}

	public function otroAction() {
                $variable = Model_Prueba::masPrueba();
                echo $variable;
		//$this->forward('prueba', 'index');
                //$this->forward('users', 'profile');            
	}
        
}
?>
