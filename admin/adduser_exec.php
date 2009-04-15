<?php
include_once 'includes/admin.inc.php';
checkIsAdmin();

define('USER_DEBUG',true);
include_once '../includes/user.inc.php';

header('Content-type: text/plain');

$username = $_POST['username'];
$password = $_POST['password'];

$mysqli->autocommit(false);

adduser($username, $password);

$mysqli->commit();

echo 'User \'', $username, '\' added successfully', $eol;
?>
