<?php
class htmlShipdesignlistView
{
    public function render($template, $content)
    {
        header('Content-Type: text/html; charset=utf8');
?><html>
 <body>
<?
	echo '<table>', "\n";
	echo '<tr><th>Design Name</th><th>Hull</th><th>Cost</th><th>Size</th><th><span title="Engines/Fuel Bay/Weapons/Shields/Cargo bay">E/F/W/S/C</th><th>Speed</th><th>Fuel Bay</th><th>Range</th><th>Attack</th><th>HP</th><th>Cargo Capacity</th></tr>', "\n";

	foreach ($content->data as $shipid => $data)
	{
        echo '<tr>';
        echo '<td>', $data['designname'], '</td>';
        echo '<td>', $data['hullname'], '</td>';
        echo '<td>', $data['metalcost'], ' Metal</td>';
        echo '<td>', $data['size'], '</td>';
        echo '<td>', $data['design']['engines'], '/', $data['design']['fuel'], '/', $data['design']['weapons'], '/', $data['design']['shields'], '/', $data['design']['cargo'], '</td>';
        echo '<td>', number_format($data['speed'], 2), ' PC/h</td>';
        echo '<td>', number_format($data['fuelcapacity']), ' D</td>';
        echo '<td>', number_format($data['range'], 2), ' PC</td>';
        echo '<td>', number_format($data['attackstrength']), '</td>';
        echo '<td>', number_format($data['hp']), '</td>';
        echo '<td>', number_format($data['cargocapacity']), ' Units</td>';
        echo '</tr>', "\n";
	}
	echo '</table>', "\n";
?> </body>
</html><?
        return true;
    }
}
