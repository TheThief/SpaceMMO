<?php
include_once 'includes/admin.inc.php';
checkIsAdmin();

$eol = "\n";
header('Content-type: text/plain');

// need to log in as power user for this
$mysqli = new mysqli($db_server,$db_admin_user,$db_admin_pass,$db_db);

//$result = $mysqli->query('DROP TABLE colonies');
$result = $mysqli->query('CREATE TABLE colonies(planetID INT NOT NULL, userID INT NOT NULL, colonylevel INT NOT NULL DEFAULT 1, metal INT NOT NULL DEFAULT 0, deuterium INT NOT NULL DEFAULT 0, energy INT NOT NULL DEFAULT 0, maxmetal INT NOT NULL DEFAULT 2000, maxdeuterium INT NOT NULL DEFAULT 2000, maxenergy INT NOT NULL DEFAULT 2000, metalproduction INT NOT NULL DEFAULT 0, deuteriumproduction INT NOT NULL DEFAULT 0, energyproduction INT NOT NULL DEFAULT 0, shipconstruction INT NOT NULL DEFAULT 0, hp INT NOT NULL DEFAULT 0, maxhp INT NOT NULL DEFAULT 0, PRIMARY KEY (planetID)) ENGINE=INNODB');
if ($result)
{
	echo 'table \'colonies\' created successfully', $eol;
}
else
{
	echo 'error: ', $mysqli->error, $eol;
}

$result = $mysqli->query('ALTER TABLE colonies ADD INDEX userID (userID)');
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

