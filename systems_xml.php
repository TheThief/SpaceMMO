<?
include_once 'includes/start.inc.php';
checkLoggedIn();
$userid = $_SESSION['userid'];

$dom = new DomDocument('1.0','UTF-8');
$dom->formatOutput = true;

$root = $dom->createElement('Galaxy');
$root = $dom->appendChild($root);
$root->setAttribute("xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");

$systems = $dom->createElement('Systems');
$systems = $root->appendChild($systems);

$stmt = $mysqli->prepare("SELECT * FROM systems;");
$stmt->execute();
$stmt->bind_result($systemID,$sysX,$sysY);

while($stmt->fetch()){
	$system = $dom->createElement('System');
	$system = $systems->appendChild($system);
	$system->setAttribute("id", $systemID);
	$system->setAttribute("x", $sysX);
	$system->setAttribute("iy", $sysY);
}
echo $dom->saveXML();
//var_dump(getWHLinks($userid));
?>