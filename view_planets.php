<?php
include_once 'includes/start.inc.php';
checkLoggedIn();

include_once 'includes/template.inc.php';
template('View Solar System', 'viewPlanetsBody');

function viewPlanetsBody()
{
	global $mysqli, $eol;
	global $lookups;
	$userid = $_SESSION['userid'];
	$systemid = $_GET['system'];
	$colonyid = $_SESSION['colony'];

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

	mt_srand($systemid);

	$stmt = $mysqli->prepare("SELECT planetID,orbit,type,userid FROM planets LEFT JOIN colonies USING (planetid) WHERE systemid=?;");
	$stmt->bind_param('i',$systemid);
	$stmt->execute();
	$stmt->bind_result($planetid,$orbit,$type,$colonyuserid);
	while ($stmt->fetch())
	{
		$image = 'images/planet'.$type.'.png';
		$image2 = null;
		$link = 'view_planet.php?planet='.$planetid;
		$tooltip = $lookups["planetType"][$type].' planet '.systemcode($systemid,$orbit);
		if ($colonyid == $planetid)
		{
			$image2 = 'images/star-cc.png';
			$tooltip = 'Current Colony, on '.$tooltip;
		}
		else if ($colonyuserid)
		{
			if ($colonyuserid != $userid)
			{
				$image2 = 'images/star-oc.png';
				$tooltip = 'Enemy colony detected on '.$tooltip;
			}
			else
			{
				$image2 = 'images/star-c.png';
				$tooltip = 'Your colony on '.$tooltip;
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
		echo '<a href="', $link, '">';
		echo '<img src="', $image, '" style="z-index: 1; width: ',$planetsize,'em; height: ',$planetsize,'em; position: absolute; left: ', $x, 'em; top: ', $y, 'em;" title="',$tooltip,'">';
		if ($image2)
		{
			echo '<img src="', $image2, '" style="z-index: 2; width: ',$planetsize,'em; height: ',$planetsize,'em; position: absolute; left: ', $x, 'em; top: ', $y, 'em;" title="',$tooltip,'">';
		}
		echo '</a>', $eol;
	}
	$stmt->close();

	$image = 'images/star-large'.($systemid%4 +1).'.png';
	echo '<img src="',$image,'" style="width: ',$starsize,'em; height: ',$starsize,'em; position: absolute; left: ', ($viewsize-$starsize)/2, 'em; top: ', ($viewsize-$starsize)/2, 'em;" title="Star of system ',$systemx,', ',$systemy,'">', $eol;

	echo '</div>', $eol;
}
