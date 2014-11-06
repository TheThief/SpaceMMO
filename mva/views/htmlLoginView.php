<?php
class htmlLoginView
{
    public function render($template, $content)
    {
        header('Content-Type: text/html; charset=utf8');
?><html>
 <body>
  <form action='.' method='post'>
   <input type="text" name="username">
   <input type="password" name="password">
   <input type="submit" value="Submit">
  </form>
 </body>
</html><?php
        return true;
    }
}
