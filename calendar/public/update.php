<?php
require_once "../components/db/db_connect.php";

// Check if the event ID is provided in the URL
if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];

    // Fetch event details from the database
    $sql = "SELECT e.id, e.date_time, e.description, e.score, e.result_status, 
                   s.name AS sport_name, e._foreignkey_sport_id, 
                   t1.name AS team1_name, t2.name AS team2_name, 
                   v.name AS venue_name, t1.logo AS team1_logo, t2.logo AS team2_logo,
                   t1.id AS team1_id, t2.id AS team2_id, v.id AS venue_id
            FROM event e
            INNER JOIN sport s ON e._foreignkey_sport_id = s.id
            LEFT JOIN event_team et1 ON et1._foreignkey_event_id = e.id
            LEFT JOIN event_team et2 ON et2._foreignkey_event_id = e.id AND et1._foreignkey_team_id != et2._foreignkey_team_id
            LEFT JOIN team t1 ON et1._foreignkey_team_id = t1.id
            LEFT JOIN team t2 ON et2._foreignkey_team_id = t2.id
            LEFT JOIN event_venue ev ON e.id = ev._foreignkey_event_id
            LEFT JOIN venue v ON ev._foreignkey_venue_id = v.id
            WHERE e.id = '$event_id'";

    $result = $conn->query($sql);
    $event = $result->fetch_assoc();

    if (!$event) {
        die("Event not found.");
    }
} else {
    die("Event ID not provided.");
}

// Get the list of sports
$sports_result = $conn->query("SELECT * FROM sport");

// Get the list of teams associated with the sport of the event (for both team1 and team2)
$teams_result = $conn->query("SELECT * FROM team WHERE _foreignkey_sport_id = '" . $event['_foreignkey_sport_id'] . "'");

// Get the list of venues associated with the teams
$venues_result = $conn->query("SELECT * FROM venue WHERE id IN 
    (SELECT _foreignkey_venue_id FROM team WHERE id IN ('" . $event['team1_id'] . "', '" . $event['team2_id'] . "'))");

// Check if the form is submitted to update the event
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data from the POST request
    $date_time = $_POST['date_time'] ?? $event['date_time'];
    $description = $_POST['description'] ?? $event['description'];
    $score = $_POST['score'] ?? $event['score'];
    $result_status = $_POST['result_status'] ?? $event['result_status'];
    $sport_id = $_POST['sport_id'] ?? $event['_foreignkey_sport_id'];
    $team1_id = $_POST['team1_id'] ?? $event['team1_id'];
    $team2_id = $_POST['team2_id'] ?? $event['team2_id'];
    $venue_id = $_POST['venue_id'] ?? $event['venue_id'];

    // Update the event in the database
    $update_sql = "UPDATE event SET 
                    date_time = '$date_time', 
                    description = '$description', 
                    score = '$score', 
                    result_status = '$result_status', 
                    _foreignkey_sport_id = '$sport_id'
                   WHERE id = '$event_id'";

    if ($conn->query($update_sql) === TRUE) {
        echo "Event updated successfully.";
    } else {
        echo "Error updating event: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="container my-5">
        <h2>Edit Event</h2>

        <form method="POST">
            <div class="mb-3">
                <label for="sport_id" class="form-label">Sport</label>
                <select name="sport_id" class="form-select" disabled>
                    <?php while ($sport = $sports_result->fetch_assoc()) { ?>
                        <option value="<?= $sport['id'] ?>" <?= $event['_foreignkey_sport_id'] == $sport['id'] ? 'selected' : '' ?>>
                            <?= $sport['name'] ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="team1_id" class="form-label">Team 1</label>
                <select name="team1_id" class="form-select" required>
                    <?php while ($team = $teams_result->fetch_assoc()) { ?>
                        <option value="<?= $team['id'] ?>" <?= isset($_POST['team1_id']) && $_POST['team1_id'] == $team['id'] || $event['team1_id'] == $team['id'] ? 'selected' : '' ?>>
                            <?= $team['name'] ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="team2_id" class="form-label">Team 2</label>
                <select name="team2_id" class="form-select" required>
                    <?php
                    // Fetching teams again for team2 to ensure all teams are shown for team 2
                    $teams_result_for_team2 = $conn->query("SELECT * FROM team WHERE _foreignkey_sport_id = '" . $event['_foreignkey_sport_id'] . "'");
                    while ($team = $teams_result_for_team2->fetch_assoc()) { ?>
                        <option value="<?= $team['id'] ?>" <?= isset($_POST['team2_id']) && $_POST['team2_id'] == $team['id'] || $event['team2_id'] == $team['id'] ? 'selected' : '' ?>>
                            <?= $team['name'] ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="venue_id" class="form-label">Venue</label>
                <select name="venue_id" class="form-select" required>
                    <?php while ($venue = $venues_result->fetch_assoc()) { ?>
                        <option value="<?= $venue['id'] ?>" <?= isset($_POST['venue_id']) && $_POST['venue_id'] == $venue['id'] || $event['venue_id'] == $venue['id'] ? 'selected' : '' ?>>
                            <?= $venue['name'] ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="date_time" class="form-label">Date and Time</label>
                <input type="datetime-local" name="date_time" class="form-control" value="<?= isset($_POST['date_time']) ? $_POST['date_time'] : $event['date_time'] ?>" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" class="form-control" required><?= isset($_POST['description']) ? $_POST['description'] : $event['description'] ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Update Event</button>
        </form>
    </div>

</body>

</html>