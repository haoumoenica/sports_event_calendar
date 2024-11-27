<?php
require_once "../components/db/db_connect.php";

// Get current date and time
$current_date_time = date('Y-m-d H:i:s');

// SQL query to fetch upcoming events without duplication of teams
$sql = "SELECT e.id AS event_id, e.date_time, e.description, e.score, e.result_status, 
               s.name AS sport_name, t1.name AS team1_name, t2.name AS team2_name, 
               v.name AS venue_name, t1.logo AS team1_logo, t2.logo AS team2_logo
        FROM event e
        INNER JOIN sport s ON e._foreignkey_sport_id = s.id
        LEFT JOIN event_team et1 ON et1._foreignkey_event_id = e.id
        LEFT JOIN event_team et2 ON et2._foreignkey_event_id = e.id AND et1._foreignkey_team_id != et2._foreignkey_team_id
        LEFT JOIN team t1 ON et1._foreignkey_team_id = t1.id
        LEFT JOIN team t2 ON et2._foreignkey_team_id = t2.id
        LEFT JOIN event_venue ev ON e.id = ev._foreignkey_event_id
        LEFT JOIN venue v ON ev._foreignkey_venue_id = v.id
        WHERE e.date_time >= '$current_date_time'
        GROUP BY e.id, e.date_time, e.description, e.score, e.result_status, 
                 s.name, t1.name, t2.name, v.name, t1.logo, t2.logo
        ORDER BY e.date_time ASC";

// Execute the query
$result = $conn->query($sql);

// Initialize an empty array to store the event IDs and group events by date
$events_by_date = [];
$layout = "";  // Initialize the layout variable to store the HTML output

// Counter for limiting days to 3
$day_count = 0;

// Check if any events are returned
if ($result->num_rows > 0) {
    // Initialize an array to track unique events to avoid duplicates
    $seen_events = [];

    // Loop through the events and group them by the date (without the time)
    while ($row = $result->fetch_assoc()) {
        // Format the date (remove the time)
        $event_date = date('Y-m-d', strtotime($row['date_time']));

        // Skip the event if it has already been added (this will prevent duplicates)
        if (in_array($row['event_id'], $seen_events)) {
            continue;  // Skip duplicate events
        }

        // Mark the event as seen
        $seen_events[] = $row['event_id'];

        // Add the event to the corresponding date in the $events_by_date array
        $events_by_date[$event_date][] = $row;
    }

    // Generate the HTML layout for events grouped by date
    $layout .= "<div class='event-container'>";

    foreach ($events_by_date as $date => $events) {
        if ($day_count >= 3) {
            break; // Stop after displaying 3 days
        }

        // Start a new section for each date
        $layout .= "<div class='event-day-container'>";
        $layout .= "<h4 class='text-center'>" . date('l, F j', strtotime($date)) . "</h4>";  // Display the formatted date

        // Limit the number of events displayed per day (Optional - based on how many events you want per day)
        $event_count = 0;

        // Loop through the events for the current date and display them in cards
        foreach ($events as $event) {
            if ($event_count >= 4) {
                break; // Stop after displaying 4 events per day
            }

            // Format the date and time
            $date_time = date('d-m-Y H:i', strtotime($event['date_time']));

            // Generate the HTML card for each event
            $layout .= "
                <div class='mt-3'>
                    <div class='card mb-4'>
                        <div class='card-body'>
                            <!-- Team Logos with Red Background -->
                            <div class='team-logos'>
                                <img src='assets/logos/{$event['team1_logo']}' alt='{$event['team1_name']} logo' class='team-logo'>
                                <span class='vs-text'>VS</span>
                                <img src='assets/logos/{$event['team2_logo']}' alt='{$event['team2_name']} logo' class='team-logo'>
                            </div>
                            <!-- Event Details -->
                            <div class='event-details'>
                                <h5 class='card-title'>{$event['sport_name']}</h5>
                                <p><strong>{$event['team1_name']}</strong> vs <strong>{$event['team2_name']}</strong></p>
                                <p><strong>Date & Time:</strong> {$date_time}</p>
                                <p><strong>Venue:</strong> {$event['venue_name']}</p>
                            </div>
                        </div>
                    </div>
                </div>
            ";

            $event_count++;
        }

        $layout .= "</div>";  // Close the event day container div
        $day_count++;
    }

    $layout .= "</div>";  // Close the event-container div

} else {
    $layout = "<p>No upcoming events found.</p>";  // If no events are found, display a message
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
        <!-- Display the event layout -->
        <?= $layout; ?>

        <!-- Carousel Navigation -->
        <div class="text-center mt-4">
            <button class="btn btn-primary" id="prev-btn">Previous</button>
            <button class="btn btn-primary" id="next-btn">Next</button>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script>
        const prevButton = document.getElementById('prev-btn');
        const nextButton = document.getElementById('next-btn');
        const eventContainer = document.querySelector('.event-container');

        let scrollPosition = 0;
        const scrollAmount = 300; // Scroll by 300px each time

        // Event listener for the previous button
        prevButton.addEventListener('click', () => {
            scrollPosition -= scrollAmount;
            if (scrollPosition < 0) scrollPosition = 0; // Prevent scrolling beyond the start
            eventContainer.scrollTo({
                left: scrollPosition,
                behavior: 'smooth'
            });
        });

        // Event listener for the next button
        nextButton.addEventListener('click', () => {
            scrollPosition += scrollAmount;
            eventContainer.scrollTo({
                left: scrollPosition,
                behavior: 'smooth'
            });
        });
    </script>
</body>

</html>