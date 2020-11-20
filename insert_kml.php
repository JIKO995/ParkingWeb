<?php

$xml = simplexml_load_file('C:\wamp64\www\parking_project\try.kml');

$placemarks = $xml->Document->Folder->Placemark;
$conn = new mysqli('localhost','root','');

if($conn->connect_error)
{
	die("connection failed: " . $conn->connect_error);
}
mysqli_select_db($conn,"project_database");

 $query = '';
 $run='';
 
for ($i = 1; $i <= sizeof($placemarks); $i++) {
	
//get coordinates from kml
     $cor_d  =  explode(' ', $placemarks[$i-1]->MultiGeometry->Polygon->outerBoundaryIs->LinearRing->coordinates);
	 
     $qtmp=array();
	 $rings=array();
	 
//for each pair of coordinates, qtmp array is the array of strings which are saved in db and rings array is the one to be used for centre calculation
     foreach($cor_d as $value){
          $tmp = explode(',',$value);
          $ttmp=$tmp[1];
          $tmp[1]=$tmp[0];
          $tmp[0]=$ttmp; 
          $qtmp[]= '(' . $tmp[0] . ',' .$tmp[1].')';
		  $rings[]=array($tmp[0],$tmp[1]);
     }
	 
	$cent = getCentroidOfPolygon($rings);
	
	$cent = implode(', ', $cent);
	 
	 $coord = implode(', ', $qtmp);
	 
	 
	 $desc = $placemarks[$i-1]->description;
	 
//get the non-CDATA description by parsing the html part as a list, getting the last element which includes the population, and trimming the string to get the number
	 $DOM = new DOMDocument;
     $DOM->loadHTML($desc);
	 $liList = $DOM->getElementsByTagName('li');
	 $liValues = array();
	 foreach ($liList as $li) {
		$liValues[] = $li->nodeValue;
	}

	$population = substr($liValues[2], 12);
	
  //  $query .='\''.$coordinates.'\', \''.$cor_d.'\'';
    $sql ="INSERT INTO blocks (id, coordinates, population, centre, places, curve) VALUES ('$i', '$coord', '$population', '$cent', '1', '1' )";

	if ($conn->query($sql) === TRUE ){
		echo "succesfully inserted";

	} 
	else
	{
		echo "error: " . $sql . "<br>" . $conn->error; 
	}

}

header("location: admin.html");
mysqli_close($conn);

function getAreaOfPolygon($geometry) {
    $area = 0.0000;
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



?>