<?php
include_once 'includes/start.inc.php';
checkLoggedIn();

include_once 'includes/template.inc.php';
template('Change API Key', 'apikeyBody');

function apikeyBody()
{
	global $eol, $mysqli;
	$userid = $_SESSION['userid'];
	$oldpwd = $_POST['oldpassword'];
	$newpwd = $_POST['newpassword'];
	$confpwd = $_POST['confirmpassword'];
	
	$query = $mysqli->prepare('SELECT userID from users WHERE userid=? AND passhash=UNHEX(?)');
	if (!$query)
	{
		echo 'error: ', $mysqli->error, $eol;
		exit;
	}

	$query->bind_param('ss', $userid, $passhash);
	$passhash = sha1($oldpwd);

	$result = $query->execute();
	if (!$result)
	{
		echo 'error: ', $query->error, $eol;
		exit;
	}

	$query->bind_result($useridb);
	$query->fetch();

	if (!$useridb)
	{
		echo 'Invaild password', $eol;
		
		header('HTTP/1.1 303 See Other');
		header('Location: account.php?error=1');

		exit;
	}

	$query->close();
	
	

	if ($newpwd!=$confpwd)
	{
		echo 'New password and confirm password do not match', $eol;
		
		header('HTTP/1.1 303 See Other');
		header('Location: account.php?error=2');

		exit;
	}
	
	$newpasshash = sha1($newpwd);
	
	$query = $mysqli->prepare('UPDATE users SET password=? WHERE userID = ?');
	if (!$query)
	{
		echo 'error: ', $mysqli->error, $eol;
		exit;
	}
	$query->bind_param('si', $newpasshash,$userid);

	$result = $query->execute();
	if (!$result)
	{
		echo 'error: ', $query->error, $eol;
		exit;
	}
	$query->close();


	header('HTTP/1.1 303 See Other');
	header('Location: account.php');

	echo 'Password changed', $eol;
	echo '<a href="account.php">Return</a> to account info.', $eol;
}
?>
