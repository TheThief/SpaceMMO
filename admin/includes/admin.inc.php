<?phpinclude_once dirname(__FILE__).'/../../includes/start.inc.php';include_once 'db_account_admin.inc.php';function checkIsAdmin_failed(){	//http_redirect('../login_form.php', array(), false, 303);	header('HTTP/1.1 303 See Other');	header('Location: ../login_form.php');	exit;}function checkIsAdmin(){	if (isAdmin())	{		return true;	}	else	{		checkIsAdmin_failed();	}}?>