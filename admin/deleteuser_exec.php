<?php
include_once 'includes/admin.inc.php';
checkIsAdmin();

//define('USER_DEBUG',true);
//include_once '../includes/user.inc.php';

header('Content-type: text/plain');

$userid = $_GET['userid'];

$mysqli->autocommit(false);

$query = $mysqli->prepare('SELECT username,bIsAdmin FROM users WHERE userid=?');
$query->bind_param('i',$userid);
$query->execute();
$query->bind_result($username,$bIsAdmin);
$result = $query->fetch();
$query->close();
if (!$result)
{
	echo 'Error: No such user', $eol;
	exit;
}
else if ($bIsAdmin)
{
	echo 'Error: Admins are protected from being deleted, demote them first', $eol;
	exit;
}

$query = $mysqli->prepare('DELETE fleets,fleetships FROM fleets LEFT JOIN fleetships USING (fleetid) WHERE userid = ?');
$query->bind_param('i',$userid);
$query->execute();
$query->close();
$query = $mysqli->prepare('DELETE shipbuildqueue FROM colonies LEFT JOIN shipbuildqueue USING (planetid) WHERE userid = ?');
$query->bind_param('i',$userid);
$query->execute();
$query->close();
$query = $mysqli->prepare('DELETE colonies,colonybuildings FROM colonies LEFT JOIN colonybuildings USING (planetid) WHERE userid = ?');
$query->bind_param('i',$userid);
$query->execute();
$query->close();
$query = $mysqli->prepare('DELETE shipdesigns FROM shipdesigns WHERE userid = ?');
$query->bind_param('i',$userid);
$query->execute();
$query->close();
$query = $mysqli->prepare('DELETE FROM users WHERE userid = ?');
$query->bind_param('i',$userid);
$query->execute();
$query->close();

$mysqli->commit();

echo 'User \'', $username, '\' removed', $eol;
?>
