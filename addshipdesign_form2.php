<?
include_once 'includes/start.inc.php';
checkLoggedIn();

include_once 'includes/ships.inc.php';

include_once 'includes/template.inc.php';
template('Create Ship Design', 'designCreateBody', null, 'designCreateHead');

function designCreateHead()
{
?><script type="text/javascript" src="functions.js.php"></script>
<?
}

function designCreateBody()
{
	global $eol, $mysqli, $lookups;
	$hullid = $_GET['hull'];
	
	$query = $mysqli->prepare('SELECT hullname,metalcost,size,maxweapons FROM shiphulls WHERE hullid=?');
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

	$query->bind_result($hullname,$cost,$hullsize,$maxweapons);
	$result = $query->fetch();
	if (!$result)
	{
		echo 'error: ', $query->error, $eol;
		exit;
	}
	$query->close();
	$maxpartsize = $hullsize-2;
	echo '<form action="addshipdesign_exec.php" method="post"  onsubmit="return validateDesForm(',$hullsize,',',$maxweapons,')">', $eol;
	echo '<table>', $eol;
	echo '<tr><td>Hull:</td><td><input type="hidden" name="hull" value="',$hullid,'">',$hullname,'</td><td rowspan="5" colspan="2"></td></tr>', $eol;
	echo '<tr><td>Cost:</td><td>',$cost,' Metal</td></tr>', $eol;
	echo '<tr><td>Size:</td><td><span id="size">',$hullsize,'</span></td></tr>', $eol;
	echo '<tr><td>Max Weapons:</td><td>',$maxweapons,'</td></tr>', $eol;
	echo '<tr><td>Ship Name:</td><td><input type="text" size="14" name="shipname" id="shipname" maxlength="20"></td></tr>', $eol;
	echo '<tr><td>Engines:</td><td><input type="button" value="-" onclick="minus(\'engines\',1)"><input type="text" size="4" name="engines" id="engines" value="1" onchange="validateField(\'engines\',',$hullsize,',1,',$maxpartsize+1,');"><input type="button" value="+" onclick="plus(\'engines\')"></td><td>Speed:</td><td><span id="speed">',number_format(speed($hullsize,1),2),'</span> PC/h</td></tr>', $eol;
	echo '<tr><td>Fuel bay:</td><td><input type="button" value="-" onclick="minus(\'fuel\',1)"><input type="text" size="4" name="fuel" id="fuel" value="1" onchange="validateField(\'fuel\',',$hullsize,',1,',$maxpartsize+1,');"><input type="button" value="+" onclick="plus(\'fuel\')"></td><td>Range:</td><td><span id="range">',number_format(shiprange($hullsize,1,1),2),'</span> PC</td></tr>', $eol;
	echo '<tr><td>Weapons:</td><td><input type="button" value="-" onclick="minus(\'weapons\',0)"><input type="text" size="4" name="weapons" id="weapons" value="0" onchange="validateField(\'weapons\',',$hullsize,',0,',$maxweapons,');"><input type="button" value="+" onclick="plus(\'weapons\',',$maxweapons,')"></td><td>Attack:</td><td><span id="attack">0</span></td></tr>', $eol;
	echo '<tr><td>Shields:</td><td><input type="button" value="-" onclick="minus(\'shields\',0)"><input type="text" size="4" name="shields" id="shields" value="0" onchange="validateField(\'shields\',',$hullsize,',0,',$maxpartsize,');"><input type="button" value="+" onclick="plus(\'shields\')" ></td><td>Defense:</td><td><span id="defense">',$hullsize,'</span> HP</td></tr>', $eol;
	echo '<tr><td>Cargo:</td><td><input type="button" value="-" onclick="minus(\'cargo\',0)"><input type="text" size="4" name="cargo" id="cargo" value="0" onchange="validateField(\'cargo\',',$hullsize,',0,',$maxpartsize,');"><input type="button" value="+" onclick="plus(\'cargo\')"></td><td>Capacity:</td><td><span id="capacity">0</span> Units</td></tr>', $eol;
	echo '<tr><td>Remaining:</td><td><input type="text" readonly size="4" id="remain" value="',$hullsize-2,'"></td><td colspan="2"></td></tr>', $eol;
	echo '</table>', $eol;
	echo '<input type="submit" value="Finish">', $eol;
	echo '</form>', $eol;
}
?>
