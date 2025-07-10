<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

redirectIfNotLoggedIn();

if (!isset($_GET['fid']) || !isset($_GET['dtid'])) {
    header("Location: index.php");
    exit();
}

$farmerId = $_GET['fid'];
$depositTypeId = $_GET['dtid'];

$stmt = $pdo->prepare("SELECT * FROM deposittypes WHERE id = ?");
$stmt->execute([$depositTypeId]);
$depositType = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM farmers WHERE id = ?");
$stmt->execute([$farmerId]);
$farmers = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT balance FROM deposits
WHERE id = (SELECT MAX(id) FROM deposits WHERE fid = ? AND dtid = ?)");
$stmt->execute([$farmerId, $depositTypeId]);
$balance = $stmt->fetch(PDO::FETCH_ASSOC);

// Get deposit type info
$stmt = $pdo->prepare("SELECT deposits.*, deposittypes.name as deposit_type_name, farmers.name as farmer_name
FROM deposits JOIN deposittypes ON deposits.dtid = deposittypes.id
JOIN farmers ON deposits.fid = farmers.id
WHERE fid = ? AND dtid = ?");
$stmt->execute([$farmerId, $depositTypeId]);
$deposits = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$deposits || count($deposits) === 0) {
    header("Location: index.php?error=deposit not found");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> <?php echo htmlspecialchars($farmers['name']); ?> -
        <?php echo htmlspecialchars($depositType['name']); ?></title>
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
                        <i class="bi bi-tag"></i>
                        <?php echo htmlspecialchars($farmers['name']); ?> -
                        <?php echo htmlspecialchars($depositType['name']); ?>
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
                    <div class="col-md-6">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Account Information</h5>
                            </div>
                            <div class="card-body">
                                <dl class="row">
                                    <dt class="col-sm-4 mb-4">Deposit Type</dt>
                                    <dd class="col-sm-8 mb-4"><?php echo htmlspecialchars($depositType['name']); ?></dd>

                                    <dt class="col-sm-4 mb-4">farmer_name</dt>
                                    <dd class="col-sm-8 mb-4"><?php echo htmlspecialchars($farmers['name'] ?? 'N/A'); ?></dd>
                                    <?php if ($depositTypeId == 6): ?>
                                        <?php foreach ($deposits as $deposit): ?>
                                            <dt class="col-sm-4 mb-4">Child Name</dt>
                                            <dd class="col-sm-8 mb-4">
                                                <?php echo htmlspecialchars($deposit['childname'] ?? 'N/A'); ?>
                                            </dd>
                                            <dt class="col-sm-4 mb-4">Date of Birth</dt>
                                            <dd class="col-sm-8 mb-4">
                                                <?php echo htmlspecialchars($deposit['dob'] ?? 'N/A'); ?>
                                            </dd>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    <dt class="col-sm-4 mb-4">Balance</dt>
                                    <dd class="col-sm-8 mb-4 fw-bold">Rs. <?php echo $balance['balance']; ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Transaction details</h5>
                            </div>

                            <div class="card-body">

                                <?php if (empty($deposits)): ?>
                                    <p class="text-muted">No recent issuances</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Deposit/Withdrawal</th>
                                                    <th>Amount</th>
                                                    <th>Balance</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($deposits as $deposit): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($deposit['date']); ?></td>
                                                        <td>
                                                            <?php
                                                            if ($deposit['dorw'] == 'd') {
                                                                echo '<button class="btn btn-sm btn-success">Deposit</button>';
                                                            } elseif ($deposit['dorw'] == 'w') {
                                                                echo '<button class="btn btn-sm btn-danger">Withdrawal</button>';
                                                            } else {
                                                                echo '<button class="btn btn-sm btn-secondary">Unknown</button>';
                                                            }
                                                            ?>
                                                        </td>
                                                        <td>Rs.<?php echo htmlspecialchars($deposit['amount']); ?></td>

                                                        <td class="col-sm-8 mb-4 fw-bold">Rs. <?php echo $deposit['balance']; ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <tr>
                                                    <td colspan="2" class="text-end fw-bold">Balance</td>
                                                    <td class="fw-bold">Rs.<?php echo $balance['balance']; ?></td>
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