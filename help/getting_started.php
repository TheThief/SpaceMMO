<?
include '../includes/start.inc.php';

include '../includes/template.inc.php';
template('Help - Getting Started', 'helpGettingStartedBody');

function helpGettingStartedBody()
{
	global $eol, $mysqli;
	echo '<p>After registering, the first thing you will want to do is to get your colony up and running. To do this, you will first need to go to to the colonies list, which will look something like this:</p>', $eol;
	echo '<table>', $eol;
	echo '<tr><th>Location</th><th>Planet Type</th><th>Metal</th><th>Deuterium</th><th>Energy</th><th>Actions</th></tr>', $eol;
	echo '<tr><td><a>0, 0 : 1</a></td><td><img src="images/planet3.png" style="width:1em;height:1em;">Ice</td><td>2000/2000 (+0)</td><td>0/2000 (+0)</td><td>0/2000 (+0)</td><td><a>Details</a></td></tr>', $eol;
	echo '</table>', $eol;
	echo 'The columns represent the following:<br>', $eol;
	echo '<dl>', $eol;
	echo '<dt>Location</dt><dd>- The coordinates of the solar system the planet is in, followed by the planet\'s orbit distance. Clicking the location will take you to a <a>view of the solar system</a> your colony is in.</dd>', $eol;
	echo '<dt>Planet Type</dt><dd>- Always an "Ice" planet to start with, as these are fairly balanced in resources and so are easier for a new player. Metal can be mined faster from a rocky planet, and deuterium can be harvested faster from a gas giant. More about that later. Clicking the planet type will take you to the <a>planet details</a> page.</dd>', $eol;
	echo '<dt>Metal</dt><dd>- Metal is used in the construction of colonies, buildings, and ships (i.e. everything). The three numbers are your current amount of metal, your metal storage (which your current metal can\'t go above), and your current metal production. Which is zero. We\'ll do something about that in a moment.</dd>', $eol;
	echo '<dt>Deuterium</dt><dd>- Deuterium is the "fuel" of the game. It is used to fuel ships and run your colony\'s fusion reactor. The three numbers are your current deuterium (zero), your deuterium storage and deuerium production rate (also zero).</dd>', $eol;
	echo '<dt>Energy</dt><dd>- Energy is generated by the solar "Solar Plant" and "Fusion Reactor", and is required to run most colony buildings, as well as colony defenses. Again, you have zero energy and zero energy production.</dd>', $eol;
	echo '</dl>', $eol;
}
