<?
include_once '../includes/ships.inc.php';
$query = $mysqli->prepare('SELECT designid,size,engines,fuel,cargo,weapons,shields FROM shipdesigns LEFT JOIN shiphulls USING (hullid)');

$updatequery->close();

?>