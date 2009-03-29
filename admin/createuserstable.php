<?php
include 'includes/admin.inc.php';
checkIsAdmin();

$eol = "\n";
header('Content-type: text/plain');

// need to log in as power user for this
$mysqli = new mysqli('mysql.dynamicarcade.co.uk',$db_admin_user,$db_admin_pass,$db_db);

//$result = $mysqli->query('DROP TABLE users');
$result = $mysqli->query('CREATE TABLE users(userID INT NOT NULL AUTO_INCREMENT, lastlogin DATETIME NULL, username CHAR(20) NOT NULL UNIQUE, passhash BINARY(20) NOT NULL, bisadmin BOOL NOT NULL, phpsessionid BINARY(20) NULL, PRIMARY KEY (userID))');
if ($result)
{
	echo 'table \'users\' created successfully', $eol;
}
else
{
	echo 'error: ', $mysqli->error, $eol;
}

$result = $mysqli->query('ALTER TABLE users ADD UNIQUE INDEX username(username)');
if ($result)
{
	echo 'index on \'username\' created successfully', $eol;
}
else
{
	echo 'error: ', $mysqli->error, $eol;
}

$mysqli->close();
?>
