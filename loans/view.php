<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

redirectIfNotLoggedIn();

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$loanId = $_GET['id'];

// Get loan details
$stmt = $pdo->prepare("
    SELECT l.*, f.name as farmer_name, lt.name as loan_type_name, lt.interest as loan_type_interest
    FROM loans l
    JOIN farmers f ON l.fid = f.id
    JOIN loantype lt ON l.ltid = lt.id
    WHERE l.id = ?
");
$stmt->execute([$loanId]);
$loan = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * from repayments r WHERE r.lid = ?");
$stmt->execute([$loanId]);
$repayments = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$loan) {
    header("Location: index.php?error=Loan not found");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan #<?php echo $loan['id']; ?></title>
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
                        <i class="bi bi-clipboard-check"></i> Loan <?php echo $loan['farmer_name']; ?> -
                        <?php echo $loan['loan_type_name']; ?>
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="index.php" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Loans
                            </a>
                            <?php
                            if ($technical_officer_status == 'admin' || $technical_officer_status == 'to') {
                            ?>
                                <?php if ($loan['is_paid']): ?>
                                    <span class="btn btn-sm btn-success">
                                        <i class="bi bi-check-circle"></i> All Returned
                                    </span>
                                <?php else: ?>
                                    <a href="return.php?id=<?php echo $loanId; ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-bag-plus"></i> Add Repayments
                                    </a>
                                <?php endif; ?>
                            <?php
                            }
                            ?>
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
                                <h5 class="mb-0">Issuance Details</h5>
                            </div>
                            <div class="card-body">
                                <dl class="row">
                                    <dt class="col-sm-4">Issued To</dt>
                                    <dd class="col-sm-8"><?php echo htmlspecialchars($loan['farmer_name']); ?></dd>

                                    <dt class="col-sm-4">Issued Date</dt>
                                    <dd class="col-sm-8"><?php echo htmlspecialchars($loan['issue_date']); ?></dd>

                                    <dt class="col-sm-4">Loan Type</dt>
                                    <dd class="col-sm-8"><?php echo htmlspecialchars($loan['loan_type_name']); ?></dd>

                                    <dt class="col-sm-4">Reason</dt>
                                    <dd class="col-sm-8"><?php echo htmlspecialchars($loan['reason']); ?></dd>

                                    <dt class="col-sm-4">Amount</dt>
                                    <dd class="col-sm-8">Rs. <?php echo htmlspecialchars($loan['price']); ?></dd>

                                    <dt class="col-sm-4">Interest Rate</dt>
                                    <dd class="col-sm-8"><?php echo htmlspecialchars($loan['loan_type_interest']); ?>%</dd>

                                    <dt class="col-sm-4">Need to pay</dt>
                                    <dd class="col-sm-8">Rs. <?php echo htmlspecialchars($loan['need_to_pay']); ?></dd>

                                    <dt class="col-sm-4">Status</dt>
                                    <dd class="col-sm-8">
                                        <?php
                                        if ($loan['is_paid']) {
                                            echo '<span class="badge bg-success">Completed</span>';
                                        } else {
                                            echo '<span class="badge bg-warning text-dark">Pending</span>';
                                        }
                                        ?>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Repayments</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($repayments as $item): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($item['date']); ?></strong>
                                                    </td>
                                                    <td>Rs. <?php echo htmlspecialchars($item['amount']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>

                                            <tr>
                                                <?php
                                                $stmt = $pdo->prepare("SELECT SUM(amount) AS total_repaid FROM repayments WHERE lid = ?");
                                                $stmt->execute([$loanId]);
                                                $sum_repayments = $stmt->fetch(PDO::FETCH_ASSOC);
                                                $balance = $loan['need_to_pay'] - $sum_repayments['total_repaid'];
                                                ?>
                                                <th>Balance</th>
                                                <th>Rs. <?php echo htmlspecialchars($balance); ?></th>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>