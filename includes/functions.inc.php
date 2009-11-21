<?
define("TICK",600); // Seconds per tick
define("TICKS_PH",6); // Ticks per hour
define("SMALLTICK",60); // Seconds per small tick
define("SMALLTICKS_PH",60); // Small ticks per hour
define("SMALL_PER_TICK",10); // Small ticks per tick

function cleanUp(){
	if(DEBUG) echo "Starting clean up\n";
	//$mysqli->close();
	if(DEBUG) echo "End clean up\n";
}

//Used to identify planet changing drop downs
$planetChangeID=0;

$lookups=array();
//planetType
$lookups["planetType"]=array(
		1 => "Rocky",
		2 => "Gas Giant",
		3 => "Ice");
//resourceType
$lookups["resourceType"]=array(
		0 => "None",
		1 => "Metal",
		2 => "Deuterium",
		3 => "Energy");
//buildingEffect
$lookups["buildingEffect"]=array(
		0 => "None",
		1 => "Metal Production",
		2 => "Deuterium Production",
		3 => "Energy Production",
		4 => "Colony Level",
		5 => "Metal Storage",
		6 => "Deuterium Storage",
		7 => "Energy Storage",
		8 => "Ship Construction Rate",
		9 => "Colony Shield HP",
		10 => "Wormhole Range",
		11 => "Sensor Range");
$lookups["buildingEffectColumn"]=array(
		1 => "metalproduction",
		2 => "deuteriumproduction",
		3 => "energyproduction",
		4 => "colonylevel",
		5 => "maxmetal",
		6 => "maxdeuterium",
		7 => "maxenergy",
		8 => "shipconstruction",
		9 => "maxhp",
		10 => "whrange",
		11 => "sensorrange");

$lookups["order"]=array(
		0 => "Unassigned",
		1 => "Defend",
		2 => "Move to",
		3 => "Transport to",
		4 => "Colonise",
		5 => "Attack",
		6 => "Wormhole jump");
		
$lookups["shipclass"]=array(
		1 => "Fighter",
		2 => "Cruiser",
		3 => "Capital");

function checkLoggedIn_failed()
{
	$page = basename($_SERVER["PHP_SELF"]);
	if(isset($_SERVER["QUERY_STRING"])){
		$page .= "&q=".urlencode($_SERVER["QUERY_STRING"]);
	}
	//http_redirect('login_form.php', array(), false, 303);
	header('HTTP/1.1 303 See Other');
	header('Location: login_form.php?error=2&p='.$page);
	exit;
}

function checkLoggedIn()
{
	if (isLoggedIn())
	{
		return true;
	}
	else
	{
		checkLoggedIn_failed();
	}
}

function isLoggedIn($forceUpdate=false)
{
	global $mysqli;

	static $isLoggedIn = false;
	static $isLoggedInSet = false;

	if (!$isLoggedInSet || $forceUpdate)
	{
		$userid = $_SESSION['userid'];
		if (!$userid)
		{
			$isLoggedIn = false;
		}
		else
		{
			$query = $mysqli->prepare('SELECT 1 from users WHERE userid=? AND phpsessionid=UNHEX(?)');
			$query->bind_param('ss', $userid, $sessionid);
			if (isset($_SESSION['adminuserid']))
			{
				$userid = $_SESSION['adminuserid'];
			}
			else
			{
				$userid = $_SESSION['userid'];
			}
			$sessionid = session_id();

			$result = $query->execute();
			if (!$result)
			{
				echo 'error: ', $query->error, $eol;
				exit;
			}

			$query->bind_result($isLoggedIn);
			$query->fetch();
			$query->close();
		}

		$isLoggedInSet = true;
	}

	return $isLoggedIn;
}

function isAdmin()
{
	global $mysqli;

	$userid = $_SESSION['userid'];
	if (!$userid)
	{
		return false;
	}

	$query = $mysqli->prepare('SELECT bisadmin from users WHERE userid=? AND phpsessionid=UNHEX(?)');
	if (!$query)
	{
		echo 'error: ', $mysqli->error, $eol;
		exit;
	}

	$query->bind_param('ss', $userid, $sessionid);
	if (isset($_SESSION['adminuserid']))
	{
		$userid = $_SESSION['adminuserid'];
	}
	else
	{
		$userid = $_SESSION['userid'];
	}
	$sessionid = session_id();

	$result = $query->execute();
	if (!$result)
	{
		echo 'error: ', $query->error, $eol;
		exit;
	}

	$query->bind_result($bisadmin);
	$query->fetch();

	return $bisadmin;
}

function htmlDropdown($name, $arrName)
{
	global $lookups;
	echo '<select name="', $name, '">';
	foreach ($lookups[$arrName] as $key => $value)
	{
		echo '<option value="', $key, '">', $value, '</option>';
	}
	echo '</select>';
}

function getSigned($val)
{
	return ($val>0) ? '+'.$val : $val;
}

function thousands($val)
{
	// over 10M show in M (millions)
	if (abs($val) >= 10000000)
	{
		return intval($val/1000000).'M';
	}
	// over 10k show in k (thousands)
	else if (abs($val) >= 10000)
	{
		return intval($val/1000).'k';
	}
	else
	{
		return intval($val);
	}
}

function clamp($val, $min, $max)
{
    return min(max($val, $min), $max);
}

function distance($x, $y)
{
    return sqrt($x*$x + $y*$y);
}

function getTickElapsed(){
        $curtime=explode(":",date("i:s"));
        $lapsed = (60*$curtime[0] + $curtime[1]) % TICK;
        return $lapsed;
}

function formatSeconds($formatstring,$seconds){
	$sec = floor($seconds)%60;
	$min = floor($seconds/60)%60;
	$hour = floor($seconds/3600);
        $replacements=array(    "i"=>padString($min,"0",2),
                                "s"=>padString($sec,"0",2),
                                "h"=>$hour);
        return str_replace(array_keys($replacements),array_values($replacements),$formatstring);
}

function padString($string,$char,$length,$right=false){
	$retstring=$string;
	if($right){
		while(strlen($retstring)<$length) $retstring = $retstring . $char;
	}else{	
		while(strlen($retstring)<$length) $retstring = $char . $retstring;
	}
	return $retstring;
}

function prodDropdown($current,$planet,$building,$maxcons,$maxeffect){
	echo "<form action=\"alter_production_exec.php\" method=\"post\">\n";
	echo "<input type=\"hidden\" name=\"planet\" value=\"" .$planet . "\">\n";
	echo "<input type=\"hidden\" name=\"building\" value=\"" .$building . "\">\n";
	$js = "onChange=\"updateProdVals(".$building .",".round($current,1) .",". $maxcons . "," .$maxeffect . ");\"";
	echo "<select id=\"pdd" . $building . "\" name=\"output\" $js >\n";
	for($i=0;$i<=100;$i+=10){
		$prodval = (float)$i/100;
		$selected=($prodval==round($current,1))?"selected":"";
		echo "<option value=\"$i\" $selected >$i%</option>\n";
	}
	echo "</select><input type=\"submit\" value=\"Set\">\n</form>\n";
}

function systemcode($systemid,$planetorbit=null)
{
	$return = chr(ord('A')+floor(($systemid-1)/99)) . padstring(((($systemid-1)%99)+1),'0',2);
	if ($planetorbit)
	{
		$return .= chr(ord('A') + $planetorbit - 1);
	}
	return $return;
}

function systemid($systemcode)
{
	if (strlen($systemcode)<3 || strlen($systemcode)>4
		|| ord($systemcode[0]) < ord('A') || ord($systemcode[0]) > ord('K')
		|| !is_numeric($systemcode[1]) || !is_numeric($systemcode[2]))
	{
		echo 'error: Invalid system code';
		exit;
	}
	return (ord($systemcode[0])-ord('A'))*99 + substr($systemcode,1,2);
}

function orbit($systemcode)
{
	if (strlen($systemcode)!=4
		|| ord($systemcode[3]) < ord('A') || ord($systemcode[3]) > ord('G'))
	{
		echo 'error: Invalid planet code';
		exit;
	}
	return ord($systemcode[3]) - ord('A') + 1;
}

function planetChanger($current=NULL,$page=NULL){
	global $planetChangeID, $mysqli;
	$userid = $_SESSION['userid'];
	if (is_null($current)) $current = $_GET['planet'];
	if (is_null($page)) $page = basename($_SERVER["PHP_SELF"]);
	$query = $mysqli->prepare('SELECT colonies.planetid,systemid,planets.orbit FROM colonies LEFT JOIN planets USING (planetid) LEFT JOIN systems USING (systemid) WHERE colonies.userID = ? ORDER BY colonylevel DESC;');
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
	?>
	<form action="<? echo $page;?>" method="get">
		<select name="planet" id="pcdd<? echo $planetChangeID;?>" onchange="changePage(<? echo $planetChangeID++;?>,'<? echo $page;?>',<? echo $current;?>)">
	<?
	$query->bind_result($planetid,$systemid,$orbit);
	while($query->fetch()){
		?>
		<option value="<? echo $planetid;?>" <? echo ($planetid==$current)?"selected":"";?>><? echo systemcode($systemid,$orbit);?></option>
		<?
	}
	?> </select><input type="Submit" value="Go"></form> <?
}

function generateAPIKey(){
	global $eol, $mysqli;
	$userid = $_SESSION['userid'];
	$queryapi = $mysqli->prepare('SELECT username,lastlogin FROM users WHERE userid = ?');
	$queryapi->bind_param('i', $userid);
	$queryapi->bind_result($username,$lastlogin);
	$queryapi->execute();
	$queryapi->fetch();
	$queryapi->close();
	$apikey = mt_rand() .$username . mt_rand() . $lastlogin . time();
	return md5($apikey);
}

function checkWHRange($dist,$range1,$range2){
	$canwhj = false;
	if(($range1 >= $dist) && ($range2 >= $dist)) $canwhj = true;
	return $canwhj;
}

function getWHLinks($userid){
	global $eol, $mysqli;
	$links = array();
	$stmt = $mysqli->prepare("SELECT planetid1,planetid2,x1,y1,x2,y2 FROM 
	(SELECT planetid as planetid1, x as x1 ,y as y1, whrange FROM colonies LEFT JOIN planets USING (planetid) LEFT JOIN systems USING (systemid) WHERE userID = ? and whrange > 0) sys1, 
	(SELECT planetid as planetid2, x as x2 ,y as y2, whrange FROM colonies LEFT JOIN planets USING (planetid) LEFT JOIN systems USING (systemid) WHERE userID = ? and whrange > 0) sys2 
	WHERE sys1.planetid1 < sys2.planetid2 
    and distance(sys1.x1,sys1.y1,sys2.x2,sys2.y2) <= sys1.whrange 
    and distance(sys1.x1,sys1.y1,sys2.x2,sys2.y2) <= sys2.whrange;");
	$stmt->bind_param('ii',$userid, $userid);
	$stmt->execute();
	$stmt->bind_result($systemID1,$systemID2,$sysX1,$sysY1,$sysX2,$sysY2);
	while($stmt->fetch()){
		echo $systemID1 . "@" . $sysX1 . "," . $sysY1 . " links to " . $systemID2 . "@" . $sysX2 . "," . $sysY2 . "<br>";
		if(!isset($links[$systemID1])){
			if(!isset($links[$systemID2])){
				$links[$systemID1] = array("x" => $sysX1, "y" => $sysY1);
				$links[$systemID1][$systemID2] = array("x" => $sysX2, "y" => $sysY2);
			}else{
				$links[$systemID2][$systemID1] = array("x" => $sysX1, "y" => $sysY1);
			}
		}else{
			$links[$systemID1][$systemID2] = array("x" => $sysX2, "y" => $sysY2);
		}
		
	}
	return $links;
}

?>