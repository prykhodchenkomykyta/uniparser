<?php
require_once('config.php');

// Получение данных из POST-запроса
$name = $_POST['name'];
$email = $_POST['email'];
$password = $_POST['password'];

// Хеширование пароля
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Проверка наличия пользователя в базе данных
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
  // Если пользователь уже существует, возвращаем ошибку
  $response['status'] = 'error';
  $response['message'] = 'User already exists';
  echo json_encode($response);
} else {
  // Добавление нового пользователя в базу данных
  $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
  $stmt->execute([$name, $email, $hashed_password]);

  // Создание токена JWT
  $payload = array(
    'iss' => $issuer, // Издатель токена
    'sub' => $email // Подпись токена
  );
  $jwt = JWT::encode($payload, $secret_key);

  // Возвращение токена в ответе
  $response['status'] = 'success';
  $response['token'] = $jwt;
  echo json_encode($response);
}
?>