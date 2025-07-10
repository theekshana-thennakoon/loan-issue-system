<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

redirectIfNotLoggedIn();

// Pagination setup
$perPage = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $perPage;


// Build the base query and count query
// Using 'tech' instead of 'to' as the alias
$loans = $pdo->query("SELECT l.*, f.name AS farmer_name, f.id AS farmer_id, f.farmer_code AS farmer_code, lt.name AS loan_type_name, lt.interest AS loan_type_interest FROM loans l
          JOIN farmers f ON l.fid = f.id
          JOIN loantype lt ON l.ltid = lt.id
          WHERE is_paid = 0")->fetchAll(PDO::FETCH_ASSOC);

// Apply filters
$params = [];
$countParams = [];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loans</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
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
                    <h1 class="h2"><i class="bi bi-clipboard-check"></i> Issued Loans</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <?php
                        if ($technical_officer_status == 'admin' || $technical_officer_status == 'to') {
                        ?>
                            <a href="create.php" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> New Loan issuance
                            </a>
                        <?php
                        }
                        ?>
                    </div>
                </div>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($_GET['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>



                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <?php if (empty($loans)): ?>
                            <div class="alert alert-info">
                                No issuances found matching your criteria.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Loan type</th>
                                            <th>Farmer name</th>
                                            <th>Farmer code</th>
                                            <th>Issued date</th>
                                            <th>Amount</th>
                                            <th>Repayment</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($loans as $loan): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($loan['loan_type_name']); ?></td>
                                                <td><?php echo htmlspecialchars($loan['farmer_name']); ?></td>
                                                <td><?php echo htmlspecialchars($loan['farmer_code']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($loan['issue_date'])); ?></td>
                                                <td>Rs. <?php echo htmlspecialchars($loan['price']); ?></td>
                                                <td>Rs. <?php echo htmlspecialchars($loan['need_to_pay']); ?></td>
                                                <td>
                                                    <?php if ($loan['is_paid'] == 0): ?>
                                                        <span class="badge bg-warning text-dark">Pending</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">Completed</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="view.php?id=<?php echo $loan['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>


                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>