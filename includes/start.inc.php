<?
if (!defined('DEBUG')) define("DEBUG",false);
ini_set('session.hash_function', 1);
ini_set('session.hash_bits_per_character',4);
ob_start();
session_start();
if (!defined('UNI_WIDTH')) define("UNI_WIDTH",201);
if (!defined('UNI_HEIGHT')) define("UNI_HEIGHT",201);
if (!defined('UNI_CENTRE_X')) define("UNI_CENTRE_X",100);
if (!defined('UNI_CENTRE_Y')) define("UNI_CENTRE_Y",100);

include_once("API.php");
$isxhr = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])=='xmlhttprequest');
$isgetapi = (isset($_GET["api"]) && $_GET["api"]=="json");
if(!defined('IS_API_CALL')) define('IS_API_CALL',$isxhr || $isgetapi);
$apiformat = APIFormat::JSON;
if(IS_API_CALL) $api = new API($apiformat);
unset($isgetapi,$isxhr,$apiformat);

include_once("db.inc.php");
include_once("functions.inc.php");
$eol = "\n";

if(!IS_API_CALL){
    include_once ('Template.php');
    $tpl = new Template("/templates/spacemmo");
}

register_shutdown_function("cleanUp");
?>
