<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

redirectIfNotLoggedIn();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    header("Location: index.php");
    exit();
}

$userId = $_POST['id'];

// Prevent deleting the currently logged in user
if ($userId == $_SESSION['technical_officer_id']) {
    header("Location: index.php?error=Cannot delete your own account");
    exit();
}

try {
    // Then delete the user
    $stmt = $pdo->prepare("DELETE FROM farmers WHERE id = ?");
    $stmt->execute([$userId]);

    header("Location: index.php?success=Farmer deleted successfully");
    exit();
} catch (PDOException $e) {
    header("Location: index.php?error=Error deleting farmer: " . urlencode($e->getMessage()));
    exit();
}
