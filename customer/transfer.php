<?php
session_start();
include('../includes/db.php');

// Restore session
if (!isset($_SESSION['role']) && isset($_COOKIE['role']) && $_COOKIE['role'] === 'customer') {
    $_SESSION['user_id'] = $_COOKIE['user_id'];
    $_SESSION['role'] = $_COOKIE['role'];
    $_SESSION['name'] = $_COOKIE['name'];
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../login/customer_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['transfer'])) {
    $to_account = trim($_POST['to_account']);
    $amount = floatval($_POST['amount']);

    // Sender account
    $from_account = $conn->query("SELECT balance FROM accounts WHERE user_id = $user_id")->fetch_assoc();

    // Receiver account
    $receiver = $conn->query("SELECT * FROM accounts WHERE account_number = '$to_account'")->fetch_assoc();

    if (!$receiver) {
        $msg = "Receiver account not found.";
    } elseif ($amount <= 0) {
        $msg = "Amount must be greater than 0.";
    } elseif ($amount > $from_account['balance']) {
        $msg = "Insufficient balance.";
    } else {
        // Transaction
        $conn->begin_transaction();

        try {
            $stmt1 = $conn->prepare("UPDATE accounts SET balance = balance - ? WHERE user_id = ?");
            $stmt1->bind_param("di", $amount, $user_id);
            $stmt1->execute();

            $stmt2 = $conn->prepare("UPDATE accounts SET balance = balance + ? WHERE account_number = ?");
            $stmt2->bind_param("ds", $amount, $to_account);
            $stmt2->execute();

            $stmt3 = $conn->prepare("INSERT INTO transactions (user_id, type, amount, receiver_id) VALUES (?, 'transfer', ?, ?)");
            $stmt3->bind_param("idi", $user_id, $amount, $receiver['user_id']);
            $stmt3->execute();

            $conn->commit();
            $msg = "Transfer successful!";
        } catch (Exception $e) {
            $conn->rollback();
            $msg = "Transfer failed.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Transfer</title></head>
<body>
    <h2>Transfer Money</h2>
    <?php if (isset($msg)) echo "<p style='color:green;'>$msg</p>"; ?>
    <form method="POST">
        <label>Receiver's Account Number:</label><br>
        <input type="text" name="to_account" required><br><br>

        <label>Amount:</label><br>
        <input type="number" step="0.01" name="amount" required><br><br>

        <button type="submit" name="transfer">Transfer</button>
    </form>
    <p><a href="dashboard.php">← Back to Dashboard</a></p>
</body>
</html>
