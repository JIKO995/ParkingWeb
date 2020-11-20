<?php 

$conn = new mysqli('localhost','root','');

if($conn->connect_error)
{
	die("connection failed: " . $conn->connect_error);
}

mysqli_select_db($conn,"project_database");

$sql="DELETE FROM blocks";

if ($conn->query($sql) === TRUE ){
	echo "succesfully deleted";
	header("location: admin.html");
} 
else
{
	echo "error: " . $sql . "<br>" . $conn->error; 
}

mysqli_close($conn);
?>