<?php
include_once 'includes/start.inc.php';
checkLoggedIn();

include_once 'includes/template.inc.php';
template('Bookmarks List', 'bookmarksListBody');

function bookmarksListBody()
{
	global $eol, $mysqli;
	global $lookups;
	$userid = $_SESSION['userid'];

	$query = $mysqli->prepare('SELECT planetid,systemid,orbit,type,username,planets.metal,planets.deuterium,description FROM bookmarks LEFT JOIN planets USING (planetid) LEFT JOIN colonies USING (planetid) LEFT JOIN users ON colonies.userid=users.userid WHERE bookmarks.userID = ?');
	$query->bind_param('i', $userid);
	$query->execute();
	$query->bind_result($planetid,$systemid,$orbit,$planettype,$colonyusername,$metal,$deuterium,$description);

	echo '<table>', $eol;
	echo '<tr><th>Location</th><th>Planet Type</th><th>Description</th><th>Colonised By</th><th>Metal Abundance</th><th>Deuterium Abundance</th><th>Actions</th></tr>', $eol;

	if ($query->fetch())
	{
		do
		{
			echo '<tr>';
			echo '<td><a href="view_planets.php?system=',$systemid,'">',systemcode($systemid, $orbit),'</a></td>';
			echo '<td><a href="view_planet.php?planet=',$planetid,'"><img src="images/planet',$planettype,'.png" style="width:1em;height:1em;">',$lookups["planetType"][$planettype],'</a></td>';
			echo "<td>$description</td>";
			if ($colonyusername)
			{
				echo '<td>',$colonyusername,'</td>', $eol;
			}
			else
			{
				echo '<td>-</td>', $eol;
			}
			echo '<td>',number_format($metal*100),'%</td>';
			echo '<td>',number_format($deuterium*100),'%</td>';
			echo '<td><a href="deletebookmark_exec.php?planet=', $planetid, '">Delete</a></td>';
			echo '</tr>', $eol;
		} while ($query->fetch());
	}
	else
	{
		echo '<tr><td colspan="7">None. Bookmark planets from their planet details page.</td></tr>';
	}
	echo '</table>', $eol;
}
?>
