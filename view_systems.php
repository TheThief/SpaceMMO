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
	$viewdistance = 10;
	$zoom = $_GET['zoom']; if (!is_numeric($zoom)) $zoom=1;
	$zoom = clamp($zoom, 0.2, 2);
	$starsize = $zoom * 2;
	$viewsize = ($viewdistance*2 + $starsize + $viewdistance*2);
	$distance = $viewdistance/$zoom;
	$scroll = 3;

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
	$xmin = $x-$distance;
	$xmax = $x+$distance;
	$ymin = $y-$distance;
	$ymax = $y+$distance;
	$stmt->bind_param('iiiiii',$userid,$userid,$xmin,$xmax,$ymin,$ymax);
	$stmt->execute();
	$stmt->bind_result($systemid,$sysX,$sysY,$colonies,$othercolonies);

	echo '<h1>View Galaxy</h1>', $eol;
	echo '<div class="starmap" style="width: ', $viewsize+2, 'em; height: ', $viewsize+2, 'em;">', $eol;

	echo '<div class="navtop"><a href="view_systems.php?x=', $x, '&y=', $y-$scroll, '&zoom=', $zoom, '"><img src="images/up.png" alt="Up" style="width:100%;height: 100%;"></a></div>', $eol;
	echo '<div class="navbottom"><a href="view_systems.php?x=', $x, '&y=', $y+$scroll, '&zoom=', $zoom, '"><img src="images/down.png" alt="Down" style="width:100%;height: 100%;"></a></div>', $eol;
	echo '<div class="navleft"><a href="view_systems.php?x=', $x-$scroll, '&y=', $y, '&zoom=', $zoom, '"><img src="images/left.png" alt="Left" style="width:100%;height: 100%;"></a></div>', $eol;
	echo '<div class="navright"><a href="view_systems.php?x=', $x+$scroll, '&y=', $y, '&zoom=', $zoom, '"><img src="images/right.png" alt="Right" style="width:100%;height: 100%;"></a></div>', $eol;
	echo '<div class="zoomin"><a href="view_systems.php?x=', $x, '&y=', $y, '&zoom=', $zoom*2, '">In</a></div>', $eol;
	echo '<div class="zoomout"><a href="view_systems.php?x=', $x, '&y=', $y, '&zoom=', $zoom/2, '">Out</a></div>', $eol;

	echo '<div class="starmap" style="width: ', $viewsize, 'em; height: ', $viewsize, 'em; position: absolute; left: 1em; top: 1em;">', $eol;

	while ($stmt->fetch())
	{
		$image = 'images/star.png';
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
			$tooltip = 'Enemy system. Colonised planets: '.$colonies;
		}
		echo '<a href="view_planets.php?system=', $systemid, '"><img src="', $image, '" style="width: ', $starsize, 'em; height ', $starsize, 'em; position: absolute; left: ', ($sysX-$xmin)*$starsize, 'em; top: ', ($sysY-$ymin)*$starsize, 'em;" title="',$tooltip,'"></a>', $eol;
	}
	$stmt->close();
	echo '</div>', $eol;
	echo '</div>', $eol;
}
