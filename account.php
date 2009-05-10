<?
include_once 'includes/start.inc.php';
checkLoggedIn();

include_once 'includes/template.inc.php';
template('Account Info', 'accountBody');

function accountBody()
{
	global $eol, $mysqli;
	$userid = $_SESSION['userid'];
	
	$queryapi = $mysqli->prepare('SELECT HEX(apikey) FROM users WHERE userid = ?');
	$queryapi->bind_param('i', $userid);
	$queryapi->bind_result($apikey);
	$queryapi->execute();
	$queryapi->fetch();
	$queryapi->close();
	if(is_null($apikey)) $apikey = "None Set";

	?>
	<form action="changepasswd_exec.php" method="post" onsubmit="return checkPasswords();">
	<fieldset>
	<legend>Change Password:</legend>
	Current Password: <input type="password" name="password"><br><br>
	
	New Password: <input type="password" id="newpw" name="newpassword"><br>
	Confirm Password: <input type="password" id="conpw" name="confirmpassword"><br>
	<input type="submit" value="Change Password">
	</fieldset>
	</form>
	<h3>API info</h3>
	API key: <? echo $apikey;?> <a href="changeapikey_exec.php">Generate new API key</a> <a href="changeapikey_exec.php?clear=1">Remove API key</a><br>
	API url: http://vps.dynamicarcade.co.uk/SpaceMMO/api/api.php?wsdl
	<?
}
