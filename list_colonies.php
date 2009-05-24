<?
include_once 'includes/start.inc.php';
checkLoggedIn();

include_once 'includes/template.inc.php';
template('Colonies List', 'colonyListBody');

function colonyListBody()
{
	global $eol, $mysqli;
	global $lookups;
	$userid = $_SESSION['userid'];

	$query = $mysqli->prepare('SELECT colonies.planetid,systemid,systems.x,systems.y,planets.orbit,planets.type,colonies.metal,colonies.maxmetal,colonies.metalproduction,colonies.deuterium,colonies.maxdeuterium,colonies.deuteriumproduction,colonies.energy,colonies.maxenergy,colonies.energyproduction FROM colonies LEFT JOIN planets USING (planetid) LEFT JOIN systems USING (systemid) WHERE colonies.userID = ? ORDER BY creationtime ASC;');
	$query->bind_param('i', $userid);
	$query->execute();
	$query->bind_result($planetid,$systemid,$systemx,$systemy,$orbit,$planettype,$metal,$maxmetal,$metalprod,$deuterium,$maxdeuterium,$deuteriumprod,$energy,$maxenergy,$energyprod);

	echo '<table>', $eol;
	echo '<tr><th>Location</th><th>Planet Type</th><th>Metal</th><th>Deuterium</th><th>Energy</th><th>Actions</th></tr>', $eol;

	while($query->fetch())
	{
		echo '<tr>';
		echo '<td><a href="view_planets.php?system=',$systemid,'">',systemcode($systemid, $orbit),'</a></td>';
		echo '<td><a href="view_planet.php?planet=',$planetid,'"><img src="images/planet',$planettype,'.png" style="width:1em;height:1em;">',$lookups["planetType"][$planettype],'</a></td>';
		echo "<td>$metal/$maxmetal (".getSigned($metalprod*TICKS_PH).")</td>";
		echo "<td>$deuterium/$maxdeuterium (".getSigned($deuteriumprod*TICKS_PH).")</td>";
		echo "<td>$energy/$maxenergy (".getSigned($energyprod*TICKS_PH).")</td>";
		if($_SESSION['colony'] != $planetid){
			echo '<td><a href="change_colony.php?planet=', $planetid, '">Change to</a></td>';
		}else{
			echo '<td></td>';
		}
		echo '</tr>', $eol;
	}
	echo '</table>', $eol;
}
?>
