<?
include_once 'includes/admin.inc.php';
checkIsAdmin();

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
for ($gx=-50;$gx<=50;$gx++){
    for ($gy=-50;$gy<=50;$gy++){
        $coords[] = array($gx,$gy);
	}
}
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
				$minDeut = 12;
				$maxDeut = 20;
				break;
			case 2:
				$minMetal = 12;
				$maxMetal = 20;
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


//var_dump($systems);
$sid =0;
foreach($systems as $sys){
	$sysid = 0;
	//$mysqli->query("INSERT INTO systems values (NULL," . $sys->x ."," . $sys->y .")");
	//$sysid = $mysqli->insert_id;
	//echo "System ID " .$sid++." (".$sysid.") created @ ".$sys->x.",".$sys->y. "<br>";
	foreach($sys->planets as $plan){
		//$mysqli->query("INSERT INTO planets values (NULL," . $sysid ."," . $plan->orbit .",".$plan->type.",".$plan->metal.",".$plan->deuterium.")");
		//echo "Planet created in $sysid Orbit=".$plan->orbit." Type=" .$plan->type ." M=".$plan->metal." D=".$plan->deuterium."<br>";
	}
}
?>
Done
