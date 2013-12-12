<?php
class htmlView
{
    public function render($template, $content)
    {
        header('Content-Type: text/html; charset=utf8');
        echo '<html><body>';
        echo '<pre>', htmlspecialchars(print_r($content, true)), '</pre>';
        echo '</body></html>';
        return true;
    }
}
