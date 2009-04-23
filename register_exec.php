<?php
include_once 'includes/start.inc.php';

include_once 'includes/user.inc.php';

function registerCode()
{
	global $mysqli;
	$username = $_POST['username'];
	$password = $_POST['password'];

	if (strlen($username) < 4)
	{
		header('HTTP/1.1 307 Redirect');
		header('Location: register_form.php?error=1');
		exit;
	}
	else if (strlen($username) > 20)
	{
		header('HTTP/1.1 307 Redirect');
		header('Location: register_form.php?error=2');
		exit;
	}
	else if (strlen($password) < 6)
	{
		header('HTTP/1.1 307 Redirect');
		header('Location: register_form.php?error=3');
		exit;
	}
	else if (strpos($username," ") !== false)
	{
		header('HTTP/1.1 307 Redirect');
		header('Location: register_form.php?error=4');
		exit;
	}

	$query = $mysqli->prepare('SELECT userID from users WHERE username=?');
	$query->bind_param('s', $username);
	$result = $query->execute();
	$query->bind_result($userid);
	$result = $query->fetch();
	$query->close();

	if ($userid)
	{
		header('HTTP/1.1 303 See Other');
		header('Location: register_form.php?error=10');
		exit;
	}

	$mysqli->autocommit(false);

	$userid = adduser($username, $password);

	$mysqli->commit();

	$query = $mysqli->prepare('UPDATE users SET phpsessionid=UNHEX(?), lastlogin=NOW() WHERE userID=?');

	session_regenerate_id();
	$_SESSION['userid']=$userid;

	$query->bind_param('si', $sessionid, $userid);
	$sessionid = session_id();

	$result = $query->execute();
}

registerCode();

include_once 'includes/template.inc.php';
template('Register', 'registerBody');

function registerBody()
{
	global $mysqli;
	$username = $_POST['username'];

	echo 'User \'', $username, '\' added successfully.<br>', $eol;
	echo 'Please don\'t forget your username or password, and welcome to the game.<br>', $eol;
	echo '<br>', $eol;
	echo 'You are also now logged in.<br>', $eol;
}
?>
