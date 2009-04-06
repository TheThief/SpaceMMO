Autio thingy works
<?php
include "../includes/start.inc.php";
prodDropdown(0.5,1,1);
prodDropdown(1,1,1);
//echo formatSeconds("h:i:n",0);

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

function systemcode($systemid)
{
	return chr(ord('A')+floor($systemid/99)) + ((($systemid-1)%99)+1);
}

echo systemcode(1),'<br>';
echo systemcode(123),'<br>';
echo systemcode(457),'<br>';
echo systemcode(1000),'<br>';

?>

