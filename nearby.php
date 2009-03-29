<?
include("includes/start.inc.php");

$x=0;
$y=0;
$distance=0;

if (isset($_GET["x"])&& is_numeric($_GET["x"])) $x = $_GET["x"];
if (isset($_GET["y"])&& is_numeric($_GET["y"])) $y = $_GET["y"];
if (isset($_GET["d"])&& is_numeric($_GET["d"])) $distance = $_GET["d"];

// optimize query to do a box check using the position index before doing the circle check
//$stmt = $mysqli->prepare("SELECT * FROM (SELECT *, (ROUND(SQRT(POW(x-?,2)+POW(y-?,2)),2)) AS dist FROM systems) dtable WHERE dist <= ? ORDER BY dist;");
//$stmt->bind_param('iid',$x,$y,$distance);
$stmt = $mysqli->prepare("SELECT * FROM (SELECT *, (ROUND(SQRT(POW(x-?,2)+POW(y-?,2)),2)) AS dist FROM systems WHERE x>=? AND x<=? AND y>=? AND y<=?) dtable WHERE dist <= ?;");
$xmin = $x-$distance;
$xmax = $x+$distance;
$ymin = $y-$distance;
$ymax = $y+$distance;
$stmt->bind_param('iiddddd',$x,$y,$xmin,$xmax,$ymin,$ymax,$distance);
$stmt->execute();
$stmt->bind_result($systemID,$sysX,$sysY,$sysDist);

?>
<html>
<head>
<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>

<div>
<table>
<tr><th>System ID</th><th>Coordinates</th><th>Distance</th></tr>
<?
while($stmt->fetch()){
?>
<tr><td><?=$systemID?></td><td><?=$sysX?>,<?=$sysY?></td><td><?=$sysDist?> 
Units</td></tr>
<?
}
$stmt->close();
?>
</table>
</div>
</html>
