<?php
include 'includes/start.inc.php';
checkLoggedIn();

include 'includes/template.inc.php';
template('Issue Order', 'fleetOrderBody');

function fleetOrderBody()
{
	global $eol, $mysqli;
	$userid = $_SESSION['userid'];
	$fleetid = $_POST['fleet'];
	$orderid = $_POST['order'];
	$orderplanetid = $_POST['orderplanet'];

	if ($orderid <= 1 || $orderid > 2)
	{
		echo 'Error: Invalid order.', $eol;
		exit;
	}

	$mysqli->autocommit(false);

	$query = $mysqli->prepare('SELECT orderid,fuel FROM fleets WHERE userID = ? AND fleetid = ? FOR UPDATE');
	$query->bind_param('ii', $userid, $fleetid);
	$query->execute();
	$query->bind_result($fleetorderid, $fuel);
	$result = $query->fetch();
	if (!$result)
	{
		echo 'Error: You have no such fleet.', $eol;
		exit;
	}
	$query->close();

	if ($fleetorderid != 1)
	{
		echo 'Error: Fleet is not in a good position to receive any orders.', $eol;
		exit;
	}

	$query = $mysqli->prepare('SELECT deuterium,x,y FROM colonies LEFT JOIN planets USING (planetid) WHERE userid=? AND planetID = ? FOR UPDATE');
	$query->bind_param('ii', $userid, $planetid);
	$query->execute();
	$query->bind_result($deuterium,$sysx,$sysy);
	$result = $query->fetch();
	if (!$result)
	{
		echo 'Error: You don\'t have a colony on this planet.', $eol;
		exit;
	}
	$query->close();

	$query = $mysqli->prepare('SELECT (ROUND(SQRT(POW(x-?,2)+POW(y-?,2)),2)) AS distance FROM planets LEFT JOIN systems USING (systemid) WHERE planetid = ?');
	$query->bind_param('iii', $sysx, $sysy, $planetid);
	$query->execute();
	$query->bind_result($orderdistance);
	$result = $query->fetch();
	if (!$result)
	{
		echo 'Error: No such destination planet.', $eol;
		exit;
	}
	$query->close();

	$query = $mysqli->prepare('SELECT MIN(engines*24/size) AS minspeed, SUM(count*fuel*6) AS totalfuelbay FROM fleetships LEFT JOIN shipdesigns USING (designid) LEFT JOIN shiphulls USING (hullid) WHERE fleetid = ?');
	$query->bind_param('i', $fleetid);
	$query->execute();
	$query->bind_result($minspeed,$totalfuelbay);
	$query->fetch();
	$query->close();

	$query = $mysqli->prepare('SELECT count, engines, engines*24/size AS speed FROM fleetships LEFT JOIN shipdesigns USING (designid) LEFT JOIN shiphulls USING (hullid) WHERE fleetid = ?');
	$query->bind_param('i', $fleetid);
	$query->execute();
	$query->bind_result($count,$engines,$speed);

	$totalfuelneed = 0;
	$orderticks = ceil($distance/$minspeed) * 6;
	while ($query->fetch())
	{
		$totalfuelneed += $count * ceil($engines * $minspeed / $speed) * $orderticks * 1;
	}
	$query->close();

	if ($totalfuelneed > $fuel)
	{
		$deuteriumneed = $totalfuelneed - $fuel;
		if ($deuteriumneed > $deuterium)
		{
			echo 'Error: You don\'t have enough deuterium for that flight.<br>', $eol;
			echo 'You need ',$deuteriumneed,' more deuterium.', $eol;
			exit;
		}

		if ($totalfuelneed > $totalfuelbay)
		{
			echo 'Error: Not enough fuel bay for that flight.<br>', $eol;
			echo 'You need ',$totalfuelneed,' deuterium, those ships only hold ',$totalfuelbay,' deuterium in their fuel bays.', $eol;
			exit;
		}
		$fuel = $totalfuelneed;

		$query = $mysqli->prepare('UPDATE colonies SET deuterium = deuterium - ? WHERE userid = ? AND planetID = ?');
		$query->bind_param('iii', $deuteriumneed, $userid, $planetid);
		$query->execute();
		$query->close();
	}

	$fueluse = $totalfuelneed / $orderticks;

	$query = $mysqli->prepare('UPDATE fleets SET orderid=?, orderplanetid=?, orderticks=?, fuel=?, fueluse=?');
	$query->bind_param('iiiii', $orderid, $orderplanetid, $orderticks, $fuel, $fueluse);
	$query->execute();
	$query->close();

	$mysqli->commit();

	header('HTTP/1.1 303 See Other');
	header('Location: view_ships.php?planet='.$planetid);

	echo 'The ships are on their way.', $eol;
	echo '<a href="view_ships.php?planet=', $planetid, '">Return</a> to ships in orbit.', $eol;
}
?>
