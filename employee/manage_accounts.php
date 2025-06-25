<?php
session_start();
include('../includes/db.php');

// Session check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employee') {
    header("Location: ../login/employee_login.php");
    exit();
}

// Handle form submission
if (isset($_POST['create_account'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $account_number = trim($_POST['account_number']);
    $initial_balance = floatval($_POST['balance']);

    if (empty($name) || empty($email) || empty($password) || empty($confirm) || empty($account_number)) {
        $msg = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "Invalid email.";
    } elseif ($password !== $confirm) {
        $msg = "Passwords do not match.";
    } else {
        // Check if email or account exists
        $check_user = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_user->bind_param("s", $email);
        $check_user->execute();
        $check_user->store_result();

        $check_acc = $conn->prepare("SELECT id FROM accounts WHERE account_number = ?");
        $check_acc->bind_param("s", $account_number);
        $check_acc->execute();
        $check_acc->store_result();

        if ($check_user->num_rows > 0) {
            $msg = "Email already exists.";
        } elseif ($check_acc->num_rows > 0) {
            $msg = "Account number already exists.";
        } else {
            // Create user
            $hashed_pw = password_hash($password, PASSWORD_DEFAULT);
            $insert_user = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'customer')");
            $insert_user->bind_param("sss", $name, $email, $hashed_pw);
            $insert_user->execute();
            $user_id = $conn->insert_id;

            // Create account
            $insert_acc = $conn->prepare("INSERT INTO accounts (user_id, account_number, balance) VALUES (?, ?, ?)");
            $insert_acc->bind_param("isd", $user_id, $account_number, $initial_balance);
            $insert_acc->execute();

            $msg = "Customer account created successfully.";
        }
    }
}

// Fetch customer accounts
$customers = $conn->query("
    SELECT u.id, u.name, u.email, a.account_number, a.balance, a.status 
    FROM users u
    JOIN accounts a ON u.id = a.user_id
    WHERE u.role = 'customer'
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Customer Accounts</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #e0f7fa, #fff3e0);
            padding: 40px 20px;
            color: #2c3e50;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h2, h3 {
            color: #1a237e;
            margin-bottom: 20px;
            text-align: center;
        }

        form {
            background: #ffffff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
            margin-bottom: 40px;
        }

        form label {
            display: block;
            margin-top: 10px;
            font-weight: 600;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
            transition: border-color 0.3s ease;
        }

        input:focus {
            border-color: #0288d1;
            outline: none;
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            background-color: #0288d1;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #01579b;
        }

        table {
            width: 100%;
            max-width: 1000px;
            border-collapse: collapse;
            background-color: #ffffff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }

        th {
            background-color: #0288d1;
            color: white;
            text-transform: uppercase;
            font-size: 14px;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #e3f2fd;
        }

        p {
            margin-top: 25px;
            text-align: center;
            font-weight: 500;
        }

        a {
            color: #d84315;
            text-decoration: none;
            font-weight: bold;
        }

        a:hover {
            color: #b71c1c;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h2>Manage Customer Accounts</h2>

    <?php if (isset($msg)) echo "<p style='color:blue;'>$msg</p>"; ?>

    <h3>Create New Customer Account</h3>
    <form method="POST">
        <label>Name:</label>
        <input type="text" name="name" required>

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Password:</label>
        <input type="password" name="password" required>

        <label>Confirm Password:</label>
        <input type="password" name="confirm_password" required>

        <label>Account Number:</label>
        <input type="text" name="account_number" required>

        <label>Initial Balance:</label>
        <input type="number" name="balance" step="0" min="0" value="0" required>

        <button type="submit" name="create_account">Create Account</button>
    </form>

    <hr>

    <h3>All Customers</h3>
    <table>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Account #</th>
            <th>Balance</th>
            <th>Status</th>
        </tr>
        <?php while ($row = $customers->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= $row['account_number'] ?></td>
            <td><?= $row['balance'] ?></td>
            <td><?= $row['status'] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <p><a href="dashboard.php"> Back to Dashboard</a>  |  <a href="../logout.php">Logout</a></p>
    <!--<p><a href="../logout.php">Logout</a></p>-->
</body>
</html>
