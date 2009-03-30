<?
include "./includes/start.inc.php";
$scale = 5;
$stmt = $mysqli->prepare("SELECT * FROM systems;");
$stmt->execute();
$stmt->bind_result($systemID,$sysX,$sysY);
header("Content-type: image/png");
$img = imagecreatetruecolor(101*$scale,101*$scale);
$syscolour = imagecolorallocate($img,255,255,255);

while($stmt->fetch()){
	//imagesetpixel($img,($sysX*$scale)+50,($sysY*$scale)+50,$syscolour);
	imagefilledellipse($img,($sysX+50.5)*$scale,($sysY+50.5)*$scale,$scale-1,$scale-1,$syscolour);
}
//test


imagepng($img);
imagedestroy($img);
?>
