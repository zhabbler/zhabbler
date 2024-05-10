<?php declare(strict_types=1);
ini_set('display_startup_errors',1); 
error_reporting(E_ALL);
ini_set('display_errors', 1);
require $_SERVER['DOCUMENT_ROOT']."/Web/Entities/Localization.php";
function autoload(): void
{
    $files = glob($_SERVER['DOCUMENT_ROOT']."/Utilities/*.php");
    foreach ($files as $file) {
        require $file;
    }
    $files = glob($_SERVER['DOCUMENT_ROOT']."/Web/Models/*.php");
    foreach ($files as $file) {
        require $file;
    }
}

function check_requirements(): void
{
    $errors = array();

    if(!is_dir($_SERVER['DOCUMENT_ROOT']."/vendor"))
        $errors[] = "Composer depencies missing";

    if(!version_compare(PHP_VERSION, "8.0.0", ">="))
        $errors[] = "Incompatible PHP version: " . PHP_VERSION . " (8.0+ required)";

    if(count($errors) > 0)
    {
        require $_SERVER['DOCUMENT_ROOT']."/Web/templates/error.phtml";
        die;
    }
}

check_requirements();
autoload();

require $_SERVER['DOCUMENT_ROOT']."/vendor/autoload.php";
$GLOBALS['config'] = Nette\Neon\Neon::decodeFile($_SERVER['DOCUMENT_ROOT'].'/config.neon');

if($GLOBALS['config']['application']['debug'] == 1){
    Tracy\Debugger::enable();
    Tracy\Debugger::$logDirectory = __DIR__ . '/log';
}else{
    error_reporting(0);
    ini_set('display_errors', 0);
}

if(!isset($_COOKIE['zhabbler_language'])){
    (new Web\Entities\Localization())->set_language($GLOBALS['config']['application']['default_language']);
    header("Location: ".$_SERVER['REQUEST_URI']);
    die;
}

$GLOBALS['db'] = Utilities\Database::DatabaseConnection();
define("BASE_URL", $GLOBALS['config']['application']['base_url']);
define("ENCRYPTION_KEY", $GLOBALS['config']['application']['encryption_key']);
date_default_timezone_set($GLOBALS['config']['application']['default_time_zone']);
$actual_link = (empty($_SERVER['HTTPS']) ? 'http' : 'https')."://$_SERVER[HTTP_HOST]/";
if($actual_link != BASE_URL){
    header("HTTP/1.0 404 Not Found");
    die;
}