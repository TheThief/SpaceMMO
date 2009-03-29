<?
include 'includes/admin.inc.php';
checkIsAdmin();

include '../includes/template.inc.php';

template('Admin List Ship Hulls', 'adminListShipHullsBody');

function adminListShipHullsBody()
{
	global $eol, $mysqli;
	$query = $mysqli->prepare('SELECT hullid,hullname,hulldescription,metalcost,size,maxweapons FROM shiphulls ORDER BY hullid');
	if (!$query)
	{
		echo 'error: ', $mysqli->error, $eol;
		exit;
	}

	$result = $query->execute();
	if (!$result)
	{
		echo 'error: ', $query->error, $eol;
		exit;
	}

	$query->bind_result($hullid,$hullname,$hulldescription,$hullcost,$hullsize,$maxweapons);

	echo '<table>', $eol;
	echo '<tr><th>Hull ID</th><th>Name</th><th>Description</th><th>Cost</th><th>Size</th><th>Max Weapons</th><th>Actions</th></tr>', $eol;

	while($query->fetch())
	{
		echo "<tr><td>$hullid</td><td>$hullname</td><td>$hulldescription</td><td>$hullcost</td><td>$hullsize</td><td>$maxweapons</td><td></td></tr>", $eol;
	}
	echo '<form action="addshiphull_exec.php" method="post">', $eol;
	echo '<tr><td></td>', $eol;
	echo '<td><input type="text" name="name" size="10"></td>', $eol;
	echo '<td><textarea name="description" rows="5" cols="35"></textarea></td>', $eol;
	echo '<td><input type="text" name="cost" size="4"></td>', $eol;
	echo '<td><input type="text" name="size" size="4"></td>', $eol;
	echo '<td><input type="text" name="maxweapons" size="4"></td>', $eol;
	echo '<td><input type="submit" value="Add"></td>', $eol;
	echo '</tr>', $eol;
	echo '</form>', $eol;
	echo '</table>', $eol;
}
?>