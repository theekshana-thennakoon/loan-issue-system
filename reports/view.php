<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

redirectIfNotLoggedIn();

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}
$depositId = $_GET['id'];
if ($depositId == 1) {
    $leastBalancesQuery = "
        SELECT f.name as farmer_name, f.id as fid, dt.name as deposit_type , dt.id as dtid, MAX(d.balance) as least_balance
        FROM deposits d JOIN deposittypes dt ON d.dtid = dt.id
        JOIN farmers f ON d.fid = f.id
        GROUP BY f.id, dt.id
    ";
    $stmt = $pdo->prepare($leastBalancesQuery);
    $stmt->execute();
    $leastBalances = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Budget report</title>
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
                        <i class="bi bi-tag"></i>Budget Report
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
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
                    <div class="col">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Transaction details</h5>
                            </div>

                            <div class="card-body">

                                <?php if (empty($leastBalances)): ?>
                                    <p class="text-muted">No recent issuances</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Farmer </th>
                                                    <th>Deposit Type</th>
                                                    <th>Balance</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                foreach ($leastBalances as $row) {
                                                    echo "<tr>";
                                                    echo "<td>" . htmlspecialchars($row['farmer_name']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['deposit_type']) . "</td>";
                                                    echo "<td>Rs." . htmlspecialchars($row['least_balance']) . "</td>";
                                                    echo "<td>
                                                        <a href=\"../deposit/view.php?fid=" . $row['fid'] . "&dtid=" . $row['dtid'] . "\" class=\"btn btn-sm btn-outline-primary\">
                                                            <i class=\"bi bi-eye\"></i> View
                                                        </a>
                                                    </td>";
                                                    echo "</tr>";
                                                }
                                                ?>
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