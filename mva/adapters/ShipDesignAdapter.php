<?php
class ShipDesignAdapter extends BaseAdapter
{
    public function ListAction($request)
    {
        $this->CheckAuth($request, 1);

        $model = $this->GetModel('Shipdesign');

        $result = $model->listDesigns($request->userid);
        
        $this->RenderView('Shipdesignlist', $request->format, $result);
    }
}
