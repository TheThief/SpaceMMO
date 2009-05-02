<?php
include_once 'includes/start.inc.php';
checkLoggedIn();

include_once 'includes/template.inc.php';
template('Queue Ship for Construction', 'queueShipBody');

function queueShipBody()
{
	global $eol, $mysqli;
	$userid = $_SESSION['userid'];
	$planetid = $_POST['planet'];
	$designid = $_POST['design'];
	$count = $_POST['count'];

	if ($count<1)
	{
		echo 'Error: You have to build <i>some</i> ships in your build request!', $eol;
		exit;
	}

	$mysqli->autocommit(false);

	$query = $mysqli->prepare('SELECT metalcost,mindrydock FROM shipdesigns LEFT JOIN shiphulls USING (hullID) WHERE userID = ? AND designID = ?;');
	$query->bind_param('ii', $userid, $designid);
	$query->execute();
	$query->bind_result($metalcost, $mindrydock);
	$result = $query->fetch();
	if (!$result)
	{
		echo 'Error: No such ship design exists.', $eol;
		exit;
	}
	$query->close();

	$query = $mysqli->prepare('SELECT metal FROM colonies WHERE userID = ? AND planetID = ? FOR UPDATE;');
	$query->bind_param('ii', $userid, $planetid);
	$query->execute();
	$query->bind_result($metal);
	$result = $query->fetch();
	if (!$result)
	{
		echo 'Error: You don\'t have a colony on that planet.', $eol;
		exit;
	}
	$query->close();

	$query = $mysqli->prepare('SELECT level FROM colonybuildings WHERE buildingid = 9 AND planetid = ?;');
	$query->bind_param('i', $planetid);
	$result = $query->execute();
	$query->bind_result($ddlevel);
	$result = $query->fetch();
	if (!$result)
	{
		echo 'Error: You need a drydock to build ships.', $eol;
		exit;
	}
	$query->close();

	if ($mindrydock > $ddlevel)
	{
		echo 'Error: Your drydock is too low level to build this kind of ship.<br>', $eol;
		echo 'Orbital Drydock level: ',$ddlevel,', required level: ',$mindrydock, $eol;
		exit;
	}

	$metalcost = $metalcost*$count;
	if ($metalcost > $metal)
	{
		echo 'Error: You can\'t afford that many ships.', $eol;
		exit;
	}

	$query = $mysqli->prepare('INSERT INTO shipbuildqueue (planetid,designid,count) VALUES (?,?,?)');
	if (!$query)
	{
		echo 'error: ', $mysqli->error, $eol;
		exit;
	}

	$query->bind_param('iii', $planetid,$designid,$count);

	$result = $query->execute();
	if (!$result)
	{
		echo 'error: ', $query->error, $eol;
		exit;
	}

	$query = $mysqli->prepare('UPDATE colonies SET metal=metal-? WHERE userID = ? AND planetID = ?');
	if (!$query)
	{
		echo 'error: ', $mysqli->error, $eol;
		exit;
	}
	$query->bind_param('iii', $metalcost, $userid, $planetid);

	$result = $query->execute();
	if (!$result)
	{
		echo 'error: ', $query->error, $eol;
		exit;
	}
	$query->close();

	$mysqli->commit();

	header('HTTP/1.1 303 See Other');
	header('Location: build_ships.php?planet='.$planetid);

	echo 'Ship(s) queued for build successfully', $eol;
	echo '<a href="build_ships.php?planet=', $planetid, '">Return</a> to build queue.', $eol;
}
?>
