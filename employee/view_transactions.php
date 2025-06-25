<?php
session_start();
include('../includes/db.php');

// Employee session check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employee') {
    header("Location: ../login/employee_login.php");
    exit();
}

// Fetch customers for dropdown
$customers_result = $conn->query("
    SELECT u.id, u.name, a.account_number 
    FROM users u
    JOIN accounts a ON u.id = a.user_id
    WHERE u.role = 'customer'
    ORDER BY u.name ASC
");

$transactions = [];
$selected_customer_id = null;

if (isset($_POST['view'])) {
    $selected_customer_id = intval($_POST['customer_id']);

    // Get account_id using selected customer user_id
    $account = $conn->query("SELECT id FROM accounts WHERE user_id = $selected_customer_id LIMIT 1")->fetch_assoc();

    if ($account) {
        $account_id = $account['id'];

        // Fetch transactions for the selected customer's account
        $stmt = $conn->prepare("
            SELECT t.*, u2.name AS receiver_name
            FROM transactions t
            LEFT JOIN accounts ra ON t.receiver_account_id = ra.id
            LEFT JOIN users u2 ON ra.user_id = u2.id
            WHERE t.account_id = ?
            ORDER BY t.created_at DESC
        ");
        $stmt->bind_param("i", $account_id);
        $stmt->execute();
        $transactions = $stmt->get_result();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>View Customer Transactions</title>
    <style>
        /* Reset & base */
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f9fafb;
            color: #333;
            margin: 0;
            padding: 30px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }

        h2 {
            color: #1565c0;
            margin-bottom: 25px;
            text-align: center;
        }

        form {
            background: white;
            padding: 20px 25px;
            border-radius: 10px;
            box-shadow: 0 6px 18px rgba(21, 101, 192, 0.15);
            width: 100%;
            max-width: 480px;
            margin-bottom: 30px;
        }

        label {
            font-weight: 600;
            font-size: 16px;
            display: block;
            margin-bottom: 8px;
        }

        select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #bbb;
            border-radius: 8px;
            font-size: 15px;
            transition: border-color 0.3s ease;
            cursor: pointer;
        }
        select:focus {
            border-color: #1565c0;
            outline: none;
        }

        button {
            margin-top: 20px;
            width: 100%;
            padding: 14px 0;
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

        p.links {
            margin-top: 20px;
            font-weight: 600;
            font-size: 14px;
            text-align: center;
        }
        p.links a {
            color: #c62828;
            text-decoration: none;
            margin: 0 10px;
            transition: color 0.3s ease;
        }
        p.links a:hover {
            color: #8e0000;
            text-decoration: underline;
        }

        /* Table styles */
        table {
            width: 95%;
            max-width: 900px;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }
        th, td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: center;
            font-size: 14px;
        }
        th {
            background-color: #f0f0f0;
            font-weight: 700;
            color: #1565c0;
        }
        tr:nth-child(even) {
            background-color: #fafafa;
        }
        tr:hover {
            background-color: #e3f0ff;
        }

        /* Responsive */
        @media (max-width: 600px) {
            table, form {
                width: 100%;
            }
            th, td {
                font-size: 12px;
                padding: 8px 10px;
            }
            button {
                font-size: 14px;
                padding: 12px 0;
            }
        }
    </style>
</head>
<body>
    <h2>Employee: View Customer Transaction History</h2>

    <form method="POST">
        <label>Select Customer:</label>
        <select name="customer_id" required>
            <option value="">-- Select --</option>
            <?php while ($cust = $customers_result->fetch_assoc()) : ?>
                <option value="<?= $cust['id'] ?>" <?= ($selected_customer_id == $cust['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cust['name']) ?> (<?= htmlspecialchars($cust['account_number']) ?>)
                </option>
            <?php endwhile; ?>
        </select>
        <br><br>
        <button type="submit" name="view">View Transactions</button>
        <p class="links"><a href="dashboard.php">← Back to Dashboard</a> | <a href="../logout.php">Logout</a></p>
    </form>

    <?php if ($transactions && $transactions->num_rows > 0): ?>
        <h3>Transaction History</h3>
        <table>
            <tr>
                <th>Type</th>
                <th>Amount</th>
                <th>Receiver (if Transfer)</th>
                <th>Date & Time</th>
            </tr>
            <?php while ($txn = $transactions->fetch_assoc()): ?>
                <tr>
                    <td><?= ucfirst(htmlspecialchars($txn['type'])) ?></td>
                    <td>$<?= number_format($txn['amount'], 2) ?></td>
                    <td><?= $txn['type'] === 'transfer' ? htmlspecialchars($txn['receiver_name'] ?? '—') : '—' ?></td>
                    <td><?= htmlspecialchars($txn['created_at']) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php elseif ($selected_customer_id): ?>
        <p>No transactions found for this customer.</p>
    <?php endif; ?>
</body>
</html>
