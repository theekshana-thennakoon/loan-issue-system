<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

redirectIfNotLoggedIn();

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$userId = $_GET['id'];

// Get user info
$stmt = $pdo->prepare("SELECT * FROM farmers WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: index.php?error=User not found");
    exit();
}

$errors = [];
$name = $user['name'];
$email = $user['email'];
$farmer_code = $user['farmer_code'];
$phone = $user['phone'];
$address = $user['address'];
$nic = $user['nic'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $farmer_code = trim($_POST['farmer_code']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $nic = trim($_POST['nic']);

    // Validation
    if (empty($name)) {
        $errors['name'] = 'Full name is required';
    } elseif (strlen($name) > 100) {
        $errors['name'] = 'Name must be less than 100 characters';
    }

    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (strlen($email) > 100) {
        $errors['email'] = 'Email must be less than 100 characters';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    } elseif (empty($farmer_code)) {
        $errors['farmer_code'] = 'Farmer code is required';
    } elseif (empty($phone)) {
        $errors['phone'] = 'Phone number is required';
    } elseif (empty($address)) {
        $errors['address'] = 'Address is required';
    } elseif (empty($nic)) {
        $errors['nic'] = 'NIC is required';
    } else {
        // Check if email already exists (excluding current user)
        $stmt = $pdo->prepare("SELECT id FROM farmers WHERE email = ? AND id != ?");
        $stmt->execute([$email, $userId]);
        if ($stmt->fetch()) {
            $errors['email'] = 'Email is already registered to another user';
        }
    }

    if (empty($errors)) {
        try {

            $stmt = $pdo->prepare("UPDATE farmers SET name = ?, email = ?, farmer_code = ?, phone = ?, address = ?, nic = ? WHERE id = ?");
            $stmt->execute([$name, $email, $farmer_code, $phone, $address, $nic, $userId]);
            header("Location: view.php?id=$userId&success=Farmer updated successfully");
            exit();
        } catch (PDOException $e) {
            $errors['database'] = "Error updating farmer: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit <?php echo htmlspecialchars($user['name']); ?> </title>
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
                        <i class="bi bi-person"></i> Edit <?php echo htmlspecialchars($user['name']); ?>
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="view.php?id=<?php echo $userId; ?>" class="btn btn-secondary">
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
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Full Name *</label>
                                        <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>"
                                            id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                                        <?php if (isset($errors['name'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['name']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="farmer_code" class="form-label">Farmer code / Organization registered No *</label>
                                        <input type="text" class="form-control <?php echo isset($errors['farmer_code']) ? 'is-invalid' : ''; ?>"
                                            id="farmer_code" name="farmer_code" value="<?php echo htmlspecialchars($farmer_code); ?>" required>
                                        <?php if (isset($errors['farmer_code'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['farmer_code']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Contact number *</label>
                                        <input type="text" class="form-control <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>"
                                            id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
                                        <?php if (isset($errors['phone'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['phone']; ?></div>
                                        <?php endif; ?>
                                        <small class="text-muted">Minimum 8 characters</small>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>"
                                            id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                                        <?php if (isset($errors['email'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="user_type" class="form-label">Address *</label>
                                        <textarea class="form-control <?php echo isset($errors['address']) ? 'is-invalid' : ''; ?>"
                                            id="address" name="address" required><?php echo htmlspecialchars($address); ?></textarea>
                                        <?php if (isset($errors['address'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['address']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="nic" class="form-label">NIC *</label>
                                        <input type="text" class="form-control <?php echo isset($errors['nic']) ? 'is-invalid' : ''; ?>"
                                            id="nic" name="nic" value="<?php echo htmlspecialchars($nic); ?>" required>
                                        <?php if (isset($errors['nic'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['nic']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Save Changes
                                    </button>
                                    <a href="view.php?id=<?php echo $userId; ?>" class="btn btn-outline-secondary">
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
    <script src="../../assets/js/password-strength.js"></script>
</body>

</html>