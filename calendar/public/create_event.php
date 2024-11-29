<?php
// Include the database connection file
require_once "../components/db/db_connect.php";

// Initialize variables
$sports = [];
$teams = [];
$selected_sport_id = null;
$selected_team1 = null;
$selected_team2 = null;
$location = '';  // Variable to store the venue (location)
$is_form_submitted = false; // Flag to check if the form is submitted

// Fetch the available sports from the database
$sql_sports = "SELECT DISTINCT s.id, s.name AS sport 
               FROM team t
               JOIN sport s ON t._foreignkey_sport_id = s.id
               ORDER BY s.name";
$result_sports = $conn->query($sql_sports);

if ($result_sports && $result_sports->num_rows > 0) {
    // Fetch all distinct sports
    while ($row = $result_sports->fetch_assoc()) {
        $sports[] = $row;  // Store sport ID and name
    }
}

// If the form is submitted (POST request)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $is_form_submitted = true; // Mark the form as submitted

    // Retrieve the selected sport ID
    if (isset($_POST['sport']) && !empty($_POST['sport'])) {
        $selected_sport_id = $_POST['sport'];

        // Fetch teams for the selected sport
        $sql_teams = "SELECT t.id, t.name AS team_name 
                      FROM team t
                      WHERE t._foreignkey_sport_id = '$selected_sport_id'
                      ORDER BY t.name";
        $result_teams = $conn->query($sql_teams);

        if ($result_teams && $result_teams->num_rows > 0) {
            // Fetch teams for the selected sport
            while ($row = $result_teams->fetch_assoc()) {
                $teams[] = $row;  // Store team ID and name
            }
        }

        // Fetch location (venue) for team1 if selected
        if (isset($_POST['team1']) && !empty($_POST['team1'])) {
            $selected_team1 = $_POST['team1'];

            // Fetch the venue for the selected team1
            $sql_venue = "SELECT v.id AS venue_id, v.name AS venue_name 
                          FROM team t
                          JOIN venue v ON t._foreignkey_venue_id = v.id
                          WHERE t.id = '$selected_team1'";
            $result_venue = $conn->query($sql_venue);

            if ($result_venue && $result_venue->num_rows > 0) {
                $venue = $result_venue->fetch_assoc();
                $location = $venue['venue_name'];  // Store venue name
                $venue_id = $venue['venue_id'];    // Store venue ID for the relationship
            }
        }

        // If form is submitted, process the event creation
        if (isset($_POST['team1'], $_POST['team2'], $_POST['date_time'], $_POST['description'])) {
            $selected_team1 = $_POST['team1'];
            $selected_team2 = $_POST['team2'];
            $date_time = $_POST['date_time'];
            $description = $_POST['description'];

            // If everything is valid (no empty fields), proceed
            if ($selected_team1 != '' && $selected_team2 != '' && $date_time != '' && $location != '' && $selected_team1 != $selected_team2) {
                // Validate if team2 exists in the team table
                $sql_validate_team2 = "SELECT id FROM team WHERE id = '$selected_team2'";
                $result_validate_team2 = $conn->query($sql_validate_team2);

                if ($result_validate_team2 && $result_validate_team2->num_rows > 0) {
                    // Insert event into the event table (without the location)
                    $sql_insert_event = "INSERT INTO event (_foreignkey_sport_id, _foreignkey_team1_id, _foreignkey_team2_id, date_time, description)
                                         VALUES ('$selected_sport_id', '$selected_team1', '$selected_team2', '$date_time', '$description')";

                    if ($conn->query($sql_insert_event)) {
                        // Get the ID of the newly inserted event
                        $event_id = $conn->insert_id;

                        // Insert into the event_venue table to link event with venue
                        $sql_insert_event_venue = "INSERT INTO event_venue (_foreignkey_event_id, _foreignkey_venue_id) 
                                                VALUES ('$event_id', '$venue_id')";

                        if ($conn->query($sql_insert_event_venue)) {
                            echo "<div class='alert alert-success' role='alert'>Event successfully created and linked to venue!</div>";
                        } else {
                            echo "<div class='alert alert-danger' role='alert'>Error linking event to venue: " . $conn->error . "</div>";
                        }
                    } else {
                        echo "<div class='alert alert-danger' role='alert'>Error creating event: " . $conn->error . "</div>";
                    }
                } else {
                    echo "<div class='alert alert-danger' role='alert'>Team 2 does not exist in the database.</div>";
                }
            }
        }
    }
}

$conn->close();  // Close the database connection
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
</head>

<body>
    <div class="container mt-5">
        <h2>Create Event</h2>

        <form id="eventForm" method="POST" action="">
            <!-- Sport Selection Dropdown -->
            <div class="mb-3">
                <label for="sport" class="form-label">Select Sport</label>
                <select class="form-select" name="sport" id="sport" onchange="this.form.submit()">
                    <option value="">Select Sport</option>
                    <?php foreach ($sports as $sport): ?>
                        <option value="<?= htmlspecialchars($sport['id']) ?>" <?= $selected_sport_id == $sport['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sport['sport']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Team 1 Selection (Visible after sport is selected) -->
            <?php if ($selected_sport_id): ?>
                <div class="mb-3">
                    <label for="team1" class="form-label">Team 1</label>
                    <select class="form-select" name="team1" id="team1" onchange="this.form.submit()">
                        <option value="">Select Team 1</option>
                        <?php foreach ($teams as $team): ?>
                            <option value="<?= $team['id'] ?>" <?= $selected_team1 == $team['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($team['team_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Team 2 Selection -->
                <div class="mb-3">
                    <label for="team2" class="form-label">Team 2</label>
                    <select class="form-select" name="team2" id="team2">
                        <option value="">Select Team 2</option>
                        <?php foreach ($teams as $team): ?>
                            <option value="<?= $team['id'] ?>" <?= $selected_team2 == $team['id'] ? 'selected' : '' ?>
                                <?php if ($team['id'] == $selected_team1) echo 'disabled'; ?>>
                                <?= htmlspecialchars($team['team_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Date and Time -->
                <div class="mb-3">
                    <label for="date_time" class="form-label">Game Time</label>
                    <input type="datetime-local" class="form-control" id="date_time" name="date_time" required>
                </div>

                <!-- Location (Venue) automatically filled based on Team 1 -->
                <div class="mb-3">
                    <label for="location" class="form-label">Location</label>
                    <input type="text" class="form-control" id="location" name="location" value="<?= htmlspecialchars($location); ?>" readonly>
                </div>

                <!-- Description -->
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary" id="submitButton">Create Event</button>
            <?php endif; ?>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('eventForm');
            const submitButton = document.getElementById('submitButton');

            form.addEventListener('submit', function(event) {
                const team1 = document.getElementById('team1').value;
                const team2 = document.getElementById('team2').value;
                const date_time = document.getElementById('date_time').value;
                const description = document.getElementById('description').value;

                // Prevent form submission if required fields are not filled
                if (!team1 || !team2 || !date_time || !description || team1 === team2) {
                    event.preventDefault();
                    alert('Please fill in all required fields');
                }
            });
        });
    </script>
</body>

</html>