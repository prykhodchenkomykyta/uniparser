<?php
require_once('config.php');

$name = $_POST['name'];
$email = $_POST['email'];
$password = $_POST['password'];

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {

  $response['status'] = 'error';
  $response['message'] = 'User already exists';
  echo json_encode($response);
} else {

  $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
  $stmt->execute([$name, $email, $hashed_password]);

  $payload = array(
    'iss' => $issuer,
    'sub' => $email
  );
  $jwt = JWT::encode($payload, $secret_key);

  $response['status'] = 'success';
  $response['token'] = $jwt;
  echo json_encode($response);
}
?>