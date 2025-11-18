<?php

require_once '../includes/_base.php';
include 'sideNav.php';
auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $promoID = trim($_POST['promoID']);
    $promoName = trim($_POST['promoName']);
    $amount = ($_POST['promoType'] == '1') ? '-' : (int)$_POST['amount'];  // Set '-' if Free Shipping
    $promoWay = ($_POST['promoType'] == '1') ? '-' : $_POST['promoWay'];    // Set '-' if Free Shipping
    $promoType = $_POST['promoType'];
    $endDate = $_POST['endDate'];
    $status = $_POST['status'];

    // Validation
    $errors = [];

    if (strlen($promoID) < 5 || strlen($promoID) > 8) {
        $errors[] = "Promo ID must be between 5 and 8 characters.";
    }

    if (strlen($promoName) < 3 || strlen($promoName) > 10) {
        $errors[] = "Promo Name must be between 3 and 10 characters.";
    }

    if ($promoType != '1' && ($amount <= 0 || $amount > 100)) {
        $errors[] = "Amount must be greater than 0 and not more than 100.";
    }

    $currentDate = date('Y-m-d');
    if ($endDate < $currentDate) {
        $errors[] = "End date cannot be in the past.";
    }

    if (empty($errors)) {
        try {
            // Insert data into the database
            $stmt = $_db->prepare("
                INSERT INTO promotion (promoID, promoName, amount, promoWay, promoType, endDate, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$promoID, $promoName, $amount, $promoWay, $promoType, $endDate, $status]);

            // Redirect to the discount list page after successful insertion
            echo "<script>alert('Discount added successfully!'); window.location.href='adminDiscount.php';</script>";
            exit();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Discount</title>
    <link rel="stylesheet" href="../css/adminDiscount.css">
    <script>
        function handlePromoTypeChange() {
            const promoType = document.getElementById('promoType').value;
            const promoWay = document.getElementById('promoWay');
            const amountGroup = document.getElementById('amountGroup');
            const amountInput = document.getElementById('amount');

            if (promoType === "1") { // If Free Shipping is selected
                promoWay.value = "-";
                promoWay.disabled = true;
                amountInput.value = "-";
                amountInput.disabled = true;
                amountGroup.style.display = "none"; // Hide amount input
            } else if (promoType === "0") { // If Subtotal Discount is selected
                promoWay.disabled = false;
                promoWay.value = ""; // Reset promoWay selection
                amountGroup.style.display = "block";
                amountInput.disabled = false;
                amountInput.value = ""; // Clear the value for amount
                amountInput.required = true;
            } else {
                promoWay.disabled = true;
                promoWay.value = "";
                amountInput.value = "";
                amountInput.disabled = true;
                amountGroup.style.display = "none";
                amountInput.required = false;
            }
        }

        function validateDiscountForm() {
            const promoID = document.getElementById('promoID').value.trim();
            const promoName = document.getElementById('promoName').value.trim();
            const amount = document.getElementById('amount').value.trim();
            const endDate = document.getElementById('endDate').value;
            const promoType = document.getElementById('promoType').value;

            let isValid = true;

            // Validate promoID length
            if (promoID.length < 5 || promoID.length > 8) {
                document.getElementById('promoID_feedback').innerText = "Promo ID must be between 5 and 8 characters.";
                document.getElementById('promoID_feedback').style.display = "block";
                isValid = false;
            } else {
                document.getElementById('promoID_feedback').style.display = "none";
            }

            // Validate promoName length
            if (promoName.length < 3 || promoName.length > 10) {
                document.getElementById('promoName_feedback').innerText = "Promo Name must be between 3 and 10 characters.";
                document.getElementById('promoName_feedback').style.display = "block";
                isValid = false;
            } else {
                document.getElementById('promoName_feedback').style.display = "none";
            }

            // Validate amount not more than 100 if not free shipping
            if (promoType === "0" && (amount <= 0 || amount > 100)) {
                document.getElementById('amount_feedback').innerText = "Amount must be greater than 0 and not more than 100.";
                document.getElementById('amount_feedback').style.display = "block";
                isValid = false;
            } else {
                document.getElementById('amount_feedback').style.display = "none";
            }

            // Validate end date
            const currentDate = new Date().toISOString().split('T')[0];
            if (endDate < currentDate) {
                document.getElementById('endDate_feedback').innerText = "End date cannot be in the past.";
                document.getElementById('endDate_feedback').style.display = "block";
                isValid = false;
            } else {
                document.getElementById('endDate_feedback').style.display = "none";
            }

            // Confirmation message
            if (isValid) {
                return confirm("Are you sure you want to add this discount?");
            }
            return false;
        }
    </script>
</head>
<body>
    <div class="add-form-container">
        <h2>Add New Discount</h2>
        <form action="" method="POST" onsubmit="return validateDiscountForm()">

            <div class="add-form-group">
                <label for="promoType">Promo Type</label>
                <select id="promoType" name="promoType" required onchange="handlePromoTypeChange()">
                    <option value="">Select</option>
                    <option value="0">Subtotal Discount</option>
                    <option value="1">Free Shipping</option>
                </select>
                <div id="promoType_feedback" class="add-feedback"></div>
            </div>

            <div class="add-form-group">
                <label for="promoID">Promo ID</label>
                <input type="text" id="promoID" name="promoID" required>
                <div id="promoID_feedback" class="add-feedback"></div>
            </div>

            <div class="add-form-group">
                <label for="promoName">Promo Name</label>
                <input type="text" id="promoName" name="promoName" required>
                <div id="promoName_feedback" class="add-feedback"></div>
            </div>

            <div class="add-form-group" id="amountGroup" style="display: none;">
                <label for="amount">Amount</label>
                <input type="number" id="amount" name="amount" min="1" required>
                <div id="amount_feedback" class="add-feedback"></div>
            </div>

            <div class="add-form-group">
                <label for="promoWay">Promo Way</label>
                <select id="promoWay" name="promoWay" required disabled>
                    <option value="">Select</option>
                    <option value="amount">Amount</option>
                    <option value="percentage">Percentage</option>
                    <option value="-">-</option>
                </select>
                <div id="promoWay_feedback" class="add-feedback"></div>
            </div>

            <div class="add-form-group">
                <label for="endDate">End Date</label>
                <input type="date" id="endDate" name="endDate" required>
                <div id="endDate_feedback" class="add-feedback"></div>
            </div>

            <div class="add-form-group">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
                <div id="status_feedback" class="add-feedback"></div>
            </div>

            <div class="add-button-group">
                <button type="submit" class="add-update-button">Add Discount</button>
                <a href="adminDiscount.php" class="add-cancel-button">Cancel</a>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="error-messages">
                    <?php foreach ($errors as $error): ?>
                        <p><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
