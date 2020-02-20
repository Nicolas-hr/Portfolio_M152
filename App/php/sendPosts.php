<?php
require_once './backend.php';

$content = filter_input(INPUT_POST, "postText", FILTER_SANITIZE_STRING);

if (isset($_FILES['inputImg'])) {
  $file = $_FILES['inputImg'];

  for ($i = 0; $i < count($file['name']); $i++) {
    $fileType = exif_imagetype($file['tmp_name'][$i]);
    if (image_type_to_mime_type($fileType)) {
      if ($file['size'][$i] < FILESIZE_MAX) {
        }
        // echo json_encode([
        //   'ReturnCode' => 2,
        //   'Success' => "Le fichier est trop gros"
        // ]);
        // exit();
      }
      // echo json_encode([
      //   'ReturnCode' => 3,
      //   'Success' => "Ce n'est pas une image"
      // ]);
      // exit();
    }

    if (InsertPost($content, $file)) {
      echo json_encode([
        'ReturnCode' => 0,
        'Success' => "Post send correctly"
      ]);
      exit();
  }
}

