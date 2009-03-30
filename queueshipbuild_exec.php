<?php
include 'includes/start.inc.php';
checkLoggedIn();

include 'includes/template.inc.php';
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

	$query = $mysqli->prepare('SELECT metalcost FROM shipdesigns LEFT JOIN shiphulls USING (hullID) WHERE userID = ? AND designID = ?;');
	if (!$query)
	{
		echo 'error: ', $mysqli->error, $eol;
		exit;
	}
	$query->bind_param('ii', $userid, $designid);

	$result = $query->execute();
	if (!$result)
	{
		echo 'error: ', $query->error, $eol;
		exit;
	}

	$query->bind_result($metalcost);
	$result = $query->fetch();
	if (!$result)
	{
		echo 'Error: No such ship design exists.', $eol;
		exit;
	}
	$query->close();

	$query = $mysqli->prepare('SELECT metal FROM colonies WHERE userID = ? AND planetID = ? FOR UPDATE;');
	if (!$query)
	{
		echo 'error: ', $mysqli->error, $eol;
		exit;
	}
	$query->bind_param('ii', $userid, $planetid);

	$result = $query->execute();
	if (!$result)
	{
		echo 'error: ', $query->error, $eol;
		exit;
	}

	$query->bind_result($metal);
	$result = $query->fetch();
	if (!$result)
	{
		echo 'Error: Not your planet!', $eol;
		exit;
	}
	$query->close();

	$query = $mysqli->prepare('SELECT level FROM colonybuildings WHERE buildingid = 9 AND planetid = ?;');
	if (!$query)
	{
		echo 'error: ', $mysqli->error, $eol;
		exit;
	}
	$query->bind_param('i', $planetid);
	$result = $query->execute();
	if (!$result)
	{
		echo 'error: ', $query->error, $eol;
		exit;
	}
	$query->bind_result($ddlevel);
	$result=$query->fetch();
	if (!$result)
	{
		echo 'Error: You need a drydock to build ships.', $eol;
		exit;
	}
	$query->close();
	
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

	$mysqli->commit();

	header('HTTP/1.1 303 See Other');
	header('Location: build_ships.php?planet='.$planetid);

	echo 'Ship(s) queued for build successfully', $eol;
	echo '<a href="build_ships.php?planet=', $planetid, '">Return</a> to build queue.', $eol;
}
?>
