<?php
include 'includes/admin.inc.php';
checkIsAdmin();

header('Content-type: text/plain');

$planetid = $_GET['planet'];
$userid = $_GET['userid'];

$mysqli->autocommit(false)

$query = $mysqli->prepare('INSERT INTO colonies (planetID,userid) VALUES (?, ?)');
$query->bind_param('ii', $planetid, $userid);
$result = $query->execute();
$query->close();

echo 'Colony \'', $planetid, '\' added successfully', $eol;

$query = $mysqli->prepare('INSERT INTO colonybuildings (planetID,buildingid,level) VALUES (?, 1, 1)');
$query->bind_param('i', $planetid);
$result = $query->execute();
$query->close();

echo 'Colony Dome at \'', $planetid, '\' added successfully', $eol;

$query = $mysqli->prepare('INSERT INTO fleets (planetID,userid,orderid) VALUES (?, ?, 0)');
$query->bind_param('ii', $planetid, $userid);
$result = $query->execute();
$query->close();

echo 'Unassigned fleet at \'', $planetid, '\' added successfully', $eol;

$mysqli->commit();

?>
