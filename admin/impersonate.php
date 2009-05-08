<?php
include_once 'includes/admin.inc.php';
checkIsAdmin();

$eol = "\n";
header('Content-type: text/plain');

if (isset($_GET['userid']))
{
	if (!isset($_SESSION['adminuserid']))
	{
		$_SESSION['adminuserid'] = $_SESSION['userid'];
	}
	$_SESSION['userid'] = $_GET['userid'];
}
else
{
	if (isset($_SESSION['adminuserid']))
	{
		$_SESSION['userid'] = $_SESSION['adminuserid'];
		unset($_SESSION['adminuserid']);
	}
	else
	{
		echo 'error: no userid to impersonate specified, and not currently impersonating anyone', $eol;
		exit;
	}
}

echo 'Now impersonating user \'', $userid, '\'', $eol;
?>
