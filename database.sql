CREATE DATABASE IF NOT EXISTS happy_reports;
USE happy_reports;

CREATE TABLE IF NOT EXISTS lots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    crm_id VARCHAR(50) NOT NULL,
    warehouse_crm_id VARCHAR(50),
    warehouse_name VARCHAR(100),
    company_crm_id VARCHAR(50),
    company_name VARCHAR(100),
    lot_value DECIMAL(15, 2) NOT NULL,
    lot_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    crm_ids VARCHAR(255), -- Comma separated IDs
    company_crm_id VARCHAR(50),
    company_name VARCHAR(100),
    total_out_value DECIMAL(15, 2) DEFAULT 0.00,
    total_in_value DECIMAL(15, 2) DEFAULT 0.00,
    transaction_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS deposits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_crm_id VARCHAR(50),
    company_name VARCHAR(100),
    amount DECIMAL(15, 2) NOT NULL,
    deposit_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS dealer_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dealer_crm_id VARCHAR(50),
    dealer_name VARCHAR(100),
    amount DECIMAL(15, 2) NOT NULL,
    payment_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
