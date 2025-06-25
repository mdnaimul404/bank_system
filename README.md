# Bank Management System

A full-featured **Bank Management System** built with PHP, MySQL, HTML, CSS, and Bootstrap. This system supports multiple user roles (Admin, Employee, Customer) and provides functionalities such as login authentication, deposit, withdrawal, fund transfers, and transaction history viewing.

---

## 🚀 Live Demo

You can view the live system here:
👉 (https://naimul.great-site.net/)

Use admin email: admin@example.com 

Password: admin123

---

## 🛠 Technologies Used

* **Frontend:** HTML, CSS, Bootstrap
* **Backend:** PHP (vanilla PHP)
* **Database:** MySQL
* **Version Control:** Git & GitHub

---

## 👥 User Roles

### 1. Admin

* Login authentication
* Create employee and customer accounts
* View all users

### 2. Employee

* Deposit, withdraw, or transfer money between customer accounts
* View transaction history of any customer

### 3. Customer

* View account details (balance, account number, status)
* View personal transaction history (dynamic toggle on click)

---

## 🖼 Features

* Multi-role login system
* Clean and modern UI (responsive and mobile-friendly)
* Transaction log and balance update
* Error and success handling with validation
* Secured session-based authentication

---

## 📂 Folder Structure

```
├── admin/
├── customer/
├── employee/
├── includes/
│   └── db.php         # Database connection
├── login/
├── logout.php
├── index.php
├── assets/            # (Optional) for CSS/JS/Images
└── README.md
```

---

## ⚙️ Setup Instructions (Local or Hosting)

### ✅ 1. Clone the Repository

```bash
git clone https://github.com/YOUR_USERNAME/bank_system.git
```

### ✅ 2. Import Database

* Import `bank_system.sql` into your MySQL using phpMyAdmin or MySQL CLI.

### ✅ 3. Configure `db.php`

Edit `includes/db.php` with your DB credentials:

```php
$host = "localhost";
$username = "root";
$password = "";
$database = "bank_system";
```

### ✅ 4. Run on Localhost

Place the project in your XAMPP `htdocs` folder and navigate to:

```
http://localhost/bank_system
```

---

## 🌍 Hosting Instructions (InfinityFree + GitHub)

1. **Push code to GitHub repo:** `bank_system`
2. **Create account** on [https://infinityfree.net](https://infinityfree.net)
3. **Create database** & upload files to `htdocs/`
4. **Import SQL** in phpMyAdmin
5. **Edit `db.php`** to match InfinityFree database credentials
6. Access live site at:

```
http://your-subdomain.epizy.com
```

---

## 🤝 Contributing

Feel free to fork this project, improve it, and submit pull requests!

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 👨‍💻 Author

**Md. Naimul Islam**
🎓 BSc in CSE @ AIUB
🔗 [LinkedIn](https://www.linkedin.com/in/naimul404) | [GitHub](https://github.com/mdnaimul404) | [Portfolio](https://sites.google.com/view/naimul404)
