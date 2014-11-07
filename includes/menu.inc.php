<ul>
<?php
if (!isLoggedIn())
{
	echo '<li><a href="/login_form.php">Log In</a></li>', $eol;
}
else
{
	echo '<li><a href="/list_colonies.php">Colonies List</a></li>', $eol;
	echo '<li><a href="/list_bookmarks.php">Bookmarks List</a></li>', $eol;
	echo '<li><a href="/list_shipdesigns.php">Ship Designs</a></li>', $eol;

	$systemid = isset($_GET['system']) ? $_GET['system'] : '';
	$planetid = isset($_GET['planet']) ? $_GET['planet'] : '';
	if (!is_numeric($systemid) && is_numeric($planetid))
	{
		$query = $mysqli->prepare('SELECT systemid FROM planets WHERE planetid=?');
		$query->bind_param('i', $planetid);
		$query->bind_result($systemid);
		$result = $query->execute();
		$query->fetch();
		$query->close();
	}
	if (is_numeric($systemid))
	{
		echo '<li><a href="/view_systems.php?system=',$systemid,'">View Galaxy</a></li>', $eol;
	}
	else
	{
		echo '<li><a href="/view_systems.php">View Galaxy</a></li>', $eol;
	}
	echo '<li><a href="/account.php">Account Info</a></li>' . $eol;
	echo '<li><a href="/help/getting_started.php">Help</a></li>', $eol;
}
?>
</ul>
