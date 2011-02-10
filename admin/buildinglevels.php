<?
include_once 'includes/admin.inc.php';
checkIsAdmin();
include_once '../includes/template.inc.php';
template('Building Levels', 'buildingLevelsBody');

function buildingLevelsBody()
{
	global $eol, $mysqli;
	global $lookups;
	$buildingid = $_GET['building'];

	$query = $mysqli->prepare('SELECT buildingname,maxbuildinglevel FROM buildings WHERE buildingid = ?');
	$query->bind_param('i', $buildingid);
	$query->execute();
	$query->bind_result($buildingname,$maxlevel);
	$query->fetch();
	$query->close();

	echo '<h3>', $buildingname, '</h3>', $eol;
	echo '<table>', $eol;
	echo '<tr><th>Level</th><th>Cost</th><th>Cumlative</th>', $eol;

	$query = $mysqli->prepare('SELECT type FROM effects WHERE buildingid = ?');
	$query->bind_param('i', $buildingid);
	$query->execute();
	$query->bind_result($effecttype);
	while($query->fetch())
	{
		if ($effectbase < 0)
		{
			echo '<th>', $lookups["resourceType"][$effecttype], ' Use</th>';
		}
		else
		{
			echo '<th>', $lookups["buildingEffect"][$effecttype], '</th>';
		}
	}
	echo '</tr>', $eol;
	$query->close();

	$query = $mysqli->prepare('SELECT building_cost(buildingid,?) AS cost FROM buildings WHERE buildingid = ?');
	$query->bind_param('ii', $level, $buildingid);
	$query->bind_result($cost);
	$query2 = $mysqli->prepare('SELECT building_effect2(buildingid,?,type) AS effect FROM effects WHERE buildingid = ?');
	$query2->bind_param('ii', $level, $buildingid);
	$query2->bind_result($effect);

	$tcost = 0;
	for($level=1;$level<=$maxlevel;$level++)
	{
		$result = $query->execute();
		$result = $query->fetch();
		$query->reset();
		$tcost += $cost;
		echo '<tr>';
		echo '<td style="text-align: right">', $level, '</td>';
		echo '<td style="text-align: right">', number_format($cost), '</td>';
		echo '<td style="text-align: right">', number_format($tcost), '</td>';
		$query2->execute();
		while ($query2->fetch())
		{
			echo '<td style="text-align: right">', number_format($effect), '</td>';
		}
		echo '</tr>', $eol;
	}
}
?>