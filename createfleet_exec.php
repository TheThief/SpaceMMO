<?php
include_once 'includes/start.inc.php';
checkLoggedIn();

include_once 'includes/ships.inc.php';

include_once 'includes/template.inc.php';
template('Issue Order', 'fleetOrderBody');

function fleetOrderBody()
{
	global $eol, $mysqli;
	$userid = $_SESSION['userid'];
	$planetid = $_POST['planet'];
	$ships = $_POST['ships'];

	$totalships = 0;
	foreach ($ships as $designid => $count)
	{
		if ($count < 0)
		{
			echo 'Error: What do you mean, a negative number of ships? That makes no sense.', $eol;
			exit;
		}
		else if ($count == 0)
		{
			unset($ships[$designid]);
		}
		else
		{
			$totalships += $count;
		}
	}
	if ($totalships <= 0)
	{
		echo 'Error: You have to add <i>SOME</i> ships to the fleet.', $eol;
		exit;
	}

	$mysqli->autocommit(false);

	$query = $mysqli->prepare('SELECT fleetid FROM fleets WHERE userID = ? AND planetid = ? AND orderid = 0');
	$query->bind_param('ii', $userid, $planetid);
	$query->execute();
	$query->bind_result($planetfleetid);
	$result = $query->fetch();
	if (!$result)
	{
		echo 'Error: You have no unassigned ships orbiting that planet.', $eol;
		exit;
	}
	$query->close();
 
	$query = $mysqli->prepare('SELECT designid,count FROM fleetships WHERE fleetid = ? FOR UPDATE');
	$query->bind_param('i', $planetfleetid);
	$query->execute();
	$query->bind_result($designid,$count);

	$planetships = array();
	while ($query->fetch())
	{
		$planetships[$designid] = $count;
	}
	$query->close();

	foreach ($ships as $designid => $count)
	{
		if (!isset($planetships[$designid]) || $planetships[$designid] <= 0)
		{
			echo 'Error: You don\'t have <i>any</i> of THAT ship in orbit.', $eol;
			exit;
		}
		else if ($count > $planetships[$designid])
		{
			echo 'Error: You don\'t have that many of those ships.', $eol;
			exit;
		}
	}

	$query = $mysqli->prepare('INSERT INTO fleets (userid,planetid,orderid,orderplanetid) VALUES (?,?,1,?)');
	$query->bind_param('iii', $userid, $planetid, $planetid);
	$query->execute();
	$fleetid = $query->insert_id;
	$query->close();

	$moveallquery = $mysqli->prepare('UPDATE fleetships SET fleetid=? WHERE fleetid=? AND designid=?');
	$moveallquery->bind_param('iii', $fleetid, $planetfleetid, $designid);
	$movesomequery1 = $mysqli->prepare('UPDATE fleetships SET count=count-? WHERE fleetid=? AND designid=?');
	$movesomequery1->bind_param('iii', $count, $planetfleetid, $designid);
	$movesomequery2 = $mysqli->prepare('INSERT INTO fleetships (fleetid,designid,count) VALUES(?,?,?)');
	$movesomequery2->bind_param('iii', $fleetid, $designid, $count);

	foreach ($ships as $designid => $count)
	{
		if ($count == $planetships[$designid])
		{
			$moveallquery->execute();
		}
		else
		{
			$movesomequery1->execute();
			$movesomequery2->execute();
		}
	}

	$moveallquery->close();
	$movesomequery1->close();
	$movesomequery2->close();

	$query = $mysqli->prepare('SELECT MIN(engines*24/size) AS minspeed, SUM(count*cargo*100) AS totalcargobay, SUM(count*fuel*12) AS totalfuelbay FROM fleetships LEFT JOIN shipdesigns USING (designid) LEFT JOIN shiphulls USING (hullid) WHERE fleetid = ?');
	$query->bind_param('i', $fleetid);
	$query->execute();
	$query->bind_result($minspeed,$totalcargobay,$totalfuelbay);
	$query->fetch();
	$query->close();

	$query = $mysqli->prepare('SELECT SUM(count * CEIL(engines * ? / (engines*24/size))) AS fueluse FROM fleetships LEFT JOIN shipdesigns USING (designid) LEFT JOIN shiphulls USING (hullid) WHERE fleetid = ?');
	$query->bind_param('di', $minspeed, $fleetid);
	$query->execute();
	$query->bind_result($fueluse);
	$query->fetch();
	$query->close();

	$query = $mysqli->prepare('UPDATE fleets SET speed=?, totalcargo=?, totalfuelbay=?, fueluse=? WHERE fleetid=?');
	$query->bind_param('diiii', $minspeed, $totalcargobay, $totalfuelbay, $fueluse, $fleetid);
	$query->execute();
	$query->close();

	$mysqli->commit();

	header('HTTP/1.1 303 See Other');
	header('Location: view_ships.php?planet='.$planetid);

	echo 'Fleet created.', $eol;
	echo '<a href="view_ships.php?planet=', $planetid, '">Return</a> to ships in orbit.', $eol;
}
?>
