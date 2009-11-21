<?
include_once "../includes/start.inc.php";
$scale = 5;
$stmt = $mysqli->prepare("SELECT * FROM systems;");
$stmt->execute();
$stmt->bind_result($systemID,$sysX,$sysY);
//header("Content-type: image/png");
$img = imagecreatetruecolor(101*$scale,101*$scale);
$syscolour = imagecolorallocate($img,255,255,255);
$whcolour = imagecolorallocate($img,0,0,255);
$whlinks = getWHLinks($_SESSION["userid"]);

while($stmt->fetch()){
	//imagesetpixel($img,($sysX*$scale)+50,($sysY*$scale)+50,$syscolour);
	imagefilledellipse($img,($sysX+50.5)*$scale,($sysY+50.5)*$scale,$scale-1,$scale-1,$syscolour);
}
//test
foreach($links as $link){
	$x1 = $link["x"];
	$y1 = $link["y"];
	foreach($link as $endkey => $endvalue){
		if($endkey != "x" && $endkey != "y"){
			$x2 = $endvalue["x"];
			$y2 = $endvalue["y"];
			imageline($img,($x1+50.5)*$scale,($y1+50.5)*$scale,($x2+50.5)*$scale,($y2+50.5)*$scale,$whcolour);
		}
	}
	
}

imagepng($img);
imagedestroy($img);
?>
