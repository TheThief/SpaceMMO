<?
include_once("../includes/db.inc.php");
include_once("../includes/functions.inc.php");

function getColonies($apikey){
	global $mysqli;
	
	$temparray= array();
	$query = $mysqli->prepare('SELECT userid FROM users WHERE username = ?;');
	if (!$query)
	{
		echo 'error: ', $mysqli->error, $eol;
		exit;
	}
	$query->bind_param('s', $apikey);
	$result = $query->execute();
	if (!$result)
	{
		echo 'error: ', $query->error, $eol;
		exit;
	}

	$query->bind_result($userid);
	$query->fetch();
	$query->close();
	
	$query = $mysqli->prepare('SELECT colonies.planetid,systemid,systems.x,systems.y,planets.orbit,planets.type,colonies.metal,colonies.maxmetal,colonies.metalproduction,colonies.deuterium,colonies.maxdeuterium,colonies.deuteriumproduction,colonies.energy,colonies.maxenergy,colonies.energyproduction FROM colonies LEFT JOIN planets USING (planetid) LEFT JOIN systems USING (systemid) WHERE colonies.userID = ? ORDER BY colonylevel DESC;');
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
	
	$query->bind_result($planetid,$systemid,$systemx,$systemy,$orbit,$planettype,$metal,$maxmetal,$metalprod,$deuterium,$maxdeuterium,$deuteriumprod,$energy,$maxenergy,$energyprod);
	while($query->fetch())
	{
		$temparray[]=array(	"colonyid"=>$planetid,
							"systemid"=>$systemid,
							"systemx"=>$systemx,
							"systemy"=>$systemy,
							"orbit"=>$orbit,
							"systemcode"=>systemcode($systemid, $orbit),
							"planettype"=>$planettype,
							"metal"=>$metal,
							"maxmetal"=>$maxmetal,
							"metalprod"=>$metalprod,
							"deuterium"=>$deuterium,
							"maxdeuterium"=>$maxdeuterium,
							"deuteriumprod"=>$deuteriumprod,
							"energy"=>$energy,
							"maxenergy"=>$maxenergy,
							"energyprod"=>$energyprod
							);
	}
	return $temparray;
}

function getPlanetType($type){
	global $lookups;
	return $lookups["planetType"][$type];
}
?>