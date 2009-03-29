<?php
function updateEffect($effecttype, $planetid)
{
	global $mysqli;
	$query = $mysqli->prepare('SELECT SUM(colony_building_effect(planetid,buildingid)) AS effect FROM (SELECT planetid,buildingid FROM colonybuildings WHERE planetid = ?) dtable LEFT JOIN buildings USING (buildingid) WHERE effecttype=?');
	if (!$query)
	{
		echo 'error: ', $mysqli->error, $eol;
		exit;
	}

	$query->bind_param('ii', $planetid, $effecttype);

	$result = $query->execute();
	if (!$result)
	{
		echo 'error: ', $query->error, $eol;
		exit;
	}

	$query->bind_result($effect);
	$query->fetch();
	$query->close();

	global $lookups;
	$effectname = $lookups["buildingEffectColumn"][$effecttype];
	if ($effecttype >= 5 && $effecttype <= 7)
	{
		$effect += 2000;
	}
	if ($effectype <= 3)
	{
		$query = $mysqli->prepare('SELECT SUM(colony_building_consumes(planetid,buildingid)) AS consumes FROM (SELECT planetid,buildingid FROM colonybuildings WHERE planetid = ?) dtable LEFT JOIN buildings USING (buildingid) WHERE consumestype=?');
		if (!$query)
		{
			echo 'error: ', $mysqli->error, $eol;
			exit;
		}

		$query->bind_param('ii', $planetid, $effecttype);

		$result = $query->execute();
		if (!$result)
		{
			echo 'error: ', $query->error, $eol;
			exit;
		}

		$query->bind_result($consumes);
		$query->fetch();
		$query->close();
		
		$effect -= $consumes;
	}

	$query = $mysqli->prepare('UPDATE colonies SET '.$effectname.' = ? WHERE colonies.planetID = ?');
	if (!$query)
	{
		echo 'error: ', $mysqli->error, $eol;
		exit;
	}

	$query->bind_param('ii', $effect, $planetid);

	$result = $query->execute();
	if (!$result)
	{
		echo 'error: ', $query->error, $eol;
		exit;
	}
	$query->close();
}
?>
