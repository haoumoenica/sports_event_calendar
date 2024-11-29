<?php

// Include the database connection file
require_once "../components/db/db_connect.php";

$event_id = $_GET["event_id"];

$sql = "SELECT * FROM `event` WHERE event_id = {$event_id}";
$result = mysqli_query($conn, $sql);

$row = mysqli_fetch_assoc($result);



// if ($row["logo"] != "pet.png") {
//     unlink("pictures/{$row["picture"]}");
// }


$sqlDelete = "DELETE FROM `event` WHERE event_id = {$event_id}";
mysqli_query($conn, $sqlDelete);
header("Location: index.php");
