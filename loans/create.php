<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

redirectIfNotLoggedIn();

// Get all loan types with interest rates
$loanTypes = $pdo->query("SELECT id, name, interest FROM loantype")->fetchAll(PDO::FETCH_ASSOC);
$farmers = $pdo->query("SELECT * FROM farmers")->fetchAll(PDO::FETCH_ASSOC);

$errors = [];
$farmerId = $loanTypeId = $reason = '';
$issueDate = date('Y-m-d');
$amount = $interestRate = $fullAmount = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $farmerId = $_POST['farmer_id'];
    $loanTypeId = $_POST['loan_type'];
    $reason = trim($_POST['reason']);
    $issueDate = $_POST['issue_date'];
    $amount = (float)$_POST['amount'];
    $interestRate = (float)$_POST['interest_rate'];
    $fullAmount = (float)$_POST['full_amount'];

    // Validation
    if (empty($farmerId)) {
        $errors['farmer_id'] = 'Please select a farmer';
    }

    if (empty($loanTypeId)) {
        $errors['loan_type'] = 'Please select a loan type';
    }

    if (empty($reason)) {
        $errors['reason'] = 'Reason for issuance is required';
    } elseif (strlen($reason) > 255) {
        $errors['reason'] = 'Reason must be less than 255 characters';
    }

    if ($amount <= 0) {
        $errors['amount'] = 'Amount must be greater than 0';
    }

    if ($interestRate <= 0) {
        $errors['interest_rate'] = 'Interest rate must be greater than 0';
    }

    if ($fullAmount <= 0) {
        $errors['full_amount'] = 'Full amount must be greater than 0';
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Create loan record
            $stmt = $pdo->prepare("
                INSERT INTO loans 
                (fid, ltid, reason, price, presentage, no_of_month, issue_date, need_to_pay) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $farmerId,
                $loanTypeId,
                $reason,
                $amount,
                $interestRate,
                6, // Assuming a fixed number of months for simplicity
                $issueDate,
                $fullAmount
            ]);
            $loanId = $pdo->lastInsertId();

            $pdo->commit();

            header("Location: view.php?id=$loanId&success=Loan issued successfully");
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = "Error issuing loan: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issue Loan</title>
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
                    <h1 class="h2"><i class="bi bi-clipboard-check"></i> Issue Loan</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="../loans/" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Loans
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
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="farmer_id" class="form-label">Select farmer *</label>
                                        <select name="farmer_id" id="farmer_id" class="form-select <?php echo isset($errors['farmer_id']) ? 'is-invalid' : ''; ?>" required>
                                            <option value="">Select Farmer</option>
                                            <?php foreach ($farmers as $farmer): ?>
                                                <option value="<?php echo $farmer['id']; ?>" <?php echo $farmerId == $farmer['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($farmer['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if (isset($errors['farmer_id'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['farmer_id']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="loan_type" class="form-label">Select Loan type *</label>
                                        <select name="loan_type" id="loan_type" class="form-select <?php echo isset($errors['loan_type']) ? 'is-invalid' : ''; ?>" required>
                                            <option value="">Select Loan Type</option>
                                            <?php foreach ($loanTypes as $type): ?>
                                                <option value="<?php echo $type['id']; ?>" data-interest-rate="<?php echo $type['interest']; ?>" <?php echo $loanTypeId == $type['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($type['name']); ?> (<?php echo $type['interest']; ?>%)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if (isset($errors['loan_type'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['loan_type']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="issue_date" class="form-label">Issue Date *</label>
                                        <input type="date" class="form-control"
                                            id="issue_date" name="issue_date"
                                            value="<?php echo htmlspecialchars($issueDate); ?>" readonly required>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="reason" class="form-label">Reason for Loan *</label>
                                        <textarea class="form-control <?php echo isset($errors['reason']) ? 'is-invalid' : ''; ?>"
                                            id="reason" name="reason" rows="3" required><?php echo htmlspecialchars($reason); ?></textarea>
                                        <?php if (isset($errors['reason'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['reason']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col">
                                        <label for="amount" class="form-label">Amount *</label>
                                        <input type="number" step="0.01" class="form-control <?php echo isset($errors['amount']) ? 'is-invalid' : ''; ?>"
                                            id="amount" name="amount" value="<?php echo htmlspecialchars($amount); ?>" required>
                                        <?php if (isset($errors['amount'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['amount']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col">
                                        <label for="interest_rate" class="form-label">Interest Rate (%) *</label>
                                        <input type="number" step="0.01" class="form-control <?php echo isset($errors['interest_rate']) ? 'is-invalid' : ''; ?>"
                                            id="interest_rate" name="interest_rate" value="<?php echo htmlspecialchars($interestRate); ?>" readonly required>
                                        <?php if (isset($errors['interest_rate'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['interest_rate']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col">
                                        <label for="full_amount" class="form-label">Full Amount to Pay *</label>
                                        <input type="number" step="0.01" class="form-control <?php echo isset($errors['full_amount']) ? 'is-invalid' : ''; ?>"
                                            id="full_amount" name="full_amount" value="<?php echo htmlspecialchars($fullAmount); ?>" readonly required>
                                        <?php if (isset($errors['full_amount'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['full_amount']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle"></i> Issue Loan
                                    </button>
                                    <a href="../loans/" class="btn btn-outline-secondary">
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loanTypeSelect = document.getElementById('loan_type');
            const interestRateInput = document.getElementById('interest_rate');
            const amountInput = document.getElementById('amount');
            const fullAmountInput = document.getElementById('full_amount');

            // Update interest rate when loan type changes
            loanTypeSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value) {
                    const interestRate = selectedOption.getAttribute('data-interest-rate');
                    interestRateInput.value = interestRate;
                    calculateFullAmount();
                } else {
                    interestRateInput.value = '';
                    fullAmountInput.value = '';
                }
            });

            // Calculate full amount when amount changes
            amountInput.addEventListener('input', calculateFullAmount);

            function calculateFullAmount() {
                const amount = parseFloat(amountInput.value) || 0;
                const interestRate = parseFloat(interestRateInput.value) || 0;

                if (amount > 0 && interestRate > 0) {
                    const interestAmount = amount * (interestRate / 100);
                    fullAmountInput.value = (amount + interestAmount / 2).toFixed(2);
                } else {
                    fullAmountInput.value = '';
                }
            }

            // Initialize if values are already set (form submission with errors)
            if (loanTypeSelect.value && amountInput.value) {
                calculateFullAmount();
            }
        });
    </script>
</body>

</html>