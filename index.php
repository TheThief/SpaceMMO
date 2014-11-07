<?php
include_once 'includes/start.inc.php';

if (isLoggedIn())
{
	header("Location: list_colonies.php", true, 303); // 303 See Other
}
else
{
	header("Location: login_form.php", true, 303); // 303 See Other
}
?>