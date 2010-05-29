<?
include_once 'includes/start.inc.php';
checkLoggedIn();

include_once 'includes/template.inc.php';
template('View Galaxy', 'viewSystemsBody');

function viewSystemsBody()
{
	global $eol, $mysqli;
	$userid = $_SESSION['userid'];
	$x = $_GET['x'];
	$y = $_GET['y'];

	$viewsize = 30; // in em
	$minstarsize = 1.2;// in em
	$maxstarsize = 2;  // in em
	$gridsize = 2;     // in em
	$scroll = 3;       // in grid squares

	$zoom = $_GET['zoom']; if (!is_numeric($zoom)) $zoom=1;
	$zoom = clamp($zoom, 0.0625, 2);
	$gridsize *= $zoom;
	$viewdistance = floor(($viewsize/$gridsize - 1) / 2); // in grid squares
	$indent = ($viewsize - ($viewdistance * 2 + 1) * $gridsize) / 2; // in em
	$minstarsize *= $zoom;
	$maxstarsize *= $zoom;
	$scroll = floor($scroll/$zoom);

	if (!is_numeric($x) || !is_numeric($y))
	{
		$systemid = $_GET['system'];

		if (is_numeric($systemid))
		{
			$query = $mysqli->prepare('SELECT x,y FROM systemsb WHERE systemid=?');
			$query->bind_param('i', $systemid);
			$query->execute();
			$query->bind_result($x, $y);
			$result = $query->fetch();
			if (!$result)
			{
				echo 'error: system id not valid.', $eol;
				exit;
			}
			$query->close();
		}
		else
		{
			$query = $mysqli->prepare('SELECT x,y FROM colonies LEFT JOIN planets USING(planetid) LEFT JOIN systemsb USING(systemid) WHERE userid=? ORDER BY colonylevel DESC LIMIT 1');
			$query->bind_param('i', $userid);
			$query->execute();
			$query->bind_result($x, $y);
			$result = $query->fetch();
			if (!$result)
			{
				echo 'error: You have no colonies!?', $eol;
				exit;
			}

			$query->close();
		}
	}

	$x = clamp($x, -50, 50);
	$y = clamp($y, -50, 50);

	$colonyid = $_SESSION['colony'];
	$query = $mysqli->prepare('SELECT systemid,x,y FROM planets LEFT JOIN systemsb USING(systemid) WHERE planetid=?');
	$query->bind_param('i', $colonyid);
	$query->execute();
	$query->bind_result($colonysystemid, $colonyx, $colonyy);
	$result = $query->fetch();
	if (!$result)
	{
		echo 'error: current colony not valid!?', $eol;
		exit;
	}
	$query->close();

	$stmt = $mysqli->prepare('SELECT systemid,x,y,COUNT(user_colonies.planetid),COUNT(other_colonies.planetid) FROM systemsb LEFT JOIN planets USING (systemid)
	LEFT JOIN (SELECT planetid FROM colonies WHERE userid=?) user_colonies USING (planetid)
	LEFT JOIN (SELECT planetid FROM colonies WHERE userid!=?) other_colonies USING (planetid)
	WHERE x>=? AND x<=? AND y>=? AND y<=? GROUP BY systemid ORDER BY NULL');
	$xmin = $x-$viewdistance;
	$xmax = $x+$viewdistance;
	$ymin = $y-$viewdistance;
	$ymax = $y+$viewdistance;
	$stmt->bind_param('iiiiii',$userid,$userid,$xmin,$xmax,$ymin,$ymax);
	$stmt->execute();
	$stmt->bind_result($systemid,$sysX,$sysY,$colonies,$othercolonies);

	echo '<div class="starmap" style="width: ', $viewsize+2, 'em; height: ', $viewsize+2, 'em;">', $eol;

	echo '<a href="view_systemsb.php?x=', $x, '&y=', $y-$scroll, '&zoom=', $zoom, '"><img class="navtop" src="images/up.png" alt="Up"></a>', $eol;
	echo '<a href="view_systemsb.php?x=', $x, '&y=', $y+$scroll, '&zoom=', $zoom, '"><img class="navbottom" src="images/down.png" alt="Down"></a>', $eol;
	echo '<a href="view_systemsb.php?x=', $x-$scroll, '&y=', $y, '&zoom=', $zoom, '"><img class="navleft" src="images/left.png" alt="Left"></a>', $eol;
	echo '<a href="view_systemsb.php?x=', $x+$scroll, '&y=', $y, '&zoom=', $zoom, '"><img class="navright" src="images/right.png" alt="Right"></a>', $eol;
	echo '<a href="view_systemsb.php?x=', $x, '&y=', $y, '&zoom=', $zoom*2, '"><img class="zoomin" src="images/in.png" alt="In"></a>', $eol;
	echo '<a href="view_systemsb.php?x=', $x, '&y=', $y, '&zoom=', $zoom/2, '"><img class="zoomout" src="images/out.png" alt="Out"></a>', $eol;

	echo '<div class="starmap" style="width: ', $viewsize, 'em; height: ', $viewsize, 'em; position: absolute; left: 1em; top: 1em; background: url(images/starbg.png) center center;">', $eol;

	while ($stmt->fetch())
	{
		$image = 'images/star'.($systemid%4 +1).'.png';
		$image2 = null;
		$syscode = systemcode($systemid);
		$tooltip = $syscode .' Not colonised';
		if ($systemid == $colonysystemid)
		{
			if ($othercolonies)
			{
				$image2 ='images/star-oc+cc.png';
				$tooltip = $syscode .' Current System (Contested). Your colonies: '.$colonies.' Other colonies: '.$othercolonies;
			}
			else
			{
				$image2 = 'images/star-c.png';
				$tooltip = $syscode .' Current system. Colonised planets: '.$colonies;
			}
		}
		else if ($colonies && $othercolonies)
		{
			$image2 ='images/star-oc+c.png';
			$tooltip = $syscode .' Contested System. Your colonies: '.$colonies.' Other colonies: '.$othercolonies;
		}
		else if ($colonies)
		{
			$image2 = 'images/star-c.png';
			$tooltip = $syscode .' Your system. Colonised planets: '.$colonies;
		}
		else if ($othercolonies)
		{
			$image2 = 'images/star-oc.png';
			$tooltip = $syscode .' Enemy system. Colonised planets: '.$othercolonies;
		}

		if ($systemid != $colonysystemid)
		{
			$tooltip .= ' Distance: '.number_format(distance($sysX-$colonyx, $sysY-$colonyy), 2).'PC';
		}

		$starsize = (floor($systemid/4)%4)/4 * ($maxstarsize-$minstarsize) + $minstarsize;
		echo '<a href="view_planets.php?system=', $systemid, '">', $eol;
		echo '<img src="', $image, '" style="width: ', $starsize, 'em; height: ', $starsize, 'em; left: ', ($sysX-$xmin+0.5)*$gridsize-$starsize/2 + $indent, 'em; top: ', ($sysY-$ymin+0.5)*$gridsize-$starsize/2 + $indent, 'em;" title="',$tooltip,'">', $eol;
		if ($image2)
		{
			echo '<img src="', $image2, '" style="width: ', $gridsize, 'em; height: ', $gridsize, 'em; left: ', ($sysX-$xmin)*$gridsize + $indent, 'em; top: ', ($sysY-$ymin)*$gridsize + $indent, 'em;" title="',$tooltip,'">', $eol;
		}
		echo '</a>', $eol;
	}
	$stmt->close();
	echo '</div>', $eol;
	echo '</div>', $eol;
}
