<?php
include_once 'includes/admin.inc.php';
checkIsAdmin();

$query = $mysqli->prepare('SELECT planetID,orbit,type,planets.metal,planets.deuterium,userid,username FROM planets LEFT JOIN colonies USING (planetid) LEFT JOIN users USING (userid) WHERE systemid=? ORDER BY orbit ASC');
if (!$query)
{
	echo 'error: ', $mysqli->error, $eol;
	exit;
}
$query->bind_param('s', $systemid);
$systemid = $_GET['systemid'];

$result = $query->execute();
if (!$result)
{
	echo 'error: ', $query->error, $eol;
	exit;
}

$query->bind_result($planetid,$orbit,$type,$metal,$deuterium,$userid,$username);
?>
<html>
<head>
<link rel="stylesheet" type="text/css" href="../style.css">
</head>
<body>

<div>
<table>
<tr><th>Planet ID</th><th>Orbit</th><th>Type</th><th>Metal</th><th>Deuterium</th><th>Colonised By</th>
<th>Actions</th></tr>
<?php
while($query->fetch())
{
	?><tr><td><?=$planetid?></td><td><?=$orbit?></td><td><?=$lookups["planetType"][$type]?></td><td><?=round($metal,2)?></td><td><?=round($deuterium,2)?></td><td><?=$username?$username:'-'?></td><td><?php
	if (!$username)
	{
		?><a href="addcolony_exec.php?planet=<?=$planetid?>&userid=<?=$_SESSION['userid']?>">Colonise</a><?php
	}
	else if ($userid == $_SESSION['userid'])
	{
		?><a href="../colony_buildings.php?planet=<?=$planetid?>">View</a><?php
	}
	?></td></tr>
<?php
}
?>
<form action="addplanet_exec.php" method="post">
<tr>
<td><input type="hidden" name="systemid" value="<?=$systemid?>" /></td>
<td><input type="text" name="orbit" /></td>
<td><?htmlDropdown('type','planetType')?></td>
<td><input type="text" name="metal" /></td>
<td><input type="text" name="deuterium" /></td>
<td></td>
<td><input type="submit" value="Add" /></td>
</tr>
</form>
</table>
</div>

</body>
</html>
