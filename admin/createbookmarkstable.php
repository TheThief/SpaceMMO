<?php
include_once 'includes/admin.inc.php';
checkIsAdmin();

header('Content-type: text/plain');

// need to log in as power user for this
$mysqli = new mysqli($db_server,$db_admin_user,$db_admin_pass,$db_db);

//$result = $mysqli->query('DROP TABLE bookmarks');
$result = $mysqli->query('CREATE TABLE bookmarks(userID INT NOT NULL, planetID INT NOT NULL, PRIMARY KEY (userid,planetID))');
if ($result)
{
	echo 'table \'bookmarks\' created successfully', $eol;
}
else
{
	echo 'error: ', $mysqli->error, $eol;
}

$result = $mysqli->query('ALTER TABLE bookmarks ADD INDEX planetid (planetID)');
if ($result)
{
	echo 'index \'planetid\' on \'planetid\' created successfully', $eol;
}
else
{
	echo 'error: ', $mysqli->error, $eol;
}

$mysqli->close();
?>
