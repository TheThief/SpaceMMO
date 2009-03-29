<?php
include 'includes/admin.inc.php';
checkIsAdmin();

$eol = "\n";
header('Content-type: text/plain');

$query = $mysqli->prepare('INSERT INTO users (username, passhash) VALUES (?, UNHEX(?))');
if (!$query)
{
	echo 'error: ', $mysqli->error, $eol;
	exit;
}

$query->bind_param('ss', $username, $passhash);
$username = $_POST['username'];
$passhash = sha1($_POST['password']);

$result = $query->execute();
if (!$result)
{
	echo 'error: ', $query->error, $eol;
	exit;
}

echo 'User \'', $username, '\' added successfully', $eol;
?>
