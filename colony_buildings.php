<?
include_once 'includes/start.inc.php';
checkLoggedIn();

include_once 'includes/template.inc.php';

$countarray=array();
template('Building at Colony', 'colonyBuildingsBody');

function colonyBuildingsBody()
{
	global $eol, $mysqli, $countarray, $lookups;
	$userid = $_SESSION['userid'];
	$planetid = $_GET['planet'];

	$countpoint=0;
	$query = $mysqli->prepare('SELECT colonylevel,metal,maxmetal,metalproduction,deuterium,maxdeuterium,deuteriumproduction,energy,maxenergy,energyproduction FROM colonies WHERE colonies.userid=? AND colonies.planetID = ?;');
	$query->bind_param('ii', $userid, $planetid);
	$query->execute();
	$query->bind_result($colonylevel,$metal,$maxmetal,$metalprod,$deuterium,$maxdeuterium,$deuteriumprod,$energy,$maxenergy,$energyprod);
	$result = $query->fetch();
	if (!$result)
	{
		echo 'Error: You don\'t have a colony on this planet.', $eol;
		exit;
	}
	$query->close();

	echo '<table>', $eol;
	echo '<tr><th>Metal</th><th>Deuterium</th><th>Energy</th></tr>', $eol;
	echo '<tr>';
	echo "<td>$metal/$maxmetal (".getSigned($metalprod*TICKS_PH).")";
	if($metalprod<0 && $metal>abs($metalprod)) {
		$mptime=(floor(abs($metal/$metalprod))*TICK)-getTickElapsed();
		$countarray[$countpoint]=$mptime;
		echo '<br><span class="error" id="btimesp',$countpoint,'">Runs out in: </span><span class="error" id="timesp',$countpoint++,'"></span>';
	}
	if($metalprod < 0 && $metal < abs($metalprod)) echo '<br><span class="error">',($metal==0)?"No":"Low",' Metal</span>';
	echo "</td>";
	echo "<td>$deuterium/$maxdeuterium (".getSigned($deuteriumprod*TICKS_PH).")";
	if($deuteriumprod<0 && $deuterium>abs($deuteriumprod)) {
                $dptime=(floor(abs($deuterium/$deuteriumprod))*TICK)-getTickElapsed();
                $countarray[$countpoint]=$dptime;
                echo '<br><span class="error" id="btimesp',$countpoint,'">Runs out in: </span><span class="error" id="timesp',$countpoint++,'"></span>';
        }
	if($deuteriumprod < 0 && $deuterium < abs($deuteriumprod)) echo '<br><span class="error">',($deuterium==0)?"No":"Low",' Deuterium</span>';
        echo "</td>";

	echo "<td>$energy/$maxenergy (".getSigned($energyprod*TICKS_PH).")";
        if($energyprod<0 && $energy>abs($energyprod)) {
                $eptime=(floor(abs($energy/$energyprod))*TICK)-getTickElapsed();
                $countarray[$countpoint]=$eptime;
                echo '<br><span class="error" id="btimesp',$countpoint,'">Runs out in: </span><span class="error" id="timesp',$countpoint++,'"></span>';
        }
	if($energyprod < 0 && $energy < abs($energyprod)) echo '<br><span class="error">',($energy==0)?"No":"Low",' Energy</span>';
        echo "</td>";
	echo '</tr>', $eol;
	echo '</table>', $eol;

	echo '<table>', $eol;
	echo '<col><col style="width: 20em;"><col>', $eol;
	echo '<tr><th>Building</th><th>Description</th><th>Actions</th></tr>', $eol;

	$query = $mysqli->prepare('SELECT buildingID,level,output,buildingname,buildingdescription,mincolonylevel,maxbuildinglevel,
		building_cost(buildingid,IF(level,level+1,1)) AS cost,
		consumestype, consumes, IF(level,colony_building_consumes_max(planetid,buildingid,level+1)-colony_building_consumes_max(planetid,buildingid,level),colony_building_consumes_max(planetid,buildingid,1)) AS consumesdelta,
		effecttype, effect, IF(level,colony_building_effect_max(planetid,buildingid,level+1)-colony_building_effect_max(planetid,buildingid,level),colony_building_effect_max(?,buildingid,1)) AS effectdelta, colony_building_consumes_max(planetid,buildingid,level) AS maxconsumes, colony_building_effect_max(planetid,buildingid,level) AS maxeffect, bignorecolonylevel
		FROM (SELECT planetid,buildingid,level,output,colony_building_consumes(planetid,buildingid) AS consumes,colony_building_effect(planetid,buildingid) AS effect FROM colonybuildings WHERE planetid = ?) dtable
		RIGHT JOIN buildings USING (buildingid)');
	$query->bind_param('ii', $planetid, $planetid);
	$query->execute();
	$query->bind_result($buildingid,$level,$output,$name,$description,$mincolonylevel,$maxlevel,$cost,$consumestype,$consumes,$consumesdelta,$effecttype,$effect,$effectdelta,$maxconsumes,$maxeffect,$bignorecolonylevel);

	while($query->fetch())
	{
		echo '<tr style="height: 8em">';
		if ($level)
		{
			echo '<td><b>', $name, '</b><br>';
			echo 'Level ', $level;
		}
		else
		{
			echo "<td><b>$name</b><br>Not built</td>";
		}

		echo '<td>';
		echo $description;
		if ($level && ($effecttype || $consumestype))
		{
			echo '<br>';
			if ($effecttype && $effecttype != 4)
			{
				if ($effecttype<=3)
				{
					echo $lookups["buildingEffect"][$effecttype], ': <span id="effsp',$buildingid,'">', $effect*TICKS_PH, '</span> ';
				}
				else
				{
					echo $lookups["buildingEffect"][$effecttype], ': <span id="effsp',$buildingid,'">', $effect, '</span> ';
				}
			}
			if ($consumestype)
			{
				if ($consumestype<=3)
				{
					echo $lookups["resourceType"][$consumestype], ' Use: <span id="conssp',$buildingid,'">', $consumes*TICKS_PH,'</span><br>';
				}
				else
				{
					echo $lookups["resourceType"][$consumestype], ' Use: <span id="conssp',$buildingid,'">', $consumes,'</span><br>';
				}
				prodDropdown($output,$planetid,$buildingid,$maxconsumes,$maxeffect);
			}
		}
		echo '</td>';

		echo '<td>';
		if ($level >= $maxlevel)
		{
			echo '<span>Max Level</span>', $eol;
		}
		else
		{
			if ($level)
			{
				echo 'Upgrade to level ', $level+1, ':';
			}
			else
			{
				echo 'Build level 1:';
			}
			$bCanBuild = true;
			if ($buildingid !=1 && $level+1 > $colonylevel && !$bignorecolonylevel)
			{
				echo '<br><span class="error">Colony level too low</span>', $eol;
				$bCanBuild = false;
			}
			if ($buildingid !=1 && $mincolonylevel > $colonylevel)
			{
				echo '<br><span class="error">Colony level needs to be at least ',$mincolonylevel,'</span>', $eol;
				$bCanBuild = false;
			}
			if ($cost > $metal)
			{
				//echo '<br><span class="error">Not enough metal</span>', $eol;
				$bCanBuild = false;
			}
			if ($cost > $maxmetal)
			{
				echo '<br><span class="error">Not enough metal storage</span>', $eol;
				echo '<br><span class="error">You need ',($cost-$maxmetal),' more metal storage</span>', $eol;
				$bCanBuild = false;
			}
			if ($bCanBuild)
			{
				if ($level)
				{
					echo ' <a href="build_building_exec.php?upgrade=1&planet=', $planetid, '&building=', $buildingid, '">Upgrade</a>';
				}
				else
				{
					echo ' <a href="build_building_exec.php?planet=', $planetid, '&building=', $buildingid, '">Build</a>';
				}
			}
			if ($cost > $metal)
			{
				echo '<br><span class="error">Cost: ', $cost, ' metal</span>';
				echo '<br><span class="error">You need: ', $cost-$metal, ' more metal</span>';
				if(($metalprod > 0) && ($cost <= $maxmetal)){
					$gtime = ceil(($cost-$metal)/$metalprod);
					$rtime = formatSeconds("h:i:s",($gtime*TICK)-getTickElapsed());
					$countarray[$countpoint]=($gtime*TICK)-getTickElapsed();
					echo '<br><span class="error" id="btimesp',$countpoint,'">Available in: </span><span class="error" id="timesp',$countpoint++,'" title="">$rtime</span>';
				}
			}
			else
			{
				echo '<br>Cost: ', $cost, ' metal';
			}
			if ($effecttype)
			{
				if ($effecttype<=3)
				{
					echo '<br>', $lookups["buildingEffect"][$effecttype], ': +', $effectdelta*TICKS_PH;
				}
				else
				{
					echo '<br>', $lookups["buildingEffect"][$effecttype], ': +', $effectdelta;
				}
			}
			if ($consumestype)
			{
				if ($consumestype<=3)
				{
					echo '<br>', $lookups["resourceType"][$consumestype], ' Use: +', $consumesdelta*TICKS_PH;
				}
				else
				{
					echo '<br>', $lookups["resourceType"][$consumestype], ' Use: +', $consumesdelta;
				}
			}
		}
		echo '</td>';
		echo '</tr>', $eol;
	}
	echo '</table>', $eol;
}
?>
<script type="text/javascript"> 
<?
//print_r($countarray);
foreach($countarray as $cid => $ctime){
	echo "liveCount(".$ctime.",\"timesp".$cid."\",1,1,1);";
}
?>
</script>

