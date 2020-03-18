<?php
require_once getcwd() . './backend.php';

$content = filter_input(INPUT_POST, "postText", FILTER_SANITIZE_STRING);

if (isset($_FILES['medias'])) {
  $file = $_FILES['medias'];
  $finfo = finfo_open(FILEINFO_MIME_TYPE);

  for ($i = 0; $i < count($file['name']); $i++) {
    $mimeType = finfo_file($finfo, $file['tmp_name'][$i]);
    $fileType = explode('/', $mimeType)[0];

    if ($file['size'][$i] > FILESIZE_MAX && $fileType == 'image') {
      if ($fileType != 'image' && $fileType  != 'audio' && $fileType != 'video') {
        echo json_encode([
          'ReturnCode' => 2,
          'Error' => "This type of media isn't accepted"
        ]);
        exit();
      }
      echo json_encode([
        'ReturnCode' => 1,
        'Error' => "File too big"
      ]);
      exit();
    }
  }

  finfo_close($finfo);

  if (InsertPost($content, $file)) {
    echo json_encode([
      'ReturnCode' => 0,
      'Success' => "Post send correctly"
    ]);
    exit();
  } else {
    echo json_encode([
      'ReturnCode' => 3,
      'Error' => "Error during the insert"
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
} else {
  echo json_encode([
    'ReturnCode' => 3,
    'Error' => "Error during the insert"
  ]);
  exit();
}
