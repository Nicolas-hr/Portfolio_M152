<?php
require_once getcwd() . './backend.php';

$content = filter_input(INPUT_POST, "postText", FILTER_SANITIZE_STRING);
$idPost = filter_input(INPUT_POST, 'idPost', FILTER_SANITIZE_NUMBER_INT);

if (isset($_FILES['medias'])) {
  $file = $_FILES['medias'];
  
  for ($i = 0; $i < count($file['name']); $i++) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name'][$i]);
    $fileType = explode('/', $mimeType)[0];

    if ($fileType == 'image' || $fileType  == 'audio' || $fileType == 'video') {
      if (($file['size'][$i] < FILESIZE_MAX && $fileType == 'image') || $fileType  == 'audio' || $fileType == 'video') {
        finfo_close($finfo);

        $fileExtension = pathinfo($file['name'][$i], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $fileExtension;
        $fileType = exif_imagetype($file['tmp_name'][$i]);

        if (InsertMedias($idPost,$filename, mime_content_type($file['tmp_name'][$i]), $file['tmp_name'][$i])) {
          if ($i == count($file['name'])-15) {
            echo json_encode([
              'ReturnCode' => 0,
              'Success' => "Post send correctly"
            ]);
            exit();
          }
        } else {
          echo json_encode([
            'ReturnCode' => 4,
            'Error' => "Error during the insert"
          ]);
          exit();
        }
      } else {
        echo json_encode([
          'ReturnCode' => 2,
          'Error' => "File is too big"
        ]);
        exit();
      }
    } else {
      echo json_encode([
        'ReturnCode' => 3,
        'Error' => "Ce type de média n'est pas accepté"
      ]);
      exit();
    }
  }
}

if (isset($_POST['postText'])) {
  if (UpdateComment($content, $idPost)) {
    echo json_encode([
      'ReturnCode' => 0,
      'Success' => "Post delete correctly"
    ]);
    exit();
  }
}
