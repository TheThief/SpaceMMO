<?phpinclude 'includes/admin.inc.php';checkIsAdmin();$eol = "\n";header('Content-type: text/plain');// need to log in as power user for this$mysqli = new mysqli($db_server,$db_admin_user,$db_admin_pass,$db_db);//$result = $mysqli->query('DROP TABLE shipbuildqueue');$result = $mysqli->query('CREATE TABLE shipbuildqueue(queueID INT NOT NULL AUTO_INCREMENT, planetID INT NOT NULL, designID INT NOT NULL, count INT NOT NULL DEFAULT 1, buildprogress INT NOT NULl DEFAULT 0, PRIMARY KEY (queueID))');if ($result){	echo 'table \'shipbuildqueue\' created successfully', $eol;}else{	echo 'error: ', $mysqli->error, $eol;}$result = $mysqli->query('ALTER TABLE shipbuildqueue ADD INDEX planetID (planetID)');if ($result){	echo 'index on \'planetID\' created successfully', $eol;}else{	echo 'error: ', $mysqli->error, $eol;}$mysqli->close();?>