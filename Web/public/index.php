<?php declare(strict_types=1);
$_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'].'/../..';
require $_SERVER['DOCUMENT_ROOT']."/bootstrap.php";
if(isset($_GET['lang'])){
    (new Web\Entities\Localization())->set_language($_GET['lang']);
    header("Location: /");
    die;
}
$GLOBALS['language'] = (new Web\Entities\Localization())->get_language($_COOKIE['zhabbler_language']);
$GLOBALS['language']['info'] = (new Web\Entities\Localization())->get_language_info($_COOKIE['zhabbler_language']);
if(isset($_COOKIE['zhabbler_session']))
    (new Web\Models\Sessions())->check_session();
require $_SERVER['DOCUMENT_ROOT']."/Web/routes.php";