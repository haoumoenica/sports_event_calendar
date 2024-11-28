<?php
require_once "../components/db/db_connect.php";

// Initialize the $layout variable
$layout = "";

// Check if event_id is passed in the URL
if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];

    // SQL query to fetch the event details based on the event_id
    $sql = "SELECT 
        e.id AS event_id, 
        e.date_time, 
        e.description, 
        e.score, 
        e.result_status, 
        es.name AS sport_name, 
        t1.name AS team1_name, 
        t2.name AS team2_name, 
        t1.logo AS team1_logo, 
        t2.logo AS team2_logo, 
        v.name AS venue_name
    FROM 
        event e
    INNER JOIN 
        sport es ON e._foreignkey_sport_id = es.id
    LEFT JOIN 
        event_team et1 ON et1._foreignkey_event_id = e.id 
    LEFT JOIN 
        team t1 ON et1._foreignkey_team_id = t1.id AND t1._foreignkey_sport_id = e._foreignkey_sport_id
    LEFT JOIN 
        event_team et2 ON et2._foreignkey_event_id = e.id AND et2._foreignkey_team_id != et1._foreignkey_team_id
    LEFT JOIN 
        team t2 ON et2._foreignkey_team_id = t2.id AND t2._foreignkey_sport_id = e._foreignkey_sport_id
    LEFT JOIN 
        event_venue ev ON e.id = ev._foreignkey_event_id
    LEFT JOIN 
        venue v ON ev._foreignkey_venue_id = v.id
    WHERE 
        e.id = ?";  // Using the placeholder for event_id

    // Prepare the SQL statement
    if ($stmt = $conn->prepare($sql)) {
        // Bind the event_id parameter to the prepared statement
        $stmt->bind_param("i", $event_id); // "i" means the event_id is an integer

        // Execute the prepared statement
        $stmt->execute();

        // Bind the result variables
        $stmt->bind_result($event_id, $date_time, $description, $score, $result_status, $sport_name, $team1_name, $team2_name, $team1_logo, $team2_logo, $venue_name);

        // Fetch the result
        if ($stmt->fetch()) {
            // Format the date and time
            $date_time = date('d-m-Y H:i', strtotime($date_time));

            // HTML layout stored in $layout as a string
            $layout = "
            <div class='container my-5'>
                <h2 class='text-center mb-4'>Event Details</h2>

                <div class='row'>
                    <div class='col-lg-8 col-md-8 col-sm-12 mx-auto'>
                        <div class='card shadow-lg border-0'>
                            <div class='card-body'>
                                <!-- Event Title -->
                                <h3 class='card-title mb-3'>{$sport_name}</h3>

                                <!-- Teams and Logos -->
                                <div class='d-flex justify-content-between align-items-center'>
                                    <div class='team-logo'>
                                        <img src='assets/logos/{$team1_logo}' alt='{$team1_name} logo' class='img-fluid rounded-circle' style='width: 80px; height: 80px;'>
                                        <h5>{$team1_name}</h5>
                                    </div>
                                    <span class='vs-text'>VS</span>
                                    <div class='team-logo'>
                                        <img src='assets/logos/{$team2_logo}' alt='{$team2_name} logo' class='img-fluid rounded-circle' style='width: 80px; height: 80px;'>
                                        <h5>{$team2_name}</h5>
                                    </div>
                                </div>

                                <!-- Event Date and Venue -->
                                <div class='mt-4'>
                                    <p><strong>Date & Time:</strong> {$date_time}</p>
                                    <p><strong>Venue:</strong> " . (!empty($venue_name) ? $venue_name : 'Venue: Not Available') . "</p>
                                </div>

                                <!-- Event Description -->
                                <div class='mt-4'>
                                    <p><strong>Description:</strong> {$description}</p>
                                </div>

                                <!-- Event Score and Result Status -->
                                <div class='mt-4'>
                                    <p><strong>Score:</strong> {$score}</p>
                                    <p><strong>Result Status:</strong> {$result_status}</p>
                                </div>

                                <!-- Back Button -->
                                <div class='mt-4 text-center'>
                                    <a href='index.php' class='btn btn-secondary'>Back to Events</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>";
        } else {
            $layout = "<p class='text-center text-danger'>Event not found.</p>";
        }

        // Close the prepared statement
        $stmt->close();
    } else {
        $layout = "<p class='text-center text-danger'>Error preparing the query.</p>";
    }

    // Close the database connection
    $conn->close();
} else {
    $layout = "<p class='text-center text-danger'>No event selected.</p>";
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Event Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
</head>

<body>
    <div class="container mt-5">
        <h1 class="mt-5">Event Details</h1>
        <?= $layout ?> <!-- Echo the layout here -->
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</body>

</html>