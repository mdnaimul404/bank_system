<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/admin_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 30px;
        }
        ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
            width: 300px;
        }
        li {
            margin: 10px 0;
        }
        a {
            display: block;
            text-align: center;
            padding: 12px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        a:hover {
            background-color: #2980b9;
        }

        .back-link {
            display: block;
            text-align: center;
            padding: 12px;
            background-color:rgb(219, 52, 52);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        .back-link:hover {
            color:rgb(0, 0, 0);
        }
    </style>
</head>
<body>
    <h1>Welcome, Admin <?php echo htmlspecialchars($_SESSION['name']); ?></h1>

    <ul>
        <li><a href="create_employee.php">Create Employee</a></li>
        <li><a href="manage_users.php">Manage Users</a></li>
        <li><a href="view_transactions.php">View Transactions</a></li>
        <li><a href="manage_employees.php">Manage Employees</a></li>
        <li><a href="../logout.php" class="back-link">Logout</a></li>
    </ul>
</body>
</html>
