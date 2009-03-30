<?
include 'includes/start.inc.php';
checkLoggedIn();

include 'includes/template.inc.php';
template('View Solar System', 'viewPlanetsBody');

function viewPlanetsBody()
{
	global $mysqli, $eol;
	global $lookups;
	$userid = $_SESSION['userid'];
	$systemid = $_GET['system'];

	$starsize = 7; // diameter
	$minorbit = 6; // radius
	$planetsize = 2; // diameter
	$orbits = 7;
	$orbitspacing = 2.1;
	$viewsize = ($minorbit + ($orbits - 1) * $orbitspacing + $planetsize/2) * 2;

	echo '<div class="starmap" style="width: ', $viewsize, 'em; height: ', $viewsize, 'em;">', $eol;
	echo '<a href="view_systems.php?system=', $systemid, '"><img class="zoomout" src="images/out.png" alt="Out" title="View surrounding systems"></a>', $eol;

	$stmt = $mysqli->prepare("SELECT x,y FROM systems WHERE systemid=?;");
	$stmt->bind_param('i',$systemid);
	$stmt->execute();
	$stmt->bind_result($systemx,$systemy);
	$stmt->fetch();
	$stmt->close();

	echo '<img src="images/star-large.png" style="width: ',$starsize,'em; height: ',$starsize,'em; position: absolute; left: ', ($viewsize-$starsize)/2, 'em; top: ', ($viewsize-$starsize)/2, 'em;" title="Star of system ',$systemx,', ',$systemy,'">', $eol;

	mt_srand($systemid);

	$stmt = $mysqli->prepare("SELECT planetID,orbit,type,userid FROM planets LEFT JOIN colonies USING (planetid) WHERE systemid=?;");
	$stmt->bind_param('i',$systemid);
	$stmt->execute();
	$stmt->bind_result($planetid,$orbit,$type,$colonyuserid);
	while ($stmt->fetch())
	{
		$image = 'images/planet'.$type.'.png';
		$link = 'view_planet.php?planet='.$planetid;
		$tooltip = $lookups["planetType"][$type].' planet at '.$systemx.', '.$systemy.' : '.$orbit;
		if ($colonyuserid)
		{
			if ($colonyuserid != $userid)
			{
				$image = 'images/planet'.$type.'-oc.png';
				//$link = 'view_planet.php?planet='.$planetid;
				$tooltip = 'Enemy colony detected on this '.$tooltip;
			}
			else
			{
				$image = 'images/planet'.$type.'-c.png';
				$link = 'colony_buildings.php?planet='.$planetid;
				$tooltip = 'Your have a colony on this '.$tooltip;
			}
		}
		else
		{
			$tooltip = 'Uncolonised '.$tooltip;
		}

		//$angle = $systemid*10+$orbit;
		$angle = mt_rand()/mt_getrandmax() * 2*M_PI;
		$x = $viewsize/2 + sin($angle) * ($minorbit + ($orbit - 1) * $orbitspacing) - $planetsize/2;
		$y = $viewsize/2 + cos($angle) * ($minorbit + ($orbit - 1) * $orbitspacing) - $planetsize/2;
		echo '<img src="images/orbit', $orbit, '.png" style="z-index: 0; width: 100%; height: 100%; position: absolute; left: 0; top: 0;">', $eol;
		echo '<a href="', $link, '"><img src="', $image, '" style="z-index: 1; width: ',$planetsize,'em; height: ',$planetsize,'em; position: absolute; left: ', $x, 'em; top: ', $y, 'em;" title="',$tooltip,'"></a>', $eol;
	}
	$stmt->close();
	echo '</div>', $eol;
}
