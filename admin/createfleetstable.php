<?php
include_once 'includes/admin.inc.php';
checkIsAdmin();

$eol = "\n";
header('Content-type: text/plain');

// need to log in as power user for this
$mysqli = new mysqli($db_server,$db_admin_user,$db_admin_pass,$db_db);

//$result = $mysqli->query('DROP TABLE fleets');
$result = $mysqli->query('CREATE TABLE fleets(fleetid INT NOT NULL AUTO_INCREMENT, userID INT NOT NULL, planetID INT NOT NULL, orderID INT NOT NULL DEFAULT 0, orderplanetID INT NOT NULL, orderticks INT NOT NULL DEFAULT 0, totalorderticks INT NOT NULL DEFAULT 0, breturnorder BOOL NOT NULL DEFAULT FALSE, speed FLOAT NOT NULL DEFAULT 0, totalcargo INT NOT NULL DEFAULT 0, metal INT NOT NULL DEFAULT 0, deuterium INT NOT NULL DEFAULT 0, totalfuelbay INT NOT NULL DEFAULT 0, fuel DECIMAL(11,2) NOT NULL DEFAULT 0, fueluse DECIMAL(8,2) NOT NULL DEFAULT 0, PRIMARY KEY (fleetID)) ENGINE=INNODB');
if ($result)
{
	echo 'table \'fleets\' created successfully', $eol;
}
else
{
	echo 'error: ', $mysqli->error, $eol;
}

$result = $mysqli->query('ALTER TABLE fleets ADD INDEX userID (userID)');
if ($result)
{
	echo 'index on \'userID\' created successfully', $eol;
}
else
{
	echo 'error: ', $mysqli->error, $eol;
}

$mysqli->close();
?>

