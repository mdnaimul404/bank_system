<?php
session_start();
include('../includes/db.php');

// Only allow admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/admin_login.php");
    exit();
}

$msg = "";

// Handle deletion
if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);

    // Get all account IDs linked to this user
    $account_ids = [];
    $res = $conn->query("SELECT id FROM accounts WHERE user_id = $user_id");
    while ($row = $res->fetch_assoc()) {
        $account_ids[] = $row['id'];
    }

    if (!empty($account_ids)) {
        $ids_string = implode(',', $account_ids);

        // Delete transactions where this account is sender or receiver
        $conn->query("DELETE FROM transactions WHERE account_id IN ($ids_string) OR receiver_account_id IN ($ids_string)");

        // Then delete the accounts
        $conn->query("DELETE FROM accounts WHERE id IN ($ids_string)");
    }

    // Then delete user
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'customer'");
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        $msg = "Customer deleted successfully.";
    } else {
        $msg = "Error deleting customer: " . $stmt->error;
    }
}

// Get all customers with balances
$sql = "
    SELECT u.id, u.name, u.email, a.account_number, a.balance
    FROM users u
    JOIN accounts a ON u.id = a.user_id
    WHERE u.role = 'customer'
    ORDER BY u.name ASC
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Customers</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 20px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 95%;
            margin: auto;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border: 1px solid #aaa;
            text-align: center;
        }
        th {
            background-color: #eee;
        }
        .btn-delete {
            background-color: #e74c3c;
            color: white;
            padding: 5px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-delete:hover {
            background-color: #c0392b;
        }
        .back {
            text-align: center;
            margin-top: 20px;
        }
        .msg {
            text-align: center;
            font-weight: bold;
            color: green;
        }
    </style>
</head>
<body>

    <h2>Customer Accounts</h2>

    <?php if (!empty($msg)) echo "<p class='msg'>$msg</p>"; ?>

    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Account Number</th>
            <th>Balance</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?= $row['id']; ?></td>
            <td><?= htmlspecialchars($row['name']); ?></td>
            <td><?= htmlspecialchars($row['email']); ?></td>
            <td><?= $row['account_number']; ?></td>
            <td>$<?= number_format($row['balance'], 2); ?></td>
            <td>
                <a href="?delete=<?= $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this customer?');">
                    <button class="btn-delete">Remove</button>
                </a>
            </td>
        </tr>
        <?php } ?>
    </table>

    <div class="back">
        <p><a href="dashboard.php">Back to Admin Dashboard</a></p>
    </div>

</body>
</html>
