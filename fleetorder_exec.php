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
	$orderid = $_POST['order'];
	$orderplanetid = $_POST['orderplanet'];
	$transportmetal = $_POST['metal'];
	$transportdeuterium = $_POST['deuterium'];

	if (!$orderplanetid)
	{
		$ordersystemid = systemid($_POST['orderplanetother']);
		$orderplanet = orbit($_POST['orderplanetother']);
		$query = $mysqli->prepare('SELECT planetid FROM planets WHERE systemid = ? AND orbit = ?');
		$query->bind_param('ii', $ordersystemid, $orderplanet);
		$query->execute();
		$query->bind_result($orderplanetid);
		$result = $query->fetch();
		if (!$result)
		{
			echo 'Error: No such destination planet.', $eol;
			exit;
		}
		$query->close();
	}

	if ($orderid < 2 || $orderid > 3)
	{
		echo 'Error: Invalid order.', $eol;
		exit;
	}

	$mysqli->autocommit(false);

	$query = $mysqli->prepare('SELECT orderid,fuel,planetid,speed,totalfuelbay,totalcargo FROM fleets WHERE userID = ? AND fleetid = ? FOR UPDATE');
	$query->bind_param('ii', $userid, $fleetid);
	$query->execute();
	$query->bind_result($fleetorderid, $fuel, $planetid, $fleetspeed, $totalfuelbay, $totalcargo);
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

	if ($transportmetal + $transportdeuterium > $totalcargo)
	{
		echo 'Error: Fleet does not have enough cargo space to carry that much cargo.', $eol;
		exit;
	}

	$query = $mysqli->prepare('SELECT colonies.metal,colonies.deuterium,x,y FROM colonies LEFT JOIN planets USING (planetid) LEFT JOIN systems USING (systemid) WHERE userid=? AND planetID = ? FOR UPDATE');
	$query->bind_param('ii', $userid, $planetid);
	$query->execute();
	$query->bind_result($metal,$deuterium,$sysx,$sysy);
	$result = $query->fetch();
	if (!$result)
	{
		echo 'Error: You don\'t have a colony on this planet.', $eol;
		exit;
	}
	$query->close();

	if ($transportmetal > $metal)
	{
		echo 'Error: You don\'t have that much metal to transport.', $eol;
		exit;
	}

	$query = $mysqli->prepare('SELECT (ROUND(SQRT(POW(x-?,2)+POW(y-?,2)),2)) AS distance FROM planets LEFT JOIN systems USING (systemid) WHERE planetid = ?');
	$query->bind_param('iii', $sysx, $sysy, $orderplanetid);
	$query->execute();
	$query->bind_result($orderdistance);
	$result = $query->fetch();
	if (!$result)
	{
		echo 'Error: No such destination planet.', $eol;
		exit;
	}
	$query->close();

	$totalfuelneed = 0;
	$orderticks = 1;
	if ($orderdistance > 0)
	{
		$orderticks = ceil($orderdistance/$fleetspeed) * 6;

		$query = $mysqli->prepare('SELECT count, engines, engines*24/size AS speed FROM fleetships LEFT JOIN shipdesigns USING (designid) LEFT JOIN shiphulls USING (hullid) WHERE fleetid = ?');
		$query->bind_param('i', $fleetid);
		$query->execute();
		$query->bind_result($count,$engines,$speed);

		while ($query->fetch())
		{
			$totalfuelneed += $count * ceil($engines * $fleetspeed / $speed) * $orderticks * 1;
		}
		$query->close();
	}

	if ($totalfuelneed > $fuel)
	{
		$deuteriumneed = $totalfuelneed - $fuel;
		if ($deuteriumneed > $deuterium)
		{
			echo 'Error: You don\'t have enough deuterium to fuel that flight.<br>', $eol;
			echo 'You need ',$deuteriumneed,' more deuterium.', $eol;
			exit;
		}

		if ($totalfuelneed > $totalfuelbay)
		{
			echo 'Error: Fleet doesn\'t have enough fuel bay to hold all the fuel needed for that flight.<br>', $eol;
			echo 'You need ',$totalfuelneed,' deuterium, those ships only hold ',$totalfuelbay,' deuterium in their fuel bays.', $eol;
			exit;
		}
		$fuel = $totalfuelneed;
		$deuterium = $deuterium - $deuteriumneed;
	}

	if ($transportdeuterium > $deuterium)
	{
		echo 'Error: After fueling your fleet for the journey, you don\'t have that much deuterium to transport.', $eol;
		exit;
	}

	$fueluse = 0;
	if ($orderticks > 0)
	{
		$fueluse = $totalfuelneed / $orderticks;
	}

	$query = $mysqli->prepare('UPDATE fleets SET orderid=?, orderplanetid=?, orderticks=?, fuel=?, fueluse=?, metal=?, deuterium=? WHERE fleetid=?');
	$query->bind_param('iiiiiiii', $orderid, $orderplanetid, $orderticks, $fuel, $fueluse, $transportmetal, $transportdeuterium, $fleetid);
	$query->execute();
	$query->close();

	$metal = $metal - $transportmetal;
	$deuterium = $deuterium - $transportdeuterium;
	$query = $mysqli->prepare('UPDATE colonies SET metal = ?, deuterium = ? WHERE userid = ? AND planetID = ?');
	$query->bind_param('iiii', $metal, $deuterium, $userid, $planetid);
	$query->execute();
	$query->close();

	$mysqli->commit();

	header('HTTP/1.1 303 See Other');
	header('Location: view_ships.php?planet='.$planetid);

	echo 'The ships are on their way.', $eol;
	echo '<a href="view_ships.php?planet=', $planetid, '">Return</a> to ships in orbit.', $eol;
}
?>
