<?php
include 'includes/admin.inc.php';
checkIsAdmin();

$eol = "\n";
header('Content-type: text/plain');

$query = $mysqli->prepare('INSERT INTO planets (systemid,orbit,type,metal,deuterium) VALUES (?, ?, ?, ?, ?)');
if (!$query)
{
	echo 'error: ', $mysqli->error, $eol;
	exit;
}

$query->bind_param('iiidd', $systemid, $orbit, $type, $metal, $deuterium);
$systemid = $_POST['systemid'];
$orbit = $_POST['orbit'];
$type = $_POST['type'];
$metal = $_POST['metal'];
$deuterium = $_POST['deuterium'];

$result = $query->execute();
if (!$result)
{
	echo 'error: ', $query->error, $eol;
	exit;
}

echo 'Planet \'', $systemid, ',', $orbit, '\' added successfully', $eol;
?>
