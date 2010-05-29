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

include_once("db.inc.php");
include_once("functions.inc.php");

$eol = "\n";

register_shutdown_function("cleanUp");
?>
