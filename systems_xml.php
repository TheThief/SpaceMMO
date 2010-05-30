<?
include_once 'includes/start.inc.php';
checkLoggedIn();
$userid = $_SESSION['userid'];
/*
$dom = new DomDocument('1.0','UTF-8');
$dom->formatOutput = true;

$root = $dom->createElement('Orders');
$root = $dom->appendChild($root);
$root->setAttribute("xsi:noNamespaceSchemaLocation", "Orders.xsd");
$root->setAttribute("xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");

$order = $dom->createElement('Order');
$order = $root->appendChild($order);
$order->setAttribute("id", "id".$id);

addChild($dom,$order,"WebOrderId",$id);
addChild($dom,$order,"Clerk","Website");
addChild($dom,$order,"Date",$oDT);

addChild($dom,$order,"BillToEmail",$fRecord["email"]);
addChild($dom,$order,"BillToFirstName",$fRecord["forename"]);
addChild($dom,$order,"BillToLastName",$fRecord["surname"]);
addChild($dom,$order,"BillToAddr1",$fRecord["address1"]);
addChild($dom,$order,"BillToAddr2",$fRecord["address2"]);
addChild($dom,$order,"BillToAddr3",$fRecord["town"]);
addChild($dom,$order,"BillToAddr4",$fRecord["county"]);
addChild($dom,$order,"BillToAddr5",$fRecord["country"]);
addChild($dom,$order,"BillToAddrPostCode",$fRecord["postcode"]);
addChild($dom,$order,"BillToPhone1",$fRecord["telephone"]);

addChild($dom,$order,"ShipToEmail",$fRecord["email"]);
addChild($dom,$order,"ShipToName",$fRecord["deliveryName"]);
addChild($dom,$order,"ShipToAddr1",$fRecord["deliveryAddress1"]);
addChild($dom,$order,"ShipToAddr2",$fRecord["deliveryAddress2"]);
addChild($dom,$order,"ShipToAddr3",$fRecord["deliveryTown"]);
addChild($dom,$order,"ShipToAddr4",$fRecord["deliveryCounty"]);
addChild($dom,$order,"ShipToAddr5",$fRecord["deliveryCountry"]);
addChild($dom,$order,"ShipToAddrPostCode",$fRecord["deliveryPostcode"]);
addChild($dom,$order,"ShipToPhone1",$fRecord["deliveryTelephone"]);

//addChild($dom,$order,"declineinfo","");

$itemresult = $dbA->query("select code,qty,price from $tableOrdersLines WHERE orderID = " . $fRecord["orderID"]);
$itemcount = $dbA->count($itemresult);
for ($i = 0; $i < $itemcount; $i++) {
	$iRecord = $dbA->fetch($itemresult);
	
	$item = $order->appendChild($dom->createElement('Item'));
	addChild($dom,$item,"ItemId",$iRecord["code"]);
	addChild($dom,$item,"Quantity",$iRecord["qty"]);
	addChild($dom,$item,"UnitPriceWithTax",$iRecord["price"]);
}

addChild($dom,$order,"PaymentType",$fRecord["paymentName"]);

addChild($dom,$order,"ShippingType",$fRecord["shippingMethod"]);
addChild($dom,$order,"ShippingCost",$fRecord["shippingTotal"]);
addChild($dom,$order,"DiscountCode",$fRecord["offerCode"]);
addChild($dom,$order,"DiscountAmmount",$fRecord["discountTotal"]);

addChild($dom,$order,"Total", $total);
*/
var_dump(getWHLinks($userid));
?>