#!/usr/bin/php
<?php
include_once '/var/www/www.dynamicarcade.co.uk/SpaceMMO/includes/start.inc.php';
//mail("mark@gethyper.co.uk","Tick",date("H:i:s"));

// Colony production

$mysqli->autocommit(false);

$query = $mysqli->prepare('UPDATE colonies SET metal=LEAST(metal+metalproduction, maxmetal), deuterium=LEAST(deuterium+deuteriumproduction, maxdeuterium), energy=LEAST(energy+energyproduction, maxenergy)'
                        .' WHERE metal+metalproduction >= 0 AND deuterium+deuteriumproduction >= 0 AND energy+energyproduction >= 0');
$query->execute();
$query->close();
$mysqli->commit();

// shield hp
$query = $mysqli->prepare('UPDATE colonies LEFT JOIN colonies AS old USING (planetid) SET colonies.hp = LEAST(old.maxhp, old.hp + old.energy), colonies.energy = GREATEST(old.energy-(old.maxhp-old.hp), 0)');
$query->execute();
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

	$fleetquery = $mysqli->prepare('INSERT INTO fleetships (fleetID, designID, count) VALUE (?,?,?) ON DUPLICATE KEY UPDATE count = count + VALUES(count)');
	$fleetquery->bind_param('iii', $fleetid, $designid, $count);
	$fleetquery->execute();
	$fleetquery->close();
}

while ($query->fetch())
{
	$query2->execute();
	$query2->bind_result($queueid, $designid, $cost, $count, $progress);
	$query2->store_result();

	$fleetid = 0;

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
			if (!$fleetid)
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
			addToFleet($fleetid, $designid, $built);
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

$mysqli->query('UPDATE fleets LEFT JOIN (SELECT fleetid, LEAST('.SMALL_PER_TICK.',orderticks,FLOOR(fuel / fueluse)) AS ticks FROM fleets) ticktable USING (fleetid) SET fuel = fuel - fueluse * ticks, orderticks = orderticks - ticks WHERE orderid > 1 AND orderticks > 0 AND fuel >= fueluse');

// Order 2 - Move
$mysqli->query('UPDATE fleets SET planetid = orderplanetid, orderid = 1, orderticks = 0, totalorderticks = 0 WHERE orderticks <= 0 AND orderid = 2 AND NOT breturnorder');
$mysqli->query('UPDATE fleets SET orderplanetid = planetid, orderid = 2, orderticks = totalorderticks, breturnorder = FALSE WHERE orderticks <= 0 AND orderid = 2 AND breturnorder');

$mysqli->commit();

// Order 3 - Transport
$mysqli->query('UPDATE colonies INNER JOIN (SELECT orderplanetid AS planetid, SUM(metal) AS fleetmetal, SUM(deuterium) AS fleetdeuterium FROM fleets WHERE orderticks <= 0 AND orderid = 3 GROUP BY orderplanetid) fleetresources USING (planetid) SET metal=LEAST(metal+fleetmetal,maxmetal), deuterium=LEAST(deuterium+fleetdeuterium,maxdeuterium)');
$mysqli->query('UPDATE fleets SET planetid = orderplanetid, orderid = 1, orderticks = 0, totalorderticks = 0, metal = 0, deuterium = 0 WHERE orderticks <= 0 AND orderid = 3 AND NOT breturnorder');
$mysqli->query('UPDATE fleets SET orderplanetid = planetid, orderid = 2, orderticks = totalorderticks, breturnorder = FALSE, metal = 0, deuterium = 0 WHERE orderticks <= 0 AND orderid = 3 AND breturnorder');

// Order 6 - WH Transport
$mysqli->query('UPDATE colonies INNER JOIN (SELECT orderplanetid AS planetid, SUM(metal) AS fleetmetal, SUM(deuterium) AS fleetdeuterium FROM fleets WHERE orderticks <= 0 AND orderid = 6 GROUP BY orderplanetid) fleetresources USING (planetid) SET metal=LEAST(metal+fleetmetal,maxmetal), deuterium=LEAST(deuterium+fleetdeuterium,maxdeuterium)');
$mysqli->query('UPDATE fleets SET planetid = orderplanetid, orderid = 1, orderticks = 0, totalorderticks = 0, breturnorder = FALSE, metal = 0, deuterium = 0 WHERE orderticks <= 0 AND orderid = 6');

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

// Order 5 - Combat
$query = $mysqli->prepare('SELECT colonies.planetid,hp FROM colonies LEFT JOIN fleets ON orderplanetid = colonies.planetid WHERE orderticks <= 0 AND orderid=5 GROUP BY colonies.planetid ORDER BY NULL');
$query->bind_result($colonyid, $colonyhp);
$query->execute();
$query->store_result();

// Totals stats
$attacktotals = $mysqli->prepare('SELECT MIN(fleets.userid), SUM(count * weapons), SUM(count * defense) FROM fleets LEFT JOIN fleetships USING (fleetid) LEFT JOIN shipdesigns USING (designid) WHERE orderticks <= 0 AND orderid=5 AND orderplanetid=?');
$attacktotals->bind_param('i', $colonyid);
$attacktotals->bind_result($attackuserid, $totalweaponsattack,$totaldefenseattack);

$defendtotals = $mysqli->prepare('SELECT SUM(count * weapons), SUM(count * defense) FROM fleets LEFT JOIN fleetships USING (fleetid) LEFT JOIN shipdesigns USING (designid) WHERE orderid<=1 AND planetid=?');
$defendtotals->bind_param('i', $colonyid);
$defendtotals->bind_result($totalweaponsdefend,$totaldefensedefend);

// Fleets stats
$attackfleets = $mysqli->prepare('SELECT fleetid, SUM(count * defense) FROM fleets LEFT JOIN fleetships USING (fleetid) LEFT JOIN shipdesigns USING (designid) WHERE orderticks <= 0 AND orderid=5 AND orderplanetid=? GROUP BY fleetid ORDER BY orderid DESC, RAND()');
$attackfleets->bind_param('i', $colonyid);
$attackfleets->bind_result($fleetid, $fleetdefense);

$defendfleets = $mysqli->prepare('SELECT fleetid, orderid, SUM(count * defense) FROM fleets LEFT JOIN fleetships USING (fleetid) LEFT JOIN shipdesigns USING (designid) WHERE orderid<=1 AND planetid=? GROUP BY fleetid ORDER BY orderid DESC, RAND()');
$defendfleets->bind_param('i', $colonyid);
$defendfleets->bind_result($fleetid, $orderid, $fleetdefense);

$fleetships = $mysqli->prepare('SELECT designid, count, defense FROM fleets LEFT JOIN fleetships USING (fleetid) LEFT JOIN shipdesigns USING (designid) WHERE fleetid=? ORDER BY RAND()');
$fleetships->bind_param('i', $fleetid);
$fleetships->bind_result($designid, $count, $defense);

// Total force destruction
$deleteallattack = $mysqli->prepare('DELETE fleets,fleetships FROM fleets LEFT JOIN fleetships USING (fleetid) WHERE orderticks <= 0 AND orderid=5 AND orderplanetid = ?');
$deleteallattack->bind_param('i', $colonyid);

// "delete all defend" is split because we don't want to delete the "unassigned" (order id 0) fleet, but we do want to delete its ships
$deletealldefend1 = $mysqli->prepare('DELETE fleetships FROM fleets LEFT JOIN fleetships USING (fleetid) WHERE orderid <= 1 AND planetid = ?');
$deletealldefend1->bind_param('i', $colonyid);
$deletealldefend2 = $mysqli->prepare('DELETE FROM fleets WHERE orderid = 1 AND planetid = ?');
$deletealldefend2->bind_param('i', $colonyid);

// Total fleet destruction
// first is for attackers or defenders with orderids > 0, second is for defending "unassigned" (order id 0) fleet
$deletefleet = $mysqli->prepare('DELETE fleets,fleetships FROM fleets LEFT JOIN fleetships USING (fleetid) WHERE fleetid = ?');
$deletefleet->bind_param('i', $fleetid);
$deletefleetships = $mysqli->prepare('DELETE fleetships FROM fleets LEFT JOIN fleetships USING (fleetid) WHERE fleetid = ?');
$deletefleetships->bind_param('i', $fleetid);

// Partial fleet destruction
$deleteships = $mysqli->prepare('DELETE FROM fleetships WHERE fleetid = ? AND designid = ?');
$deleteships->bind_param('ii', $fleetid, $designid);

$updateships = $mysqli->prepare('UPDATE fleetships SET count = ? WHERE fleetid = ? AND designid = ?');
$updateships->bind_param('iii', $newcount, $fleetid, $designid);

// Colony damage
$updatecolony = $mysqli->prepare('UPDATE colonies SET hp = ? WHERE planetid = ?');
$updatecolony->bind_param('ii', $colonyhp, $colonyid);

// Ships idling
$donequery = $mysqli->prepare('UPDATE fleets SET planetid = orderplanetid, orderid = 1, orderticks = 0, totalorderticks = 0 WHERE orderticks <= 0 AND orderid = 5 AND NOT breturnorder AND orderplanetid = ? AND userid = ?');
$donequery->bind_param('ii', $colonyid, $attackuserid);
$returnquery = $mysqli->prepare('UPDATE fleets SET orderplanetid = planetid, orderid = 2, orderticks = totalorderticks, breturnorder = FALSE WHERE orderticks <= 0 AND orderid = 5 AND breturnorder AND orderplanetid = ? AND userid = ?');
$returnquery->bind_param('ii', $colonyid, $attackuserid);

while ($query->fetch())
{
	$attacktotals->execute();
	$attacktotals->store_result();
	$attacktotals->fetch();
	$defendtotals->execute();
	$defendtotals->store_result();
	$defendtotals->fetch();
	$attackersdead = false;
	$defendersdead = false;

	if ($totalweaponsattack >= $totaldefensedefend)
	{
		$deletealldefend1->execute();
		$deletealldefend2->execute();
		$totalweaponsattack -= $totaldefensedefend;
		$defendersdead = true;
	}
	else
	{
		$defendfleets->execute();
		$defendfleets->store_result();
		while ($totalweaponsattack > 0 && $defendfleets->fetch())
		{
			if ($totalweaponsattack >= $fleetdefense)
			{
				$totalweaponsattack -= $fleetdefense;
				if ($orderid > 0)
				{
					$deletefleet->execute();
				}
				else
				{
					$deletefleetships->execute();
				}
			}
			else
			{
				$fleetships->execute();
				$fleetships->store_result();
				while ($totalweaponsattack > 0 && $fleetships->fetch())
				{
					if ($totalweaponsattack >= $count * $defense)
					{
						$totalweaponsattack -= $count * $defense;
						$deleteships->execute();
					}
					else
					{
						$newcount = $count - floor($totalweaponsattack / $defense);
						$totalweaponsattack = 0;
						$updateships->execute();
					}
				}

				// TODO: Update fleet's stats
			}
		}
	}

	if ($totalweaponsdefend >= $totaldefenseattack)
	{
		$deleteallattack->execute();
		$totalweaponsdefend -= $totaldefenseattack;
		$attackersdead = true;
	}
	else
	{
		$attackfleets->execute();
		$attackfleets->store_result();
		while ($totalweaponsdefend > 0 && $attackfleets->fetch())
		{
			if ($totalweaponsdefend >= $fleetdefense)
			{
				$totalweaponsdefend -= $fleetdefense;
				$deletefleet->execute();
			}
			else
			{
				$fleetships->execute();
				$fleetships->store_result();
				while ($totalweaponsdefend > 0 && $fleetships->fetch())
				{
					if ($totalweaponsdefend >= $count * $defense)
					{
						$totalweaponsdefend -= $count * $defense;
						$deleteships->execute();
					}
					else
					{
						$newcount = $count - floor($totalweaponsdefend / $defense);
						$totalweaponsdefend = 0;
						$updateships->execute();
					}
				}

				// TODO: Update fleet's stats
			}
		}
	}

	// Defenders all died, all attackers didn't, attackers win
	// if all attackers AND defenders die, the attackers don't get to attack the colony itself
	// even if they had leftover firepower
	if ($totalweaponsattack > 0 && $defendersdead && !$attackersdead)
	{
		if ($totalweaponsattack <= $colonyhp)
		{
			$colonyhp -= $totalweaponsattack;
			$updatecolony->execute();
		}
		else
		{
			claim_colony($colonyid, $attackuserid);
			$donequery->execute();
			$returnquery->execute();
		}
	}
}

// Wow.

$mysqli->commit();

echo 'update success!', $eol;
?>
