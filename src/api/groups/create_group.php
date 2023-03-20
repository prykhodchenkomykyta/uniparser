<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $name = $_POST['name'];

  $data = json_decode(file_get_contents('groups.json'), true);
  $id = count($data) + 1;
  $data[] = ['id' => $id, 'name' => $name, 'files' => []];
  file_put_contents('groups.json', json_encode($data, JSON_PRETTY_PRINT));

  echo json_encode(['success' => true]);
} else {
  echo json_encode(['error' => 'Invalid request method']);
}
?>
