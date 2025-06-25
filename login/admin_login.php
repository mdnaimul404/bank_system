<?php
session_start();
include('../includes/db.php');

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);

    // Query for admin
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin' LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];

            // Optional: Remember Me cookies
            if ($remember) {
                setcookie('user_id', $user['id'], time() + (7 * 24 * 60 * 60), "/");
                setcookie('role', $user['role'], time() + (7 * 24 * 60 * 60), "/");
                setcookie('name', $user['name'], time() + (7 * 24 * 60 * 60), "/");
            }

            // Redirect to dashboard
            header("Location: ../admin/dashboard.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "Admin not found.";
    }
} else {
    // Restore session from cookies
    if (isset($_COOKIE['user_id']) && $_COOKIE['role'] === 'admin') {
        $_SESSION['user_id'] = $_COOKIE['user_id'];
        $_SESSION['role'] = $_COOKIE['role'];
        $_SESSION['name'] = $_COOKIE['name'];

        header("Location: ../admin/dashboard.php");
        exit();
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Login</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f8;
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        form {
            background: white;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
            width: 320px;
            box-sizing: border-box;
            text-align: center;
        }
        h2 {
            margin-bottom: 25px;
            color: #34495e;
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #34495e;
            text-align: left;
        }
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 18px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }
        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #3498db;
            outline: none;
        }
        label input[type="checkbox"] {
            width: auto;
            margin-right: 8px;
            vertical-align: middle;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #3498db;
            color: white;
            font-weight: 600;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #2980b9;
        }
        p {
            color: red;
            font-weight: 600;
            margin-bottom: 15px;
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
    <form method="POST" action="">
        <h2>Admin Login</h2>

        <?php if (isset($error)) { echo "<p>$error</p>"; } ?>

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Password:</label>
        <input type="password" name="password" required>

        <label><input type="checkbox" name="remember"> Remember Me</label>

        <button type="submit" name="login">Login</button>

        <a href="../index.php" class="back-link">Back to Home</a>
    </form>
</body>
</html>
