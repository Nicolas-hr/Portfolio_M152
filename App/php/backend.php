<?php
require_once('../Controller/DatabaseController.php');

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
    // Traitement
    try {
        EDatabaseController::beginTransaction();

        $req = EDatabaseController::prepare("INSERT INTO post(commentaire, creationDate) VALUES (:content, GETDATE());");

        $req->bindParam(':content', $content, PDO::PARAM_STR);
        $req->execute();

        $req = EDatabaseController::prepare("INSERT INTO media(nomMedia, typeMedia, creationDate) VALUES (:nomMedia, :typeMedia, GETDATE())");

        $req->bindParam(':nomMedia', $filename, PDO::PARAM_STR);
        $req->bindParam(':typeMedia', $mediaType, PDO::PARAM_STR);

        $req->execute();
        $req->commit();
    } catch (Exception $e) {
        $req->rollBack();
        return $e->getMessage();
    }
}
