<?php
require_once "../components/db/db_connect.php";

$current_date = date('Y-m-d');

// Calculate the start of the current week based on Sunday
$start_of_week = date('Y-m-d', strtotime('last Sunday', strtotime($current_date)));
$end_of_week = date('Y-m-d', strtotime('next Saturday', strtotime($current_date)));

// Calculate the week offset (from GET request or default to 0 for the current week)
$week_offset = isset($_GET['week_offset']) ? (int)$_GET['week_offset'] : 0;

// Adjust the start and end of the week based on the week offset
$start_of_week = date('Y-m-d', strtotime("$start_of_week +$week_offset weeks"));
$end_of_week = date('Y-m-d', strtotime("$end_of_week +$week_offset weeks"));

// Fetch distinct sports for the filter dropdown
$sports_query = "SELECT id, name FROM sport ORDER BY name ASC";
$sports_result = $conn->query($sports_query);

$sports = [];
if ($sports_result && $sports_result->num_rows > 0) {
    while ($sport_row = $sports_result->fetch_assoc()) {
        $sports[] = $sport_row;
    }
}

// Get the selected sport ID from the GET request
$selected_sport = isset($_GET['sport_id']) ? (int)$_GET['sport_id'] : null;

// Construct SQL query for events
$sql = "SELECT DISTINCT e.id AS event_id, e.date_time, e.description, 
               s.name AS sport_name, t1.name AS team1_name, t2.name AS team2_name,
               t1.logo AS team1_logo, t2.logo AS team2_logo, 
               v.name AS venue_name, DATE(e.date_time) AS event_date
        FROM event e
        LEFT JOIN sport s ON e._foreignkey_sport_id = s.id
        LEFT JOIN team t1 ON e._foreignkey_team1_id = t1.id
        LEFT JOIN team t2 ON e._foreignkey_team2_id = t2.id
        LEFT JOIN event_venue ev ON e.id = ev._foreignkey_event_id
        LEFT JOIN venue v ON ev._foreignkey_venue_id = v.id
        WHERE DATE(e.date_time) BETWEEN '$start_of_week' AND '$end_of_week'";

// Add sport filter to the SQL query if a sport is selected
if ($selected_sport) {
    $sql .= " AND e._foreignkey_sport_id = $selected_sport";
}

$sql .= " ORDER BY e.date_time ASC";

$result = $conn->query($sql);

// Organize events by day (based on the date only)
$events_by_day = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $event_date = date('Y-m-d', strtotime($row['date_time']));
        $events_by_day[$event_date][] = $row;
    }
}

// Generate a list of days with events for the selected week
$all_dates_with_events = [];
for ($i = 0; $i < 7; $i++) {
    $date = date('Y-m-d', strtotime("$start_of_week +$i days"));

    // Only have the day if there are events on that day
    if (isset($events_by_day[$date]) && count($events_by_day[$date]) > 0) {
        $all_dates_with_events[] = $date;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sports Event Calendar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/calendar/public/assets/css/style.css">
    <link rel="stylesheet" href="/calendar/public/assets/css/components.css">
</head>

<body>
    <div><?php require_once "../components/navbar.php"; ?></div>

    <div class="container my-5">
        <h2 class="text-center mb-4">Upcoming Sports Events</h2>

        <div class="text-end mb-4">
            <a href="create_event.php" class="btn btn-success">Create New Event</a>
        </div>

        <!-- Sport Filter -->
        <div class="mb-4">
            <form method="GET" action="">
                <div class="row">
                    <!-- Sport Filter Dropdown -->
                    <div class="col-md-6">
                        <select name="sport_id" class="form-select" onchange="this.form.submit()">
                            <option value="">All Sports</option>
                            <?php foreach ($sports as $sport): ?>
                                <option value="<?= $sport['id'] ?>" <?= $selected_sport == $sport['id'] ? 'selected' : '' ?>>
                                    <?= $sport['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Week Offset Hidden Input -->
                    <input type="hidden" name="week_offset" value="<?= $week_offset ?>">
                </div>
            </form>
        </div>

        <!-- Navigation Buttons -->
        <div class="text-center mb-4">
            <a href="?week_offset=<?= $week_offset - 1 ?>&sport_id=<?= $selected_sport ?>" class="btn btn-primary mx-2">Previous Week</a>
            <a href="?week_offset=<?= $week_offset + 1 ?>&sport_id=<?= $selected_sport ?>" class="btn btn-primary mx-2">Next Week</a>
        </div>

        <!-- If there are no events for the selected week, display this card -->
        <?php if (empty($all_dates_with_events)): ?>
            <div class="alert alert-warning text-center" role="alert">
                <h4>No events available this week.</h4>
            </div>
        <?php else: ?>
            <!-- Loop over days with events in the week -->
            <?php foreach ($all_dates_with_events as $day): ?>
                <div class="day-container mb-5">
                    <h4 class="mb-3"><?= date('l, F j, Y', strtotime($day)) ?></h4>
                    <div class="row">
                        <?php foreach ($events_by_day[$day] as $event): ?>
                            <div class="col-md-4">
                                <div class="cardmain">
                                    <div class="mycard">
                                        <div class="info">
                                            <h5 class="card-title"><?= $event['sport_name'] ?></h5>
                                            <div class="teams">
                                                <div class="team-logo">
                                                    <img src="assets/logos/<?= $event['team1_logo'] ?>" alt="<?= $event['team1_name'] ?> logo" class="team-logo-img">
                                                </div>
                                                <span class="vs">VS</span>
                                                <div class="team-logo">
                                                    <img src="assets/logos/<?= $event['team2_logo'] ?>" alt="<?= $event['team2_name'] ?> logo" class="team-logo-img">
                                                </div>
                                            </div>
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
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div><?php require_once "../components/footer.php"; ?></div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>