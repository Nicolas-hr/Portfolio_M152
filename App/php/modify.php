<?php
require_once getcwd() . './backend.php';

if (isset($_POST['postText'])) {
  $postText = filter_var($_POST['postText'], FILTER_SANITIZE_STRING);
  $idPost = filter_var($_POST['idPost'], FILTER_SANITIZE_NUMBER_INT);

  if (UpdateComment($postText, $idPost)) {
    echo json_encode([
      'ReturnCode' => 0,
      'Success' => "Post delete correctly"
    ]);
    exit();
  }
}

if (isset($_FILES['img'])) {
  
}
