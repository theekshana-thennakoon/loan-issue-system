<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

redirectIfNotLoggedIn();

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$loanTypeId = $_GET['id'];

// Get loan type info
$stmt = $pdo->prepare("SELECT * FROM loantype WHERE id = ?");
$stmt->execute([$loanTypeId]);
$loanType = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$loanType) {
    header("Location: index.php?error=Loan Type not found");
    exit();
}

// Initialize variables
$errors = [];
$loanTypeName = $loanType['name'];
$interestRate = $loanType['interest'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loanTypeName = trim($_POST['loan_type_name']);
    $interestRate = trim($_POST['interest_rate']);
    // Validation
    if (empty($loanTypeName)) {
        $errors['loan_type_name'] = 'Loan type name is required';
    }

    if (empty($interestRate)) {
        $errors['interest_rate'] = 'Interest rate is required';
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Update loan type
            $stmt = $pdo->prepare("UPDATE loantype SET name = ?, interest = ? WHERE id = ?");
            $stmt->execute([$loanTypeName, $interestRate, $loanTypeId]);

            $pdo->commit();

            header("Location: view.php?id=$loanTypeId&success=Loan type updated successfully");
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = "Error updating loan type: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit <?php echo htmlspecialchars($loanType['name']); ?></title>
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
                        <i class="bi bi-building"></i> Edit <?php echo htmlspecialchars($loanType['name']); ?>
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="view.php?id=<?php echo $loanTypeId; ?>" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to View
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
                                    id="loan_type" name="loan_type_name" value="<?php echo htmlspecialchars($loanTypeName); ?>" required>
                                <?php if (isset($errors['loan_type'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['loan_type']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label for="interest_rate" class="form-label">Interest Rate *</label>
                                <input type="text" class="form-control <?php echo isset($errors['interest_rate']) ? 'is-invalid' : ''; ?>"
                                    id="interest_rate" name="interest_rate" value="<?php echo htmlspecialchars($interestRate); ?>" required>
                                <?php if (isset($errors['interest_rate'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['interest_rate']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Save Changes
                                </button>
                                <a href="view.php?id=<?php echo $loanTypeId; ?>" class="btn btn-outline-secondary">
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