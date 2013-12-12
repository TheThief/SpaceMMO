<?php
class BaseAdapter
{
    protected function RenderView($template, $format, $result)
    {
        $view_name = $format . 'View';
        if (class_exists($view_name))
        {
            $view = new $view_name();
            $view->render($template, $result);
        }
    }
}
