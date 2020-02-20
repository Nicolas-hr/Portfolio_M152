<?php
require_once dirname(__DIR__).'/Controller/EDatabaseController.php';
require_once dirname(dirname(__DIR__)).'/public/includes/const.inc.php';

/**
 * @author Hoarau Nicolas
 * 
 * @brief: Fonction qui upload le text du post dans la base de données
 *
 * @param string $content Contenu du post
 * @param array $file Tous les fichiers à insérer dans la base de données
 * 
 * @return boolean
 *
 * @version 1.0
 */
function InsertPost(string $content, array $file = null) : bool
{
  $date = date("Y-m-d H:i:s");

  $req = <<<EOT
  INSERT INTO post(commentaire, creationDate) VALUES (:content, :creationDate);
  EOT;

  try {
    EDatabaseController::beginTransaction();

    $query = EDatabaseController::getInstance()->prepare($req);

    $query->bindParam(':content', $content, PDO::PARAM_STR);
    $query->bindParam(':creationDate', $date);
    $query->execute();

    $latsInsertId = EdatabaseController::getInstance()->lastInsertId();

    if ($file != null) {
      for ($i = 0; $i < count($file['name']); $i++) {
        $fileExtension = pathinfo($file['name'][$i], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $fileExtension;
        $fileType = exif_imagetype($file['tmp_name'][$i]);

        if (InsertMedias($latsInsertId, $filename, $fileType, $file['tmp_name'][$i]) == false) {
          EDatabaseController::rollBack();
          return false;
        }
      }
    }

    EDatabaseController::commit();

    return true;
  } catch (Exception $e) {
    EDatabaseController::rollBack();
    return false;
  }
}

/**
 * @author Hoarau Nicolas
 * 
 * @brief: Fonction qui insère les medias dans la base de données
 *
 * @param integer $postId   id du post afin de l'envoyer dans la fonction LinkPostAndMedia
 * @param string $filename  Nom du media à insérer dans la base de données
 * @param string $filetype  Type du fichier à insérer dans la base de données
 * @param string $tmpName   Chemin temporaire du fichier afin de le déplacer dans le dossier de dossier des mediad upload
 * 
 * @return boolean
 * 
 * @version 1.0
 */
function InsertMedias(int $postId, string $filename, string $filetype, string $tmpName) : bool
{
  $date = date("Y-m-d H:i:s");
  
  $req = <<<EOT
  INSERT INTO media(nomMedia, typeMedia, creationDate) VALUES (:nomMedia, :typeMedia, :creationDate);
  EOT;

  try {
    EDatabaseController::beginTransaction();

    $query = EDatabaseController::prepare($req);

    $query->bindParam(':nomMedia', $filename, PDO::PARAM_STR);
    $query->bindParam(':typeMedia', $filetype, PDO::PARAM_STR);
    $query->bindParam(':creationDate', $date);

    $query->execute();

    $latsInsertId = EdatabaseController::getInstance()->lastInsertId();

    if (move_uploaded_file($tmpName, UPLOAD_PATH . $filename)) {
      if (LinkPostAndMedia($postId, $latsInsertId) == false) {
        EDatabaseController::rollBack();
        return false;
      }
    }

    EDatabaseController::commit();

    return true;
  } catch (Exception $e) {
    EDatabaseController::rollBack();
    return false;
  }
}

/**
 *  @author Hoarau Nicolas
 *
 * @brief: Fonction qui lie un medias à son post dans la base de données
 *
 * @param integer $postId   Id du post
 * @param integer $mediaId  Id du media
 * 
 * @return boolean
 * 
 * @version 1.0
 */
function LinkPostAndMedia(int $postId, int $mediaId) : bool
{
  $req = <<<EOT
  INSERT INTO contenir(idPost, idMedia) VALUES (:idPost, :idMedia);
  EOT;

  try {
    EDatabaseController::beginTransaction();

    $query = EDatabaseController::getInstance()->prepare($req);
    $query->bindParam(':idPost', $postId, PDO::PARAM_INT);
    $query->bindParam(':idMedia', $mediaId, PDO::PARAM_INT);

    $query->execute();

    EDatabaseController::commit();

    return true;
  } catch (Exception $e) {
    EDatabaseController::rollBack();
    return false;
  }
}
