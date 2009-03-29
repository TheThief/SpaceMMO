<?php
include 'includes/start.inc.php';

header("HTTP/1.1 303 See Other");
if (isLoggedIn())
{
	header("Location: list_colonies.php");
}
else
{
	header("Location: login_form.php");
}
?>