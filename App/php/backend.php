<?php
require_once dirname(__DIR__) . '/Controller/EDatabaseController.php';
require_once dirname(dirname(__DIR__)) . '/public/includes/const.inc.php';

date_default_timezone_set('Europe/Zurich');

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
function InsertPost(string $content, array $file = null): bool
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


    if ($file != null) {
      $latsInsertId = EdatabaseController::getInstance()->lastInsertId();

      for ($i = 0; $i < count($file['name']); $i++) {
        $fileExtension = pathinfo($file['name'][$i], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $fileExtension;
        $fileType = exif_imagetype($file['tmp_name'][$i]);

        if (InsertMedias($latsInsertId, $filename, mime_content_type($file['tmp_name'][$i]), $file['tmp_name'][$i]) == false) {
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
function InsertMedias(int $postId, string $filename, string $filetype, string $tmpName): bool
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
 * @author Hoarau Nicolas
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
function LinkPostAndMedia(int $postId, int $mediaId): bool
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

/**
 * @author Hoarau Nicolas
 * @brief Fonction qui supprime le post et ses medias s'il en a
 *
 * @param integer $idPost
 * 
 * @return boolean
 * 
 * @version 1.0
 */
function DeletePost(int $idPost): bool
{
  $medias = GetPostMedia($idPost);
  $req = <<<EOT
  DELETE FROM post WHERE idPost = :idPost;
  EOT;

  try {
    if ($medias != null) {
      if (DeleteLink($idPost)) {
        foreach ($medias as $media) {
          if (DeleteMedia($media['idMedia'], $media['nomMedia']) == false) {
            return false;
          }
        }
      } else {
        return false;
      }
    }

    EDatabaseController::beginTransaction();

    $query = EDatabaseController::prepare($req);
    $query->bindParam(':idPost', $idPost, PDO::PARAM_INT);

    $query->execute();

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
 * @brief Fonction qui récupère les medias du post choisi
 * 
 * @param integer $idPost
 * 
 * @return array|null
 * 
 * @version 1.0
 */
function GetPostMedia(int $idPost): ?array
{
  $req = <<<EOT
  SELECT media.idMedia, media.nomMedia
  FROM media 
  JOIN contenir ON contenir.idMedia = media.idMedia
  JOIN post ON post.idPost = contenir.idPost
  WHERE post.idPost = :idPost
  EOT;

  try {
    $query = EDatabaseController::getInstance()->prepare($req);
    $query->bindParam(':idPost', $idPost, PDO::PARAM_INT);
    $query->execute();

    $queryData = $query->fetchAll(PDO::FETCH_ASSOC);

    return count($queryData) > 0 ? $queryData : null;
  } catch (Exception $e) {
    return null;
  }
}

/**
 * @author Hoarau Nicolas
 * @brief Supprime le lien en un post choisi et ses medias
 *
 * @param integer $idPost
 * 
 * @return boolean
 * 
 * @version 1.0
 */
function DeleteLink(int $idPost): bool
{
  $req = <<<EOT
  DELETE FROM contenir WHERE idPost = :idPost;
  EOT;

  try {
    EDatabaseController::beginTransaction();

    $query = EDatabaseController::getInstance()->prepare($req);
    $query->bindParam(':idPost', $idPost, PDO::PARAM_INT);
    $query->execute();

    EDatabaseController::commit();
    return true;
  } catch (Exception $e) {
    EDatabaseController::rollBack();
    return false;
  }
}

/**
 * @author Hoarau Nicolas
 * @brief Fonction qui supprime un media selectionné
 *
 * @param integer $idMedia
 * 
 * @return boolean
 * 
 * @version 1.0
 */
function DeleteMedia(int $idMedia, string $mediaName): bool
{
  $req = <<<EOT
  DELETE FROM media WHERE idMedia = :idMedia
  EOT;

  try {
    if (unlink(UPLOAD_PATH . $mediaName)) {

      EDatabaseController::beginTransaction();

      $query = EDatabaseController::prepare($req);
      $query->bindParam(':idMedia', $idMedia, PDO::PARAM_INT);

      $query->execute();

      EDatabaseController::commit();

      return true;
    }
  } catch (Exception $e) {
    EDatabaseController::rollBack();
    return false;
  }
}
