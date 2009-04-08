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
		0 => 	array('name' => 'colonyid', 'type' => 'xsd:int'),
		1 => 	array('name' => 'systemid','type' => 'xsd:int'),
		2 => 	array('name' => 'systemx','type' => 'xsd:int'),
		3 => 	array('name' => 'systemy','type' => 'xsd:int'),
		4 => 	array('name' => 'orbit','type' => 'xsd:int'),
		5 => 	array('name' => 'systemcode','type' => 'xsd:string'),
		6 => 	array('name' => 'planettype','type' => 'xsd:int'),
		7 => 	array('name' => 'metal','type' => 'xsd:int'),
		8 => 	array('name' => 'maxmetal','type' => 'xsd:int'),
		9 => 	array('name' => 'metalprod','type' => 'xsd:int'),
		10 => 	array('name' => 'deuterium','type' => 'xsd:int'),
		11 => 	array('name' => 'maxdeuterium','type' => 'xsd:int'),
		12 => 	array('name' => 'deuteriumprod','type' => 'xsd:int'),
		13 => 	array('name' => 'energy','type' => 'xsd:int'),
		14 => 	array('name' => 'maxenergy','type' => 'xsd:int'),
		15 => 	array('name' => 'energyprod','type' => 'xsd:int')
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