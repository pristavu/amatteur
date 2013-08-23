<?php 

// Define application path
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/application/'));

// Define base path
defined('BASE_PATH')
    || define('BASE_PATH', realpath(dirname(__FILE__)));
    
// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../JO/v.0.9b/'),
    realpath(APPLICATION_PATH . '/library/'),
    get_include_path(),
)));

require_once 'JO/Application.php';

// Create application, bootstrap, and run
$application = new JO_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/config/application.ini',
    isset($argv) ? $argv : null
); 

// Set Routers links
$configs_files = glob(APPLICATION_PATH . '/config/config_*.ini');
if($configs_files) {
	foreach($configs_files AS $file) {
		$config = new JO_Config_Ini($file);
		$application->setOptions($config->toArray());
		JO_Registry::set(basename($file, '.ini'), $config->toArray());
	}
}

// Set Routers links
$routers_files = glob(APPLICATION_PATH . '/config/routers/*.ini');
if($routers_files) {
	foreach($routers_files AS $file) {
		$config = new JO_Config_Ini($file, null, false, true);
		$application->setOptions($config->toArray());
		JO_Registry::set('routers_'.basename($file, '.ini'), $config->toArray());
	}
}

//para las APP's
if (isset($_POST['token']) && $_POST['token'] == md5($_POST['userid']))
{
    $_SESSION['token'] = $_POST['token'];
    JO_Session::set('token', $_POST['token']);
    $_SESSION['userid'] = $_POST['userid'];
    JO_Session::set('userid', $_POST['userid']);
    
}

//dispatch application
$application->dispatch();



// error handler function
function myErrorHandler($errno, $errstr, $errfile, $errline)
{
    if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting
        return;
    }

    switch ($errno) {
    case E_USER_ERROR:
        echo "<b>My ERROR</b> [$errno] $errstr<br />\n";
        echo "  Fatal error on line $errline in file $errfile";
        echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
        echo "Aborting...<br />\n";
        exit(1);
        break;

    case E_USER_WARNING:
        echo "<b>My WARNING</b> [$errno] $errstr<br />\n";
        break;

    case E_USER_NOTICE:
        echo "<b>My NOTICE</b> [$errno] $errstr<br />\n";
        break;

    default:
        echo "Unknown error type: [$errno] $errstr<br />\n";
        break;
    }

    /* Don't execute PHP internal error handler */
    return true;
}