<?
include_once 'includes/start.inc.php';
checkLoggedIn();

include_once 'includes/colonymenu.inc.php';
include_once 'includes/template.inc.php';
template('Build Ships', 'buildShipsBody','colonyMenu');

function buildShipsBody()
{
	global $eol, $mysqli;
	$userid = $_SESSION['userid'];
 	$planetid = $_GET['planet'];
	$shiparray = array();
	$orderarray = array();
	$pcarray = array();
	$query = $mysqli->prepare('SELECT metal,maxmetal,metalproduction,shipconstruction FROM colonies WHERE userid =? AND planetid = ?;');
	if (!$query)
	{
		echo 'error: ', $mysqli->error, $eol;
		exit;
	}
	$query->bind_param('ii',$userid, $planetid);
	$result = $query->execute();
	if (!$result)
	{
		echo 'error: ', $query->error, $eol;
		exit;
	}
	$query->bind_result($metal,$maxmetal,$metalprod,$shipprod);
	$result=$query->fetch();
	if (!$result)
	{
		echo 'Error: Not your planet!', $eol;
		exit;
	}
	$query->close();
	$query = $mysqli->prepare('SELECT level FROM colonybuildings WHERE buildingid = 9 AND planetid = ?;');
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
	$query->bind_result($ddlevel);
	$result=$query->fetch();
	if (!$result)
	{
		echo 'Error: You need a drydock to build ships.', $eol;
		exit;
	}
	$query->close();
	planetChanger();
	echo '<br><table>', $eol;
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
		<tr><th>Design Name</th><th>Quantity</th><!--<th>Ship Progress</th>--><th>Next Ship In</th><th>Order Complete In</th></tr>
	<?	
	$timeoffset=0;
	$first=TRUE;
	$empty=FALSE;
	if($query->fetch())
	{
		do
		{	
			if($shipprod>0){
				$shipticks = ($metalcost-$progress)/$shipprod;
				$shiptime = (ceil(($shipticks+$timeoffset))*TICK)-getTickElapsed();
				$orderticks = (($metalcost*$count)-$progress)/$shipprod;
				$ordertime = (ceil(($orderticks+$timeoffset))*TICK)-getTickElapsed();
				$timeoffset += $orderticks;
				$shiparray[$id]=$shiptime;
				$orderarray[$id]=$ordertime;
				if($first) $pcarray=array(getTickElapsed(),$metalcost,$shipprod,$progress);
			}
			echo '<tr>';
			echo "<td>$designname</td>";
			echo "<td>$count</td>";
			//echo "<td><span id=\"pcsp".(int)$first."\">".(int)(($progress/$metalcost)*100)."</span>%</td>";
			echo "<td><span id=\"shsp".$id."\">".formatSeconds("h:i:s",$shiptime)."<span></td>";
			echo "<td><span id=\"orsp".$id."\">".formatSeconds("h:i:s",$ordertime)."<span></td>";
			echo '</tr>';
			$first=FALSE;
		} while ($query->fetch());
	}
	else
	{
		$empty=TRUE;
		echo '<tr><td colspan="4">Empty</td></tr>';
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

	<?
	foreach($shiparray as $cid => $ctime){
		echo "liveCount(".$ctime.",\"shsp".$cid."\",0,1,1);";
	}
	foreach($orderarray as $cid => $ctime){
		echo "liveCount(".$ctime.",\"orsp".$cid."\",0,0,1);";
	}
	if(!$empty){
		echo "livePercent(".$pcarray[0].",".$pcarray[1].",".$pcarray[2].",".$pcarray[3].",\"pcsp1\",1);";
	}
	?>
	</script>
	<?
}
?>
