<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

redirectIfNotLoggedIn();

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$loanTypeId = $_GET['id'];

// Get loan type info
$stmt = $pdo->prepare("SELECT * FROM loantype WHERE id = ?");
$stmt->execute([$loanTypeId]);
$loanType = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$loanType) {
    header("Location: index.php?error=Loan Type not found");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($loanType['name']); ?></title>
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
                        <i class="bi bi-building"></i> <?php echo htmlspecialchars($loanType['name']); ?>
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <?php
                            if ($technical_officer_status == 'admin') {
                            ?>
                                <a href="edit.php?id=<?php echo $loanTypeId; ?>" class="btn btn-sm btn-outline-secondary">
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
                                <h5 class="mb-0">Loan Type Information</h5>
                            </div>
                            <div class="card-body">
                                <dl class="row">
                                    <dt class="col-sm-4">Loan Type Name</dt>
                                    <dd class="col-sm-8"><?php echo htmlspecialchars($loanType['name']); ?></dd>
                                    <dt class="col-sm-4">Interest rate</dt>
                                    <dd class="col-sm-8"><?php echo htmlspecialchars($loanType['interest']); ?></dd>
                                </dl>
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