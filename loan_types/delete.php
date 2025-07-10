<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

redirectIfNotLoggedIn();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    header("Location: index.php");
    exit();
}

$loanTypeId = $_POST['id'];

try {
    $pdo->beginTransaction();

    // First delete members
    $stmt = $pdo->prepare("DELETE FROM loantype WHERE id = ?");
    $stmt->execute([$loanTypeId]);

    $pdo->commit();

    header("Location: index.php?success=Loan type deleted successfully");
    exit();
} catch (PDOException $e) {
    $pdo->rollBack();
    header("Location: index.php?error=Error deleting loan type: " . urlencode($e->getMessage()));
    exit();
}
