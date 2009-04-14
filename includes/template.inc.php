<?
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
