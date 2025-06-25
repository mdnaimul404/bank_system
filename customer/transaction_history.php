<?php
session_start();
include('../includes/db.php');

// Restore session from cookie if needed
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

// Fetch transaction history for logged-in customer
$sql = "
    SELECT t.*, u.name AS receiver_name
    FROM transactions t
    LEFT JOIN accounts a ON t.receiver_account_id = a.id
    LEFT JOIN users u ON a.user_id = u.id
    WHERE t.account_id = ?
    ORDER BY t.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $account_id);  // Make sure you already fetched $account_id from accounts table
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Transaction History</title>
    <style>
        table {
            border-collapse: collapse;
            width: 90%;
        }
        th, td {
            border: 1px solid #aaa;
            padding: 8px;
            text-align: center;
        }
        th {
            background: #eee;
        }
    </style>
</head>
<body>
    <h2>Transaction History for <?php echo htmlspecialchars($_SESSION['name']); ?></h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Type</th>
            <th>Amount</th>
            <th>Receiver (if transfer)</th>
            <th>Date/Time</th>
        </tr>

        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo ucfirst($row['type']); ?></td>
            <td>$<?php echo number_format($row['amount'], 2); ?></td>
            <td><?php echo $row['type'] === 'transfer' ? htmlspecialchars($row['receiver_name']) : '-'; ?></td>
            <td><?php echo $row['timestamp']; ?></td>
        </tr>
        <?php } ?>
    </table>

    <p><a href="dashboard.php">← Back to Dashboard</a></p>
</body>
</html>

