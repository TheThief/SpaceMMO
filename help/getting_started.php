<?php
include_once '../includes/start.inc.php';

include_once '../includes/helpmenu.inc.php';

include_once '../includes/template.inc.php';
template('Help - Getting Started', 'helpGettingStartedBody', 'helpMenu');

function helpGettingStartedBody()
{
	global $eol, $mysqli;

?><h2>Colonies</h2>
<p>After registering, the first thing you will want to do is to get your colony up and running. To do this, you will first need to go to the colonies list, which will look something like this:</p>
<table>
<tr><th>Location</th><th>Planet Type</th><th>Metal</th><th>Deuterium</th><th>Energy</th><th>Actions</th></tr>
<tr><td><a>0, 0 : 1</a></td><td><img src="../images/planet3.png" style="width:1em;height:1em;">Ice</td><td>2000/2000 (+0)</td><td>0/2000 (+0)</td><td>0/2000 (+0)</td><td><a>Details</a></td></tr>
</table>
<p>The columns represent the following:</p>
<dl>
<dt>Location</dt><dd>- The coordinates of the solar system the planet is in, followed by the planet's orbit distance. Clicking the location will take you to a <a>view of the solar system</a> your colony is in.</dd>
<dt>Planet Type</dt><dd>- Always an "Ice" planet to start with, as these are fairly balanced in resources and so are easier for a new player. Metal can be mined faster from a rocky planet, and deuterium can be harvested faster from a gas giant. More about that later. Clicking the planet type will take you to the <a>planet details</a> page.</dd>
<dt>Metal</dt><dd>- Metal is used in the construction of colonies, buildings, and ships (i.e. everything). The three numbers are your current amount of metal, your metal storage (which your current metal can't go above), and your current metal production. Which is zero. We'll do something about that in a moment.</dd>
<dt>Deuterium</dt><dd>- Deuterium is the "fuel" of the game. It is used to fuel ships and run your colony's fusion reactor. The three numbers are your current deuterium (zero), your deuterium storage and deuerium production rate (also zero).</dd>
<dt>Energy</dt><dd>- Energy is generated by the solar "Solar Plant" and "Fusion Reactor", and is required to run most colony buildings, as well as colony defenses. Again, you have zero energy and zero energy production.</dd>
</dl>
<p>Clicking on "Details" will take you to the colony's building screen</p>

<h2>Building at a colony</h2>
<p>This page is currently fairly daunting, but you'll understand it in no time.</p>
<p>At the top of the screen you will have the same metal, deuterium and energy display as you saw on the colonies list.</p>
<p>Below it is a list of all the buildings that can be built at a colony, with the most important (your colony dome) at the top. The level of the dome is the level of your colony, and no other buildings can be higher level than the colony.</p>
<p>Your first priority should be to provide power for your colony, and the best way to do that (at least to start with) is to build a solar plant. The solar planet consumes no resources while it is operating, making it the ideal building to build first.</p>
<p>Next you are going to want to build a mine, as without a source of metal, once you've spent the 2000 you started with you'll be stuck.</p>
<p>Next, a "deuterium scoop" will start you harvesting deuterium.</p>
<p>After you build a building, you should notice that the list changes to show that you have the building at level 1. On the right, it will give you the option to "upgrade to level 2", but with a warning that you need to upgrade your dome before anything else. A level 2 building is more than twice as effective as the level 1 buildings you currently have.</p>
<?php
}
