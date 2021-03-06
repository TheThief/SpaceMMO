<?php
include_once 'includes/start.inc.php';
checkLoggedIn();

include_once 'includes/template.inc.php';
template('Account Info', 'accountBody');

function accountBody()
{
	global $eol, $mysqli;
	$userid = $_SESSION['userid'];
	$error = isset($_GET['error']) ? $_GET['error'] : '';
	
	$queryapi = $mysqli->prepare('SELECT HEX(apikey) FROM users WHERE userid = ?');
	$queryapi->bind_param('i', $userid);
	$queryapi->bind_result($apikey);
	$queryapi->execute();
	$queryapi->fetch();
	$queryapi->close();
	if (is_null($apikey)) $apikey = 'None Set';
	
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
	<tr><td>Current Password:</td><td><input type="password" name="oldpassword"></td></tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td>New Password:</td><td><input type="password" id="newpw" name="newpassword"></td></tr>
	<tr><td>Confirm Password:</td><td><input type="password" id="conpw" name="confirmpassword"></td></tr>
	<tr><td colspan="2" align="right"><input type="submit" value="Change Password"></td></tr>
	</table>
	</fieldset>
	</form>
	<h3>API info</h3>
	API key: <?php echo $apikey;?> <a href="changeapikey_exec.php">Generate new API key</a> <a href="changeapikey_exec.php?clear=1">Remove API key</a><br>
	API url: http://spacemmo.dynamicarcade.co.uk/api/api.php?wsdl
	<?php
}
?>
