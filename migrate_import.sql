-- Migration script for existing databases
-- Run this if you already have the tables and need to add new columns/tables

-- Add dealer fields to lots table
ALTER TABLE lots ADD COLUMN IF NOT EXISTS dealer_crm_id VARCHAR(50) AFTER company_name;
ALTER TABLE lots ADD COLUMN IF NOT EXISTS dealer_name VARCHAR(100) AFTER dealer_crm_id;

-- Add dealer/warehouse fields to transactions table
ALTER TABLE transactions ADD COLUMN IF NOT EXISTS dealer_crm_id VARCHAR(50) AFTER company_name;
ALTER TABLE transactions ADD COLUMN IF NOT EXISTS dealer_name VARCHAR(100) AFTER dealer_crm_id;
ALTER TABLE transactions ADD COLUMN IF NOT EXISTS warehouse_crm_id VARCHAR(50) AFTER dealer_name;
ALTER TABLE transactions ADD COLUMN IF NOT EXISTS warehouse_name VARCHAR(100) AFTER warehouse_crm_id;

-- Add item_crm_id to lot_items table
ALTER TABLE lot_items ADD COLUMN IF NOT EXISTS item_crm_id VARCHAR(50) AFTER lot_crm_id;

-- Create transaction_items table
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
