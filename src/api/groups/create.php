<?php
header("Content-Type: application/json");

$file = 'groups.json';

// Load existing groups from file
$groups = json_decode(file_get_contents(__DIR__ . "/groups.json"), true);

// Get new group name from POST data
$name = $_POST["name"];

// Generate new group ID
$id = count($groups) > 0 ? max(array_column($groups, "id")) + 1 : 1;

// Add new group to array
$newGroup = [
    "id" => $id,
    "name" => $name,
];
$groups[] = $newGroup;

// Save updated groups to file
file_put_contents(__DIR__ . "/groups.json", json_encode($groups));

// Return new group as JSON response
echo json_encode($newGroup);
?>
