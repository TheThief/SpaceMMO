<?
include_once 'includes/start.inc.php';
checkLoggedIn();

include_once 'includes/template.inc.php';
template('Account Info', 'accountBody');

function accountBody()
{
	global $eol, $mysqli;
	$userid = $_SESSION['userid'];
	
	$querysys = $mysqli->prepare('SELECT UNHEX(apikey) FROM users WHERE userid = ?');
	$querysys->bind_param('i', $userid);
	$querysys->bind_result($apikey);
	$querysys->execute();
	$querysys->fetch();
	$querysys->close();
	
	?>
	<form action="changepasswd_exec.php" method="post" onsubmit="return checkPasswords();">
	<fieldset>
	<legend>Change Password:</legend>
	Current Password: <input type="password" name="password"><br><br>
	
	New Password: <input type="password" id="newpw" name="newpassword"><br>
	Confirm Password: <input type="password" id="conpw" name="confirmpassword"><br>
	<input type="submit" value="change">
	</fieldset>
	</form>
	<h3>API info</h3>
	API key: <? echo $apikey;?> <a href="regenapikey_exec.php">Generate new API key</a><br>
	API url: <a href="http://vps.dynamicarcade.co.uk/SpaceMMO/api/api.php">http://vps.dynamicarcade.co.uk/SpaceMMO/api/api.php?wsdl</a>
	<?
}
