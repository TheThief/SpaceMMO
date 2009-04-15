<?php
if (!defined('COLONY_DEBUG')) define('COLONY_DEBUG',DEBUG);
function colonise($planetid, $userid)
{
	global $mysqli, $eol;

	$query = $mysqli->prepare('INSERT INTO colonies (planetID,userid) VALUES (?, ?)');
	$query->bind_param('ii', $planetid, $userid);
	$query->execute();
	$query->close();

	if (COLONY_DEBUG) echo 'Colony \'', $planetid, '\' added successfully', $eol;

	$query = $mysqli->prepare('INSERT INTO colonybuildings (planetID,buildingid,level) VALUES (?, 1, 1)');
	$query->bind_param('i', $planetid);
	$query->execute();
	$query->close();

	if (COLONY_DEBUG) echo 'Colony Dome at \'', $planetid, '\' added successfully', $eol;

	$query = $mysqli->prepare('INSERT INTO fleets (planetID,userid,orderid) VALUES (?, ?, 0)');
	$query->bind_param('ii', $planetid, $userid);
	$query->execute();
	$query->close();

	if (COLONY_DEBUG) echo 'Unassigned fleet at \'', $planetid, '\' added successfully', $eol;
}
?>
