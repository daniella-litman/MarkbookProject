<?php
#this is the code linking the project to the database 

$servername = "localhost";
$username = "daniella_markbook";
$password = "kawaiibooks";
$dbname = "daniella_markbook";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

