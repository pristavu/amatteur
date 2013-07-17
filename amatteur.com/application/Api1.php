<?php
/*
class Api1 //extends JO_Action JO_Model {
{

    public function getUser($request) 
    {
        $resultado = "";
        if( is_array($user_data = Model_Users::checkLogin($request->getPost('email'), $request->getPost('password'))) ) {
                    JO_Session::set(array('user' => $user_data));
                    $resultado = "ok";
error_log("ok", 0);                    
                    return $user_data;
            } else {
                    $resultado = $this->translate('E-mail address and password do not match');
error_log("hostion", 0);                    
                    return false;
            }

error_log("hace algo?", 0);
        $encoded = json_encode($resultado);
        $encoded->setHeader('content-type: application/json; charset=utf-8');
        return $encoded;
    }
}
//}
    /*
            $resultado = "";
        if( is_array($user_data = Model_Users::checkLogin($request->getPost('email'), $request->getPost('password'))) ) {
                    JO_Session::set(array('user' => $user_data));
                    $resultado = "ok";
error_log("ok", 0);                    
                    return $user_data;
            } else {
                    $resultado = $this->translate('E-mail address and password do not match');
error_log("hostion", 0);                    
                    return false;
            }

error_log("hace algo?", 0);
        $encoded = json_encode($resultado);
        $encoded->setHeader('content-type: application/json; charset=utf-8');
        echo $encoded;    
*/

    function doAuthenticate() 
    {
        /*
        if (isset($_SERVER['PHP_AUTH_USER']) and isset($_SERVER['PHP_AUTH_PW'])) {


            if ($_SERVER['PHP_AUTH_USER'] == "codezone4" && $_SERVER['PHP_AUTH_PW'] == "123")
                return true;
            else
                return false;
        }
        */
        if (isset($_REQUEST['usuario']) and isset($_REQUEST['pass'])) {


            if ($_REQUEST['usuario'] == "codezone4" && $_REQUEST['pass'] == "202cb962ac59075b964b07152d234b70")
                return true;
            else
                echo " Error de autenticacion usuario " . $_REQUEST['usuario'] . " pass " . $_REQUEST['pass'];
        }

    }

    if (!doAuthenticate())
    {
        echo "mal";
        return "Invalid username or password";
    }
    else
    {
        echo "bien";
        Model_Users::checkLogin($_REQUEST['usuario'], $_REQUEST['pass']);
        /*
        echo "<pre>";
        echo 'in upload.php<br/>';
        print_r($_FILES);
        print_r($_REQUEST);
        move_uploaded_file($_FILES["file"]["tmp_name"], "uploads/" . $_REQUEST["fileName"]);
         * */
        return "fichero subido correctamente";
    }
    
?>
