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

	if ($orderid < 2 || $orderid > 3)
	{
		echo 'Error: Invalid order.', $eol;
		exit;
	}

	$fuelmult = 1;
	if ($orderid == 3)
	{
		if ($transportmetal + $transportdeuterium <= 0)
		{
			echo 'Error: A "transport" order requires <i>some</i> resources to be transported.', $eol;
			exit;
		}

		$fuelmult = 2;
	}

	if (!$orderplanetid)
	{
		$ordersystemid = systemid($_POST['orderplanetother']);
		$orderplanet = orbit($_POST['orderplanetother']);
		$query = $mysqli->prepare('SELECT planetid FROM planets WHERE systemid = ? AND orbit = ?');
		$query->bind_param('ii', $ordersystemid, $orderplanet);
		$query->execute();
		$query->bind_result($orderplanetid);
		$result = $query->fetch();
		$query->close();
		if (!$result)
		{
			echo 'Error: No such destination planet.', $eol;
			exit;
		}
	}

	$query = $mysqli->prepare('SELECT userid FROM colonies WHERE planetid = ?');
	$query->bind_param('i', $orderplanetid);
	$query->execute();
	$query->bind_result($ordercolonyuserid);
	$result = $query->fetch();
	$query->close();
	if ($orderid == 2)
	{
		if (!$ordercolonyuserid)
		{
			echo 'Error: You can\'t move ships to uncolonised planets, try a "colonise" order.', $eol;
			exit;
		}
		else if ($userid != $ordercolonyuserid)
		{
			echo 'Error: You can only move ships to your own colonies. Try an "attack" or "transport" order.', $eol;
			exit;
		}
	}
	else if ($orderid == 3)
	{
		if (!$ordercolonyuserid)
		{
			echo 'Error: You can\'t transport resources to uncolonised planets, try a "colonise" order.', $eol;
			exit;
		}
	}

	$mysqli->autocommit(false);

	$query = $mysqli->prepare('SELECT orderid,fuel,planetid,speed,totalfuelbay,totalcargo,fueluse FROM fleets WHERE userID = ? AND fleetid = ? FOR UPDATE');
	$query->bind_param('ii', $userid, $fleetid);
	$query->execute();
	$query->bind_result($fleetorderid, $fuel, $planetid, $fleetspeed, $totalfuelbay, $totalcargo, $fueluse);
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

	$orderticks = 1;
	if ($orderdistance > 0)
	{
		$orderticks = ceil($orderdistance/$fleetspeed * 6);
	}

	$totalfuelneed = $fueluse * $orderticks;
	$totalfuelneed *= $fuelmult;

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

	$query = $mysqli->prepare('UPDATE fleets SET orderid=?, orderplanetid=?, orderticks=?, fuel=?, metal=?, deuterium=? WHERE fleetid=?');
	$query->bind_param('iiiiiii', $orderid, $orderplanetid, $orderticks, $fuel, $transportmetal, $transportdeuterium, $fleetid);
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
