<?php
$con = mysqli_connect("192.168.0.202","peta2","K94679nM","peta3");

// Check connection
if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  } else 
 echo "Conectado !";

?>
