<?php
include_once 'includes/start.inc.php';
checkLoggedIn();

include_once 'includes/template.inc.php';
template('Change API Key', 'apikeyBody');

function apikeyBody()
{
	global $eol, $mysqli;
	$userid = $_SESSION['userid'];
	$clear = (is_set($_GET["clear"]))?$_GET["clear"]:0;
	if($clear == 1){
		$query = $mysqli->prepare('UPDATE users SET apikey=NULL WHERE userID = ?');
	}else{
		$query = $mysqli->prepare('UPDATE users SET apikey=UNHEX(?) WHERE userID = ?');
	}
	if (!$query)
	{
		echo 'error: ', $mysqli->error, $eol;
		exit;
	}
	if($clear==1){
		$query->bind_param('i', $userid);
	}else{
		$apikey = generateAPIKey();
		$query->bind_param('si', $apikey, $userid);
	}

	$result = $query->execute();
	if (!$result)
	{
		echo 'error: ', $query->error, $eol;
		exit;
	}
	$query->close();


	header('HTTP/1.1 303 See Other');
	header('Location: account.php';

	echo 'API Key updated', $eol;
	echo '<a href="account.php">Return</a> to account info.', $eol;
}
?>
