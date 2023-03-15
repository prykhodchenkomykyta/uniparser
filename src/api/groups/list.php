<?php
header("Content-Type: application/json");

// Load existing groups from file and return as JSON response
echo file_get_contents(__DIR__ . "/groups.json");
?>
