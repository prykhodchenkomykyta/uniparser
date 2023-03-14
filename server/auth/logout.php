<?php

$servername = "localhost";
$username = "username";
$password = "password";
$dbname = "myDB";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$authHeader = getallheaders()['Authorization'];
$token = explode(' ', $authHeader)[1];

$sql = "UPDATE users SET token = NULL WHERE token = '$token'";
if ($conn->query($sql) === TRUE) {
    echo "Token deleted successfully";
} else {
    echo "Error deleting token: " . $conn->error;
}

$conn->close();

?>