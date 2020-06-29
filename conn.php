<?php 

	  session_start();
	  $conn=mysqli_connect("localhost","root","","covid19");
	  if(!$conn)
	    die("Connection Failed".mysqli_connect_error());

?>