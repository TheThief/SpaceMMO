<?
include 'includes/start.inc.php';
checkLoggedIn();

include 'includes/colonymenu.inc.php';

include 'includes/template.inc.php';
template('Ships in Orbit', 'viewShipsBody', 'colonyMenu');

function viewShipsBody()
{
	global $eol, $mysqli;
	$userid = $_SESSION['userid'];
	$planetid = $_GET['planet'];

	$query = $mysqli->prepare('SELECT x,y FROM colonies LEFT JOIN planets USING (planetid) LEFT JOIN systems USING (systemid) WHERE userID = ? AND planetID = ?');
	if (!$query)
	{
		echo 'error: ', $mysqli->error, $eol;
		exit;
	}
	$query->bind_param('ii', $userid, $planetid);

	$result = $query->execute();
	if (!$result)
	{
		echo 'error: ', $query->error, $eol;
		exit;
	}

	$query->bind_result($sysx,$sysy);
	$result = $query->fetch();
	if (!$result)
	{
		echo 'Error: You don\'t have a colony on this planet.', $eol;
		exit;
	}
	$query->close();

	$query = $mysqli->prepare('SELECT designid,shipname,count FROM fleets LEFT JOIN fleetships USING (fleetid) LEFT JOIN shipdesigns USING (designid) WHERE fleets.userID = ? AND planetid = ? AND orderid = 0');
	if (!$query)
	{
		echo 'error: ', $mysqli->error, $eol;
		exit;
	}
	$query->bind_param('ii', $userid, $planetid);

	$result = $query->execute();
	if (!$result)
	{
		echo 'error: ', $query->error, $eol;
		exit;
	}

	$query->bind_result($designid,$shipname,$count);

	echo '<form action="fleetorder_exec.php" method="post">', $eol;
	echo '<table>', $eol;
	echo '<tr><th>Design Name</th><th>Count</th><th></th></tr>', $eol;

	if($query->fetch())
	{
		do
		{
			echo '<tr>';
			echo "<td>$shipname</td>";
			echo "<td>$count</td>";
			echo '<td><input type="text" size="4" name="ship',$designid,'" value="0"></td>';
			echo '</tr>', $eol;
		} while ($query->fetch());
	}
	else
	{
		echo '<tr><td colspan="3">None! Might want to <a href="build_ships.php?planet=',$planetid,'">build</a> some.</td></tr>';
	}
	echo '</table>', $eol;

	echo '<br>', $eol;

	echo 'Order: ';
	echo '<select name="order">', $eol;
	echo '<option value="1" selected>Move</option>', $eol;
	echo '</select><br>', $eol;

	$query = $mysqli->prepare('SELECT systemid,orbit,planetid,(ROUND(SQRT(POW(x-?,2)+POW(y-?,2)),2)) AS distance FROM colonies LEFT JOIN planets USING (planetid) LEFT JOIN systems USING (systemid) WHERE userID = ? ORDER BY distance ASC');
	if (!$query)
	{
		echo 'error: ', $mysqli->error, $eol;
		exit;
	}
	$query->bind_param('iii', $sysx, $sysy, $userid);

	$result = $query->execute();
	if (!$result)
	{
		echo 'error: ', $query->error, $eol;
		exit;
	}

	$query->bind_result($ordersystemid,$orderorbit,$orderplanetid,$orderdistance);

	echo 'Destination: ';
	echo '<select name="orderplanet">', $eol;

	if($query->fetch())
	{
		do
		{
			echo '<option value="', $orderplanetid, '">', systemcode($ordersystemid, $orderorbit), '(', format_number($orderdistance,2), ' PC)</option>', $eol;
		} while ($query->fetch());
	}
	else
	{
		echo '<option value="0" selected disabled>No colonies</option>', $eol;
	}
	echo '</select><br>', $eol;
	echo '<input type="submit" value="Dispatch">', $eol;
	echo '</form>', $eol;
}
?>
