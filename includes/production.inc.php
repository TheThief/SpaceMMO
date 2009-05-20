<?php
function updateEffect($effecttype, $planetid)
{
	global $mysqli, $lookups;

	$query = $mysqli->prepare('SELECT SUM(colony_building_effect(planetid,buildingid)) AS effect FROM colonybuildings LEFT JOIN buildings USING (buildingid) WHERE planetid=? AND effecttype=?');
	$query->bind_param('ii', $planetid, $effecttype);
	$query->execute();
	$query->bind_result($effect);
	$result = $query->fetch();
	if (!$result)
	{
		//echo 'error: ', $eol;
		//exit;
	}
	$query->close();

	$effectname = $lookups["buildingEffectColumn"][$effecttype];
	if ($effecttype >= 5 && $effecttype <= 7)
	{
		$effect += 2000;
	}
	if ($effectype <= 3)
	{
		$query = $mysqli->prepare('SELECT SUM(colony_building_consumes(planetid,buildingid)) AS consumes FROM colonybuildings LEFT JOIN buildings USING (buildingid) WHERE planetid=? AND consumestype=?');
		$query->bind_param('ii', $planetid, $effecttype);
		$query->execute();
		$query->bind_result($consumes);
		$result = $query->fetch();
		if (!$result)
		{
			//echo 'error: ', $eol;
			//exit;
		}
		$query->close();

		$effect -= $consumes;
	}

	$query = $mysqli->prepare('UPDATE colonies SET '.$effectname.' = ? WHERE colonies.planetID = ?');
	$query->bind_param('ii', $effect, $planetid);
	$query->execute();
	$query->close();
}
?>
