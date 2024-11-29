<?php
require_once "../components/db/db_connect.php";

if (!isset($_GET['event_id'])) {
    die("Event ID is required.");
}

$event_id = $_GET['event_id'];

// Delete event_venue entry first
$sql_delete_venue = "DELETE FROM event_venue WHERE _foreignkey_event_id = '$event_id'";
$conn->query($sql_delete_venue);

// Then delete the event
$sql_delete_event = "DELETE FROM event WHERE id = '$event_id'";

if ($conn->query($sql_delete_event)) {
    header("Location: index.php");
    exit;
} else {
    echo "Error deleting event: " . $conn->error;
}

$conn->close();
