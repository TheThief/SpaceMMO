<?php
include_once 'includes/admin.inc.php';
checkIsAdmin();

$eol = "\n";
header('Content-type: text/plain');

$query = $mysqli->prepare('INSERT INTO buildings (buildingname,mincolonylevel,maxbuildinglevel,buildingdescription,metalcostbase,metalcostlinear,metalcostmultiplier,consumestype,consumesbase,consumeslinear,consumesmultiplier,effecttype,effectbase,effectlinear,effectmultiplier,multiplybyplanet,bignorecolonylevel) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
if (!$query)
{
	echo 'error: ', $mysqli->error, $eol;
	exit;
}

$query->bind_param('siisiddiiddiiddi', $name, $mincolonylevel, $maxlevel, $description, $costbase, $costlinear, $costmult, $consumestype, $consumesbase, $consumeslinear, $consumesmult, $effecttype, $effectbase, $effectlinear, $effectmult, $multiplybyplanet,$bignorecolonylevel);
$name = $_POST['name'];
$mincolonylevel = $_POST['mincolonylevel'];
$maxlevel = $_POST['maxlevel'];
$description = $_POST['description'];
$costbase = $_POST['costbase'];
$costlinear = $_POST['costlinear'];
$costmult = $_POST['costmult'];
$consumestype = $_POST['consumestype'];
$consumesbase = $_POST['consumesbase'];
$consumeslinear = $_POST['consumeslinear'];
$consumesmult = $_POST['consumesmult'];
$effecttype = $_POST['effecttype'];
$effectbase = $_POST['effectbase'];
$effectlinear = $_POST['effectlinear'];
$effectmult = $_POST['effectmult'];
$multiplybyplanet = (isset($_POST['multiplybyplanet']))?$_POST['multiplybyplanet']:0;
$bignorecolonylevel = (isset($_POST['bignorecolonylevel']))?$_POST['bignorecolonylevel']:0;

$result = $query->execute();
if (!$result)
{
	echo 'error: ', $query->error, $eol;
	exit;
}

echo 'Building \'', $name, '\' added successfully', $eol;
?>
