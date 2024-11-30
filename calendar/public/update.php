<?php
require_once "../components/db/db_connect.php";

// Check if the event ID is passed
if (!isset($_GET['event_id'])) {
    die("Event ID is required.");
}

$event_id = $_GET['event_id'];

// Fetch the current event data
$sql_event = "SELECT e.*, s.id AS sport_id, s.name AS sport_name, 
           t1.id AS team1_id, t1.name AS team1_name, t1.logo AS team1_logo, 
           t2.id AS team2_id, t2.name AS team2_name, t2.logo AS team2_logo, 
           v.id AS venue_id, v.name AS venue_name
    FROM event e
    LEFT JOIN sport s ON e._foreignkey_sport_id = s.id
    LEFT JOIN team t1 ON e._foreignkey_team1_id = t1.id
    LEFT JOIN team t2 ON e._foreignkey_team2_id = t2.id
    LEFT JOIN event_venue ev ON ev._foreignkey_event_id = e.id
    LEFT JOIN venue v ON ev._foreignkey_venue_id = v.id
    WHERE e.id = '$event_id'";

$result_event = $conn->query($sql_event);

if ($result_event->num_rows == 0) {
    die("Event not found.");
}

$event = $result_event->fetch_assoc();
$sport_id = $event['sport_id'];

// Fetch teams and venues for the sport
$sql_teams = "SELECT id, name, logo FROM team WHERE _foreignkey_sport_id = '$sport_id'";
$result_teams = $conn->query($sql_teams);

$alert_message = "";

$sql_venues = "SELECT v.id, v.name 
    FROM venue v
    INNER JOIN team t ON v.id = t._foreignkey_venue_id
    WHERE t._foreignkey_sport_id = '$sport_id'";
$result_venues = $conn->query($sql_venues);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $team1_id = $_POST['team1'];
    $team2_id = $_POST['team2'];
    $venue_id = $_POST['venue'];
    $date_time = $_POST['date_time'];
    $description = $_POST['description'];

    // Update the event
    $sql_update_event = "UPDATE event 
        SET _foreignkey_team1_id = '$team1_id', 
            _foreignkey_team2_id = '$team2_id', 
            date_time = '$date_time', 
            description = '$description'
        WHERE id = '$event_id'";

    if ($conn->query($sql_update_event) === TRUE) {
        // Update the venue
        $sql_update_venue = "UPDATE event_venue 
            SET _foreignkey_venue_id = '$venue_id' 
            WHERE _foreignkey_event_id = '$event_id'";

        if ($conn->query($sql_update_venue) === TRUE) {
            $alert_message = "<div class='alert alert-success'>Event successfully updated.</div>";
        } else {
            $alert_message = "<div class='alert alert-danger'>Error updating venue: " . $conn->error . "</div>";
        }
    } else {
        $alert_message = "<div class='alert alert-danger'>Error updating event: " . $conn->error . "</div>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/update.css">
    <link rel="stylesheet" href="/calendar/public/assets/css/components.css">
</head>

<body>
    <div><?php require_once "../components/navbar.php"; ?></div>
    <div class="container mt-3">
        <?= $alert_message ?>
    </div>

    <div class="container my-5">
        <h2 class="text-center mb-4">Update Event</h2>

        <!-- Card-shaped Form Container start -->
        <div class="cardmain">
            <div class="mycard">
                <div class="info">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="team1" class="form-label">Team 1</label>
                            <select class="form-select" name="team1" id="team1" required>
                                <?php while ($team = $result_teams->fetch_assoc()) : ?>
                                    <option value="<?= $team['id'] ?>" <?= $team['id'] == $event['team1_id'] ? 'selected' : '' ?>>
                                        <img src="<?= $team['logo'] ?>" alt="<?= $team['name'] ?> logo" class="team-logo"> <?= $team['name'] ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="team2" class="form-label">Team 2</label>
                            <select class="form-select" name="team2" id="team2" required>
                                <?php $result_teams->data_seek(0); ?>
                                <?php while ($team = $result_teams->fetch_assoc()) : ?>
                                    <option value="<?= $team['id'] ?>" <?= $team['id'] == $event['team2_id'] ? 'selected' : '' ?>>
                                        <img src="<?= $team['logo'] ?>" alt="<?= $team['name'] ?> logo" class="team-logo"> <?= $team['name'] ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="venue" class="form-label">Venue</label>
                            <select class="form-select" name="venue" id="venue" required>
                                <?php while ($venue = $result_venues->fetch_assoc()) : ?>
                                    <option value="<?= $venue['id'] ?>" <?= $venue['id'] == $event['venue_id'] ? 'selected' : '' ?>><?= $venue['name'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="date_time" class="form-label">Date & Time</label>
                            <input type="datetime-local" class="form-control" name="date_time" value="<?= date('Y-m-d\TH:i', strtotime($event['date_time'])) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" name="description"><?= $event['description'] ?></textarea>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">Update Event</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Card-shaped Form Container End -->

        <div class="text-center mt-3">
            <a href="index.php" class="btn btn-secondary">Return to Home Page</a>
        </div>
    </div>
    <div><?php require_once "../components/footer.php"; ?></div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>