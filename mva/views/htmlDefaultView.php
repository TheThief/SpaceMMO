<?php
class htmlDefaultView
{
    public function render($template, $content)
    {
        $script_location = ($_SERVER['HTTPS'] ? 'https://' : 'http://') . dirname($_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF']);
        header('Location: ' . $script_location . '/user/login/', true, 303); // 303 See Other

        header('Content-Type: text/html; charset=utf8');
?><html>
 <body>
  <a href="/user/login/">Click here if you are not automatically redirected</a>
 </body>
</html><?php
        return true;
    }
}
