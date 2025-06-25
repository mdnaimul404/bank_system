<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../login/customer_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";
$error = "";

// Fetch list of other customers for transfer
$receivers = $conn->query("SELECT id, name FROM users WHERE role = 'customer' AND id != $user_id");

if (isset($_POST['submit'])) {
    $type = $_POST['type'];
    $amount = floatval($_POST['amount']);
    $receiver_id = isset($_POST['receiver_id']) ? intval($_POST['receiver_id']) : null;

    if ($amount <= 0) {
        $error = "Amount must be greater than 0.";
    } elseif ($type === 'transfer' && (!$receiver_id || $receiver_id == $user_id)) {
        $error = "Please select a valid receiver.";
    } else {
        $stmt = $conn->prepare("INSERT INTO transaction_requests (user_id, type, amount, receiver_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isdi", $user_id, $type, $amount, $receiver_id);
        if ($stmt->execute()) {
            $msg = "Your request has been submitted for review.";
        } else {
            $error = "Failed to submit request.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Request Transaction</title>
    <script>
        function toggleReceiver() {
            var type = document.getElementById('type').value;
            document.getElementById('receiver_block').style.display = (type === 'transfer') ? 'block' : 'none';
        }
    </script>
</head>
<body onload="toggleReceiver()">
    <h2>Request a Transaction</h2>
    <p><a href="dashboard.php">← Back to Dashboard</a> | <a href="../logout.php">Logout</a></p>

    <?php if ($msg): ?><p style="color:green;"><?= $msg ?></p><?php endif; ?>
    <?php if ($error): ?><p style="color:red;"><?= $error ?></p><?php endif; ?>

    <form method="POST">
        <label>Transaction Type:</label><br>
        <select name="type" id="type" onchange="toggleReceiver()" required>
            <option value="withdraw">Withdraw</option>
            <option value="transfer">Transfer</option>
        </select><br><br>

        <div id="receiver_block" style="display:none;">
            <label>Select Receiver:</label><br>
            <select name="receiver_id">
                <option value="">-- Select --</option>
                <?php while ($row = $receivers->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                <?php endwhile; ?>
            </select><br><br>
        </div>

        <label>Amount:</label><br>
        <input type="number" name="amount" step="0.01" min="0.01" required><br><br>

        <button type="submit" name="submit">Submit Request</button>
    </form>
</body>
</html>
