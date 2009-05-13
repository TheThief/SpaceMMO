<?include_once 'includes/admin.inc.php';checkIsAdmin();
include_once '../includes/template.inc.php';template('Building Levels', 'buildingLevelsBody');function buildingLevelsBody(){	global $eol, $mysqli;	global $lookups;	$buildingid = $_GET['building'];	$query = $mysqli->prepare('SELECT buildingname,maxbuildinglevel,consumestype,effecttype FROM buildings WHERE buildingid = ?');	if (!$query)	{		echo 'error: ', $mysqli->error, $eol;		exit;	}	$query->bind_param('i', $buildingid);	$result = $query->execute();	if (!$result)	{		echo 'error: ', $query->error, $eol;		exit;	}	$query->bind_result($buildingname,$maxlevel,$consumestype,$effecttype);
	$bResult = $query->fetch();
	if (!$result)	{		echo 'error: ', $query->error, $eol;		exit;	}

	$query->close();	echo '<h3>', $buildingname, '</h3>', $eol;
	echo '<table>', $eol;	echo '<tr><th>Level</th><th>Cost</th><th>Cumlative</th>', $eol;
	if ($consumestype)
	{
		echo '<th>', $lookups["resourceType"][$consumestype], ' Use</th>';
	}
	if ($effecttype)
	{
		echo '<th>', $lookups["buildingEffect"][$effecttype], '</th>';
	}
	echo '</tr>', $eol;	$query = $mysqli->prepare('SELECT building_cost(buildingid,?) AS cost, building_consumes(buildingid,?) AS consumes, building_effect(buildingid,?) AS effect FROM buildings WHERE buildingid = ?');	if (!$query)	{		echo 'error in prepare: ', $mysqli->error, $eol;		exit;	}	$query->bind_param('iiii', $level, $level, $level, $buildingid);
	$query->bind_result($cost,$consumes,$effect);
	$tcost = 0;
	for($level=1;$level<=$maxlevel;$level++)
	{
		$result = $query->execute();
		if (!$result)		{			echo 'error in execute: ', $query->error, $eol;			exit;		}		$result = $query->fetch();		if (!$result)		{			echo 'error in fetch: ', $query->error, $eol;			exit;		}		$tcost += $cost;
		echo '<tr>';
		echo '<td style="text-align: right">', $level, '</td>';
		echo '<td style="text-align: right">', number_format($cost), '</td>';		echo '<td style="text-align: right">', number_format($tcost), '</td>';
		if ($consumestype)
		{
			echo '<td style="text-align: right">', number_format($consumes), '</td>';
		}
		if ($effecttype)
		{
			echo '<td style="text-align: right">', number_format($effect), '</td>';
		}
		echo '</tr>', $eol;
	}}?>