<?php
require_once('config.php');

$email = $_POST['email'];
$password = $_POST['password'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {

  $payload = array(
    'iss' => $issuer,
    'sub' => $email
  );
  $jwt = JWT::encode($payload, $secret_key);

  $response['status'] = 'success';
  $response['token'] = $jwt;
  echo json_encode($response);
} else {

  $response['status'] = 'error';
  $response['message'] = 'Invalid email or password';
  echo json_encode($response);
}
?>