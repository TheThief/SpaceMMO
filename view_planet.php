<?
include 'includes/start.inc.php';
checkLoggedIn();

include 'includes/template.inc.php';
template('View Planet', 'viewPlanetBody');

function viewPlanetBody()
{
	global $eol, $mysqli;
	global $lookups;
	$userid = $_SESSION['userid'];
	$planetid = $_GET['planet'];

	$stmt = $mysqli->prepare("SELECT orbit,type,planets.metal,planets.deuterium,userid,username FROM planets LEFT JOIN colonies USING (planetid) LEFT JOIN users USING (userid) WHERE planetid=?;");
	$stmt->bind_param('i',$planetid);
	$stmt->execute();
	$stmt->bind_result($orbit,$planettype,$metal,$deuterium,$colonyuserid,$colonyusername);
	$stmt->fetch();
	$stmt->close();

	echo '<h1>View Planet</h1>', $eol;
	echo '<img src="images/planet',$type,'.png" style="width: 20em; height: 20em;">', $eol;

	echo '<table>', $eol;
	echo '<tr><th>Planet Type</th><td>',$lookups["planetType"][$planettype],'</td></tr>', $eol;
	echo '<tr><th>Colonised By</th>';
	if ($colonyuserid)
	{
		echo '<td>',$colonyusername,'</td></tr>', $eol;
	}
	else
	{
		echo '<td>-</td></tr>', $eol;
	}
	echo '<tr><th>Metal Reserves</th><td>',$metal,'</td></tr>', $eol;
	echo '<tr><th>Deuterium Reserves Reserves</th><td>',$deuterium,'</td></tr>', $eol;
	echo '</table>', $eol;
}
