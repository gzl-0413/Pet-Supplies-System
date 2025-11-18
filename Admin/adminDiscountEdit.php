<?php

require_once '../includes/_base.php';
include 'sideNav.php';
auth();

$promoID = isset($_GET['promoId']) ? $_GET['promoId'] : '';

if (empty($promoID)) {
    echo "<script>alert('No promo ID provided!'); window.location.href='adminDiscount.php';</script>";
    exit();
}

// Fetch promotion details
try {
    $stmt = $_db->prepare("SELECT * FROM promotion WHERE promoID = ?");
    $stmt->execute([$promoID]);
    $promo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$promo) {
        echo "<script>alert('Promotion not found!'); window.location.href='adminDiscount.php';</script>";
        exit();
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}

// Handle form submission
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
            // Update data in the database
            $stmt = $_db->prepare("
                UPDATE promotion 
                SET promoName = ?, amount = ?, promoWay = ?, promoType = ?, endDate = ?, status = ?
                WHERE promoID = ?
            ");
            $stmt->execute([$promoName, $amount, $promoWay, $promoType, $endDate, $status, $promoID]);

            // Redirect to the discount list page after successful update
            echo "<script>alert('Discount updated successfully!'); window.location.href='adminDiscount.php';</script>";
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
    <title>Edit Discount</title>
    <link rel="stylesheet" href="../css/adminDiscount.css">
    <style>/* CSS for Update and Cancel buttons */

.add-form-group {
    margin-bottom: 20px;
}

.add-form-group .btn {
    padding: 10px 20px;
    font-size: 16px;
    background-color: #4CAF50; /* Update button color */
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.add-form-group .btn:hover {
    background-color: #45a049; /* Darker shade on hover */
}

.add-form-group .cancel-btn {
    padding: 10px 20px;
    font-size: 16px;
    background-color: #f44336; /* Cancel button color */
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    margin-top: 10px;
    transition: background-color 0.3s ease;
    text-decoration: none; /* Remove underline for the link */
    text-align: center;
}

.add-form-group .cancel-btn:hover {
    background-color: #d32f2f; /* Darker shade on hover */
}
</style>
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
                return confirm("Are you sure you want to update this discount?");
            }
            return false;
        }
    </script>
</head>
<body>
    <div class="add-form-container">
        <h2>Edit Discount</h2>
        <form action="" method="POST" onsubmit="return validateDiscountForm()">

            <div class="add-form-group">
                <label for="promoType">Promo Type</label>
                <select id="promoType" name="promoType" required onchange="handlePromoTypeChange()">
                    <option value="0" <?= $promo['promoType'] == '0' ? 'selected' : '' ?>>Subtotal Discount</option>
                    <option value="1" <?= $promo['promoType'] == '1' ? 'selected' : '' ?>>Free Shipping</option>
                </select>
                <div id="promoType_feedback" class="add-feedback"></div>
            </div>

            <div class="add-form-group">
                <label for="promoID">Promo ID</label>
                <input type="text" id="promoID" name="promoID" value="<?= htmlspecialchars($promo['promoId']) ?>" readonly>
                <div id="promoID_feedback" class="add-feedback"></div>
            </div>

            <div class="add-form-group">
                <label for="promoName">Promo Name</label>
                <input type="text" id="promoName" name="promoName" value="<?= htmlspecialchars($promo['promoName']) ?>" required>
                <div id="promoName_feedback" class="add-feedback"></div>
            </div>

            <div class="add-form-group" id="amountGroup" <?= $promo['promoType'] == '1' ? 'style="display: none;"' : '' ?>>
                <label for="amount">Amount</label>
                <input type="number" id="amount" name="amount" value="<?= htmlspecialchars($promo['amount']) ?>" <?= $promo['promoType'] == '1' ? 'disabled' : '' ?> required>
                <div id="amount_feedback" class="add-feedback"></div>
            </div>

            <div class="add-form-group">
                <label for="promoWay">Promo Way</label>
                <select id="promoWay" name="promoWay" required <?= $promo['promoType'] == '1' ? 'disabled' : '' ?>>
                    <option value="amount" <?= $promo['promoWay'] == 'amount' ? 'selected' : '' ?>>Amount</option>
                    <option value="percent" <?= $promo['promoWay'] == 'percent' ? 'selected' : '' ?>>Percent</option>
                </select>
                <div id="promoWay_feedback" class="add-feedback"></div>
            </div>

            <div class="add-form-group">
                <label for="endDate">End Date</label>
                <input type="date" id="endDate" name="endDate" value="<?= htmlspecialchars($promo['endDate']) ?>" required>
                <div id="endDate_feedback" class="add-feedback"></div>
            </div>

            <div class="add-form-group">
    <label for="status">Status</label>
    <select id="status" name="status" required>
        <option value="1" <?= $promo['status'] == '1' ? 'selected' : '' ?>>Active</option>
        <option value="0" <?= $promo['status'] == '0' ? 'selected' : '' ?>>Inactive</option>
    </select>
</div>

            <div class="add-form-group">
                <button type="submit" class="btn">Update Discount</button>
                <a href="adminDiscount.php" class="cancel-btn">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
