#!/usr/bin/php
<?php
include_once '/var/www/www.dynamicarcade.co.uk/SpaceMMO/includes/start.inc.php';
//mail("mark@gethyper.co.uk","Tick",date("H:i:s"));

// Colony production

$mysqli->autocommit(false);

$query = $mysqli->prepare('UPDATE colonies SET metal=LEAST(metal+metalproduction, maxmetal), deuterium=LEAST(deuterium+deuteriumproduction, maxdeuterium), energy=LEAST(energy+energyproduction, maxenergy)'
                        .' WHERE metal+metalproduction >= 0 AND deuterium+deuteriumproduction >= 0 AND energy+energyproduction >= 0');
$result = $query->execute();
$query->close();
$mysqli->commit();

// Ship construction

$query = $mysqli->prepare('SELECT userid,planetid,shipconstruction FROM colonies WHERE shipconstruction > 0');
$query->execute();
$query->bind_result($userid, $planetid, $buildrate);
$query->store_result();

$deletequery = $mysqli->prepare('DELETE FROM shipbuildqueue WHERE planetID=? AND queueID<?');
$deletequery->bind_param('ii', $planetid, $queueid);

$updatequery = $mysqli->prepare('UPDATE shipbuildqueue SET count=?,buildprogress=? WHERE queueID=?');
$updatequery->bind_param('iii', $count, $progress, $queueid);

$query2 = $mysqli->prepare('SELECT queueid,designid,metalcost,count,buildprogress FROM shipbuildqueue LEFT JOIN shipdesigns USING (designid) LEFT JOIN shiphulls USING (hullid) WHERE planetID=? ORDER BY queueID');
$query2->bind_param('i', $planetid);

$fleetquery = $mysqli->prepare('SELECT fleetid FROM fleets WHERE userID = ? AND planetID = ? AND orderID = 0');
$fleetquery->bind_param('ii', $userid, $planetid);
$fleetquery->bind_result($fleetid);

function addToFleet($fleetid, $designid, $count)
{
	global $mysqli, $eol;

	$fleetquery = $mysqli->prepare('SELECT 1 FROM fleetships WHERE fleetID = ? AND designID = ? FOR UPDATE');
	$fleetquery->bind_param('ii', $fleetid, $designid);
	$result = $fleetquery->execute();
	$fleetquery->bind_result($fleetexists);
	$fleetquery->fetch();
	$fleetquery->close();

	if ($fleetexists)
	{
		$fleetquery = $mysqli->prepare('UPDATE fleetships SET count = count + ? WHERE fleetID = ? AND designID = ?');
		$fleetquery->bind_param('iii', $count, $fleetid, $designid);
		$fleetquery->execute();
		$fleetquery->close();
	}
	else
	{
		$fleetquery = $mysqli->prepare('INSERT INTO fleetships (fleetID, designID, count) VALUE (?,?,?)');
		$fleetquery->bind_param('iii', $fleetid, $designid, $count);
		$fleetquery->execute();
		$fleetquery->close();
	}
}

while ($query->fetch())
{
	$query2->execute();
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
				$fleetquery->execute();
				$result = $fleetquery->fetch();
				if (!$result)
				{
					echo 'error: No "unassigned" fleet on planet ',$planetid, $eol;
					break;
				}
				$fleetquery->reset();
			}
			addToFleet($fleet, $designid, $built);
		}
	}

	if ($done)
	{
		// need to update the last row we built from
		$updatequery->execute();
	}
	else
	{
		// ran out of queue, need to increment the queue id so we delete the one we were on last
		$queueid++;
	}
	$deletequery->execute();
}
$fleetquery->close();
$updatequery->close();
$deletequery->close();
$query2->close();
$query->close();

$mysqli->commit();

// Fleet movement

$query = $mysqli->prepare('UPDATE fleets SET fuel = fuel - fueluse * LEAST('.SMALL_PER_TICK.',orderticks), orderticks = orderticks - '.SMALL_PER_TICK.' WHERE orderid > 1 AND fuel >= fueluse * LEAST('.SMALL_PER_TICK.',orderticks) AND orderticks > 0');
$query->execute();
$query->close();

// Order 2 - Move
// Unloading on a move is temporary
$mysqli->query('UPDATE colonies INNER JOIN (SELECT orderplanetid AS planetid, SUM(metal) AS fleetmetal, SUM(deuterium) AS fleetdeuterium FROM fleets WHERE orderticks <= 0 AND orderid = 2 GROUP BY orderplanetid) fleetresources USING (planetid) SET metal=LEAST(metal+fleetmetal,maxmetal), deuterium=LEAST(deuterium+fleetdeuterium,maxdeuterium)');
$mysqli->query('UPDATE fleets SET planetid = orderplanetid, orderid = 1, orderticks = 0, totalorderticks = 0, metal = 0, deuterium = 0 WHERE orderticks <= 0 AND orderid = 2 AND NOT breturnorder');
$mysqli->query('UPDATE fleets SET orderplanetid = planetid, orderid = 2, orderticks = totalorderticks, breturnorder = FALSE, metal = 0, deuterium = 0 WHERE orderticks <= 0 AND orderid = 2 AND breturnorder');

$mysqli->commit();

// Order 3 - Transport
$mysqli->query('UPDATE colonies INNER JOIN (SELECT orderplanetid AS planetid, SUM(metal) AS fleetmetal, SUM(deuterium) AS fleetdeuterium FROM fleets WHERE orderticks <= 0 AND orderid = 3 GROUP BY orderplanetid) fleetresources USING (planetid) SET metal=LEAST(metal+fleetmetal,maxmetal), deuterium=LEAST(deuterium+fleetdeuterium,maxdeuterium)');
$mysqli->query('UPDATE fleets SET planetid = orderplanetid, orderid = 1, orderticks = 0, totalorderticks = 0, metal = 0, deuterium = 0 WHERE orderticks <= 0 AND orderid = 3 AND NOT breturnorder');
$mysqli->query('UPDATE fleets SET orderplanetid = planetid, orderid = 2, orderticks = totalorderticks, breturnorder = FALSE, metal = 0, deuterium = 0 WHERE orderticks <= 0 AND orderid = 3 AND breturnorder');

$mysqli->commit();

include_once '/var/www/www.dynamicarcade.co.uk/SpaceMMO/includes/colony.inc.php';

// Order 4 - Colonise
$query = $mysqli->prepare('SELECT userid, orderplanetid, SUM(fleets.metal) AS fleetmetal, SUM(fleets.deuterium) AS fleetdeuterium FROM fleets WHERE orderticks <= 0 AND orderid = 4 GROUP BY userid,orderplanetid');
$query->execute();
$query->bind_result($fleetuserid, $planetid, $fleetmetal, $fleetdeuterium);
$query->store_result();

$colonyquery = $mysqli->prepare('SELECT colonies.userid FROM colonies WHERE planetid = ?');
$colonyquery->bind_param('i', $planetid);
$colonyquery->bind_result($colonyuserid);

$transferquery1 = $mysqli->prepare('UPDATE colonies SET metal=LEAST(metal+?-?,maxmetal), deuterium=LEAST(deuterium+?,maxdeuterium) WHERE planetid = ?');
$transferquery1->bind_param('iiii', $fleetmetal, $metalcost, $fleetdeuterium, $planetid);
$transferquery2 = $mysqli->prepare('UPDATE fleets SET metal=0, deuterium=0 WHERE orderticks <= 0 AND orderid = 4 AND orderplanetid = ?');
$transferquery2->bind_param('i', $planetid);
$donequery = $mysqli->prepare('UPDATE fleets SET planetid = orderplanetid, orderid = 1, orderticks = 0, totalorderticks = 0 WHERE orderticks <= 0 AND orderid = 4 AND NOT breturnorder AND orderplanetid = ?');
$donequery->bind_param('i', $planetid);
$returnquery = $mysqli->prepare('UPDATE fleets SET orderplanetid = planetid, orderid = 2, orderticks = totalorderticks, breturnorder = FALSE WHERE orderticks <= 0 AND orderid = 4 AND breturnorder AND orderplanetid = ?');
$returnquery->bind_param('i', $planetid);

while ($query->fetch())
{
	$colonyquery->execute();
	$colonyquery->store_result();
	$result = $colonyquery->fetch();
	if (!$result || !$colonyuserid)
	{
		colonise($planetid, $fleetuserid);
		$metalcost = COLONY_COST;
		$transferquery1->execute();
		$transferquery2->execute();
	}
	else if ($colonyuserid == $fleetuserid)
	{
		$metalcost = 0;
		$transferquery1->execute();
		$transferquery2->execute();
	}
	$donequery->execute();
	$returnquery->execute();
}

$mysqli->commit();

echo 'update success!', $eol;
?>
