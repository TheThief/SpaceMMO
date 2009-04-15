<?
if (!defined('DEBUG')) define("DEBUG",false);
ini_set('session.hash_function', 1);
ini_set('session.hash_bits_per_character',4);
ob_start();
session_start();
include_once("db.inc.php");
include_once("functions.inc.php");

$eol = "\n";

register_shutdown_function("cleanUp");
?>
