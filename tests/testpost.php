<?php
include "../includes/start.inc.php";

foreach($_POST['test'] as $key => $value)
{
	echo $key,' = ',$value,'<br>', $eol;
}

?>
<form action="testpost.php" method="post">
<input name="test[a]" type="text"><br>
<input name="test[b]" type="text"><br>
<input name="test[c]" type="text"><br>
<input name="test[d]" type="text"><br>
<input type="submit">
</form>