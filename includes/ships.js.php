<?
include_once('functions.inc.php');

include_once('ships.inc.php');

/**********************************
* Keep in sync with ships.inc.php *
**********************************/
?>

// Cargo capacity in units of M or D
function cargoCapacity(cargobay)
{
	return cargobay * <?=CARGOCONST?>;
}

// Fuel capacity in D
function fuelCapacity(fuelbay)
{
	return fuelbay * <?=FUELCONST?>;
}

// Attack damage
function attackPower(weapons)
{
	return weapons;
}

// Defense HP
function defense(size, shields)
{
	return size + shields*10;
}

// Speed in PC/h
function speed(size, engines)
{
	return <?=SPEEDCONST?> * Math.pow(size/4,<?=SPEEDPOWER?>) * engines / (size/4);
}

// Fuel use in D/h
function fuelUse(size, engines)
{
	return <?=FUELUSECONST?> * Math.pow(size/4,<?=FUELUSEPOWER?>) * engines;
}

// Range in PC
function shiprange(size, engines, fuelbay)
{
	return speed(size, engines) * Math.floor(<?=SMALLTICKS_PH?> * fuelCapacity(fuelbay) / fuelUse(size, engines)) / <?=SMALLTICKS_PH?>;
}

// Return range in PC
function returnrange(size, engines, fuelbay)
{
	return speed(size, engines) * Math.floor(<?=SMALLTICKS_PH?> * fuelCapacity(fuelbay) / fuelUse(size, engines) / 2) / <?=SMALLTICKS_PH?>;
}
