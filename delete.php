
<?php
require 'db_connect.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id && $id > 0) {

    $stmt = $pdo->prepare("
        DELETE FROM maintenance_requests WHERE id = ?
    ");

    $stmt->execute([$id]);
}

header("Location: index.php");
exit;
?>
