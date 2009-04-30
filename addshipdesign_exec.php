<?php
include_once 'includes/start.inc.php';
checkLoggedIn();

include_once 'includes/ships.inc.php';

include_once 'includes/template.inc.php';
template('Save Ship Design', 'addShipDesignBody');

function addShipDesignBody()
{
	global $eol, $mysqli;
	$userid = $_SESSION['userid'];
	$hullid = $_POST['hull'];
	$shipname = $_POST['shipname'];
	$engines = $_POST['engines'];
	$fuel = $_POST['fuel'];
	$cargo = $_POST['cargo'];
	$weapons = $_POST['weapons'];
	$shields = $_POST['shields'];

	if (strlen($shipname) <= 0)
	{
		echo 'Error: If you don\'t name your design, how will we know when you want us to build it? Assign it a code for a name if you must, but we\'d prefer something cool.', $eol;
		exit;
	}
	else if (strlen($shipname) > 20)
	{
		echo 'Error: Please don\'t make the name too complicated. Try to keep it below 20 characters please.', $eol;
		exit;
	}

	if ($engines<0 || $fuel<0 || $cargo < 0 || $weapons < 0 || $shields < 0)
	{
		echo 'Error: I\'m afraid negative space is impossible. It\'s not even theoretically possible. You might want to rethink your design with that in mind...', $eol;
		exit;
	}

	if ($engines<1)
	{
		echo 'Error: You have to have engines on your ship or it won\'t move...', $eol;
		exit;
	}

	if ($fuel<1)
	{
		echo 'Error: You have to have a fuel bay or your ship\'s engines won\'t be very useful...', $eol;
		exit;
	}

	$query = $mysqli->prepare('SELECT size,maxweapons FROM shiphulls WHERE hullID = ?;');
	if (!$query)
	{
		echo 'error: ', $mysqli->error, $eol;
		exit;
	}
	$query->bind_param('i', $hullid);

	$result = $query->execute();
	if (!$result)
	{
		echo 'error: ', $query->error, $eol;
		exit;
	}

	$query->bind_result($size,$maxweapons);
	$result = $query->fetch();
	if (!$result)
	{
		echo 'Error: No such ship hull exists.', $eol;
		exit;
	}
	$query->close();

	$designsize = $engines+$fuel+$cargo+$weapons+$shields;
	if ($designsize > $size)
	{
		echo 'Error: Too much on this design, use a larger hull or trim your design a little.<br>', $eol;
		echo 'Space used: ', $designsize, '/', $size;
		exit;
	}
	else if ($designsize < $size)
	{
		echo 'Error: This design leaves empty space in the hull, if you\'ve got nothing better to add to the design I suggest you fill the remaining space with fuel bay.<br>', $eol;
		echo 'Space used: ', $designsize, '/', $size;
		exit;
	}

	if ($weapons > $maxweapons)
	{
		echo 'Error: I\'m afraid this hull can\'t mount so much firepower. Either strip off some weapons, or use a larger hull.<br>', $eol;
		echo 'Weapon slots used: ', $weapons, '/', $maxweapons;
		exit;
	}

	$query = $mysqli->prepare('INSERT INTO shipdesigns (userid,hullid,shipname,engines,fuel,cargo,weapons,shields,speed,fuelcapacity,cargocapacity,defense) VALUES (?,?,?,?,?,?,?,?)');
	if (!$query)
	{
		echo 'error: ', $mysqli->error, $eol;
		exit;
	}

	$query->bind_param('iisiiiiiiiii', $userid,$hullid,$shipname,$engines,$fuel,$cargo,$weapons,$shields,$speed,$fuelcapacity,$cargocapacity,$defense);
	$speed = speed($size, $engines);
	$fuelcapacity = fuelCapacity($fuel);
	$cargocapacity = cargoCapacity($cargo);
	$defense = defense($size, $shields);

	$result = $query->execute();
	if (!$result)
	{
		echo 'error: ', $query->error, $eol;
		exit;
	}

	header('HTTP/1.1 303 See Other');
	header('Location: list_shipdesigns.php');

	echo 'Ship Design \'', $shipname, '\' added successfully', $eol;
	echo '<a href="list_shipdesigns.php">Return</a> to designs list.', $eol;
}
?>
