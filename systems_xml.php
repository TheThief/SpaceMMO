<?php
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
    if(($coloniesCount + $othercoloniesCount) > 0){
        $colony = $dom->createElement('Colony');
    	$colony = $colonies->appendChild($colony);
    	$colony->setAttribute("id", $systemid);
    	$colony->setAttribute("x", $sysX);
    	$colony->setAttribute("y", $sysY);
        $colony->setAttribute("own", $coloniesCount);
        $colony->setAttribute("others", $othercoloniesCount);
    }
}
$stmt = $mysqli->prepare('SELECT fleetid,((totalorderticks-orderticks)/totalorderticks)*DISTANCE(s.x,s.y,os.x,os.y) as traveled,s.x as x1,s.y as y2, os.x as x2, os.y as y2, DISTANCE(s.x,s.y,os.x,os.y) as distance, (os.x-s.x)*((totalorderticks-orderticks)/totalorderticks) + s.x as currentx, (os.y-s.y)*((totalorderticks-orderticks)/totalorderticks) + s.y as currenty FROM fleets f LEFT JOIN planets p ON (f.planetID = p.planetID) LEFT JOIN planets op ON (f.orderplanetID = op.planetID) LEFT JOIN systems s ON (s.systemID = p.systemID) LEFT JOIN systems os ON (os.systemID = op.systemID) WHERE orderid > 1 and userid=?');
$stmt->bind_param('i',$userid);
$stmt->execute();
$stmt->bind_result($fleetid,$traveled,$startX,$startY,$endX,$endY,$distance,$currentX,$currentY);
$fleets = $dom->createElement('Fleets');
$fleets = $root->appendChild($fleets);
while ($stmt->fetch()){
	$fleet = $dom->createElement('Fleet');
	$fleet = $fleets->appendChild($fleet);
	$fleet->setAttribute("id", $fleetid);
	$fleet->setAttribute("x1", $startX);
	$fleet->setAttribute("y1", $startY);
	$fleet->setAttribute("x2", $endX);
	$fleet->setAttribute("y2", $endY);
	$fleet->setAttribute("distance", $distance);
	$fleet->setAttribute("traveled", $traveled);
	$fleet->setAttribute("cx", $currentX);
	$fleet->setAttribute("cy", $currentY);
}
header("Content-type: text/xml"); 
echo $dom->saveXML();

?>