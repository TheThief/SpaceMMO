<?
//include_once('functions.inc.php');

// Cargo capacity in units of M or D
function cargoCapacity($cargobay)
{
	return $cargobay * 100;
}

// Fuel capacity in D
function fuelCapacity($fuelbay)
{
	return $fuelbay * 6;
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
	return (engines*24) / size;
}

// Fuel use in D/h
function fuelUse($engines)
{
	return $engines * 6;
}

// Range in PC
function shiprange($size, $engines, $fuelbay)
{
	return speed($size, $engines) * floor(fuelCapacity($fuelbay) / fuelUse($engines));
}

// Return range in PC
function returnrange($size, $engines, $fuelbay)
{
	return speed($size, $engines) * floor(fuelCapacity($fuelbay) / fuelUse($engines) / 2);
}

?>