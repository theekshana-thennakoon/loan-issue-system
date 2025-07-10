<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

redirectIfNotLoggedIn();

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$deposittypeId = $_GET['id'];

// Get deposit type info
$stmt = $pdo->prepare("SELECT * FROM deposittypes WHERE id = ?");
$stmt->execute([$deposittypeId]);
$deposittype = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$deposittype) {
    header("Location: index.php?error=Deposit type not found");
    exit();
}

$errors = [];
$name = $deposittype['name'];
$description = $deposittype['description'];
$no_of_presentage_to_get_loan = $deposittype['no_of_presentage_to_get_loan'];
$interest = $deposittype['interest'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $no_of_presentage_to_get_loan = trim($_POST['min_no_percentage']);
    $interest = trim($_POST['interest_rate']);
    $can_withdraw = $_POST['can_withdraw'];
    $assets_or_responsibility = $_POST['assets_or_responsibility'];
    // Validation
    if (empty($name)) {
        $errors['name'] = 'Category name is required';
    } elseif (strlen($name) > 100) {
        $errors['name'] = 'Name must be less than 100 characters';
    }

    if (strlen($description) > 255) {
        $errors['description'] = 'Description must be less than 255 characters';
    }

    if (empty($no_of_presentage_to_get_loan) || !is_numeric($no_of_presentage_to_get_loan)) {
        $errors['min_no_percentage'] = 'Minimum number of percentage to get loan is required and must be a number';
    }

    if (empty($interest) || !is_numeric($interest)) {
        $errors['interest_rate'] = 'Interest rate is required and must be a number';
    }

    if (empty($can_withdraw)) {
        $errors['can_withdraw'] = 'Please select if can withdraw';
    }

    if (empty($assets_or_responsibility)) {
        $errors['assets_or_responsibility'] = 'Please select if it is an asset or responsibility';
    }


    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE deposittypes SET name = ?, description = ?, no_of_presentage_to_get_loan = ?, interest = ?, can_withdraw = ?, asset_or_respon = ?WHERE id = ?");
            $stmt->execute([$name, $description, $no_of_presentage_to_get_loan, $interest, $can_withdraw, $assets_or_responsibility, $deposittypeId]);

            header("Location: view.php?id=$deposittypeId&success=Category updated successfully");
            exit();
        } catch (PDOException $e) {
            $errors['database'] = "Error updating category: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit <?php echo htmlspecialchars($deposittype['name']); ?></title>
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
                        <i class="bi bi-tag"></i> Edit <?php echo htmlspecialchars($deposittype['name']); ?>
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="view.php?id=<?php echo $deposittypeId; ?>" class="btn btn-secondary">
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
                                <label for="name" class="form-label">Deposit Type Name *</label>
                                <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>"
                                    id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                                <?php if (isset($errors['name'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['name']; ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control <?php echo isset($errors['description']) ? 'is-invalid' : ''; ?>"
                                    id="description" name="description" rows="3"><?php echo htmlspecialchars($description); ?></textarea>
                                <?php if (isset($errors['description'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['description']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="row mb-3">
                                <div class="col">
                                    <label for="min_no_percentage" class="form-label">Minimum Number of Percentage to Get Loan *</label>
                                    <input type="text" class="form-control <?php echo isset($errors['min_no_percentage']) ? 'is-invalid' : ''; ?>"
                                        id="min_no_percentage" name="min_no_percentage" value="<?php echo htmlspecialchars($no_of_presentage_to_get_loan); ?>" required>
                                    <?php if (isset($errors['min_no_percentage'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['min_no_percentage']; ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col">
                                    <label for="interest_rate" class="form-label">Interest Rate *</label>
                                    <input type="text" class="form-control <?php echo isset($errors['interest_rate']) ? 'is-invalid' : ''; ?>"
                                        id="interest_rate" name="interest_rate" value="<?php echo htmlspecialchars($interest); ?>" required>
                                    <?php if (isset($errors['interest_rate'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['interest_rate']; ?></div>
                                    <?php endif; ?>
                                </div>


                            </div>

                            <div class="row mb-3">

                                <div class="col">
                                    <label for="can_withdraw" class="form-label">Can Withdraw *</label>
                                    <select name="can_withdraw" id="can_withdraw" class="form-select <?php echo isset($errors['can_withdraw']) ? 'is-invalid' : ''; ?>" required>
                                        <option value="">Select...</option>
                                        <option value="1" <?php echo $deposittype['can_withdraw'] == '1' ? ' selected' : ''; ?>>Yes</option>
                                        <option value="0" <?php echo $deposittype['can_withdraw'] == '0' ? ' selected' : ''; ?>>No</option>
                                    </select>
                                    <?php if (isset($errors['can_withdraw'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['can_withdraw']; ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col">
                                    <label for="assets_or_responsibility" class="form-label">Assets or Responsibility *</label>
                                    <select name="assets_or_responsibility" id="assets_or_responsibility" class="form-select <?php echo isset($errors['assets_or_responsibility']) ? 'is-invalid' : ''; ?>" required>
                                        <option value="">Select...</option>
                                        <option value="1" <?php echo $deposittype['asset_or_respon'] == '1' ? ' selected' : ''; ?>>Assets</option>
                                        <option value="0" <?php echo $deposittype['asset_or_respon'] == '0' ? ' selected' : ''; ?>>Responsibility</option>
                                    </select>
                                    <?php if (isset($errors['assets_or_responsibility'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['assets_or_responsibility']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>



                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Edit Deposit Type
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