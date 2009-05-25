<?
include_once 'includes/start.inc.php';
checkLoggedIn();

include_once 'includes/ships.inc.php';

include_once 'includes/template.inc.php';
template('Fleet Details', 'viewFleetBody');

function viewFleetBody()
{
	global $eol, $mysqli, $lookups;

	$userid = $_SESSION['userid'];
	$fleetid = $_GET['fleet'];
	$planetid = $_SESSION['colony'];
	
	$query = $mysqli->prepare('SELECT orderid,systemid,orbit,orderticks,breturnorder,fuel,totalfuelbay,fueluse,fleets.metal,fleets.deuterium,totalcargo FROM fleets LEFT JOIN planets ON orderplanetid = planets.planetid WHERE userID = ? AND fleetid = ? AND orderid > 0');
	$query->bind_param('ii', $userid, $fleetid);
	$query->execute();
	$query->bind_result($orderid, $ordersystemid, $orderorbit, $orderticks, $breturn, $fuel, $totalfuelbay, $fueluse, $fleetmetal, $fleetdeuterium, $totalcargo);
	$result = $query->fetch();
	$query->close();
	if (!$result)
	{
		echo 'error: You don\'t have a fleet by this id', $eol;
		exit;
	}
	
	$querysys = $mysqli->prepare('SELECT x,y FROM planets LEFT JOIN systems using (systemid) WHERE planetid = ?');
	$querysys->bind_param('i', $planetid);
	$querysys->bind_result($sysx,$sysy);
	$querysys->execute();
	$querysys->fetch();
	$querysys->close();
	
	$querywh = $mysqli->prepare('SELECT whrange, systemid FROM colonies LEFT JOIN planets USING (planetid) WHERE planetid = ?');
	$querywh->bind_param('i', $planetid);
	$querywh->bind_result($cwhrange,$csid);
	$querywh->execute();
	$querywh->fetch();
	$querywh->close();
	
	$range = shiprange($speed, $fueluse*SMALLTICKS_PH, $totalfuelbay);

	echo '<h2>Ships in Fleet</h2>', $eol;
	echo '<ul>', $eol;
	$queryships = $mysqli->prepare('SELECT designid,shipname,count FROM fleetships LEFT JOIN shipdesigns USING (designid) WHERE fleetid = ?');
	$queryships->bind_param('i', $fleetid);
	$queryships->bind_result($designid,$shipname,$count);
	$queryships->execute();
	while($queryships->fetch())
	{
		echo '<li>',$count,' &#215; ',$shipname,'</li>', $eol;
	}
	$queryships->close();
	echo '</ul>', $eol;
	echo '<form action="disbandfleet_exec.php" method="post">', $eol;
	echo '<input type="hidden" name="fleet" value="',$fleetid,'">', $eol;
	echo '<input type="submit" value="Disband">', $eol;
	echo '</form>', $eol;

	echo '<h2>Orders</h2>', $eol;
	echo '',$lookups['order'][$orderid],' ',systemcode($ordersystemid,$orderorbit),($breturn?' and Return':''),'<br>', $eol;

	//echo '<td>',number_format($speed,2),' PC/h</td>';
	//echo '<td>',number_format($range,2),' PC</td>';
	echo 'Fuel: ',$fuel,' / ',$totalfuelbay,' D<br>';
	echo 'Cargo: ';
	if ($totalcargo)
	{
		if ($fleetmetal && $fleetdeuterium)
		{
			echo '',$fleetmetal,' M + ',$fleetdeuterium,' D / ',$totalcargo,'<br>';
		}
		else if ($fleetmetal)
		{
			echo '',$fleetmetal,' M / ',$totalcargo,'<br>';
		}
		else if ($fleetdeuterium)
		{
			echo '',$fleetdeuterium,' D / ',$totalcargo,'<br>';
		}
		else
		{
			echo '0 / ',$totalcargo,'<br>';
		}
	}
	else
	{
		echo '-<br>';
	}

	$countarray=array();
	if ($orderid > 1)
	{
		echo 'ETA: <span id="count',$countpoint,'">',formatSeconds('h:i:s',ceil($orderticks/SMALL_PER_TICK)*TICK-getTickElapsed()),'</span><br>', $eol;
		$countarray[$countpoint++] = ceil($orderticks/SMALL_PER_TICK)*TICK-getTickElapsed();
	}
	else
	{
		echo '<form action="fleetorder_exec.php" method="post">', $eol;
		echo '<input type="hidden" name="fleet" value="',$fleetid,'">', $eol;

		$bookmarks = array();
		$destinations = array();

		$querybookmarks = $mysqli->prepare('SELECT systemid,orbit,planetid,(ROUND(distance(x,y,?,?),2)) AS distance FROM bookmarks LEFT JOIN planets USING (planetid) LEFT JOIN systems USING (systemid) WHERE userID = ? AND planetid != ? ORDER BY distance ASC');
		$querybookmarks->bind_param('iiii', $sysx, $sysy, $userid, $planetid);
		$querybookmarks->execute();
		$querybookmarks->bind_result($ordersystemid,$orderorbit,$orderplanetid,$orderdistance);
		while ($querybookmarks->fetch())
		{
			$bookmarks[$orderplanetid] = systemcode($ordersystemid, $orderorbit).' ('.number_format($orderdistance,2).' PC)';
		}
		$querybookmarks->close();
		$querydestinations = $mysqli->prepare('SELECT systemid,orbit,planetid,(ROUND(distance(x,y,?,?),2)) AS distance, whrange FROM colonies LEFT JOIN planets USING (planetid) LEFT JOIN systems USING (systemid) WHERE userID = ? AND planetid != ? ORDER BY distance ASC');
		$querydestinations->bind_param('iiii', $sysx, $sysy, $userid, $planetid);
		$querydestinations->execute();
		$querydestinations->bind_result($ordersystemid,$orderorbit,$orderplanetid,$orderdistance,$whrange);
		$canwhj = false;
		$whm = "";
		while ($querydestinations->fetch())
		{
			$canwhj = false;
			if(checkWHRange($orderdistance,$whrange,$cwhrange)) $canwhj = true;
			if($csid == $ordersystemid) $canwhj = false;
			$whm = ($canwhj)?" W":"";
			$destinations[$orderplanetid] = systemcode($ordersystemid, $orderorbit).' ('.number_format($orderdistance,2).' PC)' . $whm;
		}
		$querydestinations->close();

		echo 'Order: ';
		echo '<select name="order">', $eol;
		echo '<option value="2" selected>Move to</option>', $eol;
		echo '<option value="3">Transport to</option>', $eol;
		echo '<option value="4">Colonise</option>', $eol;
		echo '<option value="5" disabled>Attack</option>', $eol;
		echo '<option value="6">Wormhole jump</option>', $eol;
		echo '</select>', $eol;
		echo '<label>and Return <input name="breturn" type="checkbox" checked></label><br>', $eol;
		echo 'Destination: <select name="orderplanet" id="opd0" onchange="updateOtherP(0);">', $eol;
		if (count($bookmarks) > 0)
		{
			echo '<optgroup label="Bookmarks">', $eol;
			foreach ($bookmarks as $orderplanetid => $string)
			{
				echo '<option value="', $orderplanetid, '">', $string, '</option>', $eol;
			}
			echo '</optgroup>', $eol;
		}
		else
		{
			echo '<option value="0" disabled>No Bookmarks</option>', $eol;
		}
		if (count($destinations) > 0)
		{
			echo '<optgroup label="Colonies">', $eol;
			foreach ($destinations as $orderplanetid => $string)
			{
				echo '<option value="', $orderplanetid, '">', $string, '</option>', $eol;
			}
			echo '</optgroup>', $eol;
		}
		else
		{
			echo '<option value="0" disabled>No Colonies</option>', $eol;
		}
		echo '<optgroup label="Other">', $eol;
		echo '<option value="0">Other...</option>', $eol;
		echo '</optgroup>', $eol;
		echo '</select>', $eol;
		echo '<input type="text" size="4" maxlen="4" name="orderplanetother" id="opo0"><br>', $eol;

		echo 'Transport: <input type="text" size="4" name="metal"> metal, ', $eol;
		echo '<input type="text" size="4" name="deuterium"> deuterium<br>', $eol;
		echo '<input type="submit" value="Dispatch">', $eol;
		echo '</form>', $eol;
		echo '<script type="text/javascript">updateOtherP(0);</script>', $eol;

		echo '</form>', $eol;
	}

	echo '<script type="text/javascript">', $eol;
	foreach($countarray as $cid => $ctime){
		echo "liveCount(".$ctime.",\"count".$cid."\",0,1,1);";
	}
	echo '</script>', $eol;
}
?>
