<ul>
<?
if (!isLoggedIn())
{
	echo '<li><a href="/SpaceMMO/login_form.php">Log In</a></li>', $eol;
}
else
{
	echo '<li><a href="/SpaceMMO/list_colonies.php">Colonies List</a></li>', $eol;
	echo '<li><a href="/SpaceMMO/list_shipdesigns.php">Ship Designs</a></li>', $eol;

	$systemid = $_GET['system'];
	$planetid = $_GET['planet'];
	if (!is_numeric($systemid) && is_numeric($planetid))
	{
		$query = $mysqli->prepare('SELECT systemid FROM planets WHERE planetid=?');
		if (!$query)
		{
			echo 'error: ', $mysqli->error, $eol;
			exit;
		}

		$query->bind_param('i', $planetid);

		$result = $query->execute();
		if (!$result)
		{
			echo 'error: ', $query->error, $eol;
			exit;
		}

		$query->bind_result($systemid);
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
	echo '<li><a href="/SpaceMMO/help/getting_sarted.php">Help</a></li>', $eol;
}
?>
</ul>
