<?php
//include 'includes/admin.inc.php';
$scale = 1;
$tcount = (isset($_GET["count"]))?$_GET["count"]:0;
if (!$tcount) header("Content-type: image/png");
$imgmask = imagecreatefrompng('mask.png');
$white = imagecolorallocate($imgmask,255,255,255);
$black = imagecolorallocate($imgmask,0,0,0);
$img = imagecreatetruecolor(imagesx($imgmask)*$scale,imagesy($imgmask)*$scale);
$syscolour = imagecolorallocate($img,255,255,255);

if ($tcount)
{
	echo $white, "\n";
	echo $black, "\n";
}

//checkIsAdmin();

function distanceToZero($x, $y)
{
    return sqrt($x*$x + $y*$y);
}

function cmp($a, $b)
{
	$ad = distanceToZero($a->x,$a->y);
	$bd = distanceToZero($b->x,$b->y);
    if ($ad == $bd) {
        return 0;
    }
    return ($ad < $bd) ? -1 : 1;
}

class Planet {
	public $orbit;
	public $type;
	public $metal;
	public $deuterium;
	
	function Planet($porb,$ptype,$pmet,$pdeut){
		$this->orbit = $porb;
		$this->type = $ptype;
		$this->metal = $pmet;
		$this->deuterium = $pdeut;
	}
}

class System {
	public $x;
	public $y;
	public $planets = array();

	function System($sysX,$sysY,$sysPlanets){
		$this->x = $sysX;
		$this->y = $sysY;
		$this->planets = $sysPlanets;
	}
}
$systems = array();
/*$plantest = array();
$plantestb = array();

$plantest[] = new Planet(3,2,0.4,1.2);
$plantest[] = new Planet(1,3,5,0.2);
$plantestb[] = new Planet(1,1,0.3,0.2);
$plantestb[] = new Planet(5,2,0.2,1);
$plantestb[] = new Planet(7,3,2,2);
$systems[] = new System(5,3,$plantest);
$systems[] = new System(7,3,$plantestb);
*/

$coords = array();
$count =0;
$width = imagesx($imgmask); $mx = floor($width/2);
$height = imagesy($imgmask); $my = floor($height/2);
for ($gx=0;$gx<$width;$gx++){
	for ($gy=0;$gy<$height;$gy++){
		if(imagecolorat($imgmask,$gx,$gy)>=$white/2) {
			$count++;
			$coords[] = array($gx-$mx,$gy-$my);
		}
		//;
	}
}

if ($tcount) echo $count;


shuffle($coords);
$totPlan =0;
for($i=0;$i<1000;$i++){
	$planets = array();
	$numPlanets = mt_rand(2,5);
	$minMetal=0;
	$maxMetal=0;
	$minDeut=0;
	$maxDeut=0;
	$orblist = range(1,7);
	shuffle($orblist);
	$sysco = array_pop($coords);
	for($p=0;$p<$numPlanets;$p++){
		$type = mt_rand(1,3);
		switch($type){
			case 1:
				$minMetal = 95;
				$maxMetal = 110;
				$minDeut = 2;
				$maxDeut = 10;
				break;
			case 2:
				$minMetal = 2;
				$maxMetal = 10;
				$minDeut = 95;
				$maxDeut = 110;
				break;
			case 3:
				$minMetal = 50;
				$maxMetal = 70;
				$minDeut = 50;
				$maxDeut = 70;
				break;
		}
		$metal = mt_rand($minMetal,$maxMetal);
		$deut = mt_rand($minDeut,$maxDeut);
		$metal = $metal/100;
		$deut = $deut/100;
		$orbit = array_pop($orblist);
		$totPlan++;
		//echo "@".$sysco[0].",".$sysco[1]." ($i,$totPlan,$p) Orbit = $orbit Type = " . $type ." M=" . $metal . " D=" . $deut . "<br>";
		$planets[] = new Planet($orbit,$type,$metal,$deut);
	}
	$systems[] = new System($sysco[0],$sysco[1],$planets);
}

usort($systems,cmp);
//if ($tcount) var_dump($systems);

foreach($systems as $sys){
imagesetpixel($img,($sys->x+$mx)*$scale,($sys->y+$my)*$scale,$syscolour);
}

if (!$tcount) imagepng($img);
if (!$tcount) imagedestroy($img);
?>
