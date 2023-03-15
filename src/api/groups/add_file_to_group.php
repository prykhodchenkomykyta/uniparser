<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $group = $_POST['group'];
  $file = $_POST['file'];

  $data = json_decode(file_get_contents('groups.json'), true);
  foreach ($data as &$groupData) {
    if ($groupData['name'] == $group) {
      $groupData['files'][] = $file;
    }
  }
  file_put_contents('groups.json', json_encode($data, JSON_PRETTY_PRINT));

  echo json_encode(['success' => true]);
} else {
  echo json_encode(['error' => 'Invalid request method']);
}
?>
