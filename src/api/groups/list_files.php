<?php
	header('Content-Type: application/json');
	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
	header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

  // Получаем содержимое groups.json и преобразуем его в массив PHP
  $groups = json_decode(file_get_contents('groups.json'), true);

  // Получаем ID группы из параметра запроса
  $groupId = $_GET['group_id'];

  // Находим соответствующую группу
  foreach ($groups as $group) {
    if ($group['id'] == $groupId) {
      // Возвращаем список файлов для этой группы
      echo json_encode($group['files']);
      break;
    }
  }
?>