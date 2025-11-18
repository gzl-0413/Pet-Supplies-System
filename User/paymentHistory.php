<?php
require_once '../includes/_base.php';
auth();

if (!isset($_SESSION['member'])) {
    redirect('login.php');
}

$member_id = $_SESSION['member'];

// Fetch user's payment history
$stm = $_db->prepare('SELECT * FROM payment WHERE userId = ? ORDER BY timestamp DESC');
$stm->execute([$member_id]);
$payments = $stm->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment History</title>
    <link href="../CSS/userOrder.css" rel="stylesheet" type="text/css" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <style>
        .container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        .back-button {
            display: inline-block;
            padding: 15px 30px;
            margin-bottom: 20px;
            background-color: #4CAF50;
            color: white;
            font-size: 18px;
            font-weight: bold;
            text-decoration: none;
            text-align: center;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .back-button:hover {
            background-color: #388E3C;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background-color: #4CAF50;
            color: white;
        }
        /* Modal styling */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9999;
        }
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border: 1px solid #888;
            width: 70%;
            border-radius: 8px;
            max-height: 80%;
            overflow-y: auto;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease, opacity 0.3s ease;
            transform: scale(0.8);
            opacity: 0;
        }
        .modal.show .modal-content {
            transform: scale(1);
            opacity: 1;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: #000;
        }
        .modal-body {
            font-size: 16px;
            line-height: 1.5;
        }
        .loading {
            text-align: center;
            padding: 20px;
            font-style: italic;
        }
        .error {
            color: red;
            text-align: center;
        }
  
/* Container styling */
.order-details-container {
    padding: 15px;
    background-color: #fff;
    border-radius: 8px;
    margin-top: 10px;
    border: 1px solid #e0e0e0;
}

.order-item {
    display: flex;
    padding: 10px 0;
    border-bottom: 1px solid #e0e0e0;
    align-items: center;
}

.product-image-container {
    flex: 0 0 80px;
    margin-right: 10px;
}

.product-image {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 5px;
    border: 1px solid #ddd;
}

.product-details {
    flex: 1;
    padding: 0 10px;
}

.product-name {
    font-size: 16px;
    font-weight: bold;
    margin: 0;
}

.product-variation {
    font-size: 14px;
    color: #666;
}

.product-quantity {
    font-size: 14px;
    color: #333;
    margin-top: 5px;
}

.return-policy {
    font-size: 12px;
    color: #28a745;
    margin-top: 5px;
}

.product-price {
    text-align: right;
    min-width: 120px;
}

.original-price {
    font-size: 14px;
    color: #aaa;
    text-decoration: line-through;
    margin: 0;
}

.discounted-price {
    font-size: 16px;
    color: #d9534f;
    font-weight: bold;
    margin: 0;
}

.order-footer {
    display: flex;
    justify-content: space-between;
    padding: 10px;
    background-color: #f9f9f9;
    border-top: 1px solid #e0e0e0;
    align-items: center;
}

.order-total {
    font-size: 18px;
    font-weight: bold;
    color: #d9534f;
}

.action-buttons {
    display: flex;
    gap: 10px;
}

.btn-rate, .btn-request-refund, .btn-more {
    padding: 8px 15px;
    background-color: #f44336;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}

.btn-rate:hover, .btn-request-refund:hover, .btn-more:hover {
    background-color: #d32f2f;
}

.btn-request-refund {
    background-color: #4CAF50;
}

.btn-request-refund:hover {
    background-color: #388E3C;
}

.btn-more {
    background-color: #f0ad4e;
}

.btn-more:hover {
    background-color: #ec971f;
}

    </style>
</head>

<body>
    <div class="container">
        <a href="product.php" class="back-button">Return to Products</a>
        <h2>Your Payment History</h2>
        
        <?php if (count($payments) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Payment ID</th>
                        <th>Order ID</th>
                        <th>Amount (RM)</th>
                        <th>Status</th>
                        <th>Payment Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                        <tr class="payment-row" data-order-id="<?php echo htmlspecialchars($payment['orderId']); ?>">
                            <td><?php echo htmlspecialchars($payment['transactionId']); ?></td>
                            <td><?php echo htmlspecialchars($payment['orderId']); ?></td>
                            <td><?php echo htmlspecialchars(number_format($payment['subtotal'], 2)); ?></td>
                            <td><?php echo htmlspecialchars($payment['status']); ?></td>
                            <td><?php echo htmlspecialchars(date('d M Y, H:i', strtotime($payment['timestamp']))); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-data">You have no payment history at the moment.</p>
        <?php endif; ?>
    </div>

    <!-- Modal for showing payment details -->
    <div id="payment-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div class="modal-body">
                <!-- Payment details will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('payment-modal');
            const closeModal = document.querySelector('.close');
            const modalBody = document.querySelector('.modal-body');

            // Close the modal when the "x" is clicked
            closeModal.addEventListener('click', function() {
                modal.style.display = 'none';
                modal.classList.remove('show');
            });

            // Make each payment row clickable
            document.querySelectorAll('.payment-row').forEach(row => {
                row.addEventListener('click', function() {
                    const orderId = this.getAttribute('data-order-id');
                    
                    // Show the modal
                    modal.style.display = 'block';
                    setTimeout(() => modal.classList.add('show'), 10);

                    // Display loading message
                    modalBody.innerHTML = '<div class="loading">Loading payment details...</div>';

                    // Fetch order details using AJAX
                    fetch(`fetchOrderDetails.php?orderId=${encodeURIComponent(orderId)}`)
                        .then(response => response.text())
                        .then(data => {
                            console.log("Data fetched successfully", data);
                            modalBody.innerHTML = data;
                        })
                        .catch(error => {
                            modalBody.innerHTML = '<div class="error">Failed to load payment details.</div>';
                            console.error('Error fetching order details:', error);
                        });
                });
            });

            // Close modal when clicking outside of it
            window.onclick = function(event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                    modal.classList.remove('show');
                }
            };
        });
    </script>
</body>
</html>
