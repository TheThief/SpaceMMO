<?
include 'includes/start.inc.php';
checkLoggedIn();

include 'includes/colonymenu.inc.php';
include 'includes/template.inc.php';
template('Build Ships', 'buildShipsBody','colonyMenu');

function buildShipsBody()
{
	global $eol, $mysqli;
	$userid = $_SESSION['userid'];
 	$planetid = $_GET['planet'];
	echo '<h1>Build Ships</h1>', $eol;
	$shiparray = array();
	$orderarray = array();
	$pcarray = array();
	$query = $mysqli->prepare('SELECT metal,maxmetal,metalproduction,shipconstruction FROM colonies WHERE planetid = ?;');
	if (!$query)
	{
		echo 'error: ', $mysqli->error, $eol;
		exit;
	}
	$query->bind_param('i', $planetid);
	$result = $query->execute();
	if (!$result)
	{
		echo 'error: ', $query->error, $eol;
		exit;
	}
	$query->bind_result($metal,$maxmetal,$metalprod,$shipprod);
	$query->fetch();
	$query->close();

	echo '<table>', $eol;
	echo '<tr><th>Metal</th><th>Build Rate</th></tr>', $eol;
	echo '<tr><td>',$metal,'/',$maxmetal,' (',getSigned($metalprod),')','</td><td>',$shipprod,'</td></tr>';
	echo '</table>', $eol;

	$query = $mysqli->prepare('SELECT shipname,count,buildprogress,metalcost,queueid FROM shipbuildqueue LEFT JOIN shipdesigns USING (designid) LEFT JOIN shiphulls USING(hullid) WHERE planetID = ? ORDER BY queueID ASC;');
	if (!$query)
	{
		echo 'error: ', $mysqli->error, $eol;
		exit;
	}
	$query->bind_param('i', $planetid);

	$result = $query->execute();
	if (!$result)
	{
		echo 'error: ', $query->error, $eol;
		exit;
	}
	$query->bind_result($designname,$count,$progress,$metalcost,$id);
	?>
	<h2>Build Queue</h2>
	<table>
		<tr><th>Design Name</th><th>Quantity</th><th>Ship Progress</th><th>Next Ship In</th><th>Order Complete In</th></tr>
	<?	
	$timeoffset=0;
	if($query->fetch())
	{
		do
		{	
			if($shipprod>0){
				$shipticks = ($metalcost-$progress)/$shipprod;
				$shiptime = (ceil(($shipticks+$timeoffset))*600)-getTickElapsed();
				$orderticks = (($metalcost*$count)-$progress)/$shipprod;
				$ordertime = (ceil(($orderticks+$timeoffset))*600)-getTickElapsed();
				$timeoffset += $orderticks;
				$shiparray[$id]=$shiptime;
				$orderarray[$id]=$ordertime;
				$pcarray[$id]=array(getTickElapsed(),$metalcost,$shipprod,$progress);
			}
			echo '<tr>';
			echo "<td>$designname</td>";
			echo "<td>$count</td>";
			echo "<td><span id=\"pcsp".$id."\">".(int)(($progress/$metalcost)*100)."</span>%</td>";
			echo "<td><span id=\"shsp".$id."\">-<span></td>";
			echo "<td><span id=\"orsp".$id."\">-<span></td>";
			echo '</tr>';

		} while ($query->fetch());
	}
	else
	{
		echo '<tr><td colspan="3">Empty</td></tr>';
	}
	?>
	</table>
	<h2>Ship Designs</h2>
	<?
	$query = $mysqli->prepare('SELECT designid,shipname,hullname,metalcost,size,engines,fuel,cargo,weapons,shields FROM shipdesigns LEFT JOIN shiphulls USING (hullid) WHERE userID = ? ORDER BY designid ASC;');
	if (!$query)
	{
		echo 'error: ', $mysqli->error, $eol;
		exit;
	}
	$query->bind_param('i', $userid);

	$result = $query->execute();
	if (!$result)
	{
		echo 'error: ', $query->error, $eol;
		exit;
	}

	$query->bind_result($designid,$shipname,$hullname,$metalcost,$size,$engines,$fuel,$cargo,$weapons,$shields);

	echo '<table>', $eol;
	echo '<tr><th>Design Name</th><th>Hull</th><th>Cost</th><th>Size</th><th>Engines</th><th>Fuel Bay</th><th>Cargo Bay</th><th>Weapons</th><th>Shields</th><th></th></tr>', $eol;

	if($query->fetch())
	{
		do
		{
			echo '<tr>';
			echo "<td>$shipname</td>";
			echo "<td>$hullname</td>";
			echo "<td>$metalcost Metal</td>";
			echo "<td>$size</td>";
			echo "<td>$engines</td>";
			echo "<td>$fuel</td>";
			echo "<td>$cargo</td>";
			echo "<td>$weapons</td>";
			echo "<td>$shields</td>";
			echo "<td><form action=\"queueshipbuild_exec.php\" method=\"post\">";
			echo "<input type=\"hidden\" name=\"design\" value=\"$designid\">";
			echo "<input type=\"hidden\" name=\"planet\" value=\"$planetid\">";
			echo "Amount: <input type=\"text\" name=\"count\" size=\"2\">";
			echo '/', floor($metal/$metalcost);
			echo "<input type=\"submit\" value=\"Build\"></form></td>";
			echo '</tr>', $eol;
		} while ($query->fetch());
	}
	else
	{
		echo '<tr><td colspan="10">None!?</td></tr>';
	}
	echo '</table>', $eol;
	echo '<a href="list_shipdesigns.php">Ship Design</a>', $eol;

	?>
	<script type="text/javascript">
function reloadPage(){
	location.reload(true);
}
function liveCount(seconds,name,first){
	span=document.getElementById(name);
	if(first==1){
		//spanb=document.getElementById("b" + name);
		var d = new Date();
		d.setTime(d.getTime()+(seconds*1000));
		span.title = d.toLocaleDateString() + " " + d.toLocaleTimeString();
		//spanb.title = d.toLocaleDateString() + " " + d.toLocaleTimeString();
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
function livePercent(seconds,cost,buildrate,progress,name,first){
	var sec = seconds;
	var per;
	if (sec > 600) sec = 0;
	per = ((buildrate*(sec/600))+progress)/cost;
	span=document.getElementById(name);
	span.innerHTML =  Math.round(per*100);
	if (per<100){
		setTimeout('livePercent('+(sec+1)+','+cost+','+buildrate+','+progress+',"'+name+'",0);',1000);
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
foreach($shiparray as $cid => $ctime){
	echo "liveCount(".$ctime.",\"shsp".$cid."\",1);";
}
foreach($orderarray as $cid => $ctime){
	echo "liveCount(".$ctime.",\"orsp".$cid."\",1);";
}
foreach($pcarray as $cid => $ctime){
	echo "livePercent(".$ctime[0].",".$ctime[1].",".$ctime[2].",".$ctime[3].",\"pcsp".$cid."\",1);";
}
?>
</script>
	<?
}
?>
