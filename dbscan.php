<?php
// DBSCAN is a density based clustering algorithm
// see http://en.wikipedia.org/wiki/DBSCAN for more details
//
// Some great features of DBSCAN, and density based clustering methods in general,
// are that you don't need to specify the number of clusters as a parameter and every
// point does not need to belong to a cluster as would be the case in k-means for example.
// DBSCAN will also find non-spherical, arbitrarily shaped clusters.
//
// Author: Bhavik Maneck
class DBSCAN {
	
	// $points is an array of unique ids for the points you clutsering
	// should correspond to keys of point distances in $distance_matrix
	//
	// Note $points can be a subset of the point ids in the distance matrix
	// if you want to perform dbscan clustering on a cluster produced by dbscan
	// see example on github readme for this
	private $points;	
	// is 2 dimensional array with point ids as keys and values as the distance between the points
	// can be an upper triangle of distances
	private $distance_matrix; 
	// maintains array of points not assigned to a cluster
	private $noise_points;  
	private $in_a_cluster;
	private $clusters;
	
	public function __construct($distance_matrix, $points)
	{
		$this->distance_matrix = $distance_matrix;
		$this->points = $points;
		$this->noise_points = array();
		$this->clusters = array();
		$this->in_a_cluster = array();
	}
	// $new_points should be an array of unique point ids that is still a subset of 
	// the keys in the distance matrix provided on construction
	//
	// this will allow clustering of previously produced clusters from DBSCAN
	public function set_points($new_points)
	{
		$this->points = $new_points;
	}
	
	private function expand_cluster($point, $neighbor_points, $c, $epsilon, $min_points)
	{
		$this->clusters[$c][] = $point;
		$this->in_a_cluster[] = $point;
		$neighbor_point = reset($neighbor_points);
		while ($neighbor_point)
		{
			$neighbor_points2 = $this->region_query($neighbor_point, $epsilon);
			if (count($neighbor_points2) >= $min_points)
			{
				foreach ($neighbor_points2 as $neighbor_point2)
				{
					if (!in_array($neighbor_point2, $neighbor_points))
					{
						$neighbor_points[] = $neighbor_point2;
					}
				}
			}
			if (!in_array($neighbor_point, $this->in_a_cluster))
			{
				$this->clusters[$c][] = $neighbor_point;
				$this->in_a_cluster[] = $neighbor_point;
			}
			$neighbor_point = next($neighbor_points);
		}
	}
	
	private function region_query($point, $epsilon)
	{
		$neighbor_points = array();
		
		foreach ($this->points as $point2)
		{
			if ($point != $point2)
			{
				// Because we are using an upper diagonal representation of distances between points
				if (array_key_exists($point2, $this->distance_matrix[$point]))
				{	
					$distance = $this->distance_matrix[$point][$point2];
				} else {
					$distance = $this->distance_matrix[$point2][$point];
				}
				if ($distance < $epsilon)
				{
					$neighbor_points[] = $point2;
				}
			
			}
		}
		return $neighbor_points;
	}
	
	// epsilon is min distance to cluster around 
	// min_points is minimum number of points within epsilon of another point needed to form a cluster
	//
	// Returns an array of arrays
	// each inner array is a cluster with point ids belonging to that cluster as members
	public function dbscan($epsilon, $min_points)  
	{
		$this->noise_points = array();  // points that do no belong to any cluster
		$this->clusters = array();		// contains an array for each cluster, each cluster array has points ids belonging to that cluster
		$this->in_a_cluster = array();  // points that have been added to a cluster
		
		$c = 0;
		$this->clusters[$c] = array();
		foreach ($this->points as $point_id)
		{
			$neighbor_points = $this->region_query($point_id, $epsilon);
			if (count($neighbor_points) < $min_points)
			{
				$this->noise_points[] = $point_id;
			} elseif (!in_array($point_id, $this->in_a_cluster)) {
				$this->expand_cluster($point_id, $neighbor_points, $c, $epsilon, $min_points);
				$c = $c + 1;
				$this->clusters[$c] = array();
			}
		}
		
		return $this->clusters;
	}
}


$json = file_get_contents('php://input');
$json_decode = json_decode($json, true); 
//$json_encode = json_encode($json_decode);

$point_ids = array();

$distance_matrix = array();
for($i=0; $i<sizeof($json_decode[0]); $i++){
	array_push($point_ids, strval($i));
	$distance_matrix[strval($i)] = array();
	
	for($j=$i+1; $j<sizeof($json_decode[0]); $j++){
		//$temp = array(strval($j) => $json_decode[$i][$j]);
	//	echo($temp);
		$distance_matrix[strval($i)] += [strval($j) => $json_decode[$i][$j-$i]];
		
	}
}

//print_r($distance_matrixa);

$clustsize = 0;
$clustarr = [];

//echo 'Point IDs:<br />';
//print_r($point_ids);
// Setup DBSCAN with distance matrix and unique point IDs
$DBSCAN = new DBSCAN($distance_matrix, $point_ids);
$epsilon = 300;
$minpoints = 2;
// Perform DBSCAN clustering
$clusters = $DBSCAN->dbscan($epsilon, $minpoints);
//Output results
//echo '<br /><br />Clusters (using epsilon = 30 and minpoints = 3): <br /><br />';
foreach ($clusters as $index => $cluster)
{
	if (sizeof($cluster) > 0)
	{
		if(sizeof($cluster) >= $clustsize){
			if(sizeof($cluster) > $clustsize){
				$clustsize = sizeof($cluster);
				$clustarr = $cluster;
			}else{
				array_push($clustarr, $cluster);
			}
		}
	/*	echo 'Cluster number '.($index+1).':<br />';
		echo '<ul>';
		foreach ($cluster as $member_point_id)
		{
			echo '<li>'.$member_point_id.'</li>';
		}
		echo '</ul>';*/
	}
}

//print_r($clustarr);

$json_encode = json_encode($clustarr);
echo $json_encode;

?>