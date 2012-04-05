<?
include_once 'includes/start.inc.php';
$isAPICall = false;
if(isset($_GET['api']) && $_GET['api']=='json') $isAPICall = true;
$loggedIn = checkLoggedIn(!$isAPICall);

include_once 'includes/template.inc.php';
if(!$isAPICall) template('View Galaxy', 'viewSystemsBody');
if($isAPICall){
	$results=array();
	if($loggedIn){
		$userid = $_SESSION['userid'];
		$x = isset($_GET['x']) ? $_GET['x'] : '';
		$y = isset($_GET['y']) ? $_GET['y'] : '';

		$viewsize = 30; // in em
		$minstarsize = 1.2;// in em
		$maxstarsize = 2;  // in em
		$gridsize = 2;     // in em
		$scroll = 3;       // in grid squares

		$zoom = $_GET['zoom']; if (!is_numeric($zoom)) $zoom=1;
		$zoom = clamp($zoom, 0.25, 2);
		$gridsize *= $zoom;
		$viewdistance = floor(($viewsize/$gridsize - 1) / 2); // in grid squares
		$indent = ($viewsize - ($viewdistance * 2 + 1) * $gridsize) / 2; // in em
		$minstarsize *= $zoom;
		$maxstarsize *= $zoom;
		$scroll = floor($scroll/$zoom);

		if (!is_numeric($x) || !is_numeric($y))
		{
			$systemid = isset($_GET['system']) ? $_GET['system'] : '';
			$syscode = isset($_GET['syscode']) ? $_GET['syscode'] : '';
			if (!is_numeric($systemid) && (strlen($syscode) == 3 || strlen($syscode) == 4))
			{
				$systemid = systemid($syscode);
			}

			if (is_numeric($systemid))
			{
				$query = $mysqli->prepare('SELECT x,y FROM systems WHERE systemid=?');
				$query->bind_param('i', $systemid);
				$query->execute();
				$query->bind_result($x, $y);
				$result = $query->fetch();
				if (!$result)
				{
					$results['status'] = 'error';
					$results['error'] = 'Invalid System ID';
					$results['errorCode'] = 'INVALIDSYSTEM';
					echo json_encode($results);
					exit;
				}
				$query->close();
			}
			else
			{
				$query = $mysqli->prepare('SELECT x,y FROM colonies LEFT JOIN planets USING(planetid) LEFT JOIN systems USING(systemid) WHERE userid=? ORDER BY colonylevel DESC LIMIT 1');
				$query->bind_param('i', $userid);
				$query->execute();
				$query->bind_result($x, $y);
				$result = $query->fetch();
				if (!$result)
				{
					$results['status'] = 'error';
					$results['error'] = 'You have no colonies';
					$results['errorCode'] = 'NOCOLONIES';
					echo json_encode($results);
					exit;
				}

				$query->close();
			}
		}

		$x = clamp($x, -UNI_CENTRE_X, UNI_CENTRE_X);
		$y = clamp($y, -UNI_CENTRE_Y, UNI_CENTRE_Y);

		$colonyid = $_SESSION['colony'];
		$query = $mysqli->prepare('SELECT systemid,x,y FROM planets LEFT JOIN systems USING(systemid) WHERE planetid=?');
		$query->bind_param('i', $colonyid);
		$query->execute();
		$query->bind_result($colonysystemid, $colonyx, $colonyy);
		$result = $query->fetch();
		if (!$result)
		{
			$results['status'] = 'error';
			$results['error'] = 'Invalid Colony';
			$results['errorCode'] = 'INVALIDCOLONY';
			echo json_encode($results);
			exit;
		}
		$query->close();

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

		$results['status'] = 'ok';
		$results['navbuttons']['up']      ='view_systems.php?x=' . $x . '&y=' .  ($y-$scroll) . '&zoom='. $zoom;
		$results['navbuttons']['down']    ='view_systems.php?x=' . $x . '&y=' .  ($y+$scroll) . '&zoom='. $zoom;
		$results['navbuttons']['left']    ='view_systems.php?x=' . ($x-$scroll) . '&y=' .  $y . '&zoom='. $zoom;
		$results['navbuttons']['right']   ='view_systems.php?x=' . ($x+$scroll) . '&y=' .  $y . '&zoom='. $zoom;
		$results['navbuttons']['zoomin']  ='view_systems.php?x=' . $x . '&y=' .  $y . '&zoom='. ($zoom*2);
		$results['navbuttons']['zoomout'] ='view_systems.php?x=' . $x . '&y=' .  $y . '&zoom='. ($zoom/2);
		$results['systems'] = array();
		while ($stmt->fetch())
		{
			$image = 'images/star'.($systemid%4 +1).'.png';
			$image2 = null;
			$syscode = systemcode($systemid);
			$systeminfo = 'Not colonised'. $eol;
			if ($systemid == $colonysystemid)
			{
				if ($othercolonies)
				{
					$image2 ='images/star-oc+cc.png';
					$systeminfo = 'Current System (Contested).<br/>Your colonies: '.$colonies.'</br/>Other colonies: '.$othercolonies . $eol;
				}
				else
				{
					$image2 = 'images/star-c.png';
					$systeminfo = 'Current system.<br/>Colonised planets: '.$colonies . $eol;
				}
			}
			else if ($colonies && $othercolonies)
			{
				$image2 ='images/star-oc+c.png';
				$systeminfo = 'Contested System.<br/>Your colonies: '.$colonies.'<br/>Other colonies: '.$othercolonies . $eol;
			}
			else if ($colonies)
			{
				$image2 = 'images/star-c.png';
				$systeminfo = 'Your system.<br/>Colonised planets: '.$colonies . $eol;
			}
			else if ($othercolonies)
			{
				$image2 = 'images/star-oc.png';
				$systeminfo = 'Enemy system.<br/>Colonised planets: '.$othercolonies . $eol;
			}

			if ($systemid != $colonysystemid)
			{
				$systeminfo .= '<br/>Distance: '.number_format(distance($sysX-$colonyx, $sysY-$colonyy), 2).'PC';
			}

			$starsize = (floor($systemid/4)%4)/4 * ($maxstarsize-$minstarsize) + $minstarsize;
			$temp = Array();
			$temp['container'] = array('height'=>$gridsize,'width'=>$gridsize,'x'=>($sysX-$xmin)*$gridsize + $indent,'y'=>($sysY-$ymin)*$gridsize + $indent);
			$temp['link'] = 'view_planets.php?system=' . $systemid;
			$temp['star'] = array('image'=>$image,'width'=>$starsize,'height'=>$starsize,'offsetX'=>0-($starsize/2),'offsetY'=>0-($starsize/2));
			if ($image2)
			{
				$temp['indicator'] = array('image'=>$image2,'width'=>$gridsize,'height'=>$gridsize);
			}
			$temp['info'] = array('padding'=>$gridsize+0.3,'syscode'=>$syscode,'info'=>$systeminfo);
			$results['systems'][] = $temp;
		}
		$stmt->close();
	}else{
		$results['status'] = 'error';
		$results['error'] = 'Not Logged In';
		$results['errorCode'] = 'NOTAUTHED';
	}
	echo json_encode($results);
}
function viewSystemsBody()
{
	global $eol, $mysqli;
	$userid = $_SESSION['userid'];
	$x = $_GET['x'];
	$y = $_GET['y'];
	$syscode = $_GET['syscode'];
	
	$viewsize = 30; // in em
	$minstarsize = 1.2;// in em
	$maxstarsize = 2;  // in em
	$gridsize = 2;     // in em
	$scroll = 3;       // in grid squares

	$zoom = $_GET['zoom']; if (!is_numeric($zoom)) $zoom=1;
	$zoom = clamp($zoom, 0.25, 2);
	$gridsize *= $zoom;
	$viewdistance = floor(($viewsize/$gridsize - 1) / 2); // in grid squares
	$indent = ($viewsize - ($viewdistance * 2 + 1) * $gridsize) / 2; // in em
	$minstarsize *= $zoom;
	$maxstarsize *= $zoom;
	$scroll = floor($scroll/$zoom);

	if (!is_numeric($x) || !is_numeric($y))
	{
		$systemid = $_GET['system'];
		if(!is_numeric($systemid) && (strlen($syscode)==3 || strlen($syscode)==4)){
			$systemid = systemid($syscode);
		}

		if (is_numeric($systemid))
		{
			$query = $mysqli->prepare('SELECT x,y FROM systems WHERE systemid=?');
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
			$query = $mysqli->prepare('SELECT x,y FROM colonies LEFT JOIN planets USING(planetid) LEFT JOIN systems USING(systemid) WHERE userid=? ORDER BY colonylevel DESC LIMIT 1');
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

	$x = clamp($x, -UNI_CENTRE_X, UNI_CENTRE_X);
	$y = clamp($y, -UNI_CENTRE_Y, UNI_CENTRE_Y);

	$colonyid = $_SESSION['colony'];
	$query = $mysqli->prepare('SELECT systemid,x,y FROM planets LEFT JOIN systems USING(systemid) WHERE planetid=?');
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
	?>
	
	<form action="view_systems.php" method="get"><input type="hidden" name="zoom" value="<? echo $zoom;?>">System Code: <input name="syscode" size="4" maxlength="4" value="<? echo $syscode;?>"><input type="Submit" value="Jump To"></form><br>

	<?
	echo '<div class="starmap" style="width: ', $viewsize+2, 'em; height: ', $viewsize+2, 'em;">', $eol;

	echo '<a class="navbutton up" href="view_systems.php?x=', $x, '&y=', $y-$scroll, '&zoom=', $zoom, '"><img class="navtop" src="images/up.png" alt="Up"></a>', $eol;
	echo '<a class="navbutton down" href="view_systems.php?x=', $x, '&y=', $y+$scroll, '&zoom=', $zoom, '"><img class="navbottom" src="images/down.png" alt="Down"></a>', $eol;
	echo '<a class="navbutton left" href="view_systems.php?x=', $x-$scroll, '&y=', $y, '&zoom=', $zoom, '"><img class="navleft" src="images/left.png" alt="Left"></a>', $eol;
	echo '<a class="navbutton right" href="view_systems.php?x=', $x+$scroll, '&y=', $y, '&zoom=', $zoom, '"><img class="navright" src="images/right.png" alt="Right"></a>', $eol;
	echo '<a class="navbutton zmin" href="view_systems.php?x=', $x, '&y=', $y, '&zoom=', $zoom*2, '"><img class="zoomin" src="images/in.png" alt="In"></a>', $eol;
	echo '<a class="navbutton zmout" href="view_systems.php?x=', $x, '&y=', $y, '&zoom=', $zoom/2, '"><img class="zoomout" src="images/out.png" alt="Out"></a>', $eol;

	echo '<div class="starmap" id="starmaparea" style="width: ', $viewsize, 'em; height: ', $viewsize, 'em; position: absolute; left: 1em; top: 1em; background: url(images/starbg.png) center center;">', $eol;

	while ($stmt->fetch())
	{
		$image = 'images/star'.($systemid%4 +1).'.png';
		$image2 = null;
		$syscode = systemcode($systemid);
		$systeminfo = 'Not colonised'. $eol;
		if ($systemid == $colonysystemid)
		{
			if ($othercolonies)
			{
				$image2 ='images/star-oc+cc.png';
				$systeminfo = 'Current System (Contested).<br/>Your colonies: '.$colonies.'</br/>Other colonies: '.$othercolonies . $eol;
			}
			else
			{
				$image2 = 'images/star-c.png';
				$systeminfo = 'Current system.<br/>Colonised planets: '.$colonies . $eol;
			}
		}
		else if ($colonies && $othercolonies)
		{
			$image2 ='images/star-oc+c.png';
			$systeminfo = 'Contested System.<br/>Your colonies: '.$colonies.'<br/>Other colonies: '.$othercolonies . $eol;
		}
		else if ($colonies)
		{
			$image2 = 'images/star-c.png';
			$systeminfo = 'Your system.<br/>Colonised planets: '.$colonies . $eol;
		}
		else if ($othercolonies)
		{
			$image2 = 'images/star-oc.png';
			$systeminfo = 'Enemy system.<br/>Colonised planets: '.$othercolonies . $eol;
		}

		if ($systemid != $colonysystemid)
		{
			$systeminfo .= '<br/>Distance: '.number_format(distance($sysX-$colonyx, $sysY-$colonyy), 2).'PC';
		}

		$starsize = (floor($systemid/4)%4)/4 * ($maxstarsize-$minstarsize) + $minstarsize;
		echo '<div class="systemcontainer" style="width: ', $gridsize, 'em; height: ', $gridsize, 'em; left: ', ($sysX-$xmin)*$gridsize + $indent, 'em; top: ', ($sysY-$ymin)*$gridsize + $indent, 'em;">', $eol;
        echo '<a href="view_planets.php?system=', $systemid, '">', $eol;
		echo '<img class="star" src="', $image, '" style="width: ', $starsize, 'em; height: ', $starsize, 'em; margin: -',$starsize/2,'em 0 0 -',$starsize/2,'em;">', $eol;
		if ($image2)
		{
			echo '<img src="', $image2, '" style="width: ', $gridsize, 'em; height: ', $gridsize, 'em;"">', $eol;
		}
		echo '</a>', $eol;
        echo '<div class="info" style="padding-top: ',$gridsize+0.3,'em;">';
        echo '<h3>',$syscode,'</h3>', $eol;
        echo $systeminfo, $eol;
        echo '</div>', $eol;
        echo '</div>', $eol;
	}
	$stmt->close();
	echo '</div>', $eol;
	echo '</div>', $eol;
    echo '<script type="text/javascript">', $eol;
    ?>
    $(document).ready(function(){
        $(document).keydown(function(e){
            switch(e.keyCode){
                case 37:
                    $(".navbutton.left").click();
                    return false;
                    break;
                case 38:
                    $(".navbutton.up").click();
                    return false;
                    break;
                case 39:
                    $(".navbutton.right").click();
                    return false;
                    break;
                case 40:
                    $(".navbutton.down").click();
                    return false;
                    break;
                default:
                    break;
            }
        });
    });
    <?php
    echo '</script>', $eol;
}
