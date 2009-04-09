<?php
include_once 'includes/admin.inc.php';
checkIsAdmin();

$eol = "\n";
header('Content-type: text/plain');

// need to log in as power user for this
$mysqli = new mysqli($db_server,$db_admin_user,$db_admin_pass,$db_db);

//$result = $mysqli->query('DROP TABLE systems');
$result = $mysqli->query('CREATE TABLE systems(systemID INT NOT NULL AUTO_INCREMENT, x INT NOT NULL, y INT NOT NULL, PRIMARY KEY (systemID))');
if ($result)
{
	echo 'table \'systems\' created successfully', $eol;
}
else
{
	echo 'error: ', $mysqli->error, $eol;
}

$result = $mysqli->query('ALTER TABLE systems ADD UNIQUE INDEX position (x,y)');
if ($result)
{
	echo 'index on \'position(x,y)\' created successfully', $eol;
}
else
{
	echo 'error: ', $mysqli->error, $eol;
}

$mysqli->close();
?>
