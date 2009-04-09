<?php
include_once 'includes/start.inc.php';
checkLoggedIn();

include_once 'includes/production.inc.php';

include_once 'includes/template.inc.php';
template('Alter Building Production', 'alterProductionBody');

function alterProductionBody()
{
	global $eol, $mysqli;
	$userid = $_SESSION['userid'];
	$planetid = $_POST['planet'];
	$buildingid = $_POST['building'];
	$output = $_POST['output'];

	if (!in_array($output,range(0, 100, 10)))
	{
		echo 'Error: Invalid output. Please use 10% steps between 0% and 100%', $eol;
		exit;
	}

	$output = $output/100;

	$query = $mysqli->prepare('SELECT 1 FROM colonies WHERE colonies.userid=? AND colonies.planetID = ?');
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

	$query->bind_result($colony);
	$result = $query->fetch();
	if (!$result)
	{
		echo 'Error: You don\'t have a colony on this planet.', $eol;
		exit;
	}
	$query->close();

	$query = $mysqli->prepare('SELECT consumestype, effecttype FROM colonybuildings LEFT JOIN buildings USING (buildingid) WHERE planetid = ? AND buildingid = ?');
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

	$query->bind_result($consumestype,$effecttype);
	$result = $query->fetch();
	if (!$result)
	{
		echo 'Error: No such building.', $eol;
		exit;
	}
	$query->close();

	if (!$consumestype)
	{
		echo 'Error: Can\'t alter output of this building.', $eol;
		exit;
	}

	$query = $mysqli->prepare('UPDATE colonybuildings SET output=? WHERE planetid = ? AND buildingid = ?');
	if (!$query)
	{
		echo 'error: ', $mysqli->error, $eol;
		exit;
	}

	$query->bind_param('dii', $output, $planetid, $buildingid);

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

	echo 'Production of building \'', $planetid, ',', $buildingid, '\' updated successfully', $eol;

	echo '<a href="colony_buildings.php?planet=', $planetid, '">Return</a> to colony.', $eol;
}
?>
