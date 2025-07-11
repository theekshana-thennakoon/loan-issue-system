<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

redirectIfNotLoggedIn();

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$loanId = $_GET['id'];

// Verify loan exists
$stmt = $pdo->prepare("SELECT id FROM loans WHERE id = ?");
$stmt->execute([$loanId]);
if (!$stmt->fetch()) {
    header("Location: index.php?error=Loan not found");
    exit();
}

// Get issued items that haven't been fully returned
$stmt = $pdo->prepare("
    SELECT l.*, f.name as farmer_name, lt.name as loan_type_name, lt.interest as loan_type_interest
    FROM loans l
    JOIN farmers f ON l.fid = f.id
    JOIN loantype lt ON l.ltid = lt.id
    WHERE l.id = ?
");
$stmt->execute([$loanId]);
$loan = $stmt->fetch(PDO::FETCH_ASSOC);
// print_r($items);
$errors = [];
$returnDate = date('Y-m-d');
$returnConditions = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $returnDate = $_POST['return_date'];
    $return_amount = $_POST['return_amount'];

    // Validation
    if (empty($return_amount)) {
        $errors['items'] = 'Please fill in the return amounts';
    }

    if (empty($errors)) {
        try {
            if ($return_amount > 0) {

                // Update issuance item
                $stmt = $pdo->prepare("
                        INSERT INTO repayments 
                        (lid, date, amount)
                        VALUES (?, ?, ?)
                    ");
                $stmt->execute([
                    $loanId,
                    $returnDate,
                    $return_amount
                ]);
            }


            $stmt = $pdo->prepare("SELECT SUM(amount) AS total_repaid FROM repayments WHERE lid = ?");
            $stmt->execute([$loanId]);
            $sum_repayments = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($sum_repayments['total_repaid'] >= $loan['need_to_pay']) {
                // Mark loan as paid
                $stmt = $pdo->prepare("UPDATE loans SET is_paid = 1 WHERE id = ?");
                $stmt->execute([$loanId]);
            }

            header("Location: view.php?id=$loanId&success=Repayment recorded successfully");
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = "Error recording returns: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Repayments</title>
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
                        <i class="bi bi-box-arrow-in-down"></i> Add Repayments for Loan
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="view.php?id=<?php echo $loanId; ?>" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Loan
                        </a>
                    </div>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <form method="POST">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="return_date" class="form-label">Return Date *</label>
                                        <input type="date" class="form-control"
                                            id="return_date" name="return_date"
                                            value="<?php echo htmlspecialchars($returnDate); ?>" required readonly>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="mb-3">
                                        <?php if ($loan['is_paid']): ?>
                                            <div class="alert alert-info">
                                                All items from this loan have already been returned.
                                            </div>
                                        <?php else: ?>
                                            <?php if (isset($errors['items'])): ?>
                                                <div class="alert alert-danger"><?php echo $errors['items']; ?></div>
                                            <?php endif; ?>

                                            <div class="table-responsive">
                                                <label for="">Amount</label>
                                                <input type="text" class="form-control" name="return_amount" required>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-12 mt-4">
                                    <?php if (!$loan['is_paid']): ?>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-circle"></i> Add Repayments
                                        </button>
                                    <?php endif; ?>
                                    <a href="view.php?id=<?php echo $issuanceId; ?>" class="btn btn-outline-secondary">
                                        <i class="bi bi-x-circle"></i> Cancel
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>