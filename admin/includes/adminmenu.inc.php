<h3>Admin Menu</h3><ul><?if ($_SESSION['adminuserid']){	echo '<li><a href="/SpaceMMO/admin/impersonate.php">Stop Impersonating</a></li>', $eol;}	echo '<li><a href="/SpaceMMO/admin/listusers.php">Users List</a></li>', $eol;	echo '<li><a href="/SpaceMMO/admin/listsystems.php">Systems List</a></li>', $eol;	echo '<li><a href="/SpaceMMO/admin/listbuildings.php">Buildings List</a></li>', $eol;	echo '<li><a href="/SpaceMMO/admin/listshiphulls.php">Ship Hulls List</a></li>', $eol;	echo '<li><a href="/SpaceMMO/list_colonies_twig.php">Twig Test</a></li>', $eol;?></ul>