<?php

include_once 'colony.inc.php';

function adduser($username, $password, $planetid=null)
{
	global $mysql, $eol;

	$query = $mysqli->prepare('INSERT INTO users (username, passhash) VALUES (?, UNHEX(?))');
	$query->bind_param('ss', $username, $passhash);
	$passhash = sha1($password);
	$query->execute();
	$userid = $query->insert_id;
	$query->close();

	if (DEBUG) echo 'User \'', $username, '\' added successfully', $eol;

	if (!$planetid)
	{
		$query = $mysqli->prepare('SELECT planetid FROM planets LEFT JOIN (SELECT systems.*,COUNT(colonies.planetid) AS colonycount FROM systems LEFT JOIN planets USING (systemid) LEFT JOIN colonies USING (planetid) GROUP BY systemid ORDER BY NULL) systemcolonies USING (systemid) WHERE type=3 AND colonycount=0 LIMIT 1');
		$query->execute();
		$query->bind_result($planetid);
		$result = $query->execute();
		if (!$result)
		{
			echo 'Error choosing a colony planet', $eol;
			exit;
		}
		$query->close();
	}

	if (DEBUG) echo 'Chosen \'', $planetid, '\' for colony', $eol;

	colonise($planetid, $userid);

	return $userid;
}
?>
