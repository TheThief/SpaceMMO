<?php
class UserAdapter extends BaseAdapter
{
    public function loginAction($request)
    {
        $model = $this->GetModel('User');
        
        if ($request->verb === 'POST')
        {
            $result = $model->Login($request->parameters['username'], $request->parameters['password']);
            if (isset($request->parameters['redirect']))
            {
                $result->data['redirect'] = $request->parameters['redirect'];
            }

            if ($result->status === 'success')
            {
                $this->RenderView('LoginSuccess', $request->format, $result);
            }
            else
            {
                $this->RenderView('LoginFail', $request->format, $result);
            }
        }
        else
        {
            $result = new Result();
            $this->RenderView('Login', $request->format, $result);
        }
    }
}
