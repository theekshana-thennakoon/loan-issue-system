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
$query = "SELECT * FROM loans l
          JOIN farmers f ON l.farmer_id = f.id
          JOIN loantypes lt ON l.ltid = lt.id
          WHERE 1=1";

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
                        <?php if (empty($issuances)): ?>
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
                                        <?php foreach ($issuances as $issuance): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($issuance['loan_type']); ?></td>
                                                <td><?php echo htmlspecialchars($issuance['farmer_name']); ?></td>
                                                <td><?php echo htmlspecialchars($issuance['farmer_code']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($issuance['issue_date'])); ?></td>
                                                <td><?php echo htmlspecialchars($issuance['amount']); ?></td>
                                                <td><?php echo htmlspecialchars($issuance['repayment']); ?></td>
                                                <td>
                                                    <?php if ($issuance['pending_items'] > 0): ?>
                                                        <span class="badge bg-warning text-dark">Pending (<?php echo $issuance['pending_items']; ?>)</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">Completed</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="view.php?id=<?php echo $issuance['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>">
                                                <i class="bi bi-chevron-double-left"></i>
                                            </a>
                                        </li>
                                        <li class="page-item">
                                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                                <i class="bi bi-chevron-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                                <i class="bi bi-chevron-right"></i>
                                            </a>
                                        </li>
                                        <li class="page-item">
                                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $totalPages])); ?>">
                                                <i class="bi bi-chevron-double-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>