<?
require_once("./nusoap/nusoap.php");
include("api.inc.php");
$server = new soap_server;

$server->configureWSDL('SpaceMMO', 'urn:SpaceMMO');

$server->wsdl->addComplexType('Colony',
	'complexType',
	'struct',
	'all',
	'',
	array(
		'colonyid' 		=> 	array('name' => 0, 'type' => 'xsd:int'),
		'systemid' 		=> 	array('name' => 1,'type' => 'xsd:int'),
		'systemx' 		=> 	array('name' => 2,'type' => 'xsd:int'),
		'systemy' 		=> 	array('name' => 3,'type' => 'xsd:int'),
		'orbit' 		=> 	array('name' => 4,'type' => 'xsd:int'),
		'systemcode' 	=> 	array('name' => 5,'type' => 'xsd:string'),
		'planettype' 	=> 	array('name' => 6,'type' => 'xsd:int'),
		'metal' 		=> 	array('name' => 7,'type' => 'xsd:int'),
		'maxmetal'	 	=> 	array('name' => 8,'type' => 'xsd:int'),
		'metalprod' 	=> 	array('name' => 9,'type' => 'xsd:int'),
		'deuterium' 	=> 	array('name' => 10,'type' => 'xsd:int'),
		'maxdeuterium' 	=> 	array('name' => 11,'type' => 'xsd:int'),
		'deuteriumprod' => 	array('name' => 12,'type' => 'xsd:int'),
		'energy' 		=> 	array('name' => 13,'type' => 'xsd:int'),
		'maxenergy' 	=> 	array('name' => 14,'type' => 'xsd:int'),
		'energyprod' 	=> 	array('name' => 15,'type' => 'xsd:int')
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

$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);
?>