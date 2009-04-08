<?
include 'includes/start.inc.php';
checkLoggedIn();

include 'includes/colonymenu.inc.php';

include 'includes/template.inc.php';
template('Ships in Orbit', 'viewShipsBody', 'colonyMenu');

function viewShipsBody()
{
	global $eol, $mysqli, $lookups;
	$userid = $_SESSION['userid'];
	$planetid = $_GET['planet'];

	$query = $mysqli->prepare('SELECT x,y FROM colonies LEFT JOIN planets USING (planetid) LEFT JOIN systems USING (systemid) WHERE userID = ? AND planetID = ?');
	$query->bind_param('ii', $userid, $planetid);
	$result = $query->execute();
	$query->bind_result($sysx,$sysy);
	$result = $query->fetch();
	if (!$result)
	{
		echo 'Error: You don\'t have a colony on this planet.', $eol;
		exit;
	}
	$query->close();

	$query = $mysqli->prepare('SELECT designid,shipname,count FROM fleets LEFT JOIN fleetships USING (fleetid) LEFT JOIN shipdesigns USING (designid) WHERE fleets.userID = ? AND planetid = ? AND orderid = 0');
	$query->bind_param('ii', $userid, $planetid);
	$result = $query->execute();
	$query->bind_result($designid,$shipname,$count);

	echo '<h2>Unassigned</h2>', $eol;
	echo '<form action="createfleet_exec.php" method="post">', $eol;
	echo '<input type="hidden" name="planet" value="',$planetid,'">', $eol;
	echo '<table>', $eol;
	echo '<tr><th>Design Name</th><th>Count</th><th></th></tr>', $eol;

	if($query->fetch())
	{
		do
		{
			echo '<tr>';
			echo "<td>$shipname</td>";
			echo "<td>$count</td>";
			echo '<td><input type="text" size="4" name="ships[',$designid,']" value="0"></td>';
			echo '</tr>', $eol;
		} while ($query->fetch());
		echo '</table>', $eol;
		echo '<input type="submit" value="Create Fleet">', $eol;
	}
	else
	{
		echo '<tr><td colspan="2">None! Might want to <a href="build_ships.php?planet=',$planetid,'">build</a> some.</td></tr>';
		echo '</table>', $eol;
	}
	echo '</form>', $eol;

	$query = $mysqli->prepare('SELECT fleetid,orderid,systemid,orbit,orderticks FROM fleets LEFT JOIN planets USING (planetid) WHERE fleets.userID = ? AND planetid = ? AND orderid = 1');
	$query->bind_param('ii', $userid, $planetid);
	$query->execute();
	$query->bind_result($fleetid, $orderid, $systemid, $orbit, $orderticks);
	$query->store_result();

	$queryships = $mysqli->prepare('SELECT shipname,count FROM fleets LEFT JOIN fleetships USING (fleetid) LEFT JOIN shipdesigns USING (designid) WHERE fleetid = ?');
	$queryships->bind_param('i', $fleetid);
	$queryships->bind_result($shipname,$count);

	if($query->fetch())
	{
		$querydestinations = $mysqli->prepare('SELECT systemid,orbit,planetid,(ROUND(SQRT(POW(x-?,2)+POW(y-?,2)),2)) AS distance FROM colonies LEFT JOIN planets USING (planetid) LEFT JOIN systems USING (systemid) WHERE userID = ? AND planetid != ? ORDER BY distance ASC');
		$querydestinations->bind_param('iiii', $sysx, $sysy, $userid, $planetid);
		$querydestinations->execute();
		$querydestinations->bind_result($ordersystemid,$orderorbit,$orderplanetid,$orderdistance);

		$destinations = array();
		while ($querydestinations->fetch())
		{
			$destinations[$orderplanetid] = systemcode($ordersystemid, $orderorbit).' ('.number_format($orderdistance,2).' PC)';
		}
		$querydestinations->close();

		echo '<br>', $eol;
		do
		{
			echo '<form action="fleetorder_exec.php" method="post">', $eol;
			echo '<input type="hidden" name="fleet" value="',$fleetid,'">', $eol;
			echo '<h2>',$lookups['order'][$orderid],' ',systemcode($systemid,$orbit),'</h2>', $eol;
			echo '<ul>', $eol;

			$queryships->execute();

			while ($queryships->fetch())
			{
				echo '<li>',$count,' &#215; ',$shipname,'</li>', $eol;
			}
			echo '</ul>', $eol;
			echo 'Order: ';
			echo '<select name="order">', $eol;
			echo '<option value="2" selected>Move</option>', $eol;
			echo '</select><br>', $eol;
			echo 'Destination: ';
			echo '<select name="orderplanet">', $eol;
			if (count($destinations) > 0)
			{
				foreach ($destinations as $orderplanetid => $string)
				{
					echo '<option value="', $orderplanetid, '">', $string, '</option>', $eol;
				}
				echo "</select>\n";
				echo '<input type="submit" value="Dispatch">', $eol;
			}
			else
			{
				echo '<option value="0" selected disabled>No colonies</option>', $eol;
				echo '<input type="submit" value="Dispatch" disabled>', $eol;
				echo '</form>', $eol;
			}
		} while ($query->fetch());
	}

	$query = $mysqli->prepare('SELECT fleetid,orderid,systemid,orbit,orderticks FROM fleets LEFT JOIN planets USING (planetid) WHERE fleets.userID = ? AND planetid = ? AND orderid > 1');
	$query->bind_param('ii', $userid, $planetid);
	$query->execute();
	$query->bind_result($fleetid, $orderid, $systemid, $orbit, $orderticks);
	$query->store_result();

	if($query->fetch())
	{
		echo '<br>', $eol;
		do
		{
			echo '<form action="fleetorder_exec.php" method="post">', $eol;
			echo '<input type="hidden" name="fleet" value="',$fleetid,'">', $eol;
			echo '<h2>',$lookups['order'][$orderid],' ',systemcode($systemid,$orbit),'</h2>', $eol;
			echo formatSeconds('h:i:s',$orderticks*600),'<br>', $eol;
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
}
?>
