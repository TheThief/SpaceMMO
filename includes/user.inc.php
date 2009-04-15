<?php

if (!defined('USER_DEBUG')) define('USER_DEBUG',DEBUG);
if (!defined('COLONY_DEBUG')) define('COLONY_DEBUG',USER_DEBUG);
include_once 'colony.inc.php';

function adduser($username, $password, $planetid=null)
{
	global $mysqli, $eol;

	$query = $mysqli->prepare('INSERT INTO users (username, passhash) VALUES (?, UNHEX(?))');
	$query->bind_param('ss', $username, $passhash);
	$passhash = sha1($password);
	$query->execute();
	$userid = $query->insert_id;
	$query->close();

	if (USER_DEBUG) echo 'User \'', $username, '\' added successfully', $eol;

	if (!$planetid)
	{
		if (USER_DEBUG) echo 'No planet specified for initial colony, choosing one automatically', $eol;

		$query = $mysqli->prepare('SELECT planetid FROM planets LEFT JOIN (SELECT systemid FROM colonies LEFT JOIN planets USING (planetid) GROUP BY systemid ORDER BY NULL) colonisedsystems USING (systemid) WHERE type=3 AND colonisedsystems.systemid IS NULL LIMIT 1');
		$query->execute();
		$query->bind_result($planetid);
		$result = $query->execute();
		if (!$result || !$planetid)
		{
			echo 'Error choosing a colony planet', $eol;
			exit;
		}
		$query->close();
	}

	if (USER_DEBUG) echo 'Chosen \'', $planetid, '\' for colony', $eol;

	colonise($planetid, $userid);

	return $userid;
}
?>
