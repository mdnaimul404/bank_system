<?php
session_start();
include('../includes/db.php');

// Restore session from cookie if needed
if (!isset($_SESSION['role']) && isset($_COOKIE['role']) && $_COOKIE['role'] === 'customer') {
    $_SESSION['user_id'] = $_COOKIE['user_id'];
    $_SESSION['role'] = $_COOKIE['role'];
    $_SESSION['name'] = $_COOKIE['name'];
}

// Check valid customer session
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../login/customer_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch account info
$account = $conn->query("SELECT account_number, balance, status FROM accounts WHERE user_id = $user_id")->fetch_assoc();

$show_transactions = false;
$transactions = null;

// Detect if toggle button pressed
if (isset($_POST['toggle_transactions'])) {
    // If previous state was to show, now hide, and vice versa
    $show_transactions = ($_POST['toggle_transactions'] === 'view') ? true : false;

    if ($show_transactions) {
        // Get account id
        $account_id_res = $conn->query("SELECT id FROM accounts WHERE user_id = $user_id LIMIT 1");
        $account_id_row = $account_id_res->fetch_assoc();
        $account_id = $account_id_row['id'] ?? 0;

        // Fetch transactions for this account_id
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
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Customer Dashboard</title>
<style>
    /* Reset & Base */
    * {
        box-sizing: border-box;
    }
    body {
        margin: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #e0eafc, #cfdef3);
        color: #34495e;
        display: flex;
        flex-direction: column;
        align-items: center;
        min-height: 100vh;
        padding: 40px 20px;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }
    h1 {
        font-weight: 700;
        font-size: 2.75rem;
        color: #2c3e50;
        margin-bottom: 10px;
        text-align: center;
        letter-spacing: 1px;
        text-shadow: 1px 1px 3px rgba(0,0,0,0.1);
    }
    h3 {
        font-weight: 600;
        font-size: 1.4rem;
        color: #34495e;
        margin: 30px 0 15px;
        border-bottom: 2px solid #2980b9;
        padding-bottom: 6px;
        width: 95%;
        max-width: 900px;
    }
    p {
        font-size: 1rem;
        margin: 5px 0;
        max-width: 900px;
        color: #4a637d;
        letter-spacing: 0.02em;
    }

    /* Button */
    button {
        background: linear-gradient(135deg, #2980b9, #3498db);
        color: #fff;
        font-weight: 600;
        font-size: 1.1rem;
        padding: 14px 28px;
        border: none;
        border-radius: 30px;
        cursor: pointer;
        box-shadow: 0 6px 12px rgba(41, 128, 185, 0.4);
        transition: background 0.3s ease, box-shadow 0.3s ease;
        margin-top: 25px;
        display: block;
        width: 220px;
        text-align: center;
        margin-left: auto;
        margin-right: auto;
        user-select: none;
    }
    button:hover {
        background: linear-gradient(135deg, #1c5980, #2471a3);
        box-shadow: 0 8px 18px rgba(28, 89, 128, 0.7);
    }
    button:active {
        transform: scale(0.98);
        box-shadow: 0 4px 10px rgba(28, 89, 128, 0.5);
    }

    /* Table */
    table {
        width: 95%;
        max-width: 900px;
        border-collapse: separate;
        border-spacing: 0;
        margin-top: 30px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        border-radius: 12px;
        overflow: hidden;
        background: white;
        font-size: 0.95rem;
    }
    th, td {
        padding: 15px 18px;
        text-align: center;
        color: #34495e;
    }
    th {
        background: linear-gradient(135deg, #3498db, #2980b9);
        color: #ecf0f1;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        user-select: none;
    }
    tr {
        border-bottom: 1px solid #dce4ec;
        transition: background 0.25s ease;
    }
    tr:last-child {
        border-bottom: none;
    }
    tr:hover {
        background-color: #f1f7ff;
        cursor: default;
    }

    /* No transaction message */
    .no-transactions {
        margin-top: 20px;
        font-style: italic;
        color: #7f8c8d;
        font-size: 1rem;
        max-width: 900px;
        text-align: center;
    }

    /* Link List */
    ul {
        list-style: none;
        padding: 0;
        margin: 40px 0 0;
        display: flex;
        gap: 30px;
        justify-content: center;
        flex-wrap: wrap;
    }
    ul li a {
        text-decoration: none;
        color: #2980b9;
        font-weight: 600;
        font-size: 1rem;
        transition: color 0.3s ease, transform 0.2s ease;
        padding: 6px 12px;
        border-radius: 6px;
        user-select: none;
    }
    ul li a:hover {
        color: #1c5980;
        transform: scale(1.05);
        background: #d6e6fb;
        text-decoration: underline;
    }

    /* Responsive */
    @media (max-width: 700px) {
        h1 {
            font-size: 2.2rem;
        }
        h3 {
            font-size: 1.2rem;
        }
        button {
            width: 100%;
        }
        table {
            font-size: 0.85rem;
        }
        ul {
            gap: 15px;
        }
    }
</style>
</head>
<body>
<h1>Welcome, <?= htmlspecialchars($_SESSION['name']) ?></h1>

<h3>Account Details</h3>
<p><strong>Account Owner:</strong> <?= htmlspecialchars($_SESSION['name']) ?></p>
<p><strong>Account Number:</strong> <?= htmlspecialchars($account['account_number']) ?></p>
<p><strong>Balance:</strong> $<?= number_format($account['balance'], 2) ?></p>
<p><strong>Status:</strong> <?= ucfirst(htmlspecialchars($account['status'])) ?></p>

<form method="POST" aria-label="Toggle Transactions Form">
    <?php if ($show_transactions): ?>
        <button type="submit" name="toggle_transactions" value="hide">Hide Transactions</button>
    <?php else: ?>
        <button type="submit" name="toggle_transactions" value="view">View Transactions</button>
    <?php endif; ?>
</form>

<?php if ($show_transactions): ?>
    <h3>Your Transactions</h3>
    <?php if ($transactions && $transactions->num_rows > 0): ?>
        <table role="grid" aria-label="Transaction History Table">
            <thead>
                <tr>
                    <th scope="col">Type</th>
                    <th scope="col">Amount</th>
                    <th scope="col">Receiver (if Transfer)</th>
                    <th scope="col">Date & Time</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($txn = $transactions->fetch_assoc()): ?>
                <tr>
                    <td><?= ucfirst(htmlspecialchars($txn['type'])) ?></td>
                    <td>$<?= number_format($txn['amount'], 2) ?></td>
                    <td><?= $txn['type'] === 'transfer' ? htmlspecialchars($txn['receiver_name'] ?? '—') : '—' ?></td>
                    <td><?= htmlspecialchars($txn['created_at']) ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="no-transactions">No transactions found.</p>
    <?php endif; ?>
<?php endif; ?>

<ul>
    <!--<li><a href="deposit.php">Deposit</a></li>
    <li><a href="withdraw.php">Withdraw</a></li>
    <li><a href="request_transaction.php">Request Transaction</a></li>
    <li><a href="transfer.php">Transfer</a></li>-->
    <li><a href="../logout.php">Logout</a></li>
</ul>

</body>
</html>
