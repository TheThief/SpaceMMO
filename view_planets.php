<?
include 'includes/start.inc.php';
checkLoggedIn();

include 'includes/template.inc.php';
template('View Planets', 'viewPlanetsBody');

function viewPlanetsBody()
{
	global $eol, $mysqli;
	$userid = $_SESSION['userid'];
	$systemid = $_GET['system'];

	$starsize = 7; // diameter
	$minorbit = 6; // radius
	$planetsize = 2; // diameter
	$orbits = 7;
	$orbitspacing = 2.1;
	$viewsize = ($minorbit + ($orbits - 1) * $orbitspacing + $planetsize/2) * 2;

	$stmt = $mysqli->prepare("SELECT planetID,orbit,type,userid FROM planets LEFT JOIN colonies USING (planetid) WHERE systemid=?;");
	$stmt->bind_param('i',$systemid);
	$stmt->execute();
	$stmt->bind_result($planetid,$orbit,$type,$colonyuserid);

	echo '<h1>View Planets</h1>', $eol;
	echo '<div class="starmap" style="width: ', $viewsize, 'em; height: ', $viewsize, 'em;">', $eol;
	echo '<img src="images/star-large.png" style="width: ',$starsize,'em; height: ',$starsize,'em; position: absolute; left: ', ($viewsize-$starsize)/2, 'em; top: ', ($viewsize-$starsize)/2, 'em;">', $eol;

	mt_srand($systemid);

	while ($stmt->fetch())
	{
		$image = 'images/planet'.$type.'.png';
		$link = 'view_planet.php?planet='.$planetid;
		if ($colonyuserid)
		{
			if ($colonyuserid != $userid)
			{
				$image = 'images/planet'.$type.'-oc.png';
				//$link = 'view_planet.php?planet='.$planetid;
			}
			else
			{
				$image = 'images/planet'.$type.'-c.png';
				$link = 'colony_buildings.php?planet='.$planetid;
			}
		}

		//$angle = $systemid*10+$orbit;
		$angle = mt_rand()/mt_getrandmax() * 2*M_PI;
		$x = $viewsize/2 + sin($angle) * ($minorbit + ($orbit - 1) * $orbitspacing) - $planetsize/2;
		$y = $viewsize/2 + cos($angle) * ($minorbit + ($orbit - 1) * $orbitspacing) - $planetsize/2;
		echo '<a href="', $link, '"><img src="', $image, '" style="width: ',$planetsize,'em; height: ',$planetsize,'em; position: absolute; left: ', $x, 'em; top: ', $y, 'em;"></a>', $eol;
	}
	$stmt->close();
	echo '</div>', $eol;
}
