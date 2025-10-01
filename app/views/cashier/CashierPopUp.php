<?php
$conn = new mysqli("localhost", "root", "", "ESANG_DB");

$orderId = 1; // Example invoice
$cashierId = 1; // Example cashier

// Fetch latest request status
$status = null;
$sql = "SELECT status FROM INVOICE_MODIFICATION_REQUEST WHERE orderId = ? AND cashierId = ? ORDER BY requestId DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $orderId, $cashierId);
$stmt->execute();
$stmt->bind_result($status);
$stmt->fetch();
$stmt->close();

// Handle request
if (isset($_POST['request_modify'])) {
    if ($status == null) {
        $insert = $conn->prepare("INSERT INTO INVOICE_MODIFICATION_REQUEST (orderId, cashierId) VALUES (?, ?)");
        $insert->bind_param("ii", $orderId, $cashierId);
        $insert->execute();
        $status = "pending";
    }
}

// Handle Modify
$message = "";
if (isset($_POST['modify_invoice'])) {
    if ($status == 'accepted') {
        $message = "You can now modify the invoice!";
    } elseif ($status == 'rejected') {
        $message = "Modified not allowed";
    } else {
        $message = "Waiting for admin approval";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Cashier Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-5">

    <h2>Cashier Panel - Invoice #<?php echo $orderId; ?></h2>

    <form method="POST" class="mt-3">
        <button type="submit" name="request_modify" class="btn btn-primary">Request Invoice Modification</button>
        <button type="submit" name="modify_invoice" class="btn btn-warning">Modify Invoice</button>
    </form>

    <p class="mt-3">Current Status: <b><?php echo $status ?? 'No Request Sent'; ?></b></p>

    <!-- Popup Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Invoice Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php echo $message ?: "No action taken yet."; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <?php if ($message) { ?>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            let modal = new bootstrap.Modal(document.getElementById('statusModal'));
            modal.show();
        });
    </script>
    <?php } ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>