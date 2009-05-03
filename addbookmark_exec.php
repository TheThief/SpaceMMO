<?php
include_once 'includes/start.inc.php';
checkLoggedIn();

include_once 'includes/template.inc.php';
template('Add Bookmark', 'addBookmarkBody');

function addBookmarkBody()
{
	global $eol, $mysqli;
	$userid = $_SESSION['userid'];
	$planetid = $_POST['planet'];
	$description = $_POST['description'];
	if($description =="") $description = "-";
	
	if (!$planetid)
	{
		echo 'Error: You can\'t just bookmark the middle of nowhere! Choose a planet.', $eol;
		exit;
	}

	$query = $mysqli->prepare('SELECT planetid FROM bookmarks WHERE userid=? AND planetid=?');
	$query->bind_param('ii', $userid,$planetid);
	$query->execute();
	$query->bind_result($result);
	$result = $query->fetch();
	$query->close();
	if ($result)
	{
		echo 'Already bookmarked.', $eol;
		exit;
	}

	$query = $mysqli->prepare('INSERT INTO bookmarks (userid,planetid,description) VALUES (?,?,?)');
	$query->bind_param('iis', $userid,$planetid,$description);
	$query->execute();
	$query->close();

	header('HTTP/1.1 303 See Other');
	header('Location: list_bookmarks.php');

	echo 'Bookmark added successfully<br>', $eol;
	echo '<a href="list_bookmarks.php">Go</a> to bookmarks.<br>', $eol;
	echo '<a href="view_planet.php?planet=',$planetid,'">Return</a> to planet.', $eol;
}
?>
