<?php
header('Content-Type: application/json');
$data = file_get_contents('groups.json');
echo $data;
?>
