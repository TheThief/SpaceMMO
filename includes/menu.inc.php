<ul>
<?
if (!isLoggedIn())
{
	echo '<li><a href="/SpaceMMO/login_form.php">Log In</a></li>', $eol;
}
else
{
	echo '<li><a href="/SpaceMMO/list_colonies.php">Colonies List</a></li>', $eol;
	echo '<li><a href="/SpaceMMO/list_bookmarks.php">Bookmarks List</a></li>', $eol;
	echo '<li><a href="/SpaceMMO/list_shipdesigns.php">Ship Designs</a></li>', $eol;

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
		echo '<li><a href="/SpaceMMO/view_systems.php?system=',$systemid,'">View Galaxy</a></li>', $eol;
	}
	else
	{
		echo '<li><a href="/SpaceMMO/view_systems.php">View Galaxy</a></li>', $eol;
	}
	echo "<li><a href=\"/SpaceMMO/account.php\">Account Info</a></li>" . $eol;
	echo '<li><a href="/SpaceMMO/help/getting_started.php">Help</a></li>', $eol;
}
?>
</ul>
