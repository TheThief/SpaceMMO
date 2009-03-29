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

	$stmt = $mysqli->prepare("SELECT systemid,x,y,orbit,type,planets.metal,planets.deuterium,userid,username FROM planets LEFT JOIN colonies USING (planetid) LEFT JOIN users USING (userid) LEFT JOIN systems USING (systemid) WHERE planetid=?;");
	$stmt->bind_param('i',$planetid);
	$stmt->execute();
	$stmt->bind_result($systemid,$systemx,$systemy,$orbit,$planettype,$metal,$deuterium,$colonyuserid,$colonyusername);
	$stmt->fetch();
	$stmt->close();

	echo '<h1>View Planet</h1>', $eol;
	echo '<img src="images/planet',$planettype,'.png" style="width: 20em; height: 20em;">', $eol;

	echo '<table>', $eol;
	echo '<tr><th>Location</th><td><a href="view_planets.php?system=',$systemid,'">',$systemx,', ',$systemy,' : ',$orbit,'</a></td></tr>';
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
	echo '<tr><th>Metal Reserves</th><td>',number_format($metal*100),'%</td></tr>', $eol;
	echo '<tr><th>Deuterium Reserves</th><td>',number_format($deuterium*100),'%</td></tr>', $eol;
	echo '</table>', $eol;
}
