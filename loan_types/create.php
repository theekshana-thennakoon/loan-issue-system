<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

redirectIfNotLoggedIn();

$errors = [];
$departmentName = $headName = $member1Name = $member2Name = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loanType = trim($_POST['loan_type']);
    $rate = trim($_POST['rate']);
    $months = trim($_POST['months']);

    // Validation
    if (empty($loanType)) {
        $errors['department_name'] = 'Department name is required';
    }

    if (empty($rate)) {
        $errors['rate'] = 'Interest rate is required';
    }

    if (empty($months)) {
        $errors['months'] = 'Loan duration (months) is required';
    }


    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Insert loan type
            $stmt = $pdo->prepare("INSERT INTO loantype (name, interest, no_of_month) VALUES (?, ?, ?)");
            $stmt->execute([$loanType, $rate, $months]);
            $loanTypeId = $pdo->lastInsertId();
            $pdo->commit();

            header("Location: index.php?success=Loan type created successfully");
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = "Error creating loan type: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Loan Type</title>
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
                    <h1 class="h2"><i class="bi bi-building"></i> Add New Loan Type</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="index.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Loan Types
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
                            <div class="mb-3">
                                <label for="loan_type" class="form-label">Loan Type *</label>
                                <input type="text" class="form-control <?php echo isset($errors['loan_type']) ? 'is-invalid' : ''; ?>"
                                    id="loan_type" name="loan_type" required>
                                <?php if (isset($errors['loan_type'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['loan_type']; ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="head_name" class="form-label">Interest Rate *</label>
                                        <input type="text" class="form-control <?php echo isset($errors['head_name']) ? 'is-invalid' : ''; ?>"
                                            id="head_name" name="rate" value="<?php echo htmlspecialchars($headName); ?>" required>
                                        <?php if (isset($errors['head_name'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['head_name']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="member1_name" class="form-label">No of Months *</label>
                                        <input type="text" class="form-control <?php echo isset($errors['member1_name']) ? 'is-invalid' : ''; ?>"
                                            id="member1_name" name="months" value="<?php echo htmlspecialchars($member1Name); ?>" required>
                                        <?php if (isset($errors['member1_name'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['member1_name']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Create Loan Type
                                </button>
                                <a href="index.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </a>
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