<?
include 'includes/start.inc.php';
checkLoggedIn();

include 'includes/template.inc.php';
include 'includes/colonymenu.inc.php';

$countarray=array();
template('Colony Buildings', 'colonyBuildingsBody', 'colonyMenu');

function colonyBuildingsBody()
{
	global $eol, $mysqli, $countarray, $lookups;
	$userid = $_SESSION['userid'];
	$planetid = $_GET['planet'];
	$countpoint=0;

	?>
	<script type="text/javascript">
		function updateProdVals(id,output,maxc,maxe){
			cspan = document.getElementById("conssp"+id);
			espan = document.getElementById("effsp"+id);	
			var index = document.getElementById("pdd"+id).selectedIndex;
			var pval = parseFloat(document.getElementById("pdd"+id)[index].value);
			pval = pval/100;
			var cc = parseInt(maxc * output);
			var ce = parseInt(maxe * output);
			var nc = parseInt(maxc * pval);
			var ne = parseInt(maxe * pval);
			var ed =0;
			var cd=0;
			if(pval==output){
				cspan.innerHTML = cc;
				cspan.title="";
				espan.innerHTML = ce;
				espan.title="";
			}else{				
				cd = nc-cc;
				ed = ne-ce;
				cspan.innerHTML = nc + "("+ cc+")";
				cspan.title=((cd>=0)?"+":"") + cd;
                                espan.innerHTML = ne + "("+ ce+")";
				espan.title=((ed>=0)?"+":"") + ed;
			}
		}
	</script>
	<?
	$query = $mysqli->prepare('SELECT colonylevel,metal,maxmetal,metalproduction,deuterium,maxdeuterium,deuteriumproduction,energy,maxenergy,energyproduction FROM colonies WHERE colonies.userid=? AND colonies.planetID = ?;');
	if (!$query)
	{
		echo 'error: ', $mysqli->error, $eol;
		exit;
	}
	$query->bind_param('ii', $userid, $planetid);

	$result = $query->execute();
	if (!$result)
	{
		echo 'error: ', $query->error, $eol;
		exit;
	}

	$query->bind_result($colonylevel,$metal,$maxmetal,$metalprod,$deuterium,$maxdeuterium,$deuteriumprod,$energy,$maxenergy,$energyprod);
	$result = $query->fetch();
	if (!$result)
	{
		echo 'Error: You don\'t have a colony on this planet.', $eol;
		exit;
	}
	$query->close();

	echo '<h1>Build at Colony</h1>', $eol;
	echo '<table>', $eol;
	echo '<tr><th>Metal</th><th>Deuterium</th><th>Energy</th></tr>', $eol;
	echo '<tr>';
	echo "<td>$metal/$maxmetal (".getSigned($metalprod).")";
	if($metalprod<0 && $metal>abs($metalprod)) {
		$mptime=(floor(abs($metal/$metalprod))*600)-getTickElapsed();
		$countarray[$countpoint]=$mptime;
		echo '<br><span class="error" id="btimesp',$countpoint,'">Runs out in: </span><span class="error" id="timesp',$countpoint++,'"></span>';
	}
	if($metal<abs($metalprod)) echo '<br><span class="error">',($metal==0)?"No":"Low",' Metal</span>';
	echo "</td>";
	echo "<td>$deuterium/$maxdeuterium (".getSigned($deuteriumprod).")";
	if($deuteriumprod<0 && $deuterium>abs($deuteriumprod)) {
                $dptime=(floor(abs($deuterium/$deuteriumprod))*600)-getTickElapsed();
                $countarray[$countpoint]=$dptime;
                echo '<br><span class="error" id="btimesp',$countpoint,'">Runs out in: </span><span class="error" id="timesp',$countpoint++,'"></span>';
        }
	if($deuterium<abs($deuteriumprod)) echo '<br><span class="error">',($deuterium==0)?"No":"Low",' Deuterium</span>';
        echo "</td>";

	echo "<td>$energy/$maxenergy (".getSigned($energyprod).")";
        if($energyprod<0 && $energy>abs($energyprod)) {
                $eptime=(floor(abs($energy/$energyprod))*600)-getTickElapsed();
                $countarray[$countpoint]=$eptime;
                echo '<br><span class="error" id="btimesp',$countpoint,'">Runs out in: </span><span class="error" id="timesp',$countpoint++,'"></span>';
        }
	if($energy<abs($energyprod)) echo '<br><span class="error">',($energy==0)?"No":"Low",' Energy</span>';
        echo "</td>";
	echo '</tr>', $eol;
	echo '</table>', $eol;
	echo '<table>', $eol;
	echo '<col><col style="width: 20em;"><col>', $eol;
	echo '<tr><th>Building</th><th>Description</th><th>Actions</th></tr>', $eol;

	$query = $mysqli->prepare('SELECT buildingID,level,output,buildingname,buildingdescription,maxbuildinglevel,
		building_cost(buildingid,IF(level,level+1,1)) AS cost,
		consumestype, consumes, IF(level,colony_building_consumes_max(planetid,buildingid,level+1)-colony_building_consumes_max(planetid,buildingid,level),colony_building_consumes_max(planetid,buildingid,1)) AS consumesdelta,
		effecttype, effect, IF(level,colony_building_effect_max(planetid,buildingid,level+1)-colony_building_effect_max(planetid,buildingid,level),colony_building_effect_max(?,buildingid,1)) AS effectdelta, colony_building_consumes_max(planetid,buildingid,level) AS maxconsumes, colony_building_effect_max(planetid,buildingid,level) AS maxeffect
		FROM (SELECT planetid,buildingid,level,output,colony_building_consumes(planetid,buildingid) AS consumes,colony_building_effect(planetid,buildingid) AS effect FROM colonybuildings WHERE planetid = ?) dtable
		RIGHT JOIN buildings USING (buildingid)');
	if (!$query)
	{
		echo 'error: ', $mysqli->error, $eol;
		exit;
	}
	$query->bind_param('ii', $planetid, $planetid);
	$planetid = $_GET['planet'];

	$result = $query->execute();
	if (!$result)
	{
		echo 'error: ', $query->error, $eol;
		exit;
	}

	$query->bind_result($buildingid,$level,$output,$name,$description,$maxlevel,$cost,$consumestype,$consumes,$consumesdelta,$effecttype,$effect,$effectdelta,$maxconsumes,$maxeffect);

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
				echo $lookups["buildingEffect"][$effecttype], ': <span id="effsp',$buildingid,'">', $effect, '</span> ';
			}
			if ($consumestype)
			{
				echo $lookups["resourceType"][$consumestype], ' Use: <span id="conssp',$buildingid,'">', $consumes,'</span><br>';
				prodDropdown($output,$planetid,$buildingid,$maxconsumes,$maxeffect);
				
			}
		}
		echo '</td>';

		if ($level >= $maxlevel)
		{
			echo '<span>Max Level</span><br>', $eol;
		}
		else
		{
			echo '<td>';
			if ($level)
			{
				echo 'Upgrade to level ', $level+1, ':';
			}
			else
			{
				echo 'Build level 1:';
			}
			$bCanBuild = true;
			if ($buildingid !=1 && $level+1 > $colonylevel)
			{
				echo '<br><span class="error">Colony level too low</span>', $eol;
				$bCanBuild = false;
			}
			if ($cost > $metal)
			{
				//echo '<br><span class="error">Not enough metal</span>', $eol;
				$bCanBuild = false;
			}
			if ($cost > $maxmetal)
			{
				//echo '<br><span class="error">Not enough metal storage</span>', $eol;
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
				if($metalprod > 0){
					$gtime = ceil(($cost-$metal)/$metalprod);
					$rtime = formatSeconds("h:i:s",($gtime*600)-getTickElapsed());
					$countarray[$countpoint]=($gtime*600)-getTickElapsed();
					echo '<br><span class="error" id="btimesp',$countpoint,'">Available in: </span><span class="error" id="timesp',$countpoint++,'" title=""></span>';
				}
			}
			else
			{
				echo '<br>Cost: ', $cost, ' metal';
			}
			if ($effecttype)
			{
				echo '<br>', $lookups["buildingEffect"][$effecttype], ': +', $effectdelta;
			}
			if ($consumestype)
			{
				echo '<br>', $lookups["resourceType"][$consumestype], ' Use: +', $consumesdelta;
			}
			echo '</td>';
			echo '</tr>', $eol;
		}
	}
	echo '</table>', $eol;
}
?>
<script type="text/javascript"> 
function reloadPage(){
	location.reload(true);
}
function liveCount(seconds,name,first){
	span=document.getElementById(name);
	if(first==1){
		spanb=document.getElementById("b" + name);
		var d = new Date();
		d.setTime(d.getTime()+(seconds*1000));
		span.title = d.toLocaleDateString() + " " + d.toLocaleTimeString();	
		spanb.title = d.toLocaleDateString() + " " + d.toLocaleTimeString();	
	}
	hours = Math.floor(seconds/3600);
	minutes = Math.floor(seconds/60)%60;
	sec = seconds%60;
	span.innerHTML =  hours + ":" + padString(minutes,"0",2) + ":" + padString(sec,"0",2);
	//span.title = hours + ":" + padString(minutes,"0",2) + ":" + padString(sec,"0",2);
	if (seconds>0){
		 setTimeout('liveCount('+(seconds - 1)+',"'+name+'",0);',1000);
	}else{
		 setTimeout('reloadPage()',10000);
	}
	
}

function padString(string,chr,len){
	tempstring = string.toString();
	while(tempstring.length < len) tempstring = chr + tempstring;
	return tempstring;
}
<?
//print_r($countarray);
foreach($countarray as $cid => $ctime){
	echo "liveCount(".$ctime.",\"timesp".$cid."\",1);";
}
?>
</script>

