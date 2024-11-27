<?php
require_once "../components/db/db_connect.php";

// Get the current date and time
$current_date_time = date('Y-m-d H:i:s');

// Get the page number from the AJAX request
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$days_per_page = 3;  // Number of days to display per page
$events_per_day = 4; // Number of events to display per day

// Calculate the date range for the requested page
$start_date = date('Y-m-d', strtotime("+$days_per_page * ($page - 1) days"));
$end_date = date('Y-m-d', strtotime("+$days_per_page * $page days"));



// SQL query to fetch events for the specified date range
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
    WHERE e.date_time >= '$start_date 00:00:00' AND e.date_time <= '$end_date 23:59:59'
    ORDER BY e.date_time ASC";

$result = $conn->query($sql);

$events_by_date = [];

if ($result->num_rows > 0) {
    $seen_events = [];

    while ($row = $result->fetch_assoc()) {
        // Format the date (remove the time)
        $event_date = date('Y-m-d', strtotime($row['date_time']));

        if (in_array($row['event_id'], $seen_events)) {
            continue;
        }

        // Mark the event as seen
        $seen_events[] = $row['event_id'];

        // Add the event to the corresponding date in the array
        $events_by_date[$event_date][] = $row;
    }

    // Make the HTML layout for events
    $layout = "";
    foreach ($events_by_date as $date => $events) {
        // Showcasing the date header, containing events of the day
        $layout .= "<div class='event-day-container'>";
        $layout .= "<h4 class='text-center'>" . date('l, F j', strtotime($date)) . "</h4>";

        // Limit the number of events per day
        $event_count = 0;
        foreach ($events as $event) {
            if ($event_count >= $events_per_day) {
                break;
            }

            // Format the event date and time
            $date_time = date('d-m-Y H:i', strtotime($event['date_time']));

            // Variable for each event card
            $layout .= "
                <div class='mt-3'>
                    <div class='card mb-4'>
                        <div class='card-body'>
                            <div class='team-logos'>
                                <img src='assets/logos/{$event['team1_logo']}' alt='{$event['team1_name']} logo' class='team-logo'>
                                <span class='vs-text'>VS</span>
                                <img src='assets/logos/{$event['team2_logo']}' alt='{$event['team2_name']} logo' class='team-logo'>
                            </div>
                            <div class='event-details'>
                                <h5 class='card-title'>{$event['sport_name']}</h5>
                                <p><strong>{$event['team1_name']}</strong> vs <strong>{$event['team2_name']}</strong></p>
                                <p><strong>Date & Time:</strong> {$date_time}</p>
                                <p><strong>Venue:</strong> {$event['venue_name']}</p>
                            </div>
                            <div class='text-center'>
                                <button class='btn btn-primary' onclick='showNextCard({$event['event_id']})'>Next Card</button>
                            </div>
                        </div>
                    </div>
                </div>
            ";

            $event_count++;
        }

        $layout .= "</div>";
    }
} else {
    $layout = "<p>No upcoming events found.</p>";
}

$conn->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sports Calendar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../public/assets/css/style.css">
</head>

<body>

    <div class="container my-5">
        <h2 class="text-center">Upcoming Sports Events</h2>
        <!-- Event container dynamically filled by AJAX -->
        <div class="event-container" id="event-container"></div>

        <!-- Navigation buttons -->
        <div class="text-center mt-4">
            <button class="btn btn-primary" id="prev-btn">Previous</button>
            <button class="btn btn-primary" id="next-btn">Next</button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script>
        let currentPage = 1;
        const container = document.getElementById('event-container');
        const nextBtn = document.getElementById('next-btn');
        const prevBtn = document.getElementById('prev-btn');

        function loadEvents(page) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function() {
                if (xhr.status === 200) {
                    container.innerHTML = xhr.responseText;
                }
            };

            xhr.send('page=' + page);
        }

        loadEvents(currentPage);

        nextBtn.addEventListener('click', function() {
            currentPage++;
            loadEvents(currentPage);
        });

        prevBtn.addEventListener('click', function() {
            if (currentPage > 1) {
                currentPage--;
                loadEvents(currentPage);
            }
        });
    </script>

</body>

</html>