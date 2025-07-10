<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

redirectIfNotLoggedIn();

$errors = [];
$name = $description = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    // Validation
    if (empty($name)) {
        $errors['name'] = 'Deposit type name is required';
    } elseif (strlen($name) > 100) {
        $errors['name'] = 'Name must be less than 100 characters';
    }

    if (strlen($description) > 255) {
        $errors['description'] = 'Description must be less than 255 characters';
    }


    if (empty($_POST['interest_rate'])) {
        $errors['interest_rate'] = 'Interest rate is required';
    } elseif (!is_numeric($_POST['interest_rate']) || $_POST['interest_rate'] < 0) {
        $errors['interest_rate'] = 'Interest rate must be a positive number';
    }

    if (!isset($_POST['can_withdraw']) || $_POST['can_withdraw'] === '') {
        $errors['can_withdraw'] = 'Can withdraw is required';
    }

    if (!isset($_POST['assets_or_responsibility']) || $_POST['assets_or_responsibility'] === '') {
        $errors['assets_or_responsibility'] = 'Assets or responsibility is required';
    } elseif (!in_array($_POST['assets_or_responsibility'], ['1', '0'])) {
        $errors['assets_or_responsibility'] = 'Invalid selection for assets or responsibility';
    }


    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO deposittypes (name, description, interest, can_withdraw, asset_or_respon, no_of_presentage_to_get_loan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $_POST['interest_rate'], $_POST['can_withdraw'], $_POST['assets_or_responsibility'], $_POST['min_no_percentage']]);

            header("Location: index.php?success=Deposit type created successfully");
            exit();
        } catch (PDOException $e) {
            $errors['database'] = "Error creating Deposit Type: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deposit Types </title>
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
                    <h1 class="h2"><i class="bi bi-tags"></i> Create Deposit Type</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="index.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Deposit Types
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
                                        id="min_no_percentage" name="min_no_percentage" required>
                                    <?php if (isset($errors['min_no_percentage'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['min_no_percentage']; ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col">
                                    <label for="interest_rate" class="form-label">Interest Rate *</label>
                                    <input type="text" class="form-control <?php echo isset($errors['interest_rate']) ? 'is-invalid' : ''; ?>"
                                        id="interest_rate" name="interest_rate" required>
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
                                        <option value="1">Yes</option>
                                        <option value="0">No</option>
                                    </select>
                                    <?php if (isset($errors['can_withdraw'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['can_withdraw']; ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col">
                                    <label for="assets_or_responsibility" class="form-label">Assets or Responsibility *</label>
                                    <select name="assets_or_responsibility" id="assets_or_responsibility" class="form-select <?php echo isset($errors['assets_or_responsibility']) ? 'is-invalid' : ''; ?>" required>
                                        <option value="">Select...</option>
                                        <option value="1">Assets</option>
                                        <option value="0">Responsibility</option>
                                    </select>
                                    <?php if (isset($errors['assets_or_responsibility'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['assets_or_responsibility']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>



                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Create Deposit Type
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