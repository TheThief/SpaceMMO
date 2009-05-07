<?include_once 'includes/admin.inc.php';checkIsAdmin();
include_once '../includes/ships.inc.php';$mysqli->autocommit(false);$updatequery = $mysqli->prepare('UPDATE shipdesigns SET speed=?,fuelcapacity=?,fueluse=?,cargocapacity=?,defense=? WHERE designid=?');$updatequery->bind_param('didiii', $speed, $fuelcapacity, $fueluse, $cargocapacity, $defense, $designid);
$query = $mysqli->prepare('SELECT designid,size,engines,fuel,cargo,weapons,shields FROM shipdesigns LEFT JOIN shiphulls USING (hullid)');$query->bind_result($designid, $size, $engines, $fuel, $cargo, $weapons, $shields);$query->execute();$query->store_result();while ($query->fetch()){	$speed = speed($size, $engines);	$fuelcapacity = fuelCapacity($fuel);	$fueluse = fuelUse($size, $engines)/SMALLTICKS_PH;	$cargocapacity = cargoCapacity($cargo);	$defense = defense($size, $shields);	$updatequery->execute();}

$mysqli->commit();

echo 'update success!', $eol;
?>