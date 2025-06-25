<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employee') {
    header("Location: ../login/employee_login.php");
    exit();
}

$msg = "";
$error = "";

// Show success message if redirected after successful transaction
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $msg = "Transaction successful.";
}

// Fetch all customers for dropdown
$customers_result = $conn->query("SELECT u.id, u.name, a.account_number, a.balance 
                                 FROM users u JOIN accounts a ON u.id = a.user_id 
                                 WHERE u.role = 'customer' ORDER BY u.name ASC");

if (isset($_POST['submit'])) {
    $type = $_POST['type'];
    $user_id = intval($_POST['user_id']);
    $amount = floatval($_POST['amount']);
    $receiver_id = isset($_POST['receiver_id']) ? intval($_POST['receiver_id']) : null;

    if ($amount <= 0) {
        $error = "Amount must be greater than zero.";
    } else {
        $stmt = $conn->prepare("SELECT a.balance FROM accounts a WHERE a.user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows !== 1) {
            $error = "Sender account not found.";
        } else {
            $row = $result->fetch_assoc();
            $balance = $row['balance'];

            if ($type === "deposit") {
                $new_balance = $balance + $amount;

                $conn->begin_transaction();
                try {
                    $upd = $conn->prepare("UPDATE accounts SET balance = ? WHERE user_id = ?");
                    $upd->bind_param("di", $new_balance, $user_id);
                    $upd->execute();

                    $ins = $conn->prepare("INSERT INTO transactions (account_id, type, amount) VALUES ((SELECT id FROM accounts WHERE user_id = ?), 'deposit', ?)");
                    $ins->bind_param("id", $user_id, $amount);
                    $ins->execute();

                    $conn->commit();

                    // Redirect to avoid form resubmission & show success message
                    header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
                    exit();
                } catch (Exception $e) {
                    $conn->rollback();
                    $error = "Transaction failed: " . $e->getMessage();
                }
            } elseif ($type === "withdraw") {
                if ($balance < $amount) {
                    $error = "Insufficient balance for withdrawal.";
                } else {
                    $new_balance = $balance - $amount;

                    $conn->begin_transaction();
                    try {
                        $upd = $conn->prepare("UPDATE accounts SET balance = ? WHERE user_id = ?");
                        $upd->bind_param("di", $new_balance, $user_id);
                        $upd->execute();

                        $ins = $conn->prepare("INSERT INTO transactions (account_id, type, amount) VALUES ((SELECT id FROM accounts WHERE user_id = ?), 'withdraw', ?)");
                        $ins->bind_param("id", $user_id, $amount);
                        $ins->execute();

                        $conn->commit();

                        header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
                        exit();
                    } catch (Exception $e) {
                        $conn->rollback();
                        $error = "Transaction failed: " . $e->getMessage();
                    }
                }
            } elseif ($type === "transfer") {
                if (!$receiver_id || $receiver_id === $user_id) {
                    $error = "Invalid receiver account.";
                } elseif ($balance < $amount) {
                    $error = "Insufficient balance for transfer.";
                } else {
                    $stmt2 = $conn->prepare("SELECT a.balance FROM accounts a WHERE a.user_id = ?");
                    $stmt2->bind_param("i", $receiver_id);
                    $stmt2->execute();
                    $res2 = $stmt2->get_result();

                    if ($res2->num_rows !== 1) {
                        $error = "Receiver account not found.";
                    } else {
                        $receiver_balance = $res2->fetch_assoc()['balance'];
                        $new_sender_balance = $balance - $amount;
                        $new_receiver_balance = $receiver_balance + $amount;

                        $conn->begin_transaction();
                        try {
                            $upd1 = $conn->prepare("UPDATE accounts SET balance = ? WHERE user_id = ?");
                            $upd1->bind_param("di", $new_sender_balance, $user_id);
                            $upd1->execute();

                            $upd2 = $conn->prepare("UPDATE accounts SET balance = ? WHERE user_id = ?");
                            $upd2->bind_param("di", $new_receiver_balance, $receiver_id);
                            $upd2->execute();

                            $ins = $conn->prepare("INSERT INTO transactions (account_id, type, amount, receiver_account_id) VALUES ((SELECT id FROM accounts WHERE user_id = ?), 'transfer', ?, (SELECT id FROM accounts WHERE user_id = ?))");
                            $ins->bind_param("idi", $user_id, $amount, $receiver_id);
                            $ins->execute();

                            $conn->commit();

                            header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
                            exit();
                        } catch (Exception $e) {
                            $conn->rollback();
                            $error = "Transaction failed: " . $e->getMessage();
                        }
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Employee Transaction Operations</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #f0f4f8, #d9e6f2);
            color: #2c3e50;
            margin: 0;
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        h2 {
            color: #1565c0;
            margin-bottom: 30px;
            text-align: center;
        }
        form {
            background: #fff;
            padding: 30px 25px;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(21, 101, 192, 0.2);
            width: 100%;
            max-width: 480px;
            margin-bottom: 40px;
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: 600;
            font-size: 15px;
        }
        select, input[type="number"] {
            width: 100%;
            padding: 12px 10px;
            margin-top: 6px;
            border-radius: 8px;
            border: 1px solid #bbb;
            font-size: 15px;
            transition: border-color 0.3s ease;
        }
        select:focus, input[type="number"]:focus {
            border-color: #1565c0;
            outline: none;
        }
        button {
            margin-top: 25px;
            width: 100%;
            padding: 14px;
            background: #1565c0;
            border: none;
            border-radius: 10px;
            color: white;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        button:hover {
            background: #0d3f7f;
        }
        p {
            font-weight: 600;
            margin-top: 15px;
            text-align: center;
        }
        a {
            color: #c62828;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        a:hover {
            color: #8e0000;
            text-decoration: underline;
        }
    </style>

    <script>
        function toggleReceiver() {
            const type = document.getElementById("type").value;
            const receiverDiv = document.getElementById("receiverDiv");
            receiverDiv.style.display = (type === "transfer") ? "block" : "none";
        }
        window.onload = toggleReceiver;
    </script>
</head>
<body>
    <h2>Employee: Perform Transactions</h2>

    <?php
    if ($msg) echo "<p style='color:green;'>$msg</p>";
    if ($error) echo "<p style='color:red;'>$error</p>";
    ?>

    <form method="POST">
        <label>Transaction Type:</label>
        <select name="type" id="type" required onchange="toggleReceiver()">
            <option value="">Select</option>
            <option value="deposit" <?= (isset($_POST['type']) && $_POST['type'] == 'deposit') ? 'selected' : '' ?>>Deposit</option>
            <option value="withdraw" <?= (isset($_POST['type']) && $_POST['type'] == 'withdraw') ? 'selected' : '' ?>>Withdraw</option>
            <option value="transfer" <?= (isset($_POST['type']) && $_POST['type'] == 'transfer') ? 'selected' : '' ?>>Transfer</option>
        </select>

        <label>Customer Account:</label>
        <select name="user_id" required>
            <option value="">Select Customer</option>
            <?php
            $customers_result->data_seek(0);
            while ($cust = $customers_result->fetch_assoc()) : ?>
                <option value="<?= $cust['id'] ?>" <?= (isset($_POST['user_id']) && $_POST['user_id'] == $cust['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cust['name']) ?> (Acc: <?= $cust['account_number'] ?>, Bal: $<?= number_format($cust['balance'], 2) ?>)
                </option>
            <?php endwhile; ?>
        </select>

        <div id="receiverDiv" style="display:none;">
            <label>Receiver Account (for Transfer):</label>
            <select name="receiver_id">
                <option value="">Select Receiver</option>
                <?php
                $customers_result->data_seek(0);
                while ($cust = $customers_result->fetch_assoc()) :
                ?>
                    <option value="<?= $cust['id'] ?>" <?= (isset($_POST['receiver_id']) && $_POST['receiver_id'] == $cust['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cust['name']) ?> (Acc: <?= $cust['account_number'] ?>, Bal: $<?= number_format($cust['balance'], 2) ?>)
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <label>Amount:</label>
        <input type="number" name="amount" step="0.01" min="0.01" required>

        <button type="submit" name="submit">Submit Transaction</button>
    </form>
    <p><a href="dashboard.php">← Back to Dashboard</a> | <a href="../logout.php">Logout</a></p>
</body>
</html>
