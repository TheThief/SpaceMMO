<?php
include 'includes/admin.inc.php';
checkIsAdmin();

header('Content-type: text/plain');

$planetid = $_GET['planet'];
$userid = $_GET['userid'];

$query = $mysqli->prepare('INSERT INTO colonies (planetID,userid) VALUES (?, ?)');
if (!$query)
{
	echo 'error: ', $mysqli->error, $eol;
	exit;
}

$query->bind_param('ii', $planetid, $userid);
$result = $query->execute();
if (!$result)
{
	echo 'error: ', $query->error, $eol;
	exit;
}
$query->close();

echo 'Colony \'', $planetid, '\' added successfully', $eol;

$query = $mysqli->prepare('INSERT INTO colonybuildings (planetID,buildingid,level) VALUES (?, 1, 1)');
if (!$query)
{
	echo 'error: ', $mysqli->error, $eol;
	exit;
}

$query->bind_param('i', $planetid);

$result = $query->execute();
if (!$result)
{
	echo 'error: ', $query->error, $eol;
	exit;
}
$query->close();

echo 'Colony Dome at \'', $planetid, '\' added successfully', $eol;
?>
