<?
include_once '../includes/production.inc.php';
$mysqli->autocommit(false);
$query = $mysqli->prepare('SELECT planetid FROM colonies');
$query->store_result();
{
		updateEffect($effecttype, $planetid);
	}

$mysqli->commit();

echo 'update success!', $eol;
?>