<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

redirectIfNotLoggedIn();

$errors = [];

$query = "SELECT * FROM farmers";
$farmers_list = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

$query = "SELECT * FROM deposittypes";
$deposittypes = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deposit_type = trim($_POST['deposit_type']);
    $farmer = trim($_POST['farmer']);
    $transaction_date = trim($_POST['transaction_date']);
    $amount = trim($_POST['amount']);
    $deposit_or_withdraw = isset($_POST['deposit_or_withdraw']) ? trim($_POST['deposit_or_withdraw']) : '';

    if (empty($deposit_type)) {
        $errors['deposit_type'] = "Deposit type is required.";
    }

    if (empty($farmer)) {
        $errors['farmer'] = "Farmer is required.";
    }

    if (empty($transaction_date)) {
        $errors['transaction_date'] = "Transaction date is required.";
    }

    if (empty($amount) || !is_numeric($amount) || $amount <= 0) {
        $errors['amount'] = "Valid amount is required.";
    }

    if (empty($deposit_or_withdraw)) {
        $errors['deposit_or_withdraw'] = "Please select Deposit or Withdraw.";
    }

    if (empty($errors)) {
        try {
            $query = "SELECT * FROM deposits WHERE fid=? and dtid=?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$farmer, $deposit_type]);
            $farmers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($farmers) > 0) {
                $query = "SELECT MAX(id) as maxid FROM deposits WHERE fid=? and dtid=?";
                $stmt2 = $pdo->prepare($query);
                $stmt2->execute([$farmer, $deposit_type]);
                $max_Id = $stmt2->fetchAll(PDO::FETCH_ASSOC);
                $max_Id = $max_Id[0]['maxid'];

                $query = "SELECT balance FROM deposits WHERE id=?";
                $stmt3 = $pdo->prepare($query);
                $stmt3->execute([$max_Id]);
                $pre_price = $stmt3->fetchAll(PDO::FETCH_ASSOC);
                if ($deposit_or_withdraw == 'd') {
                    $balance = $pre_price[0]['balance'] + $amount;
                } else {
                    $balance = $pre_price[0]['balance'] - $amount;
                }
            } else {
                $balance = $amount;
            }

            // Handle different deposit types
            if ($deposit_type == 6) { // Peramaga
                $child_name = trim($_POST['child_name']);
                $child_dob = trim($_POST['child_dob']);

                if (empty($child_name)) {
                    $errors['child_name'] = "Child name is required.";
                }

                if (empty($child_dob)) {
                    $errors['child_dob'] = "Child date of birth is required.";
                }

                if (!empty($errors)) {
                    throw new Exception("Validation errors occurred.");
                }

                $stmt = $pdo->prepare("INSERT INTO deposits (fid, dtid, amount, dorw, balance, childname, dob, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$farmer, $deposit_type, $amount, $deposit_or_withdraw, $balance, $child_name, $child_dob, $transaction_date]);
            } elseif ($deposit_type == 7) { // Small Group Organization (assuming ID 7)
                $member1 = trim($_POST['member1']);
                $member2 = trim($_POST['member2']);
                $member3 = trim($_POST['member3']);

                $member1nic = trim($_POST['member1_nic']);
                $member2nic = trim($_POST['member2_nic']);
                $member3nic = trim($_POST['member3_nic']);

                if (empty($member1) || empty($member2) || empty($member3)) {
                    $errors['members'] = "All group members are required.";
                }

                if (!empty($errors)) {
                    throw new Exception("Validation errors occurred.");
                }

                if (empty($member1nic) || empty($member2nic) || empty($member3nic)) {
                    $errors['members_nic'] = "All group members NICs are required.";
                }

                $stmt = $pdo->prepare("INSERT INTO deposits (fid, dtid, amount, dorw, balance, member1, member2, member3, member1nic, member2nic, member3nic, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$farmer, $deposit_type, $amount, $deposit_or_withdraw, $balance, $member1, $member2, $member3, $member1nic, $member2nic, $member3nic, $transaction_date]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO deposits (fid, dtid, amount, dorw, balance, date) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$farmer, $deposit_type, $amount, $deposit_or_withdraw, $balance, $transaction_date]);
            }

            if ($deposit_or_withdraw == 'd') {
                header("Location: index.php?success=Deposit successfully");
            } else {
                header("Location: index.php?success=Withdrawal successfully");
            }
            exit();
        } catch (PDOException $e) {
            $errors['database'] = "Error creating Deposit: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deposit Types</title>
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
                    <h1 class="h2"><i class="bi bi-tags"></i> Deposit / Withdrawal</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="index.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Deposits / Withdrawals
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
                            <div class="row mb-3">
                                <div class="col">
                                    <label for="name" class="form-label">Select Deposit Type *</label>
                                    <select name="deposit_type" id="deposit_type" class="form-select">
                                        <option value="">-- Select Deposit Type --</option>
                                        <?php foreach ($deposittypes as $deposittype): ?>
                                            <option value="<?php echo $deposittype['id']; ?>"><?php echo htmlspecialchars($deposittype['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (isset($errors['deposit_type'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['deposit_type']; ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col">
                                    <label for="name" class="form-label">Select Farmer / Organization *</label>
                                    <select name="farmer" id="farmer" class="form-select">
                                        <option value="">-- Select Farmer / Organization --</option>
                                        <?php foreach ($farmers_list as $farmer): ?>
                                            <option value="<?php echo $farmer['id']; ?>"><?php echo htmlspecialchars($farmer['name']) . " (" . htmlspecialchars($farmer['farmer_code']) . ")"; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (isset($errors['farmer'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['farmer']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div id="peramaga-fields" class="row mb-3" style="display: none;">
                                <div class="col">
                                    <label for="child_name" class="form-label">Child Name *</label>
                                    <input type="text" class="form-control" id="child_name" name="child_name">
                                </div>
                                <div class="col">
                                    <label for="child_dob" class="form-label">Child Date of Birth *</label>
                                    <input type="date" class="form-control" id="child_dob" name="child_dob">
                                </div>
                            </div>

                            <div id="small-group-fields" class="row mb-3" style="display: none;">
                                <div class="row mb-3">
                                    <div class="col">
                                        <label for="member1" class="form-label">Member 1 *</label>
                                        <input type="text" class="form-control" id="member1" name="member1">
                                    </div>
                                    <div class="col mb-3">
                                        <label for="member1_nic" class="form-label">Member 1 NIC *</label>
                                        <input type="text" class="form-control" id="member1_nic" name="member1_nic">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col">
                                        <label for="member2" class="form-label">Member 2 *</label>
                                        <input type="text" class="form-control" id="member2" name="member2">
                                    </div>
                                    <div class="col">
                                        <label for="member2_nic" class="form-label">Member 2 NIC *</label>
                                        <input type="text" class="form-control" id="member2_nic" name="member2_nic">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col">
                                        <label for="member3" class="form-label">Member 3 *</label>
                                        <input type="text" class="form-control" id="member3" name="member3">
                                    </div>
                                    <div class="col">
                                        <label for="member3_nic" class="form-label">Member 3 NIC *</label>
                                        <input type="text" class="form-control" id="member3_nic" name="member3_nic">
                                    </div>
                                </div>

                            </div>


                            <div class="row mb-3">
                                <div class="col">
                                    <label for="amount" class="form-label">Amount *</label>
                                    <input type="text" class="form-control <?php echo isset($errors['amount']) ? 'is-invalid' : ''; ?>"
                                        id="amount" name="amount" required>
                                    <?php if (isset($errors['amount'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['amount']; ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col">
                                    <label for="name" class="form-label">Select Deposit or Withdraw *</label>
                                    <select name="deposit_or_withdraw" id="deposit_or_withdraw" class="form-select">
                                        <option value="">-- Select Deposit or Withdraw --</option>
                                        <option value="d">Deposit</option>
                                        <option value="w">Withdraw</option>
                                    </select>
                                </div>

                                <div class="col">
                                    <label for="transaction_date" class="form-label">Transaction Date *</label>
                                    <input type="date" class="form-control <?php echo isset($errors['transaction_date']) ? 'is-invalid' : ''; ?>"
                                        id="transaction_date" name="transaction_date" required>
                                    <?php if (isset($errors['transaction_date'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['transaction_date']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Deposit / Withdraw
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const depositTypeSelect = document.getElementById('deposit_type');
            const peramagaFields = document.getElementById('peramaga-fields');
            const smallGroupFields = document.getElementById('small-group-fields');

            // Find the peramaga and small group ids
            let peramagaId = '';
            let smallGroupId = '';

            <?php foreach ($deposittypes as $deposittype): ?>
                <?php if (strtolower($deposittype['name']) === 'peramaga'): ?>
                    peramagaId = '<?php echo $deposittype['id']; ?>';
                <?php endif; ?>
                <?php if (strtolower($deposittype['name']) === 'small group organization'): ?>
                    smallGroupId = '<?php echo $deposittype['id']; ?>';
                <?php endif; ?>
            <?php endforeach; ?>

            function toggleSpecialFields() {
                if (depositTypeSelect.value === peramagaId) {
                    peramagaFields.style.display = 'flex';
                    smallGroupFields.style.display = 'none';
                } else if (depositTypeSelect.value === smallGroupId) {
                    peramagaFields.style.display = 'none';
                    smallGroupFields.style.display = 'flex';
                } else {
                    peramagaFields.style.display = 'none';
                    smallGroupFields.style.display = 'none';
                }
            }

            depositTypeSelect.addEventListener('change', toggleSpecialFields);
            toggleSpecialFields(); // Initialize on page load
        });
    </script>
</body>

</html>