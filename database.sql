CREATE DATABASE IF NOT EXISTS quan_ly_chi_tieu
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE quan_ly_chi_tieu;

CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    color CHAR(7) NOT NULL DEFAULT '#64748b',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_category_name_type (name, type)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS transactions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    transaction_date DATE NOT NULL,
    note TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_transactions_category
        FOREIGN KEY (category_id) REFERENCES categories(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_transaction_date (transaction_date),
    INDEX idx_transaction_type (type),
    INDEX idx_transaction_category (category_id)
) ENGINE=InnoDB;

INSERT IGNORE INTO categories (name, type, color) VALUES
('Ăn uống', 'expense', '#f59e0b'),
('Đi lại', 'expense', '#3b82f6'),
('Học tập', 'expense', '#8b5cf6'),
('Mua sắm', 'expense', '#ec4899'),
('Hóa đơn', 'expense', '#64748b'),
('Giải trí', 'expense', '#14b8a6'),
('Sức khỏe', 'expense', '#ef4444'),
('Khác', 'expense', '#94a3b8'),
('Lương', 'income', '#16a34a'),
('Tiền làm thêm', 'income', '#0d9488'),
('Tiền thưởng', 'income', '#2563eb'),
('Thu nhập khác', 'income', '#7c3aed');
