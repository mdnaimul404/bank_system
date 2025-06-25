<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Login Selection</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f8;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 40px 50px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            text-align: center;
            width: 320px;
        }
        h1 {
            margin-bottom: 30px;
            color: #2c3e50;
        }
        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        a.button {
            padding: 15px 0;
            background-color: #3498db;
            color: white;
            font-weight: bold;
            text-decoration: none;
            border-radius: 6px;
            transition: background-color 0.3s ease;
            display: block;
        }
        a.button:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Select Login Type</h1>
        <div class="btn-group">
            <a href="login/admin_login.php" class="button">Login as Admin</a>
            <a href="login/employee_login.php" class="button">Login as Employee</a>
            <a href="login/customer_login.php" class="button">Login as User</a>
            
        </div>
    </div>
</body>
</html>
