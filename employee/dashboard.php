<?php
session_start();

// Restore session from cookie if available
if (!isset($_SESSION['role']) && isset($_COOKIE['role']) && $_COOKIE['role'] === 'employee') {
    $_SESSION['user_id'] = $_COOKIE['user_id'];
    $_SESSION['role'] = $_COOKIE['role'];
    $_SESSION['name'] = $_COOKIE['name'];
}

// Check role permission
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employee') {
    header("Location: ../login/employee_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Employee Dashboard</title>
    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #fdfcfb, #e2d1c3);
        margin: 0;
        padding: 50px 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
        color: #2c3e50;
        min-height: 100vh;
    }

    h1 {
        font-size: 30px;
        margin-bottom: 40px;
        color: #8e44ad;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
    }

    ul {
        list-style: none;
        padding: 0;
        margin: 0;
        width: 100%;
        max-width: 500px;
    }

    li {
        background: linear-gradient(to right, #6dd5ed, #2193b0);
        margin: 12px 0;
        padding: 16px 24px;
        border-radius: 12px;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        text-align: center;
    }

    li:hover {
        transform: scale(1.03);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    a {
        text-decoration: none;
        color: #ffffff;
        font-weight: bold;
        font-size: 18px;
        letter-spacing: 0.5px;
    }

    a:hover {
        color: #ffeaa7;
    }

    @media (max-width: 600px) {
        h1 {
            font-size: 24px;
        }

        li {
            padding: 14px 18px;
        }

        a {
            font-size: 16px;
        }
    }
</style>

</head>
<body>
    <h1>Welcome, Employee <?php echo htmlspecialchars($_SESSION['name']); ?></h1>

    <ul>
        <li><a href="manage_accounts.php">Manage Customer Accounts</a></li>
        <li><a href="transactions.php">Perform Transactions</a></li>
        <li><a href="view_transactions.php">View User Transactions</a></li>
        <li><a href="../logout.php">Logout</a></li>
    </ul>
</body>
</html>
