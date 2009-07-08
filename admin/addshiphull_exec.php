<?php
include_once 'includes/admin.inc.php';
checkIsAdmin();

$eol = "\n";
header('Content-type: text/plain');

$query = $mysqli->prepare('INSERT INTO shiphulls (hullname,hulldescription,metalcost,size,maxweapons,mindrydock,class) VALUES (?,?,?,?,?,?,?)');
$query->bind_param('ssiiii', $name,$description,$cost,$size,$maxweapons,$mindrydock,$hullclass);
$name = $_POST['name'];
$description = $_POST['description'];
$cost = $_POST['cost'];
$size = $_POST['size'];
$maxweapons = $_POST['maxweapons'];
$mindrydock = $_POST['mindrydock'];
$hullclass = $_POST['hullclass'];

$query->execute();

echo 'Ship Hull \'', $name, '\' added successfully', $eol;
?>
