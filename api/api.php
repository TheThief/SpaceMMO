<?
require_once("./nusoap/nusoap.php");
include_once("api.inc.php");
$server = new soap_server;

$server->configureWSDL('SpaceMMO', 'urn:SpaceMMO');

$server->wsdl->addComplexType('Colony',
	'complexType',
	'struct',
	'all',
	'',
	array(
		'colonyid' 		=> 	array('name' => 'colonyid', 'type' => 'xsd:int'),
		'systemid' 		=> 	array('name' => 'systemid','type' => 'xsd:int'),
		'systemx' 		=> 	array('name' => 'systemx','type' => 'xsd:int'),
		'systemy' 		=> 	array('name' => 'systemy','type' => 'xsd:int'),
		'orbit' 		=> 	array('name' => 'orbit','type' => 'xsd:int'),
		'systemcode' 	=> 	array('name' => 'systemcode','type' => 'xsd:string'),
		'planettype' 	=> 	array('name' => 'planettype','type' => 'xsd:int'),
		'planettypetext'=> 	array('name' => 'planettypetext','type' => 'xsd:string'),
		'metal' 		=> 	array('name' => 'metal','type' => 'xsd:int'),
		'maxmetal'	 	=> 	array('name' => 'maxmetal','type' => 'xsd:int'),
		'metalprod' 	=> 	array('name' => 'metalprod','type' => 'xsd:int'),
		'deuterium' 	=> 	array('name' => 'deuterium','type' => 'xsd:int'),
		'maxdeuterium' 	=> 	array('name' => 'maxdeuterium','type' => 'xsd:int'),
		'deuteriumprod' => 	array('name' => 'deuteriumprod','type' => 'xsd:int'),
		'energy' 		=> 	array('name' => 'energy','type' => 'xsd:int'),
		'maxenergy' 	=> 	array('name' => 'maxenergy','type' => 'xsd:int'),
		'energyprod' 	=> 	array('name' => 'energyprod','type' => 'xsd:int')
	)
);

$server->wsdl->addComplexType('Colonies',
				'complexType',
				'array',
				'',
				'SOAP-ENC:Array',
				array(),
				array(
					array('ref'=>'SOAP-ENC:arrayType',
					'wsdl:arrayType'=>'tns:Colony[]')
				),
				'tns:Colony'
			);


$server->register('getColonies',                // method name
    array('apikey' => 'xsd:string'),        // input parameters
    array('return' => 'tns:Colonies'),      // output parameters
    'urn:SpaceMMO',                      // namespace
    'urn:SpaceMMO#getColonies',                // soapaction
    'rpc',                                // style
    'encoded',                            // use
    'Gets list of user\'s colonies and thier stats.'            // documentation
);

$server->register('getPlanetType',                // method name
    array('type' => 'xsd:int'),        // input parameters
    array('return' => 'xsd:string'),      // output parameters
    'urn:SpaceMMO',                      // namespace
    'urn:SpaceMMO#getPlanetType',                // soapaction
    'rpc',                                // style
    'encoded',                            // use
    'Gets a textual representation of planet type.'            // documentation
);

$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);
?>