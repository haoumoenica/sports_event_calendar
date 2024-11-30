<?php
require_once "../components/db/db_connect.php";

if (!isset($_GET['event_id'])) {
    die("Event ID is required.");
}

$event_id = $_GET['event_id'];

$sql = "SELECT e.*, s.name AS sport_name, t1.name AS team1_name, t2.name AS team2_name, t1.logo AS team1_logo, t2.logo AS team2_logo, v.name AS venue_name 
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
    <link rel="stylesheet" href="/calendar/public/assets/css/details.css">
    <link rel="stylesheet" href="/calendar/public/assets/css/components.css">
</head>

<body>
    <div><?php require_once "../components/navbar.php"; ?></div>

    <div class="container my-5">
        <h2 class="text-center mb-4">Event Details</h2>

        <!-- Event Information -->
        <div class="card">
            <div class="card-body">
                <!-- Logos & VS -->
                <div class="team-logos">
                    <div class="team-logo">
                        <img src="assets/logos/<?= $event['team1_logo'] ?>" alt="<?= $event['team1_name'] ?> logo" class="team-logo-img">
                    </div>
                    <span class="vs">VS</span>
                    <div class="team-logo">
                        <img src="assets/logos/<?= $event['team2_logo'] ?>" alt="<?= $event['team2_name'] ?> logo" class="team-logo-img">
                    </div>
                </div>

                <h5 class="card-title"><?= $event['sport_name'] ?>: <?= $event['team1_name'] ?> vs <?= $event['team2_name'] ?></h5>
                <p><strong>Date & Time:</strong> <?= date('d-m-Y H:i', strtotime($event['date_time'])) ?></p>
                <p><strong>Venue:</strong> <?= $event['venue_name'] ?: 'TBD' ?></p>
                <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($event['description'])) ?></p>
            </div>
        </div>

        <div class="mt-4">
            <a href="index.php" class="btn btn-secondary">Back to Home</a>
        </div>
    </div>
    <div><?php require_once "../components/footer.php"; ?></div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>