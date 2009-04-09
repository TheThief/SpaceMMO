<?
include_once 'includes/start.inc.php';
checkLoggedIn();

include_once 'includes/template.inc.php';
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

	echo '<table>', $eol;
	echo '<tr><th>Design Name</th><th>Hull</th><th>Cost</th><th>Size</th><th><span title="Engines/Fuel Bay/Weapons/Shields/Cargo bay">E/F/W/S/C</th><th>Speed</th><th>Fuel Bay</th><th>Range</th><th>Attack</th><th>Defense</th><th>Cargo Capacity</th></tr>', $eol;

	if($query->fetch())
	{
		do
		{
			echo '<tr>';
			echo "<td>$shipname</td>";
			echo "<td>$hullname</td>";
			echo "<td>$metalcost Metal</td>";
			echo "<td>$size</td>";
			echo "<td>$engines/$fuel/$weapons/$shields/$cargo</td>";
			echo '<td>', number_format(($engines*4)/$size *6,2), ' PC/h</td>';
			echo '<td>', number_format($fuel*6), ' D</td>';
			echo '<td>', number_format(($fuel*4)/$size *6,2), ' PC</td>';
			echo '<td>', number_format($weapons), '</td>';
			echo '<td>', number_format(($shields*10)+$size), ' HP</td>';
			echo '<td>', number_format($cargo*10), ' m3</td>';
			echo '</tr>', $eol;
		} while ($query->fetch());
	}
	else
	{
		echo '<tr><td colspan="11">None!?</td></tr>';
	}
	echo '</table>', $eol;
	echo '<a href="addshipdesign_form.php">Add</a> a new design.', $eol;
}
?>
