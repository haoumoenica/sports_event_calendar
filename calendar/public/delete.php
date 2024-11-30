<?php
require_once "../components/db/db_connect.php";

if (!isset($_GET['event_id'])) {
    die("Event ID is required.");
}

$event_id = $_GET['event_id'];

$conn->begin_transaction();

$sql_delete_event_team = "DELETE FROM event_team WHERE _foreignkey_event_id = '$event_id'";
$sql_delete_venue = "DELETE FROM event_venue WHERE _foreignkey_event_id = '$event_id'";
$sql_delete_event = "DELETE FROM event WHERE id = '$event_id'";

if (
    $conn->query($sql_delete_event_team) &&
    $conn->query($sql_delete_venue) &&
    $conn->query($sql_delete_event)
) {
    $conn->commit();
    header("Location: index.php");
    exit;
} else {
    $conn->rollback();
    echo "Error deleting event.";
}

$conn->close();
