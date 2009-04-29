<?
include_once('functions.inc.php');

function prodSummary($id, $current, $max, $delta)
{
	$symbol = '.';
	if ($delta > 0)
	{
		$symbol = '+';
	}
	else if ($delta < 0)
	{
		$symbol = '-';
	}
	$title = thousands($current).'/'.thousands($max).' ('.getSigned($delta).')';
	return '<span id="'.$id.'" title="'.$title.'">'.thousands($current).' '.$symbol.'</span>';
}

function template($title, $bodyfunc, $menufunc=null, $headerfunc=null)
{
	global $eol, $mysqli;
	$userid = $_SESSION['userid'];

	/*echo '<?xml version="1.0" encoding="utf-8"?>', $eol;*/
	//echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">', $eol;
	//echo '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">', $eol;
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">', $eol;
	echo '<html>', $eol;
	echo '<head>', $eol;
	echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">', $eol;
	echo '<title>SpaceMMO - ', $title, '</title>', $eol;
	echo '<link rel="stylesheet" type="text/css" href="/SpaceMMO/style.css">', $eol;
	if ($headerfunc)
	{
		$headerfunc();
	}
	echo '</head>', $eol;
	echo '<body>', $eol;
	echo '', $eol;
	echo '<div class="menuouter">', $eol;
	echo '<div class="logo">', $eol;
	echo '<img src="/SpaceMMO/images/logo.png" alt="Logo!">', $eol;
	echo '</div>', $eol;
	echo '', $eol;
	echo '<div class="menu">', $eol;
	echo '<h1>Menu</h1>', $eol;
	include dirname(__FILE__).'/menu.inc.php';
	if ($menufunc)
	{
		$menufunc();
	}
	if (isAdmin())
	{
		include(dirname(__FILE__).'/../admin/includes/adminmenu.inc.php');
	}
	echo '</div>', $eol;
	if (isLoggedIn())
	{
		$colonyid = $_SESSION['colony'];
		if (!$colonyid)
		{
			$query = $mysqli->prepare('SELECT planetid FROM colonies WHERE userid=? ORDER BY colonylevel DESC LIMIT 1');
			$query->bind_param('i', $userid);
			$query->execute();
			$query->bind_result($colonyid);
			$query->fetch();
			$query->close();
			$_SESSION['colony'] = $colonyid;
		}

		$query = $mysqli->prepare('SELECT systemid,orbit,colonylevel,colonies.metal,maxmetal,metalproduction,colonies.deuterium,maxdeuterium,deuteriumproduction,energy,maxenergy,energyproduction,shipconstruction FROM colonies LEFT JOIN planets USING (planetid) WHERE planetid=?');
		$query->bind_param('i', $colonyid);
		$query->execute();
		$query->bind_result($systemid,$orbit,$colonylevel,$metal,$maxmetal,$metalprod,$deuterium,$maxdeuterium,$deuteriumprod,$energy,$maxenergy,$energyprod,$shipconstruction);
		$query->fetch();
		$query->close();

		echo '<div class="colonysummary">', $eol;
		echo '<h2>',systemcode($systemid,$orbit),'</h2>', $eol;
		echo '<ul>', $eol;
		echo '<li>Metal: ',prodSummary('summary_metal', $metal, $maxmetal, $metalprod),'</li>', $eol;
		echo '<li>Deuterium: ',prodSummary('summary_deuterium', $deuterium, $maxdeuterium, $deuteriumprod),'</li>', $eol;
		echo '<li>Energy: ',prodSummary('summary_energy', $energy, $maxenergy, $energyprod),'</li>', $eol;
		planetChanger();
		echo '</div>', $eol;
	}
	echo '</div>', $eol;
	echo '', $eol;
	echo '<div class="bodyouter">', $eol;
	echo '<div class="title">', $eol;
	echo '<h1>', $title, '</h1>', $eol;
	echo '</div>', $eol;
	echo '<div class="body">', $eol;
	$bodyfunc();
	echo '</div>', $eol;
	echo '</div>', $eol;
	echo '', $eol;
	echo '</body>', $eol;
	echo '</html>', $eol;
}
?>
