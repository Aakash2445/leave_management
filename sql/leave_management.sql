-- ============================================================
-- Employee Leave Management System - Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS leave_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE leave_management;

-- ============================================================
-- Table: users (login credentials + role)
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(20) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','manager','employee') NOT NULL DEFAULT 'employee',
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- Table: employees (profile details)
-- ============================================================
CREATE TABLE IF NOT EXISTS employees (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    employee_id VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    mobile VARCHAR(15),
    department VARCHAR(100) NOT NULL,
    designation VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- Table: leave_types
-- ============================================================
CREATE TABLE IF NOT EXISTS leave_types (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    default_days INT NOT NULL DEFAULT 0,
    description VARCHAR(255),
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- Table: leave_balance (per employee per leave type per year)
-- ============================================================
CREATE TABLE IF NOT EXISTS leave_balance (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    leave_type_id INT UNSIGNED NOT NULL,
    year YEAR NOT NULL,
    total_days INT NOT NULL DEFAULT 0,
    used_days INT NOT NULL DEFAULT 0,
    UNIQUE KEY unique_balance (user_id, leave_type_id, year),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (leave_type_id) REFERENCES leave_types(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- Table: leave_requests
-- ============================================================
CREATE TABLE IF NOT EXISTS leave_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    request_no VARCHAR(20) UNIQUE NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    leave_type_id INT UNSIGNED NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_days INT NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    manager_id INT UNSIGNED DEFAULT NULL,
    manager_remarks TEXT DEFAULT NULL,
    action_date TIMESTAMP NULL DEFAULT NULL,
    applied_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (leave_type_id) REFERENCES leave_types(id),
    FOREIGN KEY (manager_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- Table: audit_log
-- ============================================================
CREATE TABLE IF NOT EXISTS audit_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    action VARCHAR(100) NOT NULL,
    module VARCHAR(50) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- Seed: Leave Types
-- ============================================================
INSERT INTO leave_types (name, default_days, description) VALUES
('Casual Leave', 12, 'For personal or casual reasons'),
('Sick Leave', 10, 'For medical/health related reasons'),
('Earned Leave', 15, 'Earned through service');

-- ============================================================
-- Seed: Default Admin User
-- Password: Admin@123 (bcrypt hashed)
-- ============================================================
INSERT INTO users (employee_id, email, password, role, status) VALUES
('EMP001', 'admin@company.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active'),
('EMP002', 'manager@company.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager', 'active'),
('EMP003', 'employee@company.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee', 'active');

INSERT INTO employees (user_id, employee_id, full_name, email, mobile, department, designation) VALUES
(1, 'EMP001', 'System Admin', 'admin@company.com', '9000000001', 'IT', 'Administrator'),
(2, 'EMP002', 'John Manager', 'manager@company.com', '9000000002', 'Operations', 'Team Lead'),
(3, 'EMP003', 'Jane Employee', 'employee@company.com', '9000000003', 'Development', 'Developer');

-- Assign leave balances for current year
INSERT INTO leave_balance (user_id, leave_type_id, year, total_days, used_days) VALUES
(3, 1, YEAR(CURDATE()), 12, 0),
(3, 2, YEAR(CURDATE()), 10, 0),
(3, 3, YEAR(CURDATE()), 15, 0),
(2, 1, YEAR(CURDATE()), 12, 0),
(2, 2, YEAR(CURDATE()), 10, 0),
(2, 3, YEAR(CURDATE()), 15, 0);
