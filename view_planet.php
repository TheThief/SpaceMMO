<?
include_once 'includes/start.inc.php';
checkLoggedIn();

include_once 'includes/template.inc.php';
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
	
	$stmt = $mysqli->prepare("SELECT * FROM bookmarks WHERE planetid=? and userid=?;");
	$stmt->bind_param('ii',$planetid,$userid);
	$stmt->execute();
	$stmt->store_result();
	$rows = $stmt->num_rows;
	$stmt->close();
	echo '<img src="images/planet',$planettype,'-large.png" style="width: 20em; height: 20em;">', $eol;

	echo '<table>', $eol;
	echo '<tr><th>Location</th><td><a href="view_planets.php?system=',$systemid,'">',systemcode($systemid, $orbit),'</a></td></tr>';
	echo '<tr><th>System Coordinates</th><td><a href="view_systems.php?system=',$systemid,'">',$systemx,', ',$systemy,'</a></td></tr>';
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
	echo '<tr><th>Metal Abundance</th><td>',number_format($metal*100),'%</td></tr>', $eol;
	echo '<tr><th>Deuterium Abundance</th><td>',number_format($deuterium*100),'%</td></tr>', $eol;
	if($rows==0){
		echo '<tr><th>Actions</th><td><a href="addbookmark_exec.php?planet=', $planetid, '">Bookmark</a></td>';
	}else{
		echo '<tr><th>Actions</th><td>Bookmarked</td>';
	}
	echo '</table>', $eol;
}
