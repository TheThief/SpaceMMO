<?
include_once '../includes/start.inc.php';

include_once '../includes/colony.inc.php';

include_once '../includes/helpmenu.inc.php';

include_once '../includes/template.inc.php';
template('Help - Getting Started', 'helpGettingStartedBody', 'helpMenu');

function helpColonisingBody()
{
	global $eol, $mysqli;

?><h2>Colony Ships</h2>
<p>Unlike in other space games, the ships you use to colonise another planet aren't broken up and used to build the initial dome, nor do you need a special kind of ship. Though you are welcome to design a ship specifically for the task of colonising other planets, it's not required. Instead, normal resource transport ships are used.</p>
<p>To colonise another planet, you need to be able to transport at least <?=COLONY_COST?> Metal to your target planet, plus up to 2000 additional Metal (and optionally Deuterium) to build your first buildings. You don't have to transport it all in one ship, but the initial <?=COLONY_COST?> Metal does need to all be transported in one <em>fleet</em>. If you choose to transport only the required <?=COLONY_COST?> Metal, you won't be able to build anything at your new colony until you transport more metal in.</p>
<p>The pilots of the ships you use to colonise another planet will insist on making a <em>return</em> journey, so make sure you design your transport with enough range to make it there <em>and</em> back. Your transport ship pilots are just pilots, not pioneering colonists, and they aren't interested in staying at the new settlement for a few months until it has built up to the point where it can supply them with enough fuel to get home. They're <em>especially</em> not interested if the planet you want to place a new colony at is a long way away, which is unfortunately when you'll most wish you could send them on a one-way trip.</p>

<h2>Choosing a planet</h2>
<p>Use the Galaxy View to find a suitable planet. The tooltips on the stars show distance from your current colony (shown in the summary window on the left). You're advised to place your first colonies within a few PC of your homeworld so that if they are attacked, it won't take you too long to send ships for defense or make a retaliation strike. Planets in the same system are effectively 0PC away, but activating the ships' drives for each trip drains 10 minutes of fuel. Sorry, travel inside your own system isn't free.</p>
<p>Each planet's details page lists its statistics, as well as a bookmark link which will help when you've choosen which planet you want to colonise. Make sure you choose one which isn't already colonised. The higher the metal abundance is of the planet you choose, the quicker you will mine metal and the faster you'll be able to build up as a result. Colonising a rock or ice planet is easy, but a gas giant is hard due to metals being so rare. The higher abundance of deuterium in an ice planet will help in gathering fuel for any ships you build there.</p>

<h2>Colonising a planet</h2>
<p>Once you have chosen a planet, either bookmark it from its details page or note down its id code. Then go to the "ships in orbit" page of the colony your are sending the fleet from, and issue a "colonise" order for the fleet you are using, either choosing the bookmark from the destination dropdown, or choosing "Other" and putting the planet's code into the box. Remember to tell them to transport at least <?=COLONY_COST?> Metal (preferably 2000 more), and press dispatch.</p>

<p>Now all you have to do is wait.</p>
<?
}
