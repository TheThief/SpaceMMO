<?php
if (!defined('COLONY_DEBUG')) define('COLONY_DEBUG',DEBUG);

define('COLONY_COST',4000);
define('WH_COST_PER_PC',5000);

function colonise($planetid, $userid, $metal=0)
{
	global $mysqli, $eol;

	$query = $mysqli->prepare('INSERT INTO colonies (planetID,userid,metal) VALUES (?, ?, ?)');
	$query->bind_param('iii', $planetid, $userid, $metal);
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

function claim_colony($planetid, $userid)
{
	global $mysqli, $eol;

	$query = $mysqli->prepare('DELETE FROM colonybuildings WHERE planetid = ?');
	$query->bind_param('i', $planetid);
	$query->execute();
	$query->close();

	if (COLONY_DEBUG) echo 'Buildings of colony \'', $planetid, '\' deleted', $eol;

	$query = $mysqli->prepare('DELETE FROM shipbuildqueue WHERE planetid = ?');
	$query->bind_param('i', $planetid);
	$query->execute();
	$query->close();

	if (COLONY_DEBUG) echo 'Ship build queue of colony \'', $planetid, '\' deleted', $eol;

	$query = $mysqli->prepare('DELETE FROM fleets WHERE planetID=? AND orderid=0');
	$query->bind_param('i', $planetid);
	$query->execute();
	$query->close();

	if (COLONY_DEBUG) echo 'Unassigned fleet at \'', $planetid, '\' deleted', $eol;

	$query = $mysqli->prepare('DELETE FROM colonies WHERE planetid = ?');
	$query->bind_param('i', $planetid);
	$query->execute();
	$query->close();

	if (COLONY_DEBUG) echo 'Colony \'', $planetid, '\' deleted', $eol;

	colonise($planetid, $userid);
}
?>
