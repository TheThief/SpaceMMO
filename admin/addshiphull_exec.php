<?php
include 'includes/admin.inc.php';
checkIsAdmin();

$eol = "\n";
header('Content-type: text/plain');

$query = $mysqli->prepare('INSERT INTO shiphulls (hullname,hulldescription,metalcost,size,maxweapons) VALUES (?,?,?,?,?)');
if (!$query)
{
	echo 'error: ', $mysqli->error, $eol;
	exit;
}

$query->bind_param('ssiii', $name,$description,$cost,$size,$maxweapons);
$name = $_POST['name'];
$description = $_POST['description'];
$cost = $_POST['cost'];
$size = $_POST['size'];
$maxweapons = $_POST['maxweapons'];

$result = $query->execute();
if (!$result)
{
	echo 'error: ', $query->error, $eol;
	exit;
}

echo 'Ship Hull \'', $name, '\' added successfully', $eol;
?>
