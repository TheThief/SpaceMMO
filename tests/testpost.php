<?php
include_once "../includes/start.inc.php";

foreach($_POST['test'] as $key => $value)
{
	echo $key,' = ',$value,'<br>', $eol;
}

print_r($_POST['test']);
print($SERVER['CONTENT_TYPE']);

unset($_POST['test']['a'],$_POST['test']['b'],$_POST['test']['c'],$_POST['test']['d']);

echo count($_POST['test']);

?>
<form action="testpost.php" method="post">
<input name="test[a]" type="text"><br>
<input name="test[b]" type="text"><br>
<input name="test[c]" type="text"><br>
<input name="test[d]" type="text"><br>
<input type="submit">
</form>