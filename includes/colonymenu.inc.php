<?php
function colonyMenu()
{
	global $eol;
	$planetid = $_SESSION['colony'];
	echo '<h3>Colony Menu</h3>', $eol;
	echo '<ul>', $eol;
	echo '<li><a href="/colony_buildings.php?planet=', $planetid, '">Upgrade Buildings</a></li>', $eol;
	echo '<li><a href="/build_ships.php?planet=', $planetid, '">Build Ships</a></li>', $eol;
	echo '<li><a href="/view_ships.php?planet=', $planetid, '">Ships in Orbit</a></li>', $eol;
	echo '</ul>', $eol;
}
