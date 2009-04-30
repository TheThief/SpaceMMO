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
	$query = $mysqli->prepare('SELECT x,y,systemid,orbit FROM colonies LEFT JOIN planets USING (planetid) LEFT JOIN systems USING (systemid) WHERE userID = ? AND planetID = ?');
	$query->bind_param('ii', $userid, $planetid);
	$result = $query->execute();
	$query->bind_result($sysx,$sysy,$systemid,$orbit);
	$result = $query->fetch();
	if (!$result)
	{
		echo 'Error: You don\'t have a colony on this planet.', $eol;
		exit;
	}
	$query->close();

	$query = $mysqli->prepare('SELECT fleetid FROM fleets WHERE fleets.userID = ? AND planetid = ? AND orderid = 0');
	$query->bind_param('ii', $userid, $planetid);
	$query->execute();
	$query->bind_result($fleetid);
	$query->fetch();
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
		echo '<tr><td colspan="2">None! Might want to <a href="build_ships.php?planet=',$planetid,'">build</a> some.</td></tr>';
		echo '</table>', $eol;
	}
	echo '</form>', $eol;

	$query = $mysqli->prepare('SELECT fleetid,speed,totalcargo,fuel,totalfuelbay,fueluse FROM fleets WHERE fleets.userID = ? AND planetid = ? AND orderid = 1');
	$query->bind_param('ii', $userid, $planetid);
	$query->execute();
	$query->bind_result($fleetid,$speed,$totalcargo,$fuel,$totalfuelbay,$fueluse);
	$query->store_result();

	if($query->fetch())
	{
		$bookmarks = array();
		$destinations = array();

		$querybookmarks = $mysqli->prepare('SELECT systemid,orbit,planetid,(ROUND(SQRT(POW(x-?,2)+POW(y-?,2)),2)) AS distance FROM bookmarks LEFT JOIN planets USING (planetid) LEFT JOIN systems USING (systemid) WHERE userID = ? AND planetid != ? ORDER BY distance ASC');
		$querybookmarks->bind_param('iiii', $sysx, $sysy, $userid, $planetid);
		$querybookmarks->execute();
		$querybookmarks->bind_result($ordersystemid,$orderorbit,$orderplanetid,$orderdistance);
		while ($querybookmarks->fetch())
		{
			$bookmarks[$orderplanetid] = systemcode($ordersystemid, $orderorbit).' ('.number_format($orderdistance,2).' PC)';
		}
		$querybookmarks->close();
		$querydestinations = $mysqli->prepare('SELECT systemid,orbit,planetid,(ROUND(SQRT(POW(x-?,2)+POW(y-?,2)),2)) AS distance FROM colonies LEFT JOIN planets USING (planetid) LEFT JOIN systems USING (systemid) WHERE userID = ? AND planetid != ? ORDER BY distance ASC');
		$querydestinations->bind_param('iiii', $sysx, $sysy, $userid, $planetid);
		$querydestinations->execute();
		$querydestinations->bind_result($ordersystemid,$orderorbit,$orderplanetid,$orderdistance);
		while ($querydestinations->fetch())
		{
			$destinations[$orderplanetid] = systemcode($ordersystemid, $orderorbit).' ('.number_format($orderdistance,2).' PC)';
		}
		$querydestinations->close();

		echo '<h2>Defending Fleets</h2>', $eol;
		do
		{
			$maxrange = shiprange($speed, $fueluse*SMALLTICKS_PH, $totalfuelbay);
			$maxreturnrange = returnrange($speed, $fueluse*SMALLTICKS_PH, $totalfuelbay);
			echo '<form action="fleetorder_exec.php" method="post">', $eol;
			echo '<input type="hidden" name="fleet" value="',$fleetid,'">', $eol;
			echo '<h3>',$lookups['order'][1],' ',systemcode($systemid,$orbit),'</h3>', $eol;
			echo 'Speed: ',number_format($speed,2),' PC/h (Fuel use: ',$fueluse*SMALLTICKS_PH,' D/h)<br>', $eol;
			echo 'Max Range: ',number_format($maxrange, 2),' PC (',number_format($maxreturnrange, 2),' PC return)<br>', $eol;
			echo 'Fuel: ',$fuel,' / ',$totalfuelbay,' D<br>', $eol;
			echo 'Cargo Capacity: ',$totalcargo,' Units<br>', $eol;
			echo '<ul>', $eol;

			$queryships->execute();

			while ($queryships->fetch())
			{
				echo '<li>',$count,' &#215; ',$shipname,'</li>', $eol;
			}
			echo '</ul>', $eol;
			echo 'Order: ';
			echo '<select name="order">', $eol;
			echo '<option value="2" selected>Move to</option>', $eol;
			echo '<option value="3">Transport to</option>', $eol;
			echo '<option value="4">Colonise</option>', $eol;
			echo '<option value="5" disabled>Attack</option>', $eol;
			echo '</select>', $eol;
			echo '<select name="orderplanet" id="opd',$fleetid,'" onchange="updateOtherP(',$fleetid,');">', $eol;
			if (count($bookmarks) > 0)
			{
				echo '<option value="0" disabled>Bookmarks</option>', $eol;
				foreach ($bookmarks as $orderplanetid => $string)
				{
					echo '<option value="', $orderplanetid, '">', $string, '</option>', $eol;
				}
			}
			else
			{
				echo '<option value="0" disabled>No Bookmarks</option>', $eol;
			}
			if (count($destinations) > 0)
			{
				echo '<option value="0" disabled>Colonies</option>', $eol;
				foreach ($destinations as $orderplanetid => $string)
				{
					echo '<option value="', $orderplanetid, '">', $string, '</option>', $eol;
				}
			}
			else
			{
				echo '<option value="0" disabled>No Colonies</option>', $eol;
			}
			echo '<option value="0" disabled>Other</option>', $eol;
			echo '<option value="0">Other...</option>', $eol;
			echo '</select>', $eol;
			echo '<input type="text" size="4" maxlen="4" name="orderplanetother" id="opo',$fleetid,'" style="visibility: visible"><br>', $eol;

			echo 'Transport: <input type="text" size="4" name="metal"> metal, ', $eol;
			echo '<input type="text" size="4" name="deuterium"> deuterium<br>', $eol;
			echo '<input type="submit" value="Dispatch">', $eol;
			echo '</form>', $eol;
			echo '<form action="disbandfleet_exec.php" method="post">', $eol;
			echo '<input type="hidden" name="fleet" value="',$fleetid,'">', $eol;
			echo '<input type="submit" value="Disband">', $eol;
			echo '</form>', $eol;
			echo "<script type=\"text/javascript\">updateOtherP($fleetid);</script>\n";
		} while ($query->fetch());
	}

	$query = $mysqli->prepare('SELECT fleetid,orderid,systemid,orbit,orderticks,fuel,totalfuelbay,fleets.metal,fleets.deuterium FROM fleets LEFT JOIN planets ON orderplanetid = planets.planetid WHERE fleets.userID = ? AND fleets.planetid = ? AND fleets.orderid > 1');
	$query->bind_param('ii', $userid, $planetid);
	$query->execute();
	$query->bind_result($fleetid, $orderid, $ordersystemid, $orderorbit, $orderticks, $fuel, $totalfuelbay, $fleetmetal, $fleetdeuterium);
	$query->store_result();

	if($query->fetch())
	{
		echo '<h2>Active Fleets</h2>', $eol;
		do
		{
			echo '<form action="fleetorder_exec.php" method="post">', $eol;
			echo '<input type="hidden" name="fleet" value="',$fleetid,'">', $eol;
			echo '<h3>',$lookups['order'][$orderid],' ',systemcode($ordersystemid,$orderorbit),'</h3>', $eol;
			echo 'Fuel: ',$fuel,' / ',$totalfuelbay,' D<br>', $eol;
			if ($fleetmetal && $fleetdeuterium)
			{
				echo 'Transporting: ',$fleetmetal,' metal, ',$fleetdeuterium,' deuterium<br>', $eol;
			}
			else if ($fleetmetal)
			{
				echo 'Transporting: ',$fleetmetal,' metal<br>', $eol;
			}
			else if ($fleetdeuterium)
			{
				echo 'Transporting: ',$fleetdeuterium,' deuterium<br>', $eol;
			}
			echo '<span id="count',$countpoint,'">',formatSeconds('h:i:s',($orderticks*TICK)-getTickElapsed()),'</span><br>', $eol;
			$countarray[$countpoint++] = ($orderticks*TICK)-getTickElapsed();
			
			echo '<ul>', $eol;

			$queryships->execute();

			while ($queryships->fetch())
			{
				// &#215; = ×
				echo '<li>',$count,' &#215; ',$shipname,'</li>', $eol;
			}
			echo '</ul>', $eol;
			echo '<input type="hidden" name="order" value="1">', $eol;
			echo '<input type="submit" value="Recall" disabled>', $eol;
			echo '</form>', $eol;
		} while ($query->fetch());
	}

	$query = $mysqli->prepare('SELECT fleetid,orderid,orderticks,systemid,orbit,fuel,totalfuelbay,fleets.metal,fleets.deuterium FROM fleets LEFT JOIN planets USING (planetid) WHERE fleets.userID = ? AND fleets.orderplanetid = ? AND fleets.orderid > 1');
	$query->bind_param('ii', $userid, $planetid);
	$query->execute();
	$query->bind_result($fleetid, $orderid, $orderticks, $fromsystemid, $fromorbit, $fuel, $totalfuelbay, $fleetmetal, $fleetdeuterium);
	$query->store_result();

	if($query->fetch())
	{
		echo '<h2>Incoming Fleets</h2>', $eol;
		do
		{
			echo '<form action="fleetorder_exec.php" method="post">', $eol;
			echo '<input type="hidden" name="fleet" value="',$fleetid,'">', $eol;
			echo '<h3>',$lookups['order'][$orderid],' ',systemcode($systemid,$orbit),'</h3>', $eol;
			echo 'From: ',systemcode($fromsystemid,$fromorbit),'<br>', $eol;
			echo 'Fuel: ',$fuel,' / ',$totalfuelbay,' D<br>', $eol;
			if ($fleetmetal && $fleetdeuterium)
			{
				echo 'Transporting: ',$fleetmetal,' metal, ',$fleetdeuterium,' deuterium<br>', $eol;
			}
			else if ($fleetmetal)
			{
				echo 'Transporting: ',$fleetmetal,' metal<br>', $eol;
			}
			else if ($fleetdeuterium)
			{
				echo 'Transporting: ',$fleetdeuterium,' deuterium<br>', $eol;
			}
			echo '<span id="count',$countpoint,'">',formatSeconds('h:i:s',($orderticks*TICK)-getTickElapsed()),'</span><br>', $eol;
			$countarray[$countpoint++] = ($orderticks*TICK)-getTickElapsed();
			echo '<ul>', $eol;

			$queryships->execute();

			while ($queryships->fetch())
			{
				// &#215; = ×
				echo '<li>',$count,' &#215; ',$shipname,'</li>', $eol;
			}
			echo '</ul>', $eol;
			echo '<input type="hidden" name="order" value="1">', $eol;
			echo '<input type="submit" value="Recall" disabled>', $eol;
			echo '</form>', $eol;
		} while ($query->fetch());
	}

	$scandistance = 2;

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
			echo '<span id="count',$countpoint,'">',formatSeconds('h:i:s',($orderticks*TICK)-getTickElapsed()),'</span><br>', $eol;
			echo '<ul>', $eol;
			$countarray[$countpoint++] = ($orderticks*TICK)-getTickElapsed();
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
