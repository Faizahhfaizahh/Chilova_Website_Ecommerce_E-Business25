<?php 
$conn = new mysqli("localhost","root","","chilova_database");

if ($conn -> connect_errno) {
  echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
  exit();
}
?>