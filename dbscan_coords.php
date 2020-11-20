<?php

function getAreaOfPolygon($geometry) {
    $area = 0.0000;
	//echo $geometry;
    for ($ri=0, $rl=sizeof($geometry); $ri<$rl; $ri++) {

        for ($vi=0, $vl=sizeof($geometry); $vi<$vl; $vi++) {
            $thisx = $geometry[$vi][1];
            $thisy = $geometry[$vi][0];
            $nextx = $geometry[ ($vi+1) % $vl ][1];
            $nexty = $geometry[ ($vi+1) % $vl ][0];
						
            $area += ($thisx * $nexty) - ($thisy * $nextx);
			
        }
    }

    // done with the rings: "sign" the area and return it
    $area = abs(($area / 2));
    return $area;
}

function getCentroidOfPolygon($geometry) {
    $cx = 0;
    $cy = 0;

    for ($ri=0, $rl=sizeof($geometry); $ri<$rl; $ri++) {
        for ($vi=0, $vl=sizeof($geometry); $vi<$vl; $vi++) {
            $thisx = $geometry[ $vi ][1];
            $thisy = $geometry[ $vi ][0];
            $nextx = $geometry[ ($vi+1) % $vl ][1];
            $nexty = $geometry[ ($vi+1) % $vl ][0];

            $p = ($thisx * $nexty) - ($thisy * $nextx);
            $cx += ($thisx + $nextx) * $p;
            $cy += ($thisy + $nexty) * $p;
        }
    }

    // last step of centroid: divide by 6*A
    $area = getAreaOfPolygon($geometry);
    $cx = -$cx / ( 6 * $area);
    $cy = -$cy / ( 6 * $area);

    // done!
    return array($cx,$cy);
}

$json = file_get_contents('php://input');
$json_decode = json_decode($json, true); 

//echo $json_decode;
$cent = getCentroidOfPolygon($json_decode);

$json_encode = json_encode($cent);
echo $json_encode;


?>