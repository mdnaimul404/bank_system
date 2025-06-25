# 💼 Bank Management System

A secure and user-friendly **Bank Management System** built with **PHP**, **MySQL**, **HTML**, **CSS**, and a dash of JavaScript. This system supports **multi-role authentication** (Admin, Employee, Customer) and essential banking operations like deposits, withdrawals, transfers, and transaction history tracking.

## 🌟 Features

### ✅ General
- Clean and responsive user interface
- Secure session-based login with role-based access control
- Modern, gradient-themed UI with hover effects and mobile responsiveness

### 👤 Admin
- Admin login
- Create employee and customer accounts
- View all users and account data

### 👨‍💼 Employee
- Login and dashboard
- Deposit to customer accounts
- Withdraw from customer accounts
- Transfer funds between customer accounts
- View customer transaction history

### 👨‍👩‍👧 Customer
- Login and dashboard
- View account information (account number, balance, status)
- Toggle to view/hide personal transaction history
- Logout

## 🗂️ Folder Structure



## 🛠️ Tech Stack

- **Frontend**: HTML, CSS (custom styling), JavaScript
- **Backend**: PHP (Core PHP without frameworks)
- **Database**: MySQL
- **Authentication**: PHP Sessions and Cookies

## 🔒 Security Features

- Role-based access control (Admin, Employee, Customer)
- Session validation with fallback to cookies (for customer convenience)
- SQL prepared statements to prevent SQL injection
- Minimal exposure of sensitive data

## 🚀 Getting Started

### Prerequisites

- PHP >= 7.x
- MySQL
- Apache/Nginx or XAMPP/LAMP for local development

### Setup Instructions

