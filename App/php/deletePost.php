<?php
require_once getcwd() . './backend.php';

if ($_POST['idPost']) {
  $idPost = filter_var($_POST['idPost'], FILTER_SANITIZE_NUMBER_INT);
}

if (DeletePost($idPost)) {
  echo json_encode([
    'ReturnCode' => 0,
    'Success' => "Post delete correctly"
  ]);
  exit();
}
