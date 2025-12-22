-- إنشاء قاعدة البيانات
CREATE DATABASE IF NOT EXISTS barbershop_db;
USE barbershop_db;

-- جدول الموظفين
CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(50),
    phone VARCHAR(20),
    notes TEXT
);

-- جدول الخدمات
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2),
    notes TEXT
);

-- جدول المبيعات
CREATE TABLE IF NOT EXISTS sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_date DATE NOT NULL,
    client_name VARCHAR(100),
    service_id INT,
    amount DECIMAL(10,2),
    status ENUM('مدفوع','غير مدفوع'),
    notes TEXT,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL
);

-- جدول المصروفات
CREATE TABLE IF NOT EXISTS expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expense_date DATE NOT NULL,
    item VARCHAR(100),
    amount DECIMAL(10,2),
    notes TEXT
);

-- جدول السلف
CREATE TABLE IF NOT EXISTS advances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT,
    amount DECIMAL(10,2),
    advance_date DATE NOT NULL,
    status ENUM('مدفوع','غير مدفوع'),
    notes TEXT,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE SET NULL
);

-- جدول المدير
CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- إضافة مدير تجريبي
INSERT INTO admin (username, password) VALUES ('admin', '123456');
