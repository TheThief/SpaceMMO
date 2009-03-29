<?
include 'includes/admin.inc.php';
checkIsAdmin();

$query = $mysqli->prepare('SELECT userID,username,bisadmin,COUNT(colonies.userid) FROM users LEFT JOIN colonies USING (userid) GROUP BY userid ORDER BY NULL');
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

$query->bind_result($userid,$username,$bisadmin,$colonies);
?>
<html>
<head>
<link rel="stylesheet" type="text/css" href="../style.css">
</head>
<body>

<div>
<table>
<tr><th>User ID</th><th>User Name</th><th>Is Admin</th><th># of Colonies</th>
<th>Actions</th></tr>
<?
while($query->fetch())
{
	?><tr><td><?=$userid?></td><td><?=$username?></td><td><?=$bisadmin?'Yes':''?></td><td><?=$colonies?></td><td><?
	if (!$bisadmin)
	{
		?><a href="makeadmin.php?userid=<?=$userid?>">Make Admin</a><?
	}
	?></td></tr>
<?
}
?>
</table>
</div>

</body>
</html>
