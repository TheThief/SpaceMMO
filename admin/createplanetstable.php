<?php
include 'includes/admin.inc.php';
checkIsAdmin();

$eol = "\n";
header('Content-type: text/plain');

// need to log in as power user for this
$mysqli = new mysqli('mysql.dynamicarcade.co.uk',$db_admin_user,$db_admin_pass,$db_db);

//$result = $mysqli->query('DROP TABLE planets');
$result = $mysqli->query('CREATE TABLE planets(planetID INT NOT NULL AUTO_INCREMENT, systemID INT NOT NULL, orbit INT NOT NULL, type TINYINT NOT NULL, metal FLOAT, deuterium FLOAT, PRIMARY KEY (planetID))');
if ($result)
{
	echo 'table \'planets\' created successfully', $eol;
}
else
{
	echo 'error: ', $mysqli->error, $eol;
}

//$result = $mysqli->query('ALTER TABLE planets ADD INDEX systemID (systemID)');
//if ($result)
//{
//	echo 'index on \'systemID\' created successfully', $eol;
//}
//else
//{
//	echo 'error: ', $mysqli->error, $eol;
//}

$result = $mysqli->query('ALTER TABLE planets ADD UNIQUE INDEX orbit (systemID,orbit)');
if ($result)
{
	echo 'index on \'orbit (systemID,orbit)\' created successfully', $eol;
}
else
{
	echo 'error: ', $mysqli->error, $eol;
}

$mysqli->close();
?>
