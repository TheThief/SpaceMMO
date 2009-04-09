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
<form action="adduser_exec.php" method="post">
<input type="text" name="username" />
<input type="password" name="password" />
<input type="submit" value="Submit" />
</form>
</div>

</body>
</html>
