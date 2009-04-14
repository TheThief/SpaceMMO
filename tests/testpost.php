<?php
include_once "../includes/start.inc.php";

?><html>
<head>
</head>
<body>
<?
foreach($_POST['test'] as $key => $value)
{
	echo $key,' = ',$value, ' (',mb_strlen($value),' c, ',strlen($value),' b)','<br>', $eol;
}

print_r($_POST['test']);
print($_SERVER['CONTENT_TYPE']);

unset($_POST['test']['a'],$_POST['test']['b'],$_POST['test']['c'],$_POST['test']['d']);

echo count($_POST['test']);

?>
<form action="testpost.php" method="post" accept-charset="UTF-8">
<input name="test[a]" type="text"><br>
<input name="test[b]" type="text"><br>
<input name="test[c]" type="text"><br>
<input name="test[d]" type="text"><br>
<input type="submit">
</form>
</body>
</html>
