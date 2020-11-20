<html>
<body>
<?php

$conn = new mysqli('localhost','root','');

if($conn->connect_error)
{
	die("connection failed: " . $conn->connect_error);
}

mysqli_select_db($conn,"project_database");

$sql=$conn->query("SELECT * FROM admins WHERE username = '$_POST[username]' AND password = '$_POST[password]'");

$res=$sql->num_rows;

if ($res !=0 ){
	echo "connection succesful";
	header("location: admin.html");
}
else
{
	echo "error: wrong username or password" . "<br>" . $conn->error; 
	header("location: login_failed.html");
}

mysqli_close($conn);
?>
</body>
</html>