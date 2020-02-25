<?php
require_once getcwd() .'./backend.php';

$content = filter_input(INPUT_POST, "postText", FILTER_SANITIZE_STRING);

if (isset($_FILES['medias'])) {
  $file = $_FILES['medias'];

  for ($i = 0; $i < count($file['name']); $i++) {
    $fileType = exif_imagetype($file['tmp_name'][$i]);
    if (!image_type_to_mime_type($fileType) || mime_content_type($file['name']) == 'audio/mpeg' || mime_content_type($file['name']) == 'video/mp4') {
      if ($file['size'][$i] > FILESIZE_MAX) {
        echo json_encode([
         'ReturnCode' => 2,
         'Error' => "Le fichier est trop gros"
       ]);
       exit();
      }
      echo json_encode([
        'ReturnCode' => 3,
        'Error' => "Ce n'est pas une image"
      ]);
      exit();
      }
    }

    if (InsertPost($content, $file)) {
      echo json_encode([
        'ReturnCode' => 0,
        'Success' => "Post send correctly"
      ]);
      exit();
  }
}



if (InsertPost($content)) {
  echo json_encode([
    'ReturnCode' => 0,
    'Success' => "Post send correctly"
  ]);
  exit();
}
