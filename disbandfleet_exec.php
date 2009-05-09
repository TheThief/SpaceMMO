<?php
include_once 'includes/start.inc.php';
checkLoggedIn();

include_once 'includes/template.inc.php';
template('Issue Order', 'fleetOrderBody');

function fleetOrderBody()
{
	global $eol, $mysqli;
	$userid = $_SESSION['userid'];
	$fleetid = $_POST['fleet'];

	$mysqli->autocommit(false);

	$query = $mysqli->prepare('SELECT orderid,fuel,planetid,metal,deuterium FROM fleets WHERE userID = ? AND fleetid = ? FOR UPDATE');
	$query->bind_param('ii', $userid, $fleetid);
	$query->execute();
	$query->bind_result($fleetorderid, $fuel, $planetid, $fleetmetal, $fleetdeuterium);
	$result = $query->fetch();
	if (!$result)
	{
		echo 'Error: You have no such fleet.', $eol;
		exit;
	}
	$query->close();

	if ($fleetorderid != 1)
	{
		echo 'Error: Fleet is not in a good position to be disbanded.', $eol;
		exit;
	}

	$query = $mysqli->prepare('SELECT fleetid FROM fleets WHERE userID = ? AND planetid = ? AND orderid = 0');
	$query->bind_param('ii', $userid, $planetid);
	$query->execute();
	$query->bind_result($planetfleetid);
	$result = $query->fetch();
	if (!$result)
	{
		echo 'Error: You don\'t have a colony on that planet.', $eol;
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

	$query = $mysqli->prepare('SELECT designid,count FROM fleetships WHERE fleetid = ? FOR UPDATE');
	$query->bind_param('i', $fleetid);
	$query->execute();
	$query->bind_result($designid,$count);
	$query->store_result();

	$movequery = $mysqli->prepare('UPDATE fleetships SET fleetid=? WHERE fleetid=? AND designid=?');
	$movequery->bind_param('iii', $planetfleetid, $fleetid, $designid);
	$mergequery1 = $mysqli->prepare('UPDATE fleetships SET count=count+? WHERE fleetid=? AND designid=?');
	$mergequery1->bind_param('iii', $count, $planetfleetid, $designid);
	$mergequery2 = $mysqli->prepare('DELETE FROM fleetships WHERE fleetid=? AND designid=?');
	$mergequery2->bind_param('ii', $fleetid, $designid);

	while ($query->fetch())
	{
		if (!isset($planetships[$designid]))
		{
			$movequery->execute();
		}
		else
		{
			$mergequery1->execute();
			$mergequery2->execute();
		}
	}
	$query->close();

	$query = $mysqli->prepare('DELETE FROM fleets WHERE fleetid = ?');
	$query->bind_param('i', $fleetid);
	$query->execute();
	$query->close();

	$query = $mysqli->prepare('UPDATE colonies SET metal = LEAST(metal+?, maxmetal), deuterium = LEAST(deuterium+?+?, maxdeuterium) WHERE planetid = ?');
	$query->bind_param('ii', $fleetmetal, $fleetdeuterium, $fuel, $planetid);
	$query->execute();
	$query->close();

	$mysqli->commit();

	header('HTTP/1.1 303 See Other');
	header('Location: view_ships.php?planet='.$planetid);

	echo 'Fleet disbanded.', $eol;
	echo '<a href="view_ships.php?planet=', $planetid, '">Return</a> to ships in orbit.', $eol;
}
?>
