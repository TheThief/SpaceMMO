<?php
include_once 'includes/start.inc.php';
checkLoggedIn();

// for COLONY_COST constant
include_once 'includes/colony.inc.php';

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
	$breturn = isset($_POST['breturn']);
	
	if ($orderid == 6 && $breturn) $breturn = false;
	if ($orderid < 2 || $orderid > 4)
	{
		echo 'Error: Invalid order.', $eol;
		exit;
	}

	if ($orderid == 3 || $orderid == 4)
	{
		if ($transportmetal < 0 || $transportdeuterium < 0)
		{
			echo 'Error: Transport "negative" resources you say. How does that work then?', $eol;
			exit;
		}
	}
	else
	{
		$transportmetal = 0;
		$transportdeuterium = 0;
	}

	if ($orderid == 3)
	{
		if ($transportmetal + $transportdeuterium <= 0)
		{
			echo 'Error: A "transport" order requires <i>some</i> resources to be transported.', $eol;
			exit;
		}
	}
	else if ($orderid == 4)
	{
		//if ($transportmetal < COLONY_COST)
		//{
		//	echo 'Error: A "colonise" order requires you to transport ',COLONY_COST,' metal to construct the colony with.<br>', $eol;
		//	echo 'Transporting extra to allow you to build the colony\'s first mine and generator isn\'t a bad idea either.', $eol;
		//	exit;
		//}
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

	$query = $mysqli->prepare('SELECT userid, whrange FROM colonies WHERE planetid = ?');
	$query->bind_param('i', $orderplanetid);
	$query->execute();
	$query->bind_result($ordercolonyuserid,$destwhrange);
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
		if (!breturn && $userid != $ordercolonyuserid)
		{
			echo 'Error: You can\'t issue a one-way transport order to an enemy planet. Try ticking "and Return".', $eol;
			exit;
		}
	}
	else if ($orderid == 4)
	{
		if ($ordercolonyuserid)
		{
			if ($ordercolonyuserid == $userid)
			{
				echo 'Error: You already colonised that planet. Congratulations crew, mission complete in record time!', $eol;
				exit;
			}
			else
			{
				echo 'Error: How do you expect to colonise a planet without first wiping out its inhabitants? "Attack" first.', $eol;
				exit;
			}
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

	if ($orderid == 4)
	{
		if (COLONY_COST > $totalcargo)
		{
			echo 'Error: Fleet does not have enough cargo space to carry the ',COLONY_COST,' Metal needed to colonise a new planet.', $eol;
			exit;
		}

		if ($transportmetal + $transportdeuterium + COLONY_COST > $totalcargo)
		{
			echo 'Error: Fleet does not have enough cargo space to carry that much cargo in addition to the ',COLONY_COST,' Metal needed to colonise a new planet.', $eol;
			exit;
		}

		$transportmetal += COLONY_COST;
	}

	$query = $mysqli->prepare('SELECT colonies.metal,colonies.deuterium,colonies.energy,x,y,whrange FROM colonies LEFT JOIN planets USING (planetid) LEFT JOIN systems USING (systemid) WHERE userid=? AND planetID = ? FOR UPDATE');
	$query->bind_param('ii', $userid, $planetid);
	$query->execute();
	$query->bind_result($metal,$deuterium,$energy,$sysx,$sysy,$currentwhrange);
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

	$query = $mysqli->prepare('SELECT (ROUND(distance(x,y,?,?),2)) AS distance FROM planets LEFT JOIN systems USING (systemid) WHERE planetid = ?');
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
	
	if ($orderid ==6 && !checkWHRange($orderdistance,$currentwhrange,$destwhrange)){
		echo 'Error: You do not have a high enough level wormhole generator on both planets.', $eol;
		exit;
	}
	
	$orderticks = 1;
	if ($orderid == 6){
		$orderticks=0;
	}
	elseif ($orderdistance > 0)
	{
		$orderticks = ceil($orderdistance/$fleetspeed * SMALLTICKS_PH);
	}

	$totalfuelneed = $fueluse * $orderticks;

	if ($breturn)
	{
		$totalfuelneed *= 2;
	}

	if ($totalfuelneed > $fuel)
	{
		$deuteriumneed = ceil($totalfuelneed - $fuel);
		if ($deuteriumneed > $deuterium)
		{
			echo 'Error: You don\'t have enough deuterium to fuel that flight.<br>', $eol;
			echo 'You need ',$deuteriumneed - $deuterium,' more deuterium.', $eol;
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
	
	$whjumpcost = 0;
	if ($orderid == 6) $whjumpcost = ceil(WH_COST_PER_PC*$orderdistance);
	
	if ($whjumpcost > $deuterium){
		echo 'Error: You need ', $whjumpcost ,' deuterium to open this wormhole.', $eol;
		exit;
	}else{
		$deuterium -= $whjumpcost;
	}
	
	if ($whjumpcost > $energy){
		echo 'Error: You need ', $whjumpcost ,' energy to open this wormhole.', $eol;
		exit;
	}else{
		$energy -= $whjumpcost;
	}
	
	if ($transportdeuterium > $deuterium)
	{
		echo 'Error: After fueling your fleet for the journey, you don\'t have that much deuterium left to transport.', $eol;
		exit;
	}

	$query = $mysqli->prepare('UPDATE fleets SET orderid=?, orderplanetid=?, orderticks=?, totalorderticks=?, breturnorder=?, fuel=?, metal=?, deuterium=? WHERE fleetid=?');
	$query->bind_param('iiiiidiii', $orderid, $orderplanetid, $orderticks, $orderticks, $breturn, $fuel, $transportmetal, $transportdeuterium, $fleetid);
	$query->execute();
	$query->close();

	$metal = $metal - $transportmetal;
	$deuterium = $deuterium - $transportdeuterium;
	$query = $mysqli->prepare('UPDATE colonies SET metal = ?, deuterium = ?, energy = ? WHERE userid = ? AND planetID = ?');
	$query->bind_param('iiiii', $metal, $deuterium, $energy, $userid, $planetid);
	$query->execute();
	$query->close();

	$mysqli->commit();

	header('HTTP/1.1 303 See Other');
	header('Location: view_ships.php?planet='.$planetid);

	echo 'The ships are on their way.', $eol;
	echo '<a href="view_ships.php?planet=', $planetid, '">Return</a> to ships in orbit.', $eol;
}
?>
