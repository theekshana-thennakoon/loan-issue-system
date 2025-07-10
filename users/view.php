<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

redirectIfNotLoggedIn();

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$farmerId = $_GET['id'];

// Get user info
$stmt = $pdo->prepare("SELECT * FROM farmers WHERE id = ?");
$stmt->execute([$farmerId]);
$farmer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$farmer) {
    header("Location: index.php?error=Farmer not found");
    exit();
}

$recentLoans = $pdo->query("SELECT f.name as farmer_name, l.issue_date, l.reason, 
    lt.name as loan_type, l.*
    FROM loans l
    JOIN farmers f ON l.fid = f.id
    JOIN loantype lt ON l.ltid = lt.id
    WHERE l.is_paid = 0 and fid = $farmerId
    ORDER BY l.issue_date DESC
    LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($farmer['name']); ?> - Farmer Details</title>
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
                        <i class="bi bi-person"></i> <?php echo htmlspecialchars($farmer['name']); ?>
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <?php
                            if ($technical_officer_status == 'admin') {
                            ?>
                                <a href="edit.php?id=<?php echo $farmerId; ?>" class="btn btn-sm btn-outline-secondary">
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
                                <h5 class="mb-0">Farmer Information</h5>
                            </div>
                            <div class="card-body">
                                <dl class="row">
                                    <dt class="col-sm-4">Full Name</dt>
                                    <dd class="col-sm-8"><?php echo htmlspecialchars($farmer['name']); ?></dd>

                                    <dt class="col-sm-4">Farmer code</dt>
                                    <dd class="col-sm-8"><?php echo htmlspecialchars($farmer['farmer_code']); ?></dd>

                                    <dt class="col-sm-4">Contact</dt>
                                    <dd class="col-sm-8"><?php echo htmlspecialchars($farmer['phone']); ?></dd>

                                    <dt class="col-sm-4">Email</dt>
                                    <dd class="col-sm-8"><?php echo htmlspecialchars($farmer['email']); ?></dd>

                                    <dt class="col-sm-4">NIC</dt>
                                    <dd class="col-sm-8"><?php echo htmlspecialchars($farmer['nic']); ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Recent Loans</h5>
                                <span class="badge bg-primary rounded-pill"><?php echo count($recentLoans); ?></span>
                            </div>
                            <div class="card-body">
                                <?php if (empty($recentLoans)): ?>
                                    <p class="text-muted">No recent issuances</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Amount</th>
                                                    <th>Issued Date</th>
                                                    <th>Loan Type</th>
                                                    <th>Reason</th>
                                                    <th>Percentage</th>
                                                    <th>Need to pay</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recentLoans as $loan): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($loan['price']); ?></td>
                                                        <td><?php echo date('M d', strtotime($loan['issue_date'])); ?></td>
                                                        <td><?php echo htmlspecialchars($loan['loan_type']); ?></td>
                                                        <td><?php echo htmlspecialchars(substr($loan['reason'], 0, 20)) . (strlen($loan['reason']) > 20 ? '...' : ''); ?></td>
                                                        <td><?php echo htmlspecialchars($loan['percentage']); ?></td>
                                                        <td><?php echo htmlspecialchars($loan['need_to_pay']) - htmlspecialchars($loan['paid_date']); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <a href="../issuances/?officer_id=<?php echo $userId; ?>" class="btn btn-sm btn-outline-primary mt-2">
                                        View All Issuances
                                    </a>
                                <?php endif; ?>
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