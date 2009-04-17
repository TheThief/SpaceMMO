<?php
include_once 'includes/start.inc.php';
checkLoggedIn();

include_once 'includes/template.inc.php';
template('Add Bookmark', 'addBookmarkBody');

function addBookmarkBody()
{
	global $eol, $mysqli;
	$userid = $_SESSION['userid'];
	$planetid = $_GET['planet'];

	if (!$planetid)
	{
		echo 'Error: You have to choose a bookmark to delete...', $eol;
		exit;
	}

	$query = $mysqli->prepare('SELECT planetid FROM bookmarks WHERE userid=? AND planetid=?');
	$query->bind_param('ii', $userid,$planetid);
	$query->execute();
	$query->bind_result($result);
	$result = $query->fetch();
	$query->close();
	if (!$result)
	{
		echo 'Error: Planet is not bookmarked.', $eol;
		exit;
	}

	$query = $mysqli->prepare('DELETE FROM bookmarks WHERE userid=? AND planetid=?');
	$query->bind_param('ii', $userid,$planetid);
	$query->execute();
	$query->close();

	header('HTTP/1.1 303 See Other');
	header('Location: list_bookmarks.php');

	echo 'Bookmark deleted<br>', $eol;
	echo '<a href="list_bookmarks.php">Return</a> to bookmarks.<br>', $eol;
}
?>
