<?php
include 'includes/admin.inc.php';
checkIsAdmin();

$eol = "\n";
header('Content-type: text/plain');

// need to log in as power user for this
$mysqli = new mysqli('mysql.dynamicarcade.co.uk',$db_admin_user,$db_admin_pass,$db_db);

//$result = $mysqli->query('DROP TABLE shipdesigns');
$result = $mysqli->query('CREATE TABLE shipdesigns(designID INT NOT NULL AUTO_INCREMENT, userID INT NOT NULL, hullID INT NOT NULL, shipname CHAR(20), engines INT NOT NULL DEFAULT 0, fuel INT NOT NULL DEFAULT 0, cargo INT NOT NULL DEFAULT 0, weapons INT NOT NULL DEFAULT 0, shields INT NOT NULL DEFAULT 0, PRIMARY KEY (designID)) ENGINE=INNODB');
if ($result)
{
	echo 'table \'shipdesigns\' created successfully', $eol;
}
else
{
	echo 'error: ', $mysqli->error, $eol;
}

$result = $mysqli->query('ALTER TABLE shipdesigns ADD UNIQUE INDEX shipname (userID,shipname)');
if ($result)
{
	echo 'index on \'userID,shipname\' created successfully', $eol;
}
else
{
	echo 'error: ', $mysqli->error, $eol;
}

$mysqli->close();
?>
