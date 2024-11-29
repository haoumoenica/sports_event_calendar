<?php
require_once "../components/db/db_connect.php";

// Check if the event ID is passed via the URL
if (!isset($_GET['event_id'])) {
    die("Event ID is required.");
}

$event_id = $_GET['event_id'];

// Fetch the current event data
$sql_event = "SELECT e.*, v.id AS venue_id, 
                     t1._foreignkey_sport_id AS sport_id, 
                     et1._foreignkey_team_id AS team1_id, 
                     et2._foreignkey_team_id AS team2_id
              FROM event e
              LEFT JOIN event_team et1 ON et1._foreignkey_event_id = e.id AND et1._foreignkey_team_id = e._foreignkey_team1_id
              LEFT JOIN event_team et2 ON et2._foreignkey_event_id = e.id AND et2._foreignkey_team_id = e._foreignkey_team2_id
              LEFT JOIN team t1 ON et1._foreignkey_team_id = t1.id
              LEFT JOIN team t2 ON et2._foreignkey_team_id = t2.id
              LEFT JOIN event_venue ev ON e.id = ev._foreignkey_event_id
              LEFT JOIN venue v ON ev._foreignkey_venue_id = v.id
              WHERE e.id = '$event_id'";

$result_event = $conn->query($sql_event);

if ($result_event->num_rows == 0) {
    die("Event not found.");
}

$event = $result_event->fetch_assoc();

// Handle form submission to update the event
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sport_id = $_POST['sport'];
    $team1_id = $_POST['team1'];
    $team2_id = $_POST['team2'];
    $date_time = $_POST['date_time'];
    $description = $_POST['description'];
    $venue_id = $_POST['venue'];

    // Update the event details (excluding teams and venues)
    $sql_update = "UPDATE event 
                   SET _foreignkey_sport_id = '$sport_id', 
                       date_time = '$date_time', 
                       description = '$description'
                   WHERE id = '$event_id'";

    if ($conn->query($sql_update) === TRUE) {
        // Update teams in the event_team table
        // Update team 1
        $sql_update_team1 = "INSERT INTO event_team (_foreignkey_event_id, _foreignkey_team_id, team_type) 
                             VALUES ('$event_id', '$team1_id', 'team1')
                             ON DUPLICATE KEY UPDATE _foreignkey_team_id = '$team1_id'";

        // Update team 2
        $sql_update_team2 = "INSERT INTO event_team (_foreignkey_event_id, _foreignkey_team_id, team_type) 
                             VALUES ('$event_id', '$team2_id', 'team2')
                             ON DUPLICATE KEY UPDATE _foreignkey_team_id = '$team2_id'";

        if ($conn->query($sql_update_team1) === TRUE && $conn->query($sql_update_team2) === TRUE) {
            // Update venue in the event_venue table
            $sql_update_venue = "INSERT INTO event_venue (_foreignkey_event_id, _foreignkey_venue_id) 
                                 VALUES ('$event_id', '$venue_id')
                                 ON DUPLICATE KEY UPDATE _foreignkey_venue_id = '$venue_id'";

            if ($conn->query($sql_update_venue) === TRUE) {
                echo "<div class='alert alert-success'>Event successfully updated.</div>";
            } else {
                echo "<div class='alert alert-danger'>Error updating venue: " . $conn->error . "</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Error updating teams: " . $conn->error . "</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Error updating event: " . $conn->error . "</div>";
    }
}

// Fetch all sports for the sport dropdown
$sql_sports = "SELECT * FROM sport";
$result_sports = $conn->query($sql_sports);

// Fetch teams and venues based on the sport
if (isset($_POST['sport'])) {
    $sport_id = $_POST['sport'];

    // Fetch teams for the selected sport
    $sql_teams = "SELECT * FROM team WHERE _foreignkey_sport_id = '$sport_id'";
    $result_teams = $conn->query($sql_teams);

    // Fetch venues associated with the teams of the selected sport
    // Adjust the query to fetch venues related to any team of the sport (not just specific teams)
    $sql_venues = "SELECT v.* 
                   FROM venue v
                   INNER JOIN team t ON v.id = t._foreignkey_venue_id
                   WHERE t._foreignkey_sport_id = '$sport_id'";
    $result_venues = $conn->query($sql_venues);
} else {
    // Default to the sport of the current event
    $sport_id = $event['sport_id'];
    $sql_teams = "SELECT * FROM team WHERE _foreignkey_sport_id = '$sport_id'";
    $result_teams = $conn->query($sql_teams);

    // Fetch venues associated with the teams of the current event's sport
    $sql_venues = "SELECT v.* 
                   FROM venue v
                   INNER JOIN team t ON v.id = t._foreignkey_venue_id
                   WHERE t._foreignkey_sport_id = '$sport_id'";
    $result_venues = $conn->query($sql_venues);
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
</head>

<body>

    <div class="container my-5">
        <h2 class="text-center">Update Event</h2>

        <form action="update.php?event_id=<?= $event_id ?>" method="POST">
            <div class="mb-3">
                <label for="sport" class="form-label">Sport</label>
                <select class="form-select" name="sport" id="sport" required onchange="this.form.submit()">
                    <?php while ($sport = $result_sports->fetch_assoc()) : ?>
                        <option value="<?= $sport['id'] ?>" <?= ($sport['id'] == $event['sport_id']) ? 'selected' : '' ?>><?= $sport['name'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="team1" class="form-label">Team 1</label>
                <select class="form-select" name="team1" id="team1" required>
                    <?php while ($team = $result_teams->fetch_assoc()) : ?>
                        <option value="<?= $team['id'] ?>" <?= ($team['id'] == $event['team1_id']) ? 'selected' : '' ?>><?= $team['name'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="team2" class="form-label">Team 2</label>
                <select class="form-select" name="team2" id="team2" required>
                    <?php
                    // Reset the teams result to show again as Team 2 options
                    $result_teams->data_seek(0); // Move pointer back to the start
                    while ($team = $result_teams->fetch_assoc()) : ?>
                        <option value="<?= $team['id'] ?>" <?= ($team['id'] == $event['team2_id']) ? 'selected' : '' ?>><?= $team['name'] ?></option>
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

            <div class="mb-3">
                <label for="venue" class="form-label">Venue</label>
                <select class="form-select" name="venue" id="venue" required>
                    <?php while ($venue = $result_venues->fetch_assoc()) : ?>
                        <option value="<?= $venue['id'] ?>" <?= ($venue['id'] == $event['venue_id']) ? 'selected' : '' ?>><?= $venue['name'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3 text-center">
                <button type="submit" class="btn btn-primary">Update Event</button>
            </div>
        </form>

        <!-- Button to return to the home page -->
        <div class="text-center mt-3">
            <a href="index.php" class="btn btn-secondary">Return to Home Page</a>
        </div>
    </div>

</body>

</html>