<?php
include 'includes/start.inc.php';
checkLoggedIn();

include 'includes/production.inc.php';

include 'includes/template.inc.php';
template('Build Building', 'buildBuildingBody');

function buildBuildingBody()
{
	global $eol, $mysqli;
	$userid = $_SESSION['userid'];
	$planetid = $_GET['planet'];
	$buildingid = $_GET['building'];

	$upgrade = $_GET['upgrade'];

	$query = $mysqli->prepare('SELECT metal,colonylevel FROM colonies WHERE colonies.userid=? AND colonies.planetID = ?');
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

	$query->bind_result($metal,$colonylevel);
	$result = $query->fetch();
	if (!$result)
	{
		echo 'Error: You don\'t have a colony on this planet.', $eol;
		exit;
	}
	$query->close();

	$query = $mysqli->prepare('SELECT metalcostbase*POW(metalcostmultiplier,IFNULL(level,0)) AS cost, consumestype, effecttype, level, maxbuildinglevel FROM buildings LEFT JOIN (SELECT buildingid,level FROM colonybuildings WHERE colonybuildings.planetid = ?) dtable USING (buildingid) WHERE buildingid=?');
	if (!$query)
	{
		echo 'error: ', $mysqli->error, $eol;
		exit;
	}

	$query->bind_param('ii', $planetid, $buildingid);

	$result = $query->execute();
	if (!$result)
	{
		echo 'error: ', $query->error, $eol;
		exit;
	}

	$query->bind_result($cost,$consumestype,$effecttype,$level,$maxlevel);
	$result = $query->fetch();
	if (!$result)
	{
		echo 'Error: No such building.', $eol;
		exit;
	}
	if ($cost>$metal)
	{
		echo 'Error: Not enough metal.', $eol;
		exit;
	}
	if ($buildingid!=1 && $level+1>$colonylevel)
	{
		echo 'Error: Colony level too low, no room to upgrade.', $eol;
		exit;
	}
	if ($level+1>$maxlevel)
	{
		echo 'Error: Can\'t upgrade any further.', $eol;
		exit;
	}
	$query->close();

	if ($upgrade)
	{
		$query = $mysqli->prepare('UPDATE colonybuildings SET level=level+1 WHERE planetid = ? AND buildingid = ?');
	}
	else
	{
		$query = $mysqli->prepare('INSERT INTO colonybuildings (planetid,buildingid) VALUES (?, ?)');
	}
	if (!$query)
	{
		echo 'error: ', $mysqli->error, $eol;
		exit;
	}

	$query->bind_param('ii', $planetid, $buildingid);

	$result = $query->execute();
	if (!$result)
	{
		echo 'error: ', $query->error, $eol;
		exit;
	}
	$query->close();

	$query = $mysqli->prepare('UPDATE colonies SET metal = ? WHERE colonies.userid=? AND colonies.planetID = ?');
	if (!$query)
	{
		echo 'error: ', $mysqli->error, $eol;
		exit;
	}

	$query->bind_param('dii', $newmetal, $userid, $planetid);
	$newmetal = $metal - $cost;

	$result = $query->execute();
	if (!$result)
	{
		echo 'error: ', $query->error, $eol;
		exit;
	}
	$query->close();

	if ($effecttype)
	{
		updateEffect($effecttype, $planetid);
	}

	if ($consumestype && $consumestype != $effecttype)
	{
		updateEffect($consumestype, $planetid);
	}

	header('HTTP/1.1 303 See Other');
	header('Location: colony_buildings.php?planet='.$planetid);

	if ($upgrade)
	{
		echo 'Building \'', $planetid, ',', $buildingid, '\' upgraded successfully', $eol;
	}
	else
	{
		echo 'Building \'', $planetid, ',', $buildingid, '\' built successfully', $eol;
	}

	echo '<a href="colony_buildings.php?planet=', $planetid, '">Return</a> to colony.', $eol;
}
?>
