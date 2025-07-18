<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

redirectIfNotLoggedIn();

// Get all deposits with item counts
$query = "SELECT d.* , dt.name as data_type_name, f.name as farmer_name, f.farmer_code as farmer_code
FROM deposits d JOIN deposittypes dt ON d.dtid = dt.id
JOIN farmers f ON d.fid = f.id
GROUP BY d.fid, d.dtid ORDER BY d.id DESC";

$query = "SELECT d1.*, dt.name as data_type_name, f.name as farmer_name, f.farmer_code as farmer_code
FROM deposits d1
JOIN (
    SELECT fid, dtid, MAX(id) as max_id
    FROM deposits
    GROUP BY fid, dtid
) d2 ON d1.id = d2.max_id
JOIN deposittypes dt ON d1.dtid = dt.id
JOIN farmers f ON d1.fid = f.id
ORDER BY d1.id DESC LIMIT 10";
$deposits = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);



$today = date("m-d");
$year = date("Y");
if ($today == '03-31') {
    // Reset balances on New Year's Day
    // Get least balance of every user grouped by deposit type in the first 3 months of the current year
    $startDate = "$year-01-01";
    $endDate = "$year-03-31";
    $leastBalancesQuery = "
        SELECT fid, dtid, MIN(balance) as least_balance
        FROM deposits
        WHERE date >= :startDate AND date <= :endDate
        GROUP BY fid, dtid
    ";
    $stmt = $pdo->prepare($leastBalancesQuery);
    $stmt->execute([':startDate' => $startDate, ':endDate' => $endDate]);
    $leastBalances = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($leastBalances as $row) {
        // Get interest rate for this deposit type
        $dtid = $row['dtid'];
        $fid = $row['fid'];
        $least_balance = $row['least_balance'];

        $interestStmt = $pdo->prepare("SELECT interest FROM deposittypes WHERE id = :dtid");
        $interestStmt->execute([':dtid' => $dtid]);
        $interestRow = $interestStmt->fetch(PDO::FETCH_ASSOC);

        $interest = $interestRow ? $interestRow['interest'] : 0;

        $interest = ($interest / 100) / 4;
        // Convert percentage to decimal


        $last_balanceStmt = $pdo->prepare("SELECT balance FROM deposits WHERE id = (
            SELECT MAX(id) FROM deposits WHERE fid = :fid AND dtid = :dtid
        )");
        $last_balanceStmt->execute([':fid' => $fid, ':dtid' => $dtid]);
        $last_balanceRow = $last_balanceStmt->fetch(PDO::FETCH_ASSOC);

        $last_balance = $last_balanceRow ? $last_balanceRow['balance'] : 0;


        $insert_interest = "INSERT INTO deposits (fid, dtid, amount, drow, date, balance)
         VALUES (:fid, :dtid, :amount, :drow, :date, :balance)";
        $insertStmt = $pdo->prepare($insert_interest);
        $insertStmt->execute([
            ':fid' => $fid,
            ':dtid' => $dtid,
            ':amount' => $interest,
            ':drow' => 'interest',
            ':date' => date('Y-m-d'),
            ':balance' => $last_balance + $interest
        ]);
        // You can now use $interest for further calculations
        // Example: $interestAmount = $least_balance * $interest;
    }
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
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
                    <h1 class="h2"><i class="bi bi-tags"></i> All Reports</h1>
                </div>



                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Report type</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Budget Report</td>
                                        <td>
                                            <a href="view.php?id=1" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>Detors Report</td>
                                        <td>
                                            <a href="view.php?id=2" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this category? Items in this category will not be deleted but will become uncategorized.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST" action="delete.php">
                        <input type="hidden" name="id" id="deleteId">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Delete confirmation
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const categoryId = this.getAttribute('data-id');
                document.getElementById('deleteId').value = categoryId;
                const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
                deleteModal.show();
            });
        });
    </script>
</body>

</html>