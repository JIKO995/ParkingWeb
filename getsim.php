<?php 
session_start();

if (isset($_SESSION['newsession'])) {
      // This session already exists, should already contain data
		$ses=$_SESSION['newsession'];
		session_destroy();
        echo json_encode($ses);
    }
?>