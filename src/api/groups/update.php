<?php
header("Content-Type: application/json");

// Load existing groups from file
$groups = json_decode(file_get_contents(__DIR__ . "/groups.json"), true);

// Get group ID and new name from query parameters and POST data
$id = $_GET["id"];
$newName = $_POST["name"];

// Find group to update and update its name
foreach ($groups as &$group) {
    if ($group["id"] == $id) {
        $group["name"] = $newName;
    }
}

// Save updated groups to file
file_put_contents(__DIR__ . "/groups.json", json_encode($groups));

// Return success message as JSON response
echo json_encode(["message" => "Group updated successfully"]);
?>
