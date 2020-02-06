<?php
require_once '../Controller/EDatabaseController.php';

/**
 * @author Hoarau Nicolas
 * 
 * @brief: Fonction qui upload les fichiers dans la base de données
 *
 * @param string $content Contenu du post
 * @param string $filePath Chemin du fichier
 * 
 * @version 1.0.0
 */
function SendPost($content, $filename = null, $mediaType = null)
{
  $date = date("Y-m-d H:i:s");

  // Traitement
  try {
    $db = EDatabaseController::beginTransaction();

    $req = <<<EOT
        INSERT INTO post(commentaire, creationDate) VALUES (:content, :creationDate);
        EOT;

    $query = EDatabaseController::prepare($req);

    $query->bindParam(':content', $content, PDO::PARAM_STR);
    $query->bindParam(':creationDate', $date);
    $query->execute();

    $req = <<<EOT
        INSERT INTO media(nomMedia, typeMedia, creationDate) VALUES (:nomMedia, :typeMedia, :creationDate);
        EOT;

    $query = EDatabaseController::prepare($req);

    $query->bindParam(':nomMedia', $filename, PDO::PARAM_STR);
    $query->bindParam(':typeMedia', $mediaType, PDO::PARAM_STR);
    $query->bindParam(':creationDate', $date);

    $query->execute();
    EDatabaseController::commit();
  } catch (Exception $e) {
    $query->rollBack();
    return $e->getMessage();
  }
}
