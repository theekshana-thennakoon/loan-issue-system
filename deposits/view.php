<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

redirectIfNotLoggedIn();

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$deposittypeId = $_GET['id'];

// Get deposit type info
$stmt = $pdo->prepare("SELECT * FROM deposittypes WHERE id = ?");
$stmt->execute([$deposittypeId]);
$deposittype = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$deposittype) {
    header("Location: index.php?error=deposit type not found");
    exit();
}

// Get items in this category
$stmt = $pdo->prepare("SELECT COUNT(DISTINCT fid) as no_of_accounts FROM deposits WHERE dtid = ?");
$stmt->execute([$deposittypeId]);
$no_of_accounts = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($deposittype['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="../../assets/css/style.css" rel="stylesheet">
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-lg-3 col-md-4 d-md-block sidebar collapse" id="sidebarMenu">
                <?php include '../includes/sidebar.php'; ?>
            </div>

            <main class="col-lg-9 col-md-8 ms-sm-auto px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="bi bi-tag"></i> <?php echo htmlspecialchars($deposittype['name']); ?>
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <?php
                            if ($technical_officer_status == 'admin') {
                            ?>
                                <a href="edit.php?id=<?php echo $deposittypeId; ?>" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                            <?php
                            }
                            ?>
                            <a href="index.php" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                </div>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($_GET['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Deposit Type Information</h5>
                            </div>
                            <div class="card-body">
                                <dl class="row">
                                    <dt class="col-sm-4 mb-4">Deposit Type</dt>
                                    <dd class="col-sm-8 mb-4"><?php echo htmlspecialchars($deposittype['name']); ?></dd>

                                    <dt class="col-sm-4 mb-4">Description</dt>
                                    <dd class="col-sm-8 mb-4"><?php echo htmlspecialchars($deposittype['description'] ?? 'N/A'); ?></dd>

                                    <dt class="col-sm-4 mb-4">Interest Rate</dt>
                                    <dd class="col-sm-8 mb-4"><?php echo $deposittype['interest']; ?>%</dd>

                                    <dt class="col-sm-4 mb-4">Assets or Responsibility</dt>
                                    <dd class="col-sm-8 mb-4">
                                        <?php
                                        if (empty($deposittype['asset_or_respon'])) {
                                            echo 'N/A';
                                        } elseif ($deposittype['asset_or_respon'] == '1') {
                                            echo 'Asset';
                                        } elseif ($deposittype['asset_or_respon'] == '0') {
                                            echo 'Responsibility';
                                        } else {
                                            echo 'Unknown';
                                        }
                                        ?>
                                    </dd>

                                    <dt class="col-sm-4 mb-4">Can withdraw</dt>
                                    <dd class="col-sm-8 mb-4">
                                        <?php
                                        if (empty($deposittype['can_withdraw'])) {
                                            echo 'N/A';
                                        } elseif ($deposittype['can_withdraw'] == '1') {
                                            echo 'Yes';
                                        } elseif ($deposittype['can_withdraw'] == '0') {
                                            echo 'No';
                                        } else {
                                            echo 'Unknown';
                                        }
                                        ?>
                                    </dd>

                                    <dt class="col-sm-4 mb-4">Minimum amount percentage to get loan</dt>
                                    <dd class="col-sm-8 mb-4"><?php echo $deposittype['no_of_presentage_to_get_loan']; ?>%</dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">No of Accounts</h5>
                                <span class="badge bg-primary rounded-pill"><?php echo $no_of_accounts; ?></span>
                            </div>

                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>