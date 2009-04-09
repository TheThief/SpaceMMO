<?php
include_once 'includes/admin.inc.php';
checkIsAdmin();

$eol = "\n";
header('Content-type: text/plain');

$query = $mysqli->prepare('UPDATE users SET bisadmin=? WHERE userid=?');
if (!$query)
{
	echo 'error: ', $mysqli->error, $eol;
	exit;
}

$query->bind_param('ii', $bisadmin, $userid);
$bisadmin = true;
$userid = $_GET['userid'];

$result = $query->execute();
if (!$result)
{
	echo 'error: ', $query->error, $eol;
	exit;
}

echo 'User \'', $userid, '\' is now admin', $eol;
?>
