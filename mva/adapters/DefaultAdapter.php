<?php
class DefaultAdapter extends BaseAdapter
{
    public function DefaultAction($request)
    {
        $result = new stdClass;
        $result->status = 'fail';
        $result->data['reason'] = '';

        $this->RenderView('Default', $request->format, $result);
    }
}
