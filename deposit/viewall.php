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
ORDER BY d1.id DESC";
$deposits = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deposits / Withdrawals</title>
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
                    <h1 class="h2"><i class="bi bi-tags"></i> Deposits / Withdrawals</h1>
                </div>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($_GET['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($_GET['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <?php if (empty($deposits)): ?>
                            <div class="alert alert-info">
                                No Deposit Types found. <a href="create.php" class="alert-link">Create your first Deposit</a>.
                            </div>
                        <?php else: ?>
                            <div class="mb-3">
                                <input type="text" id="searchBox" class="form-control" placeholder="Search by Deposit Type, Farmer Name, balance or Farmer Code...">
                            </div>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const searchBox = document.getElementById('searchBox');
                                    const table = document.querySelector('.table tbody');
                                    searchBox.addEventListener('input', function() {
                                        const filter = this.value.toLowerCase();
                                        Array.from(table.rows).forEach(row => {
                                            const depositType = row.cells[0].textContent.toLowerCase();
                                            const farmerName = row.cells[1].textContent.toLowerCase();
                                            const farmerCode = row.cells[2].textContent.toLowerCase();
                                            const balance = row.cells[3].textContent.toLowerCase();
                                            if (
                                                depositType.includes(filter) ||
                                                farmerName.includes(filter) ||
                                                farmerCode.includes(filter) ||
                                                balance.includes(filter)
                                            ) {
                                                row.style.display = '';
                                            } else {
                                                row.style.display = 'none';
                                            }
                                        });
                                    });
                                });
                            </script>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Deposit type</th>
                                            <th>Farmer Name</th>
                                            <th>Farmer Code</th>
                                            <th>Balance</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($deposits as $deposit): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($deposit['data_type_name']); ?></td>
                                                <td><?php echo htmlspecialchars($deposit['farmer_name'] ?? 'N/A'); ?></td>
                                                <td><?php echo $deposit['farmer_code']; ?></td>
                                                <td>Rs. <?php echo $deposit['balance']; ?></td>
                                                <td>
                                                    <a href="view.php?fid=<?= $deposit['fid']; ?>&dtid=<?= $deposit['dtid']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i> View
                                                    </a>

                                                    <!-- <?php
                                                            if ($technical_officer_status == 'admin') {
                                                            ?>
                                                        <a href="edit.php?id=<?php echo $deposittype['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                                            <i class="bi bi-pencil"></i> Edit
                                                        </a>
                                                        <button class="btn btn-sm btn-outline-danger delete-btn" data-id="<?php echo $deposittype['id']; ?>">
                                                            <i class="bi bi-trash"></i> Delete
                                                        </button>

                                                    <?php
                                                            }
                                                    ?> -->
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