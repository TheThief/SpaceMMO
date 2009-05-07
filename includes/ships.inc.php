<?
include_once('functions.inc.php');

/**********************************
* Keep in sync with ships.js.php  *
**********************************/

define('SPEEDCONST',25);
define('SPEEDPOWER',-0.5);
define('FUELUSECONST',100);
define('FUELUSEPOWER',-1.1);

// Cargo capacity in units of M or D
function cargoCapacity($cargobay)
{
	return $cargobay * 100;
}

// Fuel capacity in D
function fuelCapacity($fuelbay)
{
	return $fuelbay * 100;
}

// Attack damage
function attackPower($weapons)
{
	return $weapons;
}

// Defense HP
function defense($size, $shields)
{
	return $size + $shields*10;
}

// Speed in PC/h
function speed($size, $engines)
{
	return SPEEDCONST * pow($size/4,SPEEDPOWER) * $engines / ($size/4);
}

// Fuel use in D/h
function fuelUse($size, $engines)
{
	return FUELUSECONST * pow($size/4,FUELUSEPOWER) * $engines;
}

// Range in PC based on ship design
function shiprangeraw($size, $engines, $fuelbay)
{
	return shiprange(speed($size, $engines), fuelUse($size, $engines), fuelCapacity($fuelbay));
}

// Range in PC.
// Speed is in PC/h, fuel use is in D/h, fuel is in D.
// Calculated in small ticks
function shiprange($speed, $fueluse, $fuel)
{
	return $speed * floor($fuel / ($fueluse / SMALLTICKS_PH)) / SMALLTICKS_PH;
}

// Return range in PC based on ship design
function returnrangeraw($size, $engines, $fuelbay)
{
	return returnrange(speed($size, $engines), fuelUse($size, $engines), fuelCapacity($fuelbay));
}

// Return range in PC
// Speed is in PC/h, fuel use is in D/h, fuel is in D.
// Calculated in small ticks
function returnrange($speed, $fueluse, $fuel)
{
	return $speed * floor($fuel / ($fueluse / SMALLTICKS_PH) / 2) / SMALLTICKS_PH;
}

?>