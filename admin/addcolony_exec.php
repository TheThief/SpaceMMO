<?php
include_once 'includes/admin.inc.php';
checkIsAdmin();

define("DEBUG",true);
include_once '../includes/colony.inc.php';

header('Content-type: text/plain');

$planetid = $_GET['planet'];
$userid = $_GET['userid'];

$mysqli->autocommit(false)

colonise($planetid, $userid);

$mysqli->commit();

echo 'Colony \'', $planetid, '\' added successfully', $eol;

?>
