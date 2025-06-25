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

if (isset($_POST['withdraw'])) {
    $amount = floatval($_POST['amount']);

    $account = $conn->query("SELECT balance FROM accounts WHERE user_id = $user_id")->fetch_assoc();

    if ($amount <= 0) {
        $msg = "Amount must be greater than 0.";
    } elseif ($amount > $account['balance']) {
        $msg = "Insufficient balance.";
    } else {
        $stmt = $conn->prepare("UPDATE accounts SET balance = balance - ? WHERE user_id = ?");
        $stmt->bind_param("di", $amount, $user_id);
        $stmt->execute();

        $stmt = $conn->prepare("INSERT INTO transactions (account_id, type, amount) VALUES (?, 'withdraw', ?)");
        $stmt->bind_param("id", $user_id, $amount);
        $stmt->execute();

        $msg = "Withdrawal successful!";
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Withdraw</title></head>
<body>
    <h2>Withdraw Money</h2>
    <?php if (isset($msg)) echo "<p style='color:green;'>$msg</p>"; ?>
    <form method="POST">
        <label>Amount:</label><br>
        <input type="number" step="0.01" name="amount" required><br><br>
        <button type="submit" name="withdraw">Withdraw</button>
    </form>
    <p><a href="dashboard.php">← Back to Dashboard</a></p>
</body>
</html>
