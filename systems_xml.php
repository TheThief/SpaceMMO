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

$stmt = $mysqli->prepare("SELECT * FROM systems ORDER BY systemID;");
$stmt->execute();
$stmt->bind_result($systemID,$sysX,$sysY);

while($stmt->fetch()){
	$system = $dom->createElement('System');
	$system = $systems->appendChild($system);
	$system->setAttribute("id", $systemID);
	$system->setAttribute("x", $sysX);
	$system->setAttribute("y", $sysY);
}
$wormholes = $dom->createElement('Wormholes');
$wormholes = $root->appendChild($wormholes);

$wholes = getWHLinks($userid);
foreach($wholes as $systemID => $info){
	$startX = $info["x"];
	$startY = $info["y"];
	foreach($info as $destID => $dest){
		if($destID != "x" && $destID != "y"){
			$wh = $dom->createElement('Wormhole');
			$wh = $wormholes->appendChild($wh);
			$wh->setAttribute("id", $systemID);
			$wh->setAttribute("x1", $startX);
			$wh->setAttribute("y1", $startY);
			$wh->setAttribute("destID", $destID);
			$wh->setAttribute("x2", $dest["x"]);
			$wh->setAttribute("y2", $dest["y"]);
		}
	}
}
$stmt = $mysqli->prepare('SELECT systemid,x,y,COUNT(user_colonies.planetid),COUNT(other_colonies.planetid) FROM systems LEFT JOIN planets USING (systemid)
LEFT JOIN (SELECT planetid FROM colonies WHERE userid=?) user_colonies USING (planetid)
LEFT JOIN (SELECT planetid FROM colonies WHERE userid!=?) other_colonies USING (planetid) GROUP BY systemid ORDER BY NULL');
$stmt->bind_param('ii',$userid,$userid);
$stmt->execute();
$stmt->bind_result($systemid,$sysX,$sysY,$coloniesCount,$othercoloniesCount);
$colonies = $dom->createElement('Colonies');
$colonies = $root->appendChild($colonies);
while ($stmt->fetch()){
    $colony = $dom->createElement('Colony');
	$colony = $colonies->appendChild($colony);
	$colony->setAttribute("id", $systemID);
	$colony->setAttribute("x", $sysX);
	$colony->setAttribute("y", $sysY);
    $colony->setAttribute("own", $coloniesCount);
    $colony->setAttribute("others", $othercoloniesCount);
}
   
header("Content-type: text/xml"); 
echo $dom->saveXML();

?>