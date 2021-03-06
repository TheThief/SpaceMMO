<?php
include_once 'includes/start.inc.php';
checkLoggedIn();

require_once 'Twig/Autoloader.php';
Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    'cache' => 'templates/compiled',
    'auto_reload' => true,
));
function prodSummary($id, $current, $max, $delta)
{
    $symbol = getSign($delta);
	$title = thousands($current).'/'.thousands($max).' ('.getSigned($delta*TICKS_PH).'/hour)';
	return '<span id="'.$id.'" title="'.$title.'">'.thousands($current).' '.$symbol.'</span>';
}

function getSign($delta) {
    $symbol = '.';
    if ($delta > 0) {
        $symbol = '+';
    } else if ($delta < 0) {
        $symbol = '-';
    }
    return $symbol;
}

$twig->addFilter('signed', new Twig_Filter_Function('getSigned'));
$twig->addFilter('thousands', new Twig_Filter_Function('thousands'));
$twig->addFilter('sign', new Twig_Filter_Function('getSign'));
//$twig->addFunction('prodSummary', new Twig_Function_Function('prodSummary'));
$template = $twig->loadTemplate('colonies_list.html.twig');

function getColonyArray($type,$systemid, $planetid, $orbit, $planettype, $metal = null, $maxmetal = null, $metalprod = null, $deuterium = null, $maxdeuterium = null, $deuteriumprod = null, $energy = null, $maxenergy = null, $energyprod = null,$colonyhp = null ,$colonymaxhp = null) {
    global $lookups;
    $temp = array();
    $temp["systemID"] = $systemid;
    $temp["planetID"] = $planetid;
    $temp["systemCode"] = systemcode($systemid, $orbit);
    $temp["systemLink"] = 'view_planets.php?system=' . $systemid;
    $temp["planetLink"] = 'view_planet.php?planet=' . $planetid;
    $temp["planetTypeID"] = $planettype;
    $temp["planetType"] = $lookups["planetType"][$planettype];
    $temp["planetImage"] = 'images/planet' . $planettype . '.png';
    $temp["metal"] = $metal;
    $temp["metalStorage"] = $maxmetal;
    $temp["metalProduction"] = $metalprod * TICKS_PH;
    $temp["deuterium"] = $deuterium;
    $temp["deuteriumStorage"] = $maxdeuterium;
    $temp["deuteriumProduction"] = $deuteriumprod * TICKS_PH;
    $temp["energy"] = $energy;
    $temp["energyStorage"] = $maxenergy;
    $temp["energyProduction"] = $energyprod * TICKS_PH;
    $temp["hp"] = $colonyhp;
    $temp["maxHP"] = $colonymaxhp;
    if($type == "f"){
        if ($_SESSION['colony'] != $planetid) {
            $temp["isCurrent"] = 'N';
            $temp["changeToLink"] = 'change_colony.php?planet=' . $planetid;
        } else {
            $temp["isCurrent"] = 'Y';
        }
    }
    return $temp;
}


$userid = $_SESSION['userid'];


//User
$user = array();
$user["userID"] = $userid;
$user["loggedIn"] = "N";
if(isLoggedIn()) $user["loggedIn"] = "Y";
$user["isAdmin"] = "N";
if(isAdmin()) $user["isAdmin"] = "Y";
if ($_SESSION['adminuserid']) $user["adminuserID"] = $_SESSION['adminuserid'];

//Current
$systemid = $_GET['system'];
$planetid = $_GET['planet'];

$colonyid = $_SESSION['colony'];
if (!$colonyid)
{
    $query = $mysqli->prepare('SELECT planetid FROM colonies WHERE userid=? ORDER BY colonylevel DESC LIMIT 1');
    $query->bind_param('i', $userid);
    $query->execute();
    $query->bind_result($colonyid);
    $query->fetch();
    $query->close();
    $_SESSION['colony'] = $colonyid;
}

$current = array();
if (!is_numeric($systemid) && is_numeric($planetid)){
    $query = $mysqli->prepare('SELECT systemid FROM planets WHERE planetid=?');
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

    $query->bind_result($systemid);
    $query->fetch();
    $query->close();
}
if (is_numeric($systemid)) $current["systemID"] = $systemid;
$current["colonyID"] = $colonyid;

if(is_numeric($colonyid)){
    $query = $mysqli->prepare('SELECT systemid,orbit,type,colonylevel,colonies.metal,maxmetal,metalproduction,colonies.deuterium,maxdeuterium,deuteriumproduction,energy,maxenergy,energyproduction,shipconstruction,hp,maxhp,userid FROM colonies LEFT JOIN planets USING (planetid) WHERE planetid=?');
    $query->bind_param('i', $colonyid);
    $query->execute();
    $query->bind_result($systemid,$orbit,$planettype,$colonylevel,$metal,$maxmetal,$metalprod,$deuterium,$maxdeuterium,$deuteriumprod,$energy,$maxenergy,$energyprod,$shipconstruction,$colonyhp,$colonymaxhp,$owner);
    $result = $query->fetch();
    $query->close();
    if($owner == $userid){
        $current["colony"] = getColonyArray("f",$systemid, $planetid, $orbit, $planettype, $metal, $maxmetal, $metalprod, $deuterium, $maxdeuterium, $deuteriumprod, $energy, $maxenergy, $energyprod,$colonyhp,$colonymaxhp);
        $current["colony"]["playerOwned"] = "Y";
    }else{
        $current["colony"] = getColonyArray("b",$systemid,$colonyid,$orbit,$planettype);
        $current["colony"]["playerOwned"] = "N";
    }
}

//Colonies
$query = $mysqli->prepare('SELECT colonies.planetid,systemid,systems.x,systems.y,planets.orbit,planets.type,colonies.metal,colonies.maxmetal,colonies.metalproduction,colonies.deuterium,colonies.maxdeuterium,colonies.deuteriumproduction,colonies.energy,colonies.maxenergy,colonies.energyproduction FROM colonies LEFT JOIN planets USING (planetid) LEFT JOIN systems USING (systemid) WHERE colonies.userID = ? ORDER BY creationtime ASC;');
$query->bind_param('i', $userid);
$query->execute();
$query->bind_result($planetid,$systemid,$systemx,$systemy,$orbit,$planettype,$metal,$maxmetal,$metalprod,$deuterium,$maxdeuterium,$deuteriumprod,$energy,$maxenergy,$energyprod);
$colonies = array();

while($query->fetch())
{
    $colonies[$planetid] = getColonyArray("f",$systemid, $planetid, $orbit, $planettype, $metal, $maxmetal, $metalprod, $deuterium, $maxdeuterium, $deuteriumprod, $energy, $maxenergy, $energyprod);
}

echo $template->render(array('colonies' => $colonies,'user' => $user,'current' => $current));
?>
