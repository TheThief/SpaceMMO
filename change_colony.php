<?
include_once 'includes/start.inc.php';
checkLoggedIn();

include_once 'includes/template.inc.php';
template('Change Active Colony', 'changeColonyBody');

function changeColonyBody()
{
	global $eol, $mysqli;
	$userid = $_SESSION['userid'];

	$colonyid = $_GET['planet'];

	$query = $mysqli->prepare('SELECT userid FROM colonies WHERE planetid=?');
	$query->bind_param('i', $colonyid);
	$query->execute();
	$query->bind_result($colonyuserid);
	$result = $query->fetch();
	if (!$result)
	{
		echo 'error: No such planet', $eol;
		exit;
	}
	$query->close();

	if ($userid != $colonyuserid)
	{
		echo 'error: Not your colony', $eol;
		exit;
	}

	$_SESSION['colony'] = $colonyid;

	header('HTTP/1.1 303 See Other');
	header('Location: view_planet.php?planet='.$colonyid);
}
