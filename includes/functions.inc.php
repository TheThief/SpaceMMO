<?
define("TICK",600);

function cleanUp(){
	if(DEBUG) echo "Starting clean up\n";
	//$mysqli->close();
	if(DEBUG) echo "End clean up\n";
}

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
		8 => "Ship Construction Rate");
$lookups["buildingEffectColumn"]=array(
		1 => "metalproduction",
		2 => "deuteriumproduction",
		3 => "energyproduction",
		4 => "colonylevel",
		5 => "maxmetal",
		6 => "maxdeuterium",
		7 => "maxenergy",
		8 => "shipconstruction");

$lookups["order"]=array(
		0 => "Unassigned",
		1 => "Defend",
		2 => "Move to",
		3 => "Transport to",
		4 => "Colonise",
		5 => "Attack");

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

function isLoggedIn()
{
	global $mysqli;

	$userid = $_SESSION['userid'];
	if (!$userid)
	{
		return false;
	}

	$query = $mysqli->prepare('SELECT 1 from users WHERE userid=? AND phpsessionid=UNHEX(?)');
	if (!$query)
	{
		echo 'error: ', $mysqli->error, $eol;
		exit;
	}

	$query->bind_param('ss', $userid, $sessionid);
	$userid = $_SESSION['userid'];
	$sessionid = session_id();

	$result = $query->execute();
	if (!$result)
	{
		echo 'error: ', $query->error, $eol;
		exit;
	}

	$query->bind_result($loggedin);
	$query->fetch();

	return $loggedin;
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
	$userid = $_SESSION['userid'];
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

function getSigned($val){
	return ($val<0)?$val:"+".$val;
}

function clamp($val, $min, $max)
{
     return min(max($val, $min), $max);
}

function getTickElapsed(){
        $curtime=explode(":",date("i:s"));
        $elapsedMins = $curtime[0];
        $lapsed=60*$elapsedMins[1] + $curtime[1];
        return $lapsed;
}

function formatSeconds($formatstring,$seconds){
	$sec = $seconds%60;
	$min = ($seconds/60);
	$hour = floor($seconds/3600);
        $replacements=array(    "i"=>padString($min%60,"0",2),
                                "s"=>padString($sec,"0",2),
                                "h"=>$hour,
				"m"=>floor($min));
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
		$return .= chr(ord('A') + $planetorbit);
	}
	return $return;
}

function systemid($systemcode)
{
	if (strlen($systemcode)!=3
		|| ord($systemcode[0]) < ord('A') || ord($systemcode[0]) > ord('K')
		|| !is_numeric($systemcode[1]) || !is_numeric($systemcode[2]))
	{
		echo 'error: Invalid system code';
		exit;
	}
	return (ord($systemcode[0])-ord('A'))*99 + substr($systemcode,1);
}

?>