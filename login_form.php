<?php
include_once 'includes/start.inc.php';

include_once 'includes/template.inc.php';
template('Log In', 'loginBody');

function loginBody()
{
	global $eol, $mysqli;
	$error = isset($_GET['error']) ? $_GET['error'] : '';
	$page = isset($_GET['p']) ? $_GET['p'] : '';
	$qs = isset($_GET['q']) ? $_GET['q'] : '';
	if ($error)
	{
		echo '<p class="error">';
		switch ($error)
		{
			case 1:
				echo 'Login failed';
			break;
			case 2:
				echo 'Session expired, please log in again';
			break;
		}
		echo '</p>', $eol;
	}
?><form action="login_exec.php" method="post">
<?php
if($page != "") echo "<input type=\"hidden\" name=\"p\" value=\"".$page."\">\n";
if($qs != "") echo "<input type=\"hidden\" name=\"q\" value=\"".$qs."\">\n";
?>
<input type="text" name="username">
<input type="password" name="password">
<input type="submit" value="Submit">
</form>
<a href="register_form.php">Register</a>
<?php
}
?>
