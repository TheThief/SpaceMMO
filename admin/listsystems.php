<?
include 'includes/admin.inc.php';
checkIsAdmin();

$query = $mysqli->prepare('SELECT systems.systemid,systems.x,systems.y,COUNT(planets.planetid),COUNT(colonies.planetid) FROM systems LEFT JOIN planets USING (systemid) LEFT JOIN colonies USING (planetid) GROUP BY systemid ORDER BY NULL');
if (!$query)
{
	echo 'error: ', $mysqli->error, $eol;
	exit;
}

$result = $query->execute();
if (!$result)
{
	echo 'error: ', $query->error, $eol;
	exit;
}

$query->bind_result($systemid,$x,$y,$planets,$colonies);
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?><!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">";
?>
<html>
<head>
<title>SpaceMMO - Systems List</title>
<link rel="stylesheet" type="text/css" href="../style.css">
</head>
<body>

<div>
<form action="addsystem_exec.php" method="post">

<table>
<tr><th>System ID</th><th>x</th><th>y</th><th>Planets</th><th>Colonies</th>
<th>Actions</th></tr>
<?
while($query->fetch())
{
	?><tr><td><?=$systemid?></td><td><?=$x?></td><td><?=$y?></td><td><?=$planets?></td><td><?=$colonies ? $colonies : '-'?></td><td>
	<a href="listplanets.php?systemid=<?=$systemid?>">List Planets</a>
	</td></tr>
<?
}
?>
<tr><td>&nbsp;</td>
<td><input type="text" name="x" /></td>
<td><input type="text" name="y" /></td>
<td>&nbsp;</td>
<td><input type="submit" value="Add" /></td>
<td>&nbsp;</td>
</tr>
</table>
</form>
</div>

</body>
</html>
