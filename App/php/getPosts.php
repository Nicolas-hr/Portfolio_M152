<?php
header('Content-type: application/json');

require_once dirname(__DIR__) . '/Controller/EDatabaseController.php';

$req = <<<EOT
SELECT p.creationDate AS creaDate, p.modificationDate AS modifDate, p.commentaire AS comment,
group_concat(m.nomMedia ORDER BY m.idMedia) AS medias,
group_concat(m.typeMedia ORDER BY m.idMedia) AS types
FROM post AS p
JOIN contenir AS c ON p.idPost = c.idPost
JOIN media AS m ON m.idMedia = c.idMedia
GROUP BY p.idPost
UNION
SELECT p.creationDate, p.modificationDate, p.commentaire,
null AS medias,
null AS types
FROM post AS p
WHERE p.idPost NOT IN (
  SELECT contenir.idPost
  FROM contenir)
GROUP BY p.idPost
ORDER BY creaDate DESC;
EOT;

try {
  $query = EDatabaseController::getInstance()->prepare($req);

  $query->execute();

  $queryData = $query->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode($queryData);
} catch (PDOException $e) {
  return $e->getMessage();
}
