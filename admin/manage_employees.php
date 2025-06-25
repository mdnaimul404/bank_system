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
    $employee_id = intval($_GET['delete']);
    $del = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'employee'");
    $del->bind_param("i", $employee_id);
    if ($del->execute()) {
        $msg = "Employee removed successfully!";
    } else {
        $msg = "Error removing employee: " . $del->error;
    }
}

// Handle new employee registration
if (isset($_POST['add_employee'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'employee')");
    $stmt->bind_param("sss", $name, $email, $password);

    if ($stmt->execute()) {
        $msg = "Employee added successfully!";
    } else {
        $msg = "Error: " . $stmt->error;
    }
}

// Get all employees
$result = $conn->query("SELECT id, name, email, created_at FROM users WHERE role = 'employee'");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Employees</title>
    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #eef2f5;
        margin: 0;
        padding: 40px 20px;
        color: #2c3e50;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    h2, h3 {
        margin: 10px 0;
        text-align: center;
        font-weight: 600;
    }

    form {
        background-color: #ffffff;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.08);
        width: 100%;
        max-width: 480px;
        margin-bottom: 35px;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"] {
        width: 100%;
        padding: 12px;
        margin-bottom: 20px;
        border: 1px solid #ccd1d9;
        border-radius: 6px;
        font-size: 15px;
        transition: border-color 0.3s ease;
    }

    input[type="text"]:focus,
    input[type="email"]:focus,
    input[type="password"]:focus {
        border-color: #3498db;
        outline: none;
    }

    button {
        width: 100%;
        padding: 12px;
        background-color: #3498db;
        color: #ffffff;
        font-size: 15px;
        font-weight: 600;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    button:hover {
        background-color: #2878c3;
    }

    table {
        width: 100%;
        max-width: 1100px;
        border-collapse: collapse;
        background-color: #ffffff;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.07);
        border-radius: 8px;
        overflow: hidden;
        margin-top: 30px;
    }

    th, td {
        padding: 14px 12px;
        border-bottom: 1px solid #e0e0e0;
        text-align: center;
        font-size: 15px;
    }

    th {
        background-color: #3498db;
        color: #ffffff;
        text-transform: uppercase;
        font-size: 14px;
        font-weight: 600;
    }

    tr:nth-child(even) {
        background-color: #f7f9fb;
    }

    tr:hover {
        background-color: #eef6fd;
    }

    a {
        color: #e74c3c;
        font-weight: 600;
        transition: color 0.2s ease;
        text-decoration: none;
    }

    a:hover {
        color: #c0392b;
        text-decoration: underline;
    }

    p {
        margin-top: 25px;
        font-weight: 500;
        text-align: center;
        font-size: 14px;
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
    <h2>Manage Employees</h2>

    <?php if ($msg) echo "<p style='color:green;'>$msg</p>"; ?>

    <h3>Add New Employee</h3>
    <form method="POST">
        <label>Name:</label><br>
        <input type="text" name="name" required><br>
        <label>Email:</label><br>
        <input type="email" name="email" required><br>
        <label>Password:</label><br>
        <input type="password" name="password" required><br>
        <button type="submit" name="add_employee">Add Employee</button>
    </form>

    <hr>

    <h3>Employee List</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Joined On</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?= $row['id']; ?></td>
            <td><?= htmlspecialchars($row['name']); ?></td>
            <td><?= htmlspecialchars($row['email']); ?></td>
            <td><?= $row['created_at']; ?></td>
            <td>
                <a href="?delete=<?= $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this employee?');">
                    Remove
                </a>
            </td>
        </tr>
        <?php } ?>
    </table>

    <p><a href="dashboard.php" class="back-link">Back to Admin Dashboard</a></p>
</body>
</html>
