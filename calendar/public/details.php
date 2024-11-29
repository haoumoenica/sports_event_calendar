<?php
require_once "../components/db/db_connect.php";

if (!isset($_GET['event_id'])) {
    die("Event ID is required.");
}

$event_id = $_GET['event_id'];

$sql = "
    SELECT e.*, s.name AS sport_name, t1.name AS team1_name, t2.name AS team2_name, v.name AS venue_name 
    FROM event e
    LEFT JOIN sport s ON e._foreignkey_sport_id = s.id
    LEFT JOIN team t1 ON e._foreignkey_team1_id = t1.id
    LEFT JOIN team t2 ON e._foreignkey_team2_id = t2.id
    LEFT JOIN event_venue ev ON e.id = ev._foreignkey_event_id
    LEFT JOIN venue v ON ev._foreignkey_venue_id = v.id
    WHERE e.id = '$event_id'";

$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die("Event not found.");
}

$event = $result->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container my-5">
        <h2 class="text-center mb-4">Event Details</h2>
        <p><strong>Sport:</strong> <?= $event['sport_name'] ?></p>
        <p><strong>Teams:</strong> <?= $event['team1_name'] ?> vs <?= $event['team2_name'] ?></p>
        <p><strong>Date & Time:</strong> <?= date('d-m-Y H:i', strtotime($event['date_time'])) ?></p>
        <p><strong>Venue:</strong> <?= $event['venue_name'] ?></p>
        <p><strong>Description:</strong> <?= $event['description'] ?></p>
        <a href="index.php" class="btn btn-secondary">Back to Home</a>
    </div>
</body>

</html>