<?php
$hostname = "localhost";
$username = "root";
$password = "";
$dbname = "sports_event_calendar";

//create connection
$conn = mysqli_connect($hostname, $username, $password, $dbname);

//check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

//cleaning Input

function cleanInput($input)
{
    $data = trim($input);
    $data = strip_tags($data);
    $data = htmlspecialchars($data);

    return $data;
};
