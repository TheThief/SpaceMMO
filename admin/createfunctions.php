<?php
include_once 'includes/admin.inc.php';
checkIsAdmin();

$eol = "\n";
header('Content-type: text/plain');

// need to log in as power user for this
$mysqli = new mysqli($db_server,$db_admin_user,$db_admin_pass,$db_db);

//$result = $mysqli->query('DROP FUNCTION mult_exp');
$result = $mysqli->query('CREATE FUNCTION mult_exp(level INT, base FLOAT, linear FLOAT, multiplier FLOAT) returns FLOAT DETERMINISTIC NO SQL RETURN ROUND((base+base*(level-1)*linear)*POW(multiplier,level-1))');
if ($result)
{
	echo 'function \'mult_exp\' created successfully', $eol;
}
else
{
	echo 'error: ', $mysqli->error, $eol;
}

//$result = $mysqli->query('DROP FUNCTION round_sf');
$result = $mysqli->query('CREATE FUNCTION round_sf(number FLOAT, sf INT) returns FLOAT DETERMINISTIC NO SQL RETURN ROUND(number, sf-CEIL(LOG10(ABS(number))))');
if ($result)
{
	echo 'function \'round_sf\' created successfully', $eol;
}
else
{
	echo 'error: ', $mysqli->error, $eol;
}

//CREATE FUNCTION building_cost(id INT, level INT)
//	RETURNS FLOAT
//	DETERMINISTIC
//	READS SQL DATA
//	BEGIN
//		DECLARE cost FLOAT;
//		SELECT round_sf(mult_exp(level,metalcostbase,metalcostlinear,metalcostmultiplier),3) INTO cost FROM buildings WHERE buildingid=id;
//		RETURN cost;
//	END

//CREATE FUNCTION building_consumes(id INT, level INT)
//	RETURNS FLOAT
//	DETERMINISTIC
//	READS SQL DATA
//	BEGIN
//		DECLARE consumes FLOAT;
//		SELECT round_sf(mult_exp(level,consumesbase,consumeslinear,consumesmultiplier),3) INTO consumes FROM buildings WHERE buildingid=id;
//		RETURN consumes;
//	END

//CREATE FUNCTION colony_building_consumes_max(colonyid INT, id INT, level INT)
//	RETURNS INT
//	DETERMINISTIC
//	READS SQL DATA
//	BEGIN
//		DECLARE consumes INT;
//		SELECT ROUND(building_consumes(id,level)) INTO consumes;
//		RETURN consumes;
//	END

//CREATE FUNCTION colony_building_consumes(colonyid INT, id INT)
//	RETURNS INT
//	READS SQL DATA
//	DETERMINISTIC
//	BEGIN
//		DECLARE consumes INT;
//		SELECT ROUND(colony_building_consumes_max(colonyid,id,level)*output) INTO consumes FROM colonybuildings WHERE planetID = colonyid AND buildingID = id;
//		RETURN consumes;
//	END

//CREATE FUNCTION building_effect(id INT, level INT)
//	RETURNS FLOAT
//	DETERMINISTIC
//	READS SQL DATA
//	BEGIN
//		DECLARE effect FLOAT;
//		SELECT round_sf(mult_exp(level,effectbase,effectlinear,effectmultiplier),3) INTO effect FROM buildings WHERE buildingid=id;
//		RETURN effect;
//	END

//CREATE FUNCTION colony_building_effect_max(colonyid INT, id INT, level INT)
//	RETURNS INT
//	DETERMINISTIC
//	READS SQL DATA
//	BEGIN
//		DECLARE effect INT;
//		DECLARE effect_type TINYINT;
//		DECLARE multiply BOOL;
//		SELECT multiplybyplanet, effecttype, ROUND(building_effect(id, level)) INTO multiply, effect_type, effect FROM buildings WHERE buildingid=id;
//		IF multiply THEN
//			CASE effect_type
//				WHEN 1 THEN SELECT ROUND(effect*metal) INTO effect FROM planets WHERE planetid=colonyid;
//				WHEN 2 THEN SELECT ROUND(effect*deuterium) INTO effect FROM planets WHERE planetid=colonyid;
//				ELSE BEGIN END;
//			END CASE;
//		END IF;
//		RETURN effect;
//	END

//CREATE FUNCTION colony_building_effect(colonyid INT, id INT)
//	RETURNS INT
//	DETERMINISTIC
//	READS SQL DATA
//	BEGIN
//		DECLARE effect INT;
//		SELECT ROUND(output*colony_building_effect_max(colonyid, id, level)) INTO effect FROM colonybuildings WHERE planetid=colonyid AND buildingid=id;
//		RETURN effect;
//	END





//CREATE FUNCTION building_effect2(id INT, level INT, effecttype INT)
//	RETURNS FLOAT
//	DETERMINISTIC
//	READS SQL DATA
//	BEGIN
//		DECLARE effect INT;
//		SELECT round_sf(mult_exp(level,base,linear,multiplier),3) INTO effect FROM effects WHERE buildingid=id AND type=effecttype;
//		RETURN effect;
//	END

//CREATE FUNCTION colony_building_effect2_max(colonyid INT, id INT, level INT, effecttype INT)
//	RETURNS INT
//	DETERMINISTIC
//	READS SQL DATA
//	BEGIN
//		DECLARE effect INT;
//		DECLARE multiply BOOL;
//		SELECT multiplybyplanet, ROUND(building_effect2(id, level, effecttype)) INTO multiply, effect FROM buildings INNER JOIN effects USING (buildingid) WHERE buildingid=id AND type=effecttype;
//		IF multiply THEN
//			CASE effecttype
//				WHEN 1 THEN SELECT ROUND(effect*metal) INTO effect FROM planets WHERE planetid=colonyid;
//				WHEN 2 THEN SELECT ROUND(effect*deuterium) INTO effect FROM planets WHERE planetid=colonyid;
//				ELSE BEGIN END;
//			END CASE;
//		END IF;
//		RETURN effect;
//	END

//CREATE FUNCTION colony_building_effect2(colonyid INT, id INT, effecttype INT)
//	RETURNS INT
//	DETERMINISTIC
//	READS SQL DATA
//	BEGIN
//		DECLARE effect INT;
//		SELECT ROUND(output*colony_building_effect2_max(colonyid, id, level, effecttype)) INTO effect FROM colonybuildings WHERE planetid=colonyid AND buildingid=id;
//		RETURN effect;
//	END

mysqli->close();
?>
