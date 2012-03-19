<?php
include_once "../includes/start.inc.php";
//prodDropdown(0.5,1,1);
//prodDropdown(1,1,1);
//echo formatSeconds("h:i:n",0);
//
foreach($_SESSION as $key => $value){
	echo "$key = $value \n";
}
 //tests
$linkstest = getWHLinks($_SESSION["userid"]);
echo "\n";
var_dump($linkstest);

echo $_SERVER["PHP_SELF"];
foreach (range(0.0, 1.0, 0.1) as $number) {
    echo $number . "\n";
}

$tests = Array(
        "42", 
        1337, 
        "1e4", 
        "not numeric", 
        Array(), 
        9.1
        );

foreach($tests as $element)
{
    if(is_numeric($element))
    {
        echo "'{$element}' is numeric", PHP_EOL;
    }
    else
    {
        echo "'{$element}' is NOT numeric", PHP_EOL;
    }
}

echo '<br><br>';

//function systemcode($systemid)
//{
//	return chr(ord('A')+floor(($systemid-1)/99)) . padstring(((($systemid-1)%99)+1),'0',2);
//}

//function systemid($systemcode)
//{
//	if (strlen($systemcode)!=3
//		|| ord($systemcode[0]) < ord('A') || ord($systemcode[0]) > ord('K')
//		|| !is_numeric($systemcode[1]) || !is_numeric($systemcode[2]))
//	{
//		echo 'error: Invalid system code';
//		exit;
//	}
//	return (ord($systemcode[0])-ord('A'))*99 + substr($systemcode,1);
//}

for ($i=1;$i<200;$i++)
{
	echo $i,'=',systemcode($i),'=',systemid(systemcode($i)),', ';
}
//test3
?>

