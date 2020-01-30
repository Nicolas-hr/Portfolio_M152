<?php
require_once '../../public/includes/const.inc.php';

$content = filter_input(INPUT_POST, "postText", FILTER_SANITIZE_STRING);

if (isset($_FILES['inputImg'])) {
    $file = $_FILES['inputImg'];
}

$fileType = exif_imagetype($file['tmp_name']);

for ($i=0; $i < count($file['name']); $i++) { 
    if (image_type_to_mime_type($fileType)) {
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileType = image_type_to_mime_type($file['tmp_name']);
        $filename = uniqid() . $fileExtension;

        if (move_uploaded_file($file['tmp_name'][$i], UPLOAD_PATH . $filename)) {
//            if (SendPost($content, $filename, $fileType)) { }
        }
    }
}