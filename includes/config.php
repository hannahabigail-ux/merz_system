<?php

$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'merzsalonsystem';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error){
    echo ('Connection Failed: ' .$conn->connect_error);
}else{
    echo ("Database Connected");
}