CREATE DATABASE IF NOT EXISTS happy_reports;
USE happy_reports;

CREATE TABLE IF NOT EXISTS lots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    crm_id VARCHAR(50) NOT NULL,
    warehouse_crm_id VARCHAR(50),
    warehouse_name VARCHAR(100),
    company_crm_id VARCHAR(50),
    company_name VARCHAR(100),
    dealer_crm_id VARCHAR(50),
    dealer_name VARCHAR(100),
    lot_value DECIMAL(15, 2) NOT NULL,
    lot_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    crm_ids VARCHAR(255), -- Comma separated IDs
    company_crm_id VARCHAR(50),
    company_name VARCHAR(100),
    dealer_crm_id VARCHAR(50),
    dealer_name VARCHAR(100),
    warehouse_crm_id VARCHAR(50),
    warehouse_name VARCHAR(100),
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

CREATE TABLE IF NOT EXISTS lot_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lot_crm_id VARCHAR(50) NOT NULL,
    item_crm_id VARCHAR(50),
    item_name VARCHAR(255) NOT NULL,
    item_qty INT NOT NULL DEFAULT 1,
    item_price DECIMAL(15, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS transaction_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    item_crm_id VARCHAR(50),
    item_name VARCHAR(255) NOT NULL,
    item_out_qty INT DEFAULT 0,
    item_in_qty INT DEFAULT 0,
    item_sell_qty INT DEFAULT 0,
    item_out_value DECIMAL(15, 2) DEFAULT 0.00,
    item_in_value DECIMAL(15, 2) DEFAULT 0.00,
    item_sell_value DECIMAL(15, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE
);
