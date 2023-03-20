<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_FILES) {
  $file = $_FILES['file']['tmp_name'];
  $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
  if ($ext === 'csv' && ($handle = fopen($file, "r")) !== FALSE) {
    $data = array();
    while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
      $data[] = $row;
    }
    fclose($handle);
    header('Content-Type: application/json');
    echo json_encode($data);
  } else {
    // Если расширение файла не .csv, отправляем соответствующий HTTP-код
    header("HTTP/1.1 400 Bad Request");
    echo "Only CSV files are allowed.";
  }
  exit();
}
?>
