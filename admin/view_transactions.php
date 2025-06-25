<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/admin_login.php");
    exit();
}

// Get all transactions with sender & receiver names
$sql = "
    SELECT 
        t.*, 
        sender_user.name AS sender_name, 
        receiver_user.name AS receiver_name
    FROM transactions t
    JOIN accounts sender_acc ON t.account_id = sender_acc.id
    JOIN users sender_user ON sender_acc.user_id = sender_user.id
    LEFT JOIN accounts receiver_acc ON t.receiver_account_id = receiver_acc.id
    LEFT JOIN users receiver_user ON receiver_acc.user_id = receiver_user.id
    ORDER BY t.created_at DESC
";

$result = $conn->query($sql);

if (!$result) {
    // Query failed — get error
    $error_message = "Database query failed: " . $conn->error;
} else {
    $error_message = "";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Transactions</title>
    <style>
        /* Your professional CSS here */
        *, *::before, *::after {
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
            margin: 0;
            padding: 30px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #2c3e50;
            min-height: 100vh;
        }
        h2 {
            margin-bottom: 30px;
            font-weight: 700;
            font-size: 2rem;
            color: #34495e;
        }
        table {
            width: 98%;
            max-width: 1100px;
            border-collapse: collapse;
            background-color: #ffffff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            padding: 14px 18px;
            border-bottom: 1px solid #e1e4e8;
            text-align: center;
            font-size: 15px;
            color: #34495e;
        }
        th {
            background-color: #34495e;
            color: #ecf0f1;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        tr:hover:not(:first-child) {
            background-color: #f1f6f9;
        }
        p {
            margin-top: 25px;
            font-size: 1rem;
            font-weight: 600;
            color: #2980b9;
        }
        p a {
            color: #2980b9;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        p a:hover {
            color: #1c5980;
            text-decoration: underline;
        }
        /* Responsive */
        @media (max-width: 768px) {
            th, td {
                padding: 12px 10px;
                font-size: 13px;
            }
            h2 {
                font-size: 1.5rem;
            }
        }
        @media (max-width: 480px) {
            body {
                padding: 15px 10px;
            }
            table {
                font-size: 12px;
            }
            h2 {
                font-size: 1.3rem;
            }
        }

        .back-link {
            margin-top: 20px;
            display: inline-block;
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        .back-link:hover {
            color: #2980b9;
        }
    </style>
</head>
<body>

    <h2>All Transactions</h2>

    <?php if ($error_message): ?>
        <p style="color:red; font-weight:bold;"><?= htmlspecialchars($error_message) ?></p>
    <?php else: ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Sender</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Receiver</th>
                <th>Timestamp</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= $row['id']; ?></td>
                <td><?= htmlspecialchars($row['sender_name']); ?></td>
                <td><?= ucfirst($row['type']); ?></td>
                <td>$<?= number_format($row['amount'], 2); ?></td>
                <td><?= $row['type'] === 'transfer' ? htmlspecialchars($row['receiver_name']) : '-'; ?></td>
                <td><?= $row['created_at']; ?></td>
            </tr>
            <?php } ?>
        </table>
    <?php endif; ?>

    <p><a href="dashboard.php" class="back-link">Back to Admin Dashboard</a></p>

</body>
</html>
