<?php
include_once 'includes/admin.inc.php';
checkIsAdmin();

$eol = "\n";
header('Content-type: text/plain');

$query = $mysqli->prepare('INSERT INTO systems (x, y) VALUES (?, ?)');
if (!$query)
{
	echo 'error: ', $mysqli->error, $eol;
	exit;
}

$query->bind_param('ii', $x, $y);
$x = $_POST['x'];
$y = $_POST['y'];

$result = $query->execute();
if (!$result)
{
	echo 'error: ', $query->error, $eol;
	exit;
}

echo 'System \'', $x, ',', $y, '\' added successfully', $eol;
?>
