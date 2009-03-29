<?php
include 'includes/admin.inc.php';
checkIsAdmin();

$eol = "\n";
header('Content-type: text/plain');

// need to log in as power user for this
$mysqli = new mysqli('mysql.dynamicarcade.co.uk',$db_admin_user,$db_admin_pass,$db_db);

//$result = $mysqli->query('DROP TABLE shiphulls');
$result = $mysqli->query('CREATE TABLE shiphulls(hullID INT NOT NULL AUTO_INCREMENT, hullname CHAR(20), hulldescription VARCHAR(256), metalcost INT NOT NULL DEFAULT 0, size INT NOT NULL DEFAULT 0, maxweapons INT NOT NULL DEFAULT 0, PRIMARY KEY (hullID)) ENGINE=INNODB');
if ($result)
{
	echo 'table \'shiphulls\' created successfully', $eol;
}
else
{
	echo 'error: ', $mysqli->error, $eol;
}

//$result = $mysqli->query('ALTER TABLE buildings ADD INDEX planetID (planetID)');
//if ($result)
//{
//	echo 'index on \'planetID\' created successfully', $eol;
//}
//else
//{
//	echo 'error: ', $mysqli->error, $eol;
//}

$mysqli->close();
?>
