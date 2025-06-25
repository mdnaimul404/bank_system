<?php
session_start();
include('includes/db.php');

if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if (empty($name) || empty($email) || empty($password) || empty($confirm)) {
        $msg = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "Invalid email format.";
    } elseif ($password !== $confirm) {
        $msg = "Passwords do not match.";
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $msg = "Email already exists.";
        } else {
            // Insert user
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'customer')");
            $stmt->bind_param("sss", $name, $email, $hash);
            $stmt->execute();
            $user_id = $conn->insert_id;

            // Generate account number
            $acc_no = 'AC' . rand(10000000, 99999999);

            // Insert into accounts
            $stmt = $conn->prepare("INSERT INTO accounts (user_id, account_number, balance) VALUES (?, ?, 0.00)");
            $stmt->bind_param("is", $user_id, $acc_no);
            $stmt->execute();

            $msg = "Registration successful! You can now log in.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customer Registration</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f8;
            padding: 40px;
        }
        h2 {
            color: #2c3e50;
        }
        form {
            background: #fff;
            padding: 25px 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            width: 400px;
            margin-top: 20px;
        }
        label {
            font-weight: bold;
            display: block;
            margin-bottom: 6px;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background: #3498db;
            color: white;
            padding: 10px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover {
            background: #2980b9;
        }
        p {
            margin-top: 20px;
        }
        a {
            color: #3498db;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .message {
            margin-top: 10px;
            color: #2980b9;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h2>Register as a Customer</h2>
    <?php if (isset($msg)) echo "<div class='message'>" . htmlspecialchars($msg) . "</div>"; ?>

    <form method="POST">
        <label>Name:</label>
        <input type="text" name="name" required>

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Password:</label>
        <input type="password" name="password" required>

        <label>Confirm Password:</label>
        <input type="password" name="confirm_password" required>

        <button type="submit" name="register">Register</button>
    </form>

    <p>Already have an account? <a href="login/customer_login.php">Login here</a></p>
</body>
</html>
