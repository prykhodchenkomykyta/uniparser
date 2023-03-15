<?php
header("Content-Type: application/json");

// Load existing groups from file
$groups = json_decode(file_get_contents(__DIR__ . "/groups.json"), true);

// Get group ID to delete from query parameters
$id = $_GET["id"];

// Find index of group to delete
$index = array_search($id, array_column($groups, "id"));

// If group exists, remove it from array
if ($index !== false) {
    array_splice($groups, $index, 1);
}

// Save updated groups to file
file_put_contents(__DIR__ . "/groups.json", json_encode($groups));

// Return success message as JSON response
echo json_encode(["message" => "Group deleted successfully"]);
?>
