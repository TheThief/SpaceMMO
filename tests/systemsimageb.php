<?php
include_once "../includes/start.inc.php";
$whlinks = getWHLinks($_SESSION["userid"]);
$scale = 5;
if (isset($_GET["scale"])) $scale = $_GET["scale"];
$stmt = $mysqli->prepare("SELECT * FROM systems;");
$stmt->execute();
$stmt->bind_result($systemID,$sysX,$sysY);
header("Content-type: image/png");
$img = imagecreatetruecolor(UNI_WIDTH*$scale,UNI_HEIGHT*$scale);
$syscolour = imagecolorallocate($img,255,255,255);
$whcolour = imagecolorallocate($img,0,0,255);

while($stmt->fetch()){
	imagefilledellipse($img,($sysX+UNI_CENTRE_X)*$scale,($sysY+UNI_CENTRE_Y)*$scale,$scale-1,$scale-1,$syscolour);
}
//test
foreach($whlinks as $link){
	$x1 = $link["x"];
	$y1 = $link["y"];
	foreach($link as $endkey => $endvalue){
		if($endkey != "x" && $endkey != "y"){
			$x2 = $endvalue["x"];
			$y2 = $endvalue["y"];
			imageline($img,($x1+UNI_CENTRE_X)*$scale,($y1+UNI_CENTRE_Y)*$scale,($x2+UNI_CENTRE_X)*$scale,($y2+UNI_CENTRE_Y)*$scale,$whcolour);
		}
	}
	
}

imagepng($img);
imagedestroy($img);
?>
