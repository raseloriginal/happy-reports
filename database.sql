CREATE DATABASE IF NOT EXISTS happy_bangladesh;
USE happy_bangladesh;

-- Local deposits table (matches CRM schema)
CREATE TABLE IF NOT EXISTS deposits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    operation_date DATE NOT NULL,
    company_id INT NOT NULL DEFAULT 1,
    amount DECIMAL(10,2) NOT NULL,
    note TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Local dealer withdrawals
CREATE TABLE IF NOT EXISTS dealer_withdrawals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dealer_name VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    withdrawal_date DATE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Companies reference (for deposit company_id join)
CREATE TABLE IF NOT EXISTS companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(200) NOT NULL
);

-- Local lots table (manually managed)
CREATE TABLE IF NOT EXISTS lots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    lot_date DATE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    description TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Chat history for AI conversations
CREATE TABLE IF NOT EXISTS chat_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role ENUM('user','assistant') NOT NULL,
    message TEXT NOT NULL,
    tokens_used INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed company data
INSERT IGNORE INTO companies (id, company_name) VALUES (1, 'Happy Bangladesh');

-- Seed some sample data
INSERT INTO deposits (operation_date, company_id, amount, note) VALUES 
('2026-06-01', 1, 50000.00, 'Initial Capital'),
('2026-06-10', 1, 12000.50, 'Client A Payment'),
('2026-06-15', 1, 8500.00, 'Product Sales');

INSERT INTO dealer_withdrawals (dealer_name, amount, withdrawal_date, description) VALUES 
('Rahim Traders', 5000.00, '2026-06-05', 'Stock Purchase'),
('Karim Enterprise', 3000.00, '2026-06-12', 'Transportation');

