<?
include_once 'includes/admin.inc.php';
checkIsAdmin();

include_once '../includes/template.inc.php';

template('Admin List Users', 'adminListUsersBody');

function adminListUsersBody()
{
	global $eol, $mysqli;
	$query = $mysqli->prepare('SELECT userID,username,bisadmin,COUNT(colonies.userid) FROM users LEFT JOIN colonies USING (userid) GROUP BY userid ORDER BY NULL');
	$result = $query->execute();
	$query->bind_result($userid,$username,$bisadmin,$colonies);

	echo '<table>', $eol;
	echo '<tr><th>User ID</th><th>User Name</th><th>Is Admin</th><th># of Colonies</th><th>Actions</th></tr>', $eol;

	while($query->fetch())
	{
		echo '<tr><td>',$userid,'</td><td>',$username,'</td><td>',$bisadmin?'Yes':'','</td><td>',$colonies,'</td><td>';
		if (!$bisadmin)
		{
			echo '<a href="makeadmin.php?userid=',$userid,'">Make Admin</a><br>';
			echo '<a href="impersonate.php?userid=',$userid,'">Impersonate</a><br>';
			echo '<a href="deleteuser_exec.php?userid=',$userid,'">Delete User</a>';
		}
		echo '</td></tr>', $eol;
	}
	echo '</table>', $eol;
	echo '<br>', $eol;
	echo 'Add User:<br>', $eol;
	echo '<form action="adduser_exec.php" method="post">', $eol;
	echo 'Username: <input type="text" name="username"><br>', $eol;
	echo 'Password: <input type="password" name="password"><br>', $eol;
	echo '<input type="submit" value="Submit">', $eol;
	echo '</form>', $eol;
}
