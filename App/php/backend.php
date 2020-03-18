<?php
require_once dirname(__DIR__) . '/Controller/EDatabaseController.php';
require_once dirname(dirname(__DIR__)) . '/public/includes/const.inc.php';

date_default_timezone_set('Europe/Zurich');

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ INSERT FUNCTIONS ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
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
    throw $e->getMessage();
    EDatabaseController::rollBack();
    return false;
  }
}

/**
 * @author Hoarau Nicolas
 * 
 * @brief: Fonction qui insère les medias dans la base de données
 *
 * @param integer $idPost   id du post afin de l'envoyer dans la fonction LinkPostAndMedia
 * @param string $filename  Nom du media à insérer dans la base de données
 * @param string $filetype  Type du fichier à insérer dans la base de données
 * @param string $tmpName   Chemin temporaire du fichier afin de le déplacer dans le dossier de dossier des mediad upload
 * 
 * @return boolean
 * 
 * @version 1.0
 */
function InsertMedias(int $idPost, string $filename, string $filetype, string $tmpName): bool
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
      if (LinkPostAndMedia($idPost, $latsInsertId) == false) {
        EDatabaseController::rollBack();
        return false;
      }
    }

    EDatabaseController::commit();

    return true;
  } catch (Exception $e) {
    throw $e->getMessage();
    EDatabaseController::rollBack();
    return false;
  }
}

/**
 * @author Hoarau Nicolas
 *
 * @brief: Fonction qui lie un medias à son post dans la base de données
 *
 * @param integer $idPost   Id du post
 * @param integer $idMedia  Id du media
 * 
 * @return boolean
 * 
 * @version 1.0
 */
function LinkPostAndMedia(int $idPost, int $idMedia): bool
{
  $req = <<<EOT
  INSERT INTO contenir(idPost, idMedia) VALUES (:idPost, :idMedia);
  EOT;

  try {
    EDatabaseController::beginTransaction();

    $query = EDatabaseController::getInstance()->prepare($req);
    $query->bindParam(':idPost', $idPost, PDO::PARAM_INT);
    $query->bindParam(':idMedia', $idMedia, PDO::PARAM_INT);

    $query->execute();

    EDatabaseController::commit();

    return true;
  } catch (Exception $e) {
    throw $e->getMessage();
    EDatabaseController::rollBack();
    return false;
  }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ DELETE FUNCTIONS ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

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
    throw $e->getMessage();
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
    throw $e->getMessage();
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
function DeleteLink(int $idPost, int $idMedia = null): bool
{
  if ($idMedia == null) {
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
      throw $e->getMessage();

      EDatabaseController::rollBack();
      return false;
    }
  } else {
    $req = <<<EOT
  DELETE FROM contenir WHERE idPost = :idPost AND idMedia = :idMedia;
  EOT;

    try {
      EDatabaseController::beginTransaction();

      $query = EDatabaseController::getInstance()->prepare($req);
      $query->bindParam(':idPost', $idPost, PDO::PARAM_INT);
      $query->bindParam(':idMedia', $idMedia, PDO::PARAM_INT);

      $query->execute();

      EDatabaseController::commit();

      return true;
    } catch (Exception $e) {
      EDatabaseController::rollBack();

      throw $e->getMessage();
      return false;
    }
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
    throw $e->getMessage();
    EDatabaseController::rollBack();
    return false;
  }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ UPDATE FUNCTIONS ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
/**
 * @author Hoarau Nicolas
 * @brief Fonction qui modifie le text d'un post choisis
 *
 * @param string $comment
 * @param integer $idPost
 * 
 * @return boolean
 * 
 * @version 1.0
 */
function UpdateComment(string $comment, int $idPost): bool
{
  $date = date("Y-m-d H:i:s");

  $req = <<<EOT
  UPDATE post 
  SET commentaire = :comment, modificationDate = :date 
  WHERE idPost = :idPost;
  EOT;

  try {
    EDatabaseController::beginTransaction();

    $query = EDatabaseController::prepare($req);

    $query->bindParam(':comment', $comment, PDO::PARAM_STR);
    $query->bindParam(':idPost', $idPost, PDO::PARAM_INT);
    $query->bindParam(':date', $date);

    $query->execute();

    EDatabaseController::commit();

    return true;
  } catch (Exception $e) {
    EDatabaseController::rollBack();

    throw $e->getMessage();
    return false;
  }
}

/**
 * @author Hoarau Nicolas
 * @brief fonction qui récupère l'id d'un media via son nom
 *
 * @param string $filename
 * 
 * @return integer
 * 
 * @version 1.0
 */
function GetMediaIdByName(string $filename) : int
{
  $req = <<<EOT
  SELECT idMedia FROM media WHERE nomMedia = :filename;
  EOT;

  try {
    $query = EDatabaseController::prepare($req);

    $query->bindParam(':filename', $filename, PDO::PARAM_STR);

    $query->execute();

    $queryData = $query->fetch(PDO::FETCH_ASSOC);

    return $queryData['idMedia'];
  } catch (Exception $e) {
    throw $e->getMessage();
  }
}