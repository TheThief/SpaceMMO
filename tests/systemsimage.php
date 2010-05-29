<?
include_once "./includes/start.inc.php";
$scale = 5;
$stmt = $mysqli->prepare("SELECT * FROM systems;");
$stmt->execute();
$stmt->bind_result($systemID,$sysX,$sysY);
header("Content-type: image/png");
$img = imagecreatetruecolor(UNI_WIDTH*$scale,UNI_HEIGHT*$scale);
$syscolour = imagecolorallocate($img,255,255,255);

while($stmt->fetch()){
	imagefilledellipse($img,($sysX+UNI_CENTRE_X)*$scale,($sysY+UNI_CENTRE_Y)*$scale,$scale-1,$scale-1,$syscolour);
}
//test


imagepng($img);
imagedestroy($img);
?>
