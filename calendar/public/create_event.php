<?php
require_once "../components/db/db_connect.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sport_id = $_POST['sport'];
    $team1_id = $_POST['team1'];
    $team2_id = $_POST['team2'];
    $date_time = $_POST['date_time'];
    $description = $_POST['description'];
    $venue_id = $_POST['venue'];

    $sql_event = "INSERT INTO event (_foreignkey_sport_id, _foreignkey_team1_id, _foreignkey_team2_id, date_time, description)
              VALUES ('$sport_id', '$team1_id', '$team2_id', '$date_time', '$description')";

    if ($conn->query($sql_event)) {
        $event_id = $conn->insert_id;
        $sql_venue = "INSERT INTO event_venue (_foreignkey_event_id, _foreignkey_venue_id) VALUES ('$event_id', '$venue_id')";
        if ($conn->query($sql_venue)) {
            header("Location: index.php");
            exit;
        }
    }
    $error = $conn->error;
}

$sports = $conn->query("SELECT * FROM sport");
$teams = $conn->query("SELECT * FROM team");
$venues = $conn->query("SELECT * FROM venue");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/create_event.css">
    <link rel="stylesheet" href="/calendar/public/assets/css/components.css">
</head>

<body>
    <div><?php require_once "../components/navbar.php"; ?></div>

    <div class="container create-event-container">
        <h2>Create Event</h2>

        <!-- Display error message if any -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="sport" class="form-label">Sport</label>
                <select class="form-select" name="sport" id="sport" required>
                    <?php while ($sport = $sports->fetch_assoc()): ?>
                        <option value="<?= $sport['id'] ?>"><?= $sport['name'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="team1" class="form-label">Team 1</label>
                <select class="form-select" name="team1" id="team1" required>
                    <?php while ($team = $teams->fetch_assoc()): ?>
                        <option value="<?= $team['id'] ?>"><?= $team['name'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="team2" class="form-label">Team 2</label>
                <select class="form-select" name="team2" id="team2" required>
                    <?php
                    // Reset the result pointer to allow reuse of teams
                    $teams->data_seek(0);
                    while ($team = $teams->fetch_assoc()): ?>
                        <option value="<?= $team['id'] ?>"><?= $team['name'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="date_time" class="form-label">Date & Time</label>
                <input type="datetime-local" class="form-control" name="date_time" id="date_time" required>
            </div>

            <div class="form-group">
                <label for="venue" class="form-label">Venue</label>
                <select class="form-select" name="venue" id="venue" required>
                    <?php while ($venue = $venues->fetch_assoc()): ?>
                        <option value="<?= $venue['id'] ?>"><?= $venue['name'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Add a description text area here -->
            <div class="form-group">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" name="description" id="description" rows="4" placeholder="Enter event description..." required></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Create Event</button>
            <a href="index.php" class="btn btn-secondary">Back to Home</a>
        </form>

    </div>
    <div><?php require_once "../components/footer.php"; ?></div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>