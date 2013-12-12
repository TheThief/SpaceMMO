<?php
class BaseAdapter
{
    protected function CheckAuth($request, $requiredlevel)
    {
        $authlevel = 0;

        $model = GetModel('User');
        if (isset($request->apikey))
        {
            $userid = $model->validateApiKey($request->apikey);
            if (isset($userid))
            {
                $authlevel = 1;
                $request->userid = $userid;
            }
        }
        elseif (isset($request->sessionkey))
        {
            $userid = $model->validateSession($request->sessionkey);
            if (isset($userid))
            {
                $authlevel = 2;
                $request->userid = $userid;
            }
        }
        
        if ($authlevel < $requiredlevel)
        {
            $result = new Result();
            $result->status = 'error';
            RenderView('LoginRequired', $request->format, $result);
        }
        
        return true;
    }

    protected function GetModel($model)
    {
        $model_name = $model . 'Model';
        if (!class_exists($model_name))
        {
            user_error('Failed to find model - ' . $model, E_USER_ERROR);
        }
        $model = new $model_name();
        return $model;
    }

    protected function RenderView($template, $format, $result)
    {
        $view_name = strtolower($format) . $template . 'View';
        if (!class_exists($view_name))
        {
            $view_name = strtolower($format) . 'View';
            if (!class_exists($view_name))
            {
                user_error('Failed to find view for format:template - ' . $format . ':' . $template, E_USER_ERROR);
            }
        }
        $view = new $view_name();
        $view->render($template, $result);
        exit;
    }
}
