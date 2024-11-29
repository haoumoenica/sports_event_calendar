<?php
require_once "../components/db/db_connect.php";

// Check if connection is established
if (!isset($conn)) {
    die("Database connection failed.");
}

// Get current date and time
$current_date_time = date('Y-m-d H:i:s');

// SQL query to fetch upcoming events without duplication of teams
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
        venue v ON t1._foreignkey_venue_id = v.id 
    WHERE 
        e.date_time >= '$current_date_time'
    GROUP BY 
        e.id
    ORDER BY 
        e.date_time ASC;";

// Execute the query
$result = $conn->query($sql);

// Error handling if query fails
if ($result === false) {
    die("Error with the query: " . $conn->error);
}

// Initialize an empty array to store the event data grouped by date
$events_by_date = [];
$layout = "";  // Initialize the layout variable to store the HTML output

// Check if any events are returned
if ($result->num_rows > 0) {
    // Loop through the events and group them by the date (without the time)
    while ($row = $result->fetch_assoc()) {
        // Format the date (remove the time)
        $event_date = date('Y-m-d', strtotime($row['date_time']));

        // Add the event to the corresponding date in the $events_by_date array
        $events_by_date[$event_date][] = $row;
    }

    // Generate the HTML layout for events grouped by date
    $layout .= "<div class='container my-5'>";
    $layout .= "<div class='row'>";

    $day_count = 0;  // Counter to limit days to 3

    // Loop through the grouped events
    foreach ($events_by_date as $date => $events) {
        if ($day_count >= 3) {
            break; // Stop after displaying 3 days
        }

        // Add a date header
        $layout .= "<div class='col-12'>";
        $layout .= "<h4 class='text-center'>" . date('l, F j', strtotime($date)) . "</h4>";  // Display the formatted date
        $layout .= "</div>";

        $event_count = 0; // Limit events per day to 3
        foreach ($events as $event) {
            if ($event_count >= 3) {
                break; // Stop after displaying 3 events per day
            }

            // Format the date and time
            $date_time = date('d-m-Y H:i', strtotime($event['date_time']));

            // Check venue name fallback
            $venue_name = !empty($event['venue_name']) ? $event['venue_name'] : 'Venue not available';

            // Generate the HTML card for each event
            $layout .= "
    <div class='col-md-6 col-lg-4 mt-3'>
        <div class='card mb-4'>
            <div class='card-body'>
                <div class='team-logos text-center mb-3'>
                    <img src='assets/logos/{$event['team1_logo']}' alt='{$event['team1_name']} logo' class='team-logo'>
                    <span class='vs-text'>VS</span>
                    <img src='assets/logos/{$event['team2_logo']}' alt='{$event['team2_name']} logo' class='team-logo'>
                </div>
                <div class='event-details text-center'>
                    <h5 class='card-title'>{$event['sport_name']}</h5>
                    <p><strong>{$event['team1_name']}</strong> vs <strong>{$event['team2_name']}</strong></p>
                    <p><strong>Date & Time:</strong> {$date_time}</p>
                    <p><strong>Venue:</strong> {$event['venue_name']}</p>
                    <a href='update.php?event_id={$event['event_id']}' class='btn btn-secondary'>Edit</a>
                    <a href='details.php?event_id={$event['event_id']}' class='btn btn-primary'>See Event</a>
                </div>
            </div>
        </div>
    </div>
";

            $event_count++;
        }

        $day_count++;
    }

    $layout .= "</div>"; // Close row
    $layout .= "</div>"; // Close container

} else {
    $layout = "<div class='container'><p>No upcoming events found.</p></div>";  // If no events are found, display a message
}

$conn->close();  // Close the database connection
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sports Calendar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/calendar/public/assets/css/style.css">
</head>

<body>

    <div class="container my-5">
        <h2 class="text-center">Upcoming Sports Events</h2>

        <!-- Button to create a new event -->
        <div class="text-center mb-4">
            <a href="create_event.php" class="btn btn-success">Create New Event</a>
        </div>

        <!-- Display the event layout -->
        <?= $layout; ?>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

</body>

</html>