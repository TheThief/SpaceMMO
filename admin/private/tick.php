#!/usr/bin/php
<?php
include_once '/var/www/www.dynamicarcade.co.uk/SpaceMMO/includes/start.inc.php';
//mail("mark@gethyper.co.uk","Tick",date("H:i:s"));
$eol = "\n";

// Colony production

$mysqli->autocommit(false);

$query = $mysqli->prepare('UPDATE colonies SET metal=LEAST(metal+metalproduction, maxmetal), deuterium=LEAST(deuterium+deuteriumproduction, maxdeuterium), energy=LEAST(energy+energyproduction, maxenergy)'
                        .' WHERE metal+metalproduction >= 0 AND deuterium+deuteriumproduction >= 0 AND energy+energyproduction >= 0');
if (!$query)
{
	echo 'error: ', $mysqli->error, $eol;
	exit;
}

$result = $query->execute();
if (!$result)
{
	echo 'error: ', $query->error, $eol;
	exit;
}
$query->close();

$mysqli->commit();

// Ship construction

$query = $mysqli->prepare('SELECT userid,planetid,shipconstruction FROM colonies WHERE shipconstruction > 0');
if (!$query)
{
	echo 'error: ', $mysqli->error, $eol;
	exit;
}

$result = $query->execute();
if (!$result)
{
	echo 'error: ', $query->error, $eol;
	exit;
}

$query->bind_result($userid, $planetid, $buildrate);
$query->store_result();

$deletequery = $mysqli->prepare('DELETE FROM shipbuildqueue WHERE planetID=? AND queueID<?');
if (!$deletequery)
{
	echo 'error: ', $mysqli->error, $eol;
	exit;
}
$deletequery->bind_param('ii', $planetid, $queueid);

$updatequery = $mysqli->prepare('UPDATE shipbuildqueue SET count=?,buildprogress=? WHERE queueID=?');
if (!$updatequery)
{
	echo 'error: ', $mysqli->error, $eol;
	exit;
}
$updatequery->bind_param('iii', $count, $progress, $queueid);

$query2 = $mysqli->prepare('SELECT queueid,designid,metalcost,count,buildprogress FROM shipbuildqueue LEFT JOIN shipdesigns USING (designid) LEFT JOIN shiphulls USING (hullid) WHERE planetID=? ORDER BY queueID');
if (!$query2)
{
	echo 'error: ', $mysqli->error, $eol;
	exit;
}
$query2->bind_param('i', $planetid);

function getFleetID($userid, $planetid)
{
	global $mysqli, $eol;

	$fleetquery = $mysqli->prepare('SELECT fleetid FROM fleets WHERE userID = ? AND planetID = ? AND orderID = 0');
	if (!$fleetquery)
	{
		echo 'error: ', $mysqli->error, $eol;
		exit;
	}
	$fleetquery->bind_param('ii', $userid, $planetid);
	$result = $fleetquery->execute();
	if (!$result)
	{
		echo 'error: ', $fleetquery->error, $eol;
		exit;
	}
	$fleetquery->bind_result($fleetid);
	$fleetquery->fetch();
	$fleetquery->close();
	
	if (!$fleetid)
	{
		$fleetquery = $mysqli->prepare('INSERT INTO fleets (userID, planetID) VALUE (?,?)');
		if (!$fleetquery)
		{
			echo 'error: ', $mysqli->error, $eol;
			exit;
		}
		$fleetquery->bind_param('ii', $userid, $planetid);
		$result = $fleetquery->execute();
		if (!$result)
		{
			echo 'error: ', $fleetquery->error, $eol;
			exit;
		}
		$fleetquery->close();

		$fleetid = $mysqli->insert_id;
	}

	return $fleetid;
}

function addToFleet($fleetid, $designid, $count)
{
	global $mysqli, $eol;

	$fleetquery = $mysqli->prepare('SELECT 1 FROM fleetships WHERE fleetID = ? AND designID = ? FOR UPDATE');
	if (!$fleetquery)
	{
		echo 'error: ', $mysqli->error, $eol;
		exit;
	}
	$fleetquery->bind_param('ii', $fleetid, $designid);
	$result = $fleetquery->execute();
	if (!$result)
	{
		echo 'error: ', $fleetquery->error, $eol;
		exit;
	}
	$fleetquery->bind_result($fleetexists);
	$fleetquery->fetch();
	$fleetquery->close();

	if ($fleetexists)
	{
		$fleetquery = $mysqli->prepare('UPDATE fleetships SET count = count + ? WHERE fleetID = ? AND designID = ?');
		if (!$fleetquery)
		{
			echo 'error: ', $mysqli->error, $eol;
			exit;
		}
		$fleetquery->bind_param('iii', $count, $fleetid, $designid);
		$result = $fleetquery->execute();
		if (!$result)
		{
			echo 'error: ', $fleetquery->error, $eol;
			exit;
		}
		$fleetquery->close();
	}
	else
	{
		$fleetquery = $mysqli->prepare('INSERT INTO fleetships (fleetID, designID, count) VALUE (?,?,?)');
		if (!$fleetquery)
		{
			echo 'error: ', $mysqli->error, $eol;
			exit;
		}
		$fleetquery->bind_param('iii', $fleetid, $designid, $count);
		$result = $fleetquery->execute();
		if (!$result)
		{
			echo 'error: ', $fleetquery->error, $eol;
			exit;
		}
		$fleetquery->close();
	}
}

while ($query->fetch())
{
	$result = $query2->execute();
	if (!$result)
	{
		echo 'error: ', $query2->error, $eol;
		exit;
	}

	$query2->bind_result($queueid, $designid, $cost, $count, $progress);
	$query2->store_result();

	$fleet = 0;

	$done = false;
	while ($buildrate > 0 && $query2->fetch())
	{
		$built = 0;
		if ($cost - $progress > $buildrate)
		{
			$progress += $buildrate;
			$buildrate = 0;
			$done = true;
		}
		else if ($cost*$count - $progress > $buildrate)
		{
			$built = floor(($buildrate + $progress) / $cost);
			$count -= $built;
			$progress = ($buildrate + $progress) % $cost;
			$buildrate = 0;
			$done = true;
		}
		else
		{
			$built = $count;
			$buildrate -= ($cost*$count - $progress);
		}

		// if we built any ships we add them to an idle fleet orbiting the planet they were built at
		if ($built)
		{
			if (!$fleet)
			{
				$fleet = getFleetID($userid, $planetid);
			}
			addToFleet($fleet, $designid, $built);
		}
	}

	if ($done)
	{
		// need to update the last row we built from
		$result = $updatequery->execute();
		if (!$result)
		{
			echo 'error: ', $query2->error, $eol;
			exit;
		}
	}
	else
	{
		// ran out of queue, need to increment the queue id so we delete the one we were on last
		$queueid++;
	}
	$result = $deletequery->execute();
	if (!$result)
	{
		echo 'error: ', $query2->error, $eol;
		exit;
	}
}
$updatequery->close;
$deletequery->close;
$query2->close();
$query->close();

$mysqli->commit();

// Fleet movement

$query = $mysqli->prepare('UPDATE fleets SET fuel = fuel - fueluse, orderticks = orderticks - 1 WHERE orderid > 1 AND fuel >= fueluse');
$query->execute();
$query->close();

$query = $mysqli->prepare('UPDATE fleets SET planetid = orderplanetid, orderid = 1, orderticks = 0 WHERE orderticks <= 0 AND orderid = 2');
$query->execute();
$query->close();

$mysqli->commit();

echo 'update success!', $eol;
?>
