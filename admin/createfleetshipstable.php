<?phpinclude 'includes/admin.inc.php';checkIsAdmin();$eol = "\n";header('Content-type: text/plain');// need to log in as power user for this$mysqli = new mysqli($db_server,$db_admin_user,$db_admin_pass,$db_db);//$result = $mysqli->query('DROP TABLE fleetships');$result = $mysqli->query('CREATE TABLE fleetships(fleetID INT NOT NULL, designID INT NOT NULL, count INT NOT NULL DEFAULT 1, PRIMARY KEY (fleetID, designID))');if ($result){	echo 'table \'fleetships\' created successfully', $eol;}else{	echo 'error: ', $mysqli->error, $eol;}//$result = $mysqli->query('ALTER TABLE colonybuildings ADD INDEX planetID (planetID)');//if ($result)//{//	echo 'index on \'planetID\' created successfully', $eol;//}//else//{//	echo 'error: ', $mysqli->error, $eol;//}$mysqli->close();?>