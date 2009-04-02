<?
include 'includes/start.inc.php';
checkLoggedIn();

include 'includes/template.inc.php';
template('View Galaxy', 'viewSystemsBody');

function viewSystemsBody()
{
	global $eol, $mysqli;
	$userid = $_SESSION['userid'];
	$x = $_GET['x'];
	$y = $_GET['y'];

	$viewdistance = 7; // in grid squares at normal zoom
	$minstarsize = 1.2;// in em
	$maxstarsize = 2;  // in em
	$gridsize = 2;     // in em
	$scroll = 3;       // in grid squares

	$zoom = $_GET['zoom']; if (!is_numeric($zoom)) $zoom=1;
	$zoom = clamp($zoom, 0.25, 2);
	$viewdistance = floor($viewdistance/$zoom); // in grid squares
	$minstarsize *= $zoom;
	$maxstarsize *= $zoom;
	$gridsize *= $zoom;
	$scroll = floor($scroll/$zoom);
	$viewsize = ($viewdistance + 1 + $viewdistance) * $gridsize;

	if (!is_numeric($x) || !is_numeric($y))
	{
		$systemid = $_GET['system'];

		if (is_numeric($systemid))
		{
			$query = $mysqli->prepare('SELECT x,y FROM systems WHERE systemid=?');
			if (!$query)
			{
				echo 'error: ', $mysqli->error, $eol;
				exit;
			}

			$query->bind_param('i', $systemid);

			$result = $query->execute();
			if (!$result)
			{
				echo 'error: ', $query->error, $eol;
				exit;
			}

			$query->bind_result($x, $y);
			$query->fetch();
			$query->close();
		}
		else
		{
			$query = $mysqli->prepare('SELECT x,y FROM colonies LEFT JOIN planets USING(planetid) LEFT JOIN systems USING(systemid) WHERE userid=? ORDER BY colonylevel DESC LIMIT 1');
			if (!$query)
			{
				echo 'error: ', $mysqli->error, $eol;
				exit;
			}

			$query->bind_param('i', $userid);

			$result = $query->execute();
			if (!$result)
			{
				echo 'error: ', $query->error, $eol;
				exit;
			}

			$query->bind_result($x, $y);
			$query->fetch();
			$query->close();
		}
	}

	$x = clamp($x, -50, 50);
	$y = clamp($y, -50, 50);

	$stmt = $mysqli->prepare('SELECT systemid,x,y,COUNT(user_colonies.planetid),COUNT(other_colonies.planetid) FROM systems LEFT JOIN planets USING (systemid)
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

	echo '<a href="view_systems.php?x=', $x, '&y=', $y-$scroll, '&zoom=', $zoom, '"><img class="navtop" src="images/up.png" alt="Up"></a>', $eol;
	echo '<a href="view_systems.php?x=', $x, '&y=', $y+$scroll, '&zoom=', $zoom, '"><img class="navbottom" src="images/down.png" alt="Down"></a>', $eol;
	echo '<a href="view_systems.php?x=', $x-$scroll, '&y=', $y, '&zoom=', $zoom, '"><img class="navleft" src="images/left.png" alt="Left"></a>', $eol;
	echo '<a href="view_systems.php?x=', $x+$scroll, '&y=', $y, '&zoom=', $zoom, '"><img class="navright" src="images/right.png" alt="Right"></a>', $eol;
	echo '<a href="view_systems.php?x=', $x, '&y=', $y, '&zoom=', $zoom*2, '"><img class="zoomin" src="images/in.png" alt="In"></a>', $eol;
	echo '<a href="view_systems.php?x=', $x, '&y=', $y, '&zoom=', $zoom/2, '"><img class="zoomout" src="images/out.png" alt="Out"></a>', $eol;

	echo '<div class="starmap" style="width: ', $viewsize, 'em; height: ', $viewsize, 'em; position: absolute; left: 1em; top: 1em; background: url(images/starbg.png) center center;">', $eol;

	while ($stmt->fetch())
	{
		$image = 'images/star'.($systemid%4 +1).'.png';
		$tooltip = 'Not colonised';
		if ($colonies && $othercolonies)
		{
			$image ='images/star-oc+c.png';
			$tooltip = 'Contested System. Your colonies: '.$colonies.' Other colonies: '.$othercolonies;
		}
		else if ($colonies)
		{
			$image = 'images/star-c.png';
			$tooltip = 'Your system. Colonised planets: '.$colonies;
		}
		else if ($othercolonies)
		{
			$image = 'images/star-oc.png';
			$tooltip = 'Enemy system. Colonised planets: '.$othercolonies;
		}
		$starsize = (floor($systemid/4)%4)/4 * ($maxstarsize-$minstarsize) + $minstarsize;
		echo '<a href="view_planets.php?system=', $systemid, '"><img src="', $image, '" style="width: ', $starsize, 'em; height: ', $starsize, 'em; position: absolute; left: ', ($sysX-$xmin+0.5)*$gridsize-$starsize/2, 'em; top: ', ($sysY-$ymin+0.5)*$gridsize-$starsize/2, 'em;" title="',$tooltip,'"></a>', $eol;
	}
	$stmt->close();
	echo '</div>', $eol;
	echo '</div>', $eol;
}
