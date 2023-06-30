<?php 

$conn = mysqli_connect('localhost', 'Jonah', 'pass1234', 'incubation_system');

if(!$conn){
    die("Connection failed: ".mysqli_connect_error());
}

?>