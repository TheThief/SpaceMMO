<?php
class ShipdesignModel extends BaseModel
{
    public function listDesigns($userid)
    {
        $query = $this->mysqli->prepare('SELECT designid,shipname,hullname,metalcost,size,engines,fuel,cargo,weapons,shields,speed,fueluse,fuelcapacity,cargocapacity,defense FROM shipdesigns LEFT JOIN shiphulls USING (hullid) WHERE userID = ? ORDER BY designid ASC;');
        $query->bind_param('i', $userid);
        $query->execute();
        $query->bind_result($designid,$shipname,$hullname,$metalcost,$size,$engines,$fuel,$cargo,$weapons,$shields,$speed,$fueluse,$fuelcapacity,$cargocapacity,$defense);

        $result = new Result();
        $result->status = 'success';
        
        while ($query->fetch())
		{
            $result->data[$designid] = array(
                'designname' => $shipname,
                'hullname' => $hullname,
                'metalcost' => $metalcost,
                'size' => $size,
                'design' => array(
                    'engines' => $engines,
                    'fuel' => $fuel,
                    'cargo' => $cargo,
                    'weapons' => $weapons,
                    'shields' => $shields,
                ),
                'speed' => $speed,
                'fuelcapacity' => $fuelcapacity,
                'range' => shiprange($speed, $fueluse*SMALLTICKS_PH, $fuelcapacity),
                'attackstrength' => attackPower($weapons),
                'hp' => $defense,
                'cargocapacity' => $cargocapacity,
            );
		}
        
        return $result;
    }
}
