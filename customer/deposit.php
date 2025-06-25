<?php
session_start();
include('../includes/db.php');

// Restore session from cookie if available
if (!isset($_SESSION['role']) && isset($_COOKIE['role']) && $_COOKIE['role'] === 'customer') {
    $_SESSION['user_id'] = $_COOKIE['user_id'];
    $_SESSION['role'] = $_COOKIE['role'];
    $_SESSION['name'] = $_COOKIE['name'];
}

// Allow only logged-in customers
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../login/customer_login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // ✅ Make sure user_id is defined
$msg = "";
$error = "";

if (isset($_POST['deposit'])) {
    $amount = floatval($_POST['amount']);

    if ($amount <= 0) {
        $error = "Amount must be greater than 0.";
    } else {
        // Get account ID securely using prepared statement
        $stmt = $conn->prepare("SELECT id FROM accounts WHERE user_id = ? LIMIT 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $account = $result->fetch_assoc();

        if (!$account) {
            $error = "Account not found.";
        } else {
            $account_id = $account['id'];

            // Begin transaction for safety
            $conn->begin_transaction();

            try {
                // 1. Update balance
                $update = $conn->prepare("UPDATE accounts SET balance = balance + ? WHERE id = ?");
                $update->bind_param("di", $amount, $account_id);
                $update->execute();

                // 2. Insert transaction
                $insert = $conn->prepare("INSERT INTO transactions (account_id, type, amount) VALUES (?, 'deposit', ?)");
                $insert->bind_param("id", $account_id, $amount);
                $insert->execute();

                $conn->commit();
                $msg = "Deposit successful!";
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Transaction failed: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Deposit</title>
</head>
<body>
    <h2>Deposit Money</h2>

    <?php if ($msg) echo "<p style='color:green;'>$msg</p>"; ?>
    <?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>

    <form method="POST">
        <label>Amount:</label><br>
        <input type="number" step="0.01" name="amount" required><br><br>
        <button type="submit" name="deposit">Deposit</button>
    </form>

    <p><a href="dashboard.php">← Back to Dashboard</a></p>
</body>
</html>
