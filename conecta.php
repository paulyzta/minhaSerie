<?php


$servername = 'localhost';
if ($_SERVER['HTTP_HOST'] === 'localhost') {    
    $dbname = 'minhasseries';
    $username = 'root';
    $password = '';
} else {
    $dbname = 'id4840789_iptv';
    $username = 'id4840789_danilo';
    $password = 'abc@123';
}

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die('Falha na conexção: ' . $conn->connect_error);
}