<?
include_once 'includes/admin.inc.php';
checkIsAdmin();
$effects = array();
$i =0;
$query = $mysqli->prepare('SELECT buildingid,consumestype,consumesbase,consumeslinear,consumesmultiplier,effecttype,effectbase,effectlinear,effectmultiplier FROM buildings ORDER BY buildingid');
	if (!$query)
	{
		echo 'error: ', $mysqli->error, $eol;
		exit;
	}

	$result = $query->execute();
	if (!$result)
	{
		echo 'error: ', $query->error, $eol;
		exit;
	}

	$query->bind_result($buildingid,$consumestype,$consumesbase,$consumeslinear,$consumesmultiplier,$effecttype,$effectbase,$effectlinear,$effectmultiplier);


	while($query->fetch()){
			if($consumesbase > 0){
				$effects[$i][0] = $buildingid;
				$effects[$i][1] = $consumestype;
				$effects[$i][2] = 0-$consumesbase;
				$effects[$i][3] = $consumeslinear;
				$effects[$i][4] = $consumesmultiplier;
				$i++;
			}
			if($effectbase > 0){
				$effects[$i][0] = $buildingid;
				$effects[$i][1] = $effecttype;
				$effects[$i][2] = $effectbase;
				$effects[$i][3] = $effectlinear;
				$effects[$i][4] = $effectmultiplier;
				$i++;
			}
	}
	var_dump($effects);
?>