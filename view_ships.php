<?
include_once 'includes/start.inc.php';
checkLoggedIn();

include_once 'includes/ships.inc.php';

include_once 'includes/template.inc.php';
template('Ships in Orbit', 'viewShipsBody');

function viewShipsBody()
{
	global $eol, $mysqli, $lookups;
	$userid = $_SESSION['userid'];
	$planetid = $_GET['planet'];
	$countarray=array();
	$cointpoint=0;
	$query = $mysqli->prepare('SELECT x,y,systemid,orbit,sensorrange FROM colonies LEFT JOIN planets USING (planetid) LEFT JOIN systems USING (systemid) WHERE userID = ? AND planetID = ?');
	$query->bind_param('ii', $userid, $planetid);
	$result = $query->execute();
	$query->bind_result($sysx,$sysy,$systemid,$orbit,$sensorrange);
	$result = $query->fetch();
	if (!$result)
	{
		echo 'Error: You don\'t have a colony on this planet.', $eol;
		exit;
	}
	$query->close();
	
	$querywh = $mysqli->prepare('SELECT whrange, systemid FROM colonies LEFT JOIN planets USING (planetid) WHERE planetid = ?');
	$querywh->bind_param('i', $planetid);
	$querywh->bind_result($cwhrange,$csid);
	$querywh->execute();
	$querywh->fetch();
	$querywh->close();

	$query = $mysqli->prepare('SELECT fleetid FROM fleets WHERE fleets.userID = ? AND planetid = ? AND orderid = 0');
	$query->bind_param('ii', $userid, $planetid);
	$query->execute();
	$query->bind_result($fleetid);
	$result = $query->fetch();
	if (!$result)
	{
		echo 'Error: Unassigned ships fleet not found.', $eol;
		exit;
	}
	$query->close();

	$queryships = $mysqli->prepare('SELECT designid,shipname,count FROM fleetships LEFT JOIN shipdesigns USING (designid) WHERE fleetid = ?');
	$queryships->bind_param('i', $fleetid);
	$queryships->bind_result($designid,$shipname,$count);

	echo '<h2>Unassigned</h2>', $eol;
	echo '<form action="createfleet_exec.php" method="post">', $eol;
	echo '<input type="hidden" name="planet" value="',$planetid,'">', $eol;
	echo '<table>', $eol;
	echo '<tr><th>Design Name</th><th>Count</th><th></th></tr>', $eol;

	$queryships->execute();
	if($queryships->fetch())
	{
		do
		{
			echo '<tr>';
			echo "<td>$shipname</td>";
			echo "<td>$count</td>";
			echo '<td><input type="text" size="4" name="ships[',$designid,']" value="0"></td>';
			echo '</tr>', $eol;
		} while ($queryships->fetch());
		echo '</table>', $eol;
		echo '<input type="submit" value="Create Fleet">', $eol;
	}
	else
	{
		echo '<tr><td colspan="3">None! Might want to <a href="build_ships.php?planet=',$planetid,'">build</a> some.</td></tr>';
		echo '</table>', $eol;
	}
	echo '</form>', $eol;

	$query = $mysqli->prepare('SELECT fleetid,speed,fuel,totalfuelbay,fueluse,metal,deuterium,totalcargo FROM fleets WHERE fleets.userID = ? AND planetid = ? AND orderid = 1');
	$query->bind_param('ii', $userid, $planetid);
	$query->execute();
	$query->bind_result($fleetid,$speed,$fuel,$totalfuelbay,$fueluse,$fleetmetal,$fleetdeuterium,$totalcargo);
	$query->store_result();

	if($query->fetch())
	{
		echo '<h2>Idle Fleets</h2>', $eol;
		echo '<form action="fleetorder_exec.php" method="post">', $eol;
		echo '<table>', $eol;
		echo '<tr><th></th><th>Ships</th><th>Speed</th><th>Range</th><th>Fuel</th><th>Cargo</th><th>Actions</th></tr>', $eol;
		do
		{
			echo '<tr>';
			echo '<td><input type="radio" name="fleet" value="',$fleetid,'"></td>';

			echo '<td>';
			$queryships->execute();
			if ($queryships->fetch())
			{
				echo '',$count,' &#215; ',$shipname,'', $eol;
				while ($queryships->fetch())
				{
					echo ', ',$count,' &#215; ',$shipname,'', $eol;
				}
			}
			echo '</td>';
			echo '<td>',number_format($speed,2),' PC/h</td>';
			$range = shiprange($speed, $fueluse*SMALLTICKS_PH, $totalfuelbay);
			echo '<td>',number_format($range,2),' PC</td>';
			echo '<td>',$fuel,' / ',$totalfuelbay,' D</td>';
			if ($totalcargo)
			{
				if ($fleetmetal && $fleetdeuterium)
				{
					echo '<td>',$fleetmetal,' M + ',$fleetdeuterium,' D / ',$totalcargo,'</td>';
				}
				else if ($fleetmetal)
				{
					echo '<td>',$fleetmetal,' M / ',$totalcargo,'</td>';
				}
				else if ($fleetdeuterium)
				{
					echo '<td>',$fleetdeuterium,' D / ',$totalcargo,'</td>';
				}
				else
				{
					echo '<td>0 / ',$totalcargo,'</td>';
				}
			}
			else
			{
				echo '<td>-</td>';
			}
			echo '<td><a href="view_fleet.php?fleet=',$fleetid,'">Details</a></td>';
			echo '</tr>', $eol;
		} while ($query->fetch());
		echo '</table>', $eol;

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
		echo '<option value="5">Attack</option>', $eol;
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
	}

	$query = $mysqli->prepare('SELECT fleetid,orderid,systemid,orbit,orderticks, breturnorder,fuel,totalfuelbay,fleets.metal,fleets.deuterium,totalcargo FROM fleets LEFT JOIN planets ON orderplanetid = planets.planetid WHERE fleets.userID = ? AND fleets.planetid = ? AND fleets.orderid > 1');
	$query->bind_param('ii', $userid, $planetid);
	$query->execute();
	$query->bind_result($fleetid, $orderid, $ordersystemid, $orderorbit, $orderticks, $breturn, $fuel, $totalfuelbay, $fleetmetal, $fleetdeuterium, $totalcargo);
	$query->store_result();

	if($query->fetch())
	{
		echo '<h2>Active Fleets</h2>', $eol;
		echo '<form action="fleetorder_exec.php" method="post">', $eol;
		echo '<table>', $eol;
		echo '<tr><th></th><th>Ships</th><th>Order</th><th>Fuel</th><th>Cargo</th><th>ETA</th></tr>', $eol;
		do
		{
			echo '<tr>';
			echo '<td><input type="radio" name="fleet" value="',$fleetid,'"></td>';

			echo '<td>';
			$queryships->execute();
			if ($queryships->fetch())
			{
				echo '',$count,' &#215; ',$shipname,'', $eol;
				while ($queryships->fetch())
				{
					echo ', ',$count,' &#215; ',$shipname,'', $eol;
				}
			}
			echo '</td>';
			echo '<td>',$lookups['order'][$orderid],' ',systemcode($ordersystemid,$orderorbit),($breturn?' and Return':''),'</td>', $eol;
			echo '<td>',$fuel,' / ',$totalfuelbay,' D</td>';
			if ($totalcargo)
			{
				if ($fleetmetal && $fleetdeuterium)
				{
					echo '<td>',$fleetmetal,' M + ',$fleetdeuterium,' D / ',$totalcargo,'</td>';
				}
				else if ($fleetmetal)
				{
					echo '<td>',$fleetmetal,' M / ',$totalcargo,'</td>';
				}
				else if ($fleetdeuterium)
				{
					echo '<td>',$fleetdeuterium,' D / ',$totalcargo,'</td>';
				}
				else
				{
					echo '<td>0 / ',$totalcargo,'</td>';
				}
			}
			else
			{
				echo '<td>-</td>';
			}
			if ($orderid == 5 && $orderticks <=0)
			{
				echo '<td>In Combat</td>';
			}
			else
			{
				if ($orderticks <=0) $orderticks = 1;
				echo '<td><span id="count',$countpoint,'">',formatSeconds('h:i:s',ceil($orderticks/SMALL_PER_TICK)*TICK-getTickElapsed()),'</span></td>', $eol;
				$countarray[$countpoint++] = ceil($orderticks/SMALL_PER_TICK)*TICK-getTickElapsed();
			}
			echo '</tr>', $eol;
		} while ($query->fetch());
		echo '</table>', $eol;
		echo '<input type="hidden" name="order" value="1">', $eol;
		echo '<input type="submit" value="Recall" disabled>', $eol;
		echo '</form>', $eol;
	}

	$query = $mysqli->prepare('SELECT fleetid,orderid,orderticks,breturnorder,systemid,orbit,fuel,totalfuelbay,fleets.metal,fleets.deuterium,totalcargo FROM fleets LEFT JOIN planets USING (planetid) WHERE fleets.userID = ? AND fleets.orderplanetid = ? AND fleets.orderid > 1');
	$query->bind_param('ii', $userid, $planetid);
	$query->execute();
	$query->bind_result($fleetid, $orderid, $orderticks, $breturn, $fromsystemid, $fromorbit, $fuel, $totalfuelbay, $fleetmetal, $fleetdeuterium, $totalcargo);
	$query->store_result();

	if($query->fetch())
	{
		echo '<h2>Incoming Fleets</h2>', $eol;
		echo '<form action="fleetorder_exec.php" method="post">', $eol;
		echo '<table>', $eol;
		echo '<tr><th></th><th>Ships</th><th>Order</th><th>From</th><th>Fuel</th><th>Cargo</th><th>ETA</th></tr>', $eol;
		do
		{
			echo '<tr>';
			echo '<td><input type="radio" name="fleet" value="',$fleetid,'"></td>';

			echo '<td>';
			$queryships->execute();
			if ($queryships->fetch())
			{
				echo '',$count,' &#215; ',$shipname,'', $eol;
				while ($queryships->fetch())
				{
					echo ', ',$count,' &#215; ',$shipname,'', $eol;
				}
			}
			echo '</td>';
			echo '<td>',$lookups['order'][$orderid],' ',systemcode($systemid,$orbit),($breturn?' and Return':''),'</td>', $eol;
			echo '<td>',systemcode($fromsystemid,$fromorbit),'</td>', $eol;
			echo '<td>',$fuel,' / ',$totalfuelbay,' D</td>';
			if ($totalcargo)
			{
				if ($fleetmetal && $fleetdeuterium)
				{
					echo '<td>',$fleetmetal,' M + ',$fleetdeuterium,' D / ',$totalcargo,'</td>';
				}
				else if ($fleetmetal)
				{
					echo '<td>',$fleetmetal,' M / ',$totalcargo,'</td>';
				}
				else if ($fleetdeuterium)
				{
					echo '<td>',$fleetdeuterium,' D / ',$totalcargo,'</td>';
				}
				else
				{
					echo '<td>0 / ',$totalcargo,'</td>';
				}
			}
			else
			{
				echo '<td>-</td>';
			}
			if ($orderid == 5 && $orderticks <=0)
			{
				echo '<td>In Combat</td>';
			}
			else
			{
				if ($orderticks <=0) $orderticks = 1;
				echo '<td><span id="count',$countpoint,'">',formatSeconds('h:i:s',ceil($orderticks/SMALL_PER_TICK)*TICK-getTickElapsed()),'</span></td>', $eol;
				$countarray[$countpoint++] = ceil($orderticks/SMALL_PER_TICK)*TICK-getTickElapsed();
			}
			echo '</tr>', $eol;
		} while ($query->fetch());
		echo '</table>', $eol;
		echo '<input type="hidden" name="order" value="1">', $eol;
		echo '<input type="submit" value="Recall" disabled>', $eol;
		echo '</form>', $eol;
	}

	$scandistance = $sensorrange+10;

	$query = $mysqli->prepare('SELECT fleetid,username,orderid,orderticks FROM fleets LEFT JOIN users USING (userid) WHERE fleets.userID != ? AND fleets.orderplanetid = ? AND fleets.orderid > 1 AND orderticks <= ?');
	$query->bind_param('iii', $userid, $planetid, $scandistance);
	$query->execute();
	$query->bind_result($fleetid, $username, $orderid, $orderticks);
	$query->store_result();

	if($query->fetch())
	{
		echo '<h2>Incoming Enemy Fleets</h2>', $eol;
		do
		{
			// todo: "intercept" order
			//echo '<form action="fleetorder_exec.php" method="post">', $eol;
			//echo '<input type="hidden" name="fleet" value="',$fleetid,'">', $eol;
			echo '<h3>',$lookups['order'][$orderid],' ',systemcode($systemid,$orbit),'</h3>', $eol;
			echo 'Owner: ',$username,'<br>', $eol;
			if ($orderid == 5 && $orderticks <=0)
			{
				echo '<td>In Combat</td>';
			}
			else
			{
				if ($orderticks <=0) $orderticks = 1;
				echo '<span id="count',$countpoint,'">',formatSeconds('h:i:s',ceil($orderticks/SMALL_PER_TICK)*TICK-getTickElapsed()),'</span><br>', $eol;
				$countarray[$countpoint++] = ceil($orderticks/SMALL_PER_TICK)*TICK-getTickElapsed();
			}

			echo '<ul>', $eol;
			$queryships->execute();
			while ($queryships->fetch())
			{
				// &#215; = ×
				echo '<li>',$count,' &#215; ',$shipname,'</li>', $eol;
			}
			echo '</ul>', $eol;
			//echo '<input type="hidden" name="order" value="1">', $eol;
			//echo '<input type="submit" value="Intercept" disabled>', $eol;
			//echo '</form>', $eol;
		} while ($query->fetch());
	}
	?>
<script type="text/javascript"> 
<?
//print_r($countarray);
foreach($countarray as $cid => $ctime){
	echo "liveCount(".$ctime.",\"count".$cid."\",0,1,1);";
}
?>
</script>
<?
}
?>
