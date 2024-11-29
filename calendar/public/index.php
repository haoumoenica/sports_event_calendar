<?php
require_once "../components/db/db_connect.php";

// Define constants
$events_per_day = 3;  // Maximum number of events per day
$days_per_page = 4;   // Maximum number of days per page

// Get the current page number from the URL, defaulting to 1
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $days_per_page;  // Calculate offset for pagination

// Get the current date (today)
$current_date = date('Y-m-d');

// Fetch events starting from today, limited to the next 4 days
$sql = "SELECT e.id AS event_id, e.date_time, e.description, 
               s.name AS sport_name, t1.name AS team1_name, t2.name AS team2_name,
               v.name AS venue_name, DATE(e.date_time) AS event_date
        FROM event e
        LEFT JOIN sport s ON e._foreignkey_sport_id = s.id
        LEFT JOIN team t1 ON e._foreignkey_team1_id = t1.id
        LEFT JOIN team t2 ON e._foreignkey_team2_id = t2.id
        LEFT JOIN event_venue ev ON e.id = ev._foreignkey_event_id
        LEFT JOIN venue v ON ev._foreignkey_venue_id = v.id
        WHERE e.date_time >= CURDATE()
        ORDER BY e.date_time ASC
        LIMIT " . ($events_per_day * $days_per_page) . " OFFSET $offset";

// Execute the query
$result = $conn->query($sql);

// Organize events by day
$events_by_day = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $event_date = date('Y-m-d', strtotime($row['date_time']));
        $events_by_day[$event_date][] = $row;
    }
} else {
    $events_by_day = null;
}

// Count total number of event days starting from today
$total_days_query = "SELECT COUNT(DISTINCT DATE(e.date_time)) AS total_days
                       FROM event e
                       WHERE e.date_time >= CURDATE()";
$total_days_result = $conn->query($total_days_query);
$total_days = $total_days_result->fetch_assoc()['total_days'];

// Calculate the total number of pages (based on the number of event days)
$total_pages = ceil($total_days / $days_per_page);

// Close DB connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sports Event Calendar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container my-5">
        <h2 class="text-center mb-4">Upcoming Sports Events</h2>
        <div class="text-end mb-4">
            <a href="create_event.php" class="btn btn-success">Create New Event</a>
        </div>

        <?php if ($events_by_day): ?>
            <div class="row">
                <?php foreach ($events_by_day as $day => $events): ?>
                    <div class="col-12 mb-5">
                        <div class="day-container">
                            <h4 class="mb-3"><?= date('l, F j, Y', strtotime($day)) ?></h4>

                            <?php foreach ($events as $event): ?>
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= $event['sport_name'] ?></h5>
                                        <p>
                                            <strong><?= $event['team1_name'] ?></strong> vs
                                            <strong><?= $event['team2_name'] ?></strong>
                                        </p>
                                        <p><strong>Venue:</strong> <?= $event['venue_name'] ?: 'TBD' ?></p>
                                        <p><strong>Date & Time:</strong> <?= date('d-m-Y H:i', strtotime($event['date_time'])) ?></p>
                                        <p><strong>Description:</strong> <?= $event['description'] ?></p>

                                        <div class="d-flex justify-content-between">
                                            <a href="details.php?event_id=<?= $event['event_id'] ?>" class="btn btn-primary btn-sm">View</a>
                                            <a href="update.php?event_id=<?= $event['event_id'] ?>" class="btn btn-secondary btn-sm">Update</a>
                                            <a href="delete.php?event_id=<?= $event['event_id'] ?>" class="btn btn-danger btn-sm">Delete</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center">No upcoming events for the selected period.</p>
        <?php endif; ?>

        <!-- Previous and Next buttons -->
        <div class="d-flex justify-content-between">
            <a class="btn btn-secondary" href="?page=<?= max($page - 1, 1) ?>" role="button">
                Previous
            </a>
            <a class="btn btn-secondary" href="?page=<?= min($page + 1, $total_pages) ?>" role="button">
                Next
            </a>
        </div>
    </div>
</body>

</html>