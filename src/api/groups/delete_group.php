<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $id = $_POST['id'];

  $data = json_decode(file_get_contents('groups.json'), true);
  foreach ($data as $key => $groupData) {
    if ($groupData['id'] == $id) {
      unset($data[$key]);
    }
  }
  file_put_contents('groups.json', json_encode(array_values($data), JSON_PRETTY_PRINT));

  echo json_encode(['success' => true]);
} else {
  echo json_encode(['error' => 'Invalid request method']);
}
?>
