<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

redirectIfNotLoggedIn();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    header("Location: index.php");
    exit();
}

$categoryId = $_POST['id'];

try {
    $stmt = $pdo->prepare("DELETE FROM deposittypes WHERE id = ?");
    $stmt->execute([$categoryId]);

    header("Location: index.php?success=Deposit type deleted successfully");
    exit();
} catch (PDOException $e) {
    header("Location: index.php?error=Error deleting deposit type: " . urlencode($e->getMessage()));
    exit();
}
