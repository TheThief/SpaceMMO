<?
include 'includes/start.inc.php';
checkLoggedIn();

include 'includes/template.inc.php';
template('Ships in Orbit', 'viewShipsBody');

function viewShipsBody()
{
	global $eol, $mysqli;
	$userid = $_SESSION['userid'];
	$userid = $_GET['planet'];

	$query = $mysqli->prepare('SELECT count,shipname FROM fleets LEFT JOIN fleetships USING (fleetid) LEFT JOIN shipdesigns USING (designid) WHERE fleets.userID = ? AND planetid = ? AND orderid = 0');
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

	$query->bind_result($count,$shipname);

	echo '<table>', $eol;
	echo '<tr><th>Design Name</th><th>Count</th></tr>', $eol;

	if($query->fetch())
	{
		do
		{
			echo '<tr>';
			echo "<td>$shipname</td>";
			echo "<td>$count</td>";
			echo '</tr>', $eol;
		} while ($query->fetch());
	}
	else
	{
		echo '<tr><td colspan="2">None!</td></tr>';
	}
	echo '</table>', $eol;
	echo '<a href="buiild_ships.php">Build</a> more ships.', $eol;
}
?>
