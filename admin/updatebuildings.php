<?include_once 'includes/admin.inc.php';checkIsAdmin();
include_once '../includes/production.inc.php';
$mysqli->autocommit(false);
$query = $mysqli->prepare('SELECT planetid FROM colonies');if (!$query){	echo 'error: ', $mysqli->error, $eol;	exit;}$result = $query->execute();if (!$result){	echo 'error: ', $query->error, $eol;	exit;}$query->bind_result($planetid);
$query->store_result();while($query->fetch())
{	foreach ($lookups["buildingEffectColumn"] as $effecttype => $ignored)	{
		updateEffect($effecttype, $planetid);
	}}

$mysqli->commit();

echo 'update success!', $eol;
?>