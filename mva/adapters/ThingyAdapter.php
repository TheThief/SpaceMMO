<?php
class ThingyAdapter extends BaseAdapter
{
    public function defaultAction($request)
    {
        $this->RenderView('Thingy', $request->format, $request);
    }
}
