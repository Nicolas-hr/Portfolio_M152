<?php
require_once dirname(__DIR__).'/Controller/EDatabaseController.php';

$req = <<<EOT
SELECT p.creationDate, p.modificationDate, p.commentaire, m.nomMedia 
FROM post AS p
JOIN contenir AS c ON p.idPost = c.idPost
JOIN media AS m ON m.idMedia = c.idMedia
GROUP BY p.idPost
UNION
SELECT p.creationDate, p.modificationDate, p.commentaire, null
FROM post AS p
GROUP BY p.idPost;
EOT;

try {
  $query = EDatabaseController::getInstance()->prepare($req);

  $query->execute();

  $queryData = $query->fetchAll();

  echo json_encode($queryData);
} catch (PDOException $e) {
  return $e->getMessage();
}