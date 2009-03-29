<?
include 'includes/start.inc.php';
checkLoggedIn();

include 'includes/template.inc.php';
template('Ship Designs List', 'designListBody');

function designListBody()
{
	global $eol, $mysqli;
	$userid = $_SESSION['userid'];

	$query = $mysqli->prepare('SELECT designid,shipname,hullname,metalcost,size,engines,fuel,cargo,weapons,shields FROM shipdesigns LEFT JOIN shiphulls USING (hullid) WHERE userID = ? ORDER BY designid ASC;');
	if (!$query)
	{
		echo 'error: ', $mysqli->error, $eol;
		exit;
	}
	$query->bind_param('i', $userid);

	$result = $query->execute();
	if (!$result)
	{
		echo 'error: ', $query->error, $eol;
		exit;
	}

	$query->bind_result($designid,$shipname,$hullname,$metalcost,$size,$engines,$fuel,$cargo,$weapons,$shields);

	echo '<h1>Ship Designs List</h1>', $eol;
	echo '<table>', $eol;
	echo '<tr><th>Design Name</th><th>Hull</th><th>Cost</th><th>Size</th><th>Engines</th><th>Fuel Bay</th><th>Cargo Bay</th><th>Weapons</th><th>Shields</th></tr>', $eol;

	if($query->fetch())
	{
		do
		{
			echo '<tr>';
			echo "<td>$shipname</td>";
			echo "<td>$hullname</td>";
			echo "<td>$metalcost Metal</td>";
			echo "<td>$size</td>";
			echo "<td>$engines</td>";
			echo "<td>$fuel</td>";
			echo "<td>$cargo</td>";
			echo "<td>$weapons</td>";
			echo "<td>$shields</td>";
			echo '</tr>', $eol;
		} while ($query->fetch());
	}
	else
	{
		echo '<tr><td colspan="9">None!?</td></tr>';
	}
	echo '</table>', $eol;
	echo '<a href="addshipdesign_form.php">Add</a> a new design.', $eol;
}
?>
