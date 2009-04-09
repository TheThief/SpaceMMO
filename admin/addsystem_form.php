<?
include_once 'includes/admin.inc.php';
checkIsAdmin();
?>
<html>
<head>
<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>

<div>
<form action="addsystem_exec.php" method="post">
<input type="text" name="x" />
<input type="text" name="y" />
<input type="submit" value="Submit" />
</form>
</div>

</body>
</html>
