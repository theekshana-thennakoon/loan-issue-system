<?php
$technical_officer_status = $_SESSION["technical_officer_status"];
?>
<div class="card h-100 border-0 shadow-none">
    <div class="card-body p-2">
        <div class="list-group list-group-flush">
            <a href="./dashboard.php" class="list-group-item list-group-item-action">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
            <?php
            if ($technical_officer_status == 'admin' || $technical_officer_status == 'to') {
            ?>
                <a href="./loan_types/" class="list-group-item list-group-item-action">
                    <i class="bi bi-building me-2"></i> Loan Types
                </a>
            <?php
            }
            ?>
            <?php
            if ($technical_officer_status == 'admin' || $technical_officer_status == 'to') {
            ?>
                <a href="./deposits/" class="list-group-item list-group-item-action">
                    <i class="bi bi-people me-2"></i> Deposit Types
                </a>
            <?php
            }
            ?>
            <?php
            if ($technical_officer_status == 'admin' || $technical_officer_status == 'to') {
            ?>
                <a href="./deposit/" class="list-group-item list-group-item-action">
                    <i class="bi bi-people me-2"></i> Deposits / Withdrawals
                </a>
            <?php
            }
            ?>
            <?php
            if ($technical_officer_status == 'admin' || $technical_officer_status == 'to') {
            ?>

                <a href="./users/" class="list-group-item list-group-item-action">
                    <i class="bi bi-box-seam me-2"></i> Farmers / Organizations List
                </a>
            <?php
            }
            ?>
            <a href="./issuances/" class="list-group-item list-group-item-action">
                <i class="bi bi-clipboard-check me-2"></i> Loans
            </a>
            <a href="./reports.php" class="list-group-item list-group-item-action">
                <i class="bi bi-graph-up me-2"></i> Reports
            </a>
        </div>
    </div>
</div>