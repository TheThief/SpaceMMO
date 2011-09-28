<?
include_once 'includes/start.inc.php';
checkLoggedIn();

require_once 'Twig/Autoloader.php';
Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    'cache' => 'templates/compiled',
    'auto_reload' => true,
));
$twig->addFilter('signed', new Twig_Filter_Function('getSigned'));
$template = $twig->loadTemplate('colonies_list.html');

global $eol, $mysqli;
global $lookups;
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

//Colonies
$query = $mysqli->prepare('SELECT colonies.planetid,systemid,systems.x,systems.y,planets.orbit,planets.type,colonies.metal,colonies.maxmetal,colonies.metalproduction,colonies.deuterium,colonies.maxdeuterium,colonies.deuteriumproduction,colonies.energy,colonies.maxenergy,colonies.energyproduction FROM colonies LEFT JOIN planets USING (planetid) LEFT JOIN systems USING (systemid) WHERE colonies.userID = ? ORDER BY creationtime ASC;');
$query->bind_param('i', $userid);
$query->execute();
$query->bind_result($planetid,$systemid,$systemx,$systemy,$orbit,$planettype,$metal,$maxmetal,$metalprod,$deuterium,$maxdeuterium,$deuteriumprod,$energy,$maxenergy,$energyprod);
$colonies = array();
while($query->fetch())
{
    $temp = array();
    $temp["systemID"] = $systemid;
    $temp["systemCode"] = systemcode($systemid, $orbit);
    $temp["planetID"] = $planetid;
    $temp["systemLink"] = 'view_planets.php?system=' . $systemid;
    $temp["planetLink"] = 'view_planet.php?planet=' . $planetid;
    $temp["planetTypeID"] = $planettype;
    $temp["planetType"] = $lookups["planetType"][$planettype];
    $temp["planetImage"] = 'images/planet'.$planettype.'.png';
    $temp["metal"] = $metal;
    $temp["metalStorage"] = $maxmetal;
    $temp["metalProduction"] = $metalprod*TICKS_PH;
    $temp["deuterium"] = $deuterium;
    $temp["deuteriumStorage"] = $maxdeuterium;
    $temp["deuteriumProduction"] = $deuteriumprod*TICKS_PH;
    $temp["energy"] = $energy;
    $temp["energyStorage"] = $maxenergy;
    $temp["energyProduction"] = $energyprod*TICKS_PH;
    if($_SESSION['colony'] != $planetid){
        $temp["isCurrent"] = 'N';
        $temp["changeToLink"] = 'change_colony.php?planet='.$planetid;
    }else{
         $temp["isCurrent"] = 'Y';
    }
    $colonies[] = $temp;
}

echo $template->render(array('colonies' => $colonies,'user' => $user,'current' => $current));
?>
