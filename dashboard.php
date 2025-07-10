<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

redirectIfNotLoggedIn();

// Get counts for dashboard cards
$farmersCount = $pdo->query("SELECT COUNT(*) FROM farmers")->fetchColumn();
$loantypesCount = $pdo->query("SELECT COUNT(*) FROM loantype")->fetchColumn();
$deposittypesCount = $pdo->query("SELECT COUNT(*) FROM deposittypes")->fetchColumn();

// Get recent loans
$recentLoans = $pdo->query("SELECT f.name as farmer_name, l.issue_date, l.reason, 
                               lt.name as loan_type, l.*
                               FROM loans l
                               JOIN farmers f ON l.fid = f.id
                               JOIN loantype lt ON l.ltid = lt.id
                               WHERE l.is_paid = 0
                               ORDER BY l.issue_date DESC
                               LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - FOT Media Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="assets/css/style.css" rel="stylesheet">
</head>

<body>
    <?php include 'includes/header2.php'; ?>

    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Sidebar - Collapse on mobile -->
            <div class="col-lg-3 col-md-4 d-md-block sidebar collapse" id="sidebarMenu">
                <?php include 'includes/sidebar2.php'; ?>
            </div>

            <!-- Main content area -->
            <main class="col-lg-9 col-md-8 ms-sm-auto px-md-4">
                <!-- Mobile sidebar toggle button -->
                <div class="d-flex d-md-none justify-content-between align-items-center mb-3">
                    <h2><i class="bi bi-speedometer2"></i> Dashboard</h2>
                    <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
                        <i class="bi bi-list"></i> Menu
                    </button>
                </div>

                <!-- Desktop title -->
                <div class="d-none d-md-block">
                    <h2><i class="bi bi-speedometer2"></i> Dashboard</h2>
                    <hr>
                </div>

                <!-- Summary Cards - Responsive grid -->
                <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4 mb-4">
                    <!-- Departments Card -->
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="text-muted mb-2">farmers</h6>
                                        <h3 class="mb-0"><?php echo $farmersCount; ?></h3>
                                    </div>
                                    <div class="bg-primary bg-opacity-10 p-3 rounded">
                                        <i class="bi bi-people fs-4 text-primary"></i>
                                    </div>
                                </div>
                                <a href="users/" class="stretched-link"></a>
                            </div>
                        </div>
                    </div>

                    <!-- Users Card -->
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="text-muted mb-2">loan Types</h6>
                                        <h3 class="mb-0"><?php echo $loantypesCount; ?></h3>
                                    </div>
                                    <div class="bg-success bg-opacity-10 p-3 rounded">
                                        <i class="bi bi-people fs-4 text-success"></i>
                                    </div>
                                </div>
                                <a href="loan_types/" class="stretched-link"></a>
                            </div>
                        </div>
                    </div>

                    <!-- Items Card -->
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="text-muted mb-2">Deposit Types</h6>
                                        <h3 class="mb-0"><?php echo $deposittypesCount; ?></h3>
                                    </div>
                                    <div class="bg-info bg-opacity-10 p-3 rounded">
                                        <i class="bi bi-box-seam fs-4 text-info"></i>
                                    </div>
                                </div>
                                <a href="items/" class="stretched-link"></a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Issuances - Responsive table -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Loans</h5>
                        <a href="loans/" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Farmer</th>
                                        <th>Issued date</th>
                                        <th class="d-none d-md-table-cell">Loan type</th>
                                        <th class="d-none d-sm-table-cell">Price</th>
                                        <th>presentage</th>
                                        <th>Need to pay</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentLoans as $loan): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($loan['farmer_name']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($loan['issue_date'])); ?></td>
                                            <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($loan['loan_type']); ?></td>
                                            <td class="d-none d-sm-table-cell"><?php echo htmlspecialchars($loan['price']); ?></td>
                                            <td><?php echo htmlspecialchars($loan['presentage']); ?></td>
                                            <td><?php echo htmlspecialchars($loan['need_to_pay']) . ' - ' . date('M d, Y', strtotime($loan['paid_date'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>

</html>