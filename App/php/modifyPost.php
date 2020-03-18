<?php
require_once getcwd() . './backend.php';

$content = filter_input(INPUT_POST, "postText", FILTER_SANITIZE_STRING);
$idPost = filter_input(INPUT_POST, 'idPost', FILTER_SANITIZE_NUMBER_INT);
$mediaSuppressed = isset($_POST['mediasSuppressed']) ? $_POST['mediasSuppressed'] : null;

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

        if (InsertMedias($idPost, $filename, mime_content_type($file['tmp_name'][$i]), $file['tmp_name'][$i])) {
          if ($i == count($file['name']) - 1) {
            if ($mediaSuppressed != null) {
              for ($j = 0; $j < count($mediaSuppressed); $j++) {
                $filename = explode('/', $mediaSuppressed[$j])[3];
                $idMedia = GetMediaIdByName($filename);
        
                if (DeleteLink($idPost, $idMedia)) {
                  if (DeleteMedia($idMedia, $filename)) {
                    if ($j == count($mediaSuppressed) - 1) {
        
                    echo json_encode([
                      'ReturnCode' => 0,
                      'Success' => "Post updated correctly"
                    ]);
                    exit();
                    }
                  }
                }
              }
            }

            echo json_encode([
              'ReturnCode' => 0,
              'Success' => "Post updated correctly"
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

if (isset($content)) {
  if (UpdateComment($content, $idPost)) {
    if ($mediaSuppressed != null) {
      for ($j = 0; $j < count($mediaSuppressed); $j++) {
        $filename = explode('/', $mediaSuppressed[$j])[3];
        $idMedia = GetMediaIdByName($filename);

        if (DeleteLink($idPost, $idMedia)) {
          if (DeleteMedia($idMedia, $filename)) {
            if ($j == count($mediaSuppressed) - 1) {

            echo json_encode([
              'ReturnCode' => 0,
              'Success' => "Post updated correctly"
            ]);
            exit();
            }
          }
        }
      }
    }

    echo json_encode([
      'ReturnCode' => 0,
      'Success' => "Post updated correctly"
    ]);
    exit();
  }
}
