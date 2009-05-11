<?
include_once 'includes/start.inc.php';
checkLoggedIn();

include_once 'includes/template.inc.php';
template('Account Info', 'accountBody');

function accountBody()
{
	global $eol, $mysqli;
	$userid = $_SESSION['userid'];
	$error = $_GET['error'];
	
	$queryapi = $mysqli->prepare('SELECT HEX(apikey) FROM users WHERE userid = ?');
	$queryapi->bind_param('i', $userid);
	$queryapi->bind_result($apikey);
	$queryapi->execute();
	$queryapi->fetch();
	$queryapi->close();
	if(is_null($apikey)) $apikey = "None Set";
	
	if ($error)
	{
		echo '<p class="error">';
		switch ($error)
		{
			case 1:
				echo 'Current password incorrect';
			break;
			case 2:
				echo 'New password and confirm password must match';
			break;
		}
		echo '</p>', $eol;
	}
	
	?>
	<form action="changepasswd_exec.php" method="post" onsubmit="return checkPasswords();">
	<fieldset>
	<legend>Change Password:</legend>
	<table border="0" class="noborder">
	<tr><td class="noborder">Current Password:</td><td class="noborder"><input type="password" name="oldpassword"></td></tr>
	<tr><td colspan="2" class="noborder">&nbsp;</td></tr>
	<tr><td class="noborder">New Password:</td><td class="noborder"><input type="password" id="newpw" name="newpassword"></td></tr>
	<tr><td class="noborder">Confirm Password:</td><td class="noborder"><input type="password" id="conpw" name="confirmpassword"></td></tr>
	<tr><td colspan="2" class="noborder"><input type="submit" value="Change Password" style="right:0px;"></td></tr>
	</table>
	</fieldset>
	</form>
	<h3>API info</h3>
	API key: <? echo $apikey;?> <a href="changeapikey_exec.php">Generate new API key</a> <a href="changeapikey_exec.php?clear=1">Remove API key</a><br>
	API url: http://vps.dynamicarcade.co.uk/SpaceMMO/api/api.php?wsdl
	<?
}
