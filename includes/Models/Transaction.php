<?php
class Transaction {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function addTransaction($data) {
        $this->db->query('INSERT INTO transactions (crm_ids, company_crm_id, company_name, dealer_crm_id, dealer_name, warehouse_crm_id, warehouse_name, total_out_value, total_in_value, transaction_date) VALUES (:crm_ids, :company_crm_id, :company_name, :dealer_crm_id, :dealer_name, :warehouse_crm_id, :warehouse_name, :total_out_value, :total_in_value, :transaction_date)');
        
        $this->db->bind(':crm_ids', $data['crm_ids'] ?? '');
        $this->db->bind(':company_crm_id', $data['company_crm_id']);
        $this->db->bind(':company_name', $data['company_name']);
        $this->db->bind(':dealer_crm_id', $data['dealer_crm_id'] ?? '');
        $this->db->bind(':dealer_name', $data['dealer_name'] ?? '');
        $this->db->bind(':warehouse_crm_id', $data['warehouse_crm_id'] ?? '');
        $this->db->bind(':warehouse_name', $data['warehouse_name'] ?? '');
        $this->db->bind(':total_out_value', $data['total_out_value']);
        $this->db->bind(':total_in_value', $data['total_in_value']);
        $this->db->bind(':transaction_date', $data['transaction_date']);

        return $this->db->execute();
    }

    // Returns the last inserted ID
    public function getLastInsertId() {
        $this->db->query('SELECT LAST_INSERT_ID() as last_id');
        $result = $this->db->single();
        return $result->last_id;
    }

    // Update a single Transaction by ID (partial — only touches the specified row)
    public function updateTransaction($id, $data) {
        $this->db->query('UPDATE transactions SET crm_ids = :crm_ids, company_crm_id = :company_crm_id, company_name = :company_name, total_out_value = :total_out_value, total_in_value = :total_in_value, transaction_date = :transaction_date WHERE id = :id');
        $this->db->bind(':id', intval($id));
        $this->db->bind(':crm_ids', $data['crm_ids']);
        $this->db->bind(':company_crm_id', $data['company_crm_id']);
        $this->db->bind(':company_name', $data['company_name']);
        $this->db->bind(':total_out_value', $data['total_out_value']);
        $this->db->bind(':total_in_value', $data['total_in_value']);
        $this->db->bind(':transaction_date', $data['transaction_date']);
        return $this->db->execute();
    }

    // Delete a single Transaction by ID (partial — does NOT affect other rows)
    public function deleteTransaction($id) {
        $this->db->query('DELETE FROM transactions WHERE id = :id');
        $this->db->bind(':id', intval($id));
        return $this->db->execute();
    }

    public function getTransactions() {
        $this->db->query('
            SELECT t.*, 
                   (SELECT SUM(ti.item_sell_value) 
                    FROM transaction_items ti 
                    WHERE ti.transaction_id = t.id AND ti.item_out_qty > 0) AS total_sale_value
            FROM transactions t 
            ORDER BY t.transaction_date DESC
        ');
        return $this->db->resultSet();
    }

    public function getTotalRevenue() {
        $this->db->query('SELECT SUM(total_in_value) as revenue FROM transactions');
        $result = $this->db->single();
        return $result->revenue ? $result->revenue : 0;
    }

    // Total value of goods sold out — used for Floor Stock calculation
    public function getTotalSalesOutValue() {
        $this->db->query('SELECT SUM(total_out_value) as total FROM transactions');
        $result = $this->db->single();
        return $result->total ? $result->total : 0;
    }

    public function getCompanyStats() {
        $this->db->query('SELECT company_crm_id, company_name, SUM(total_in_value) as total_in, SUM(total_out_value) as total_out FROM transactions GROUP BY company_crm_id, company_name');
        return $this->db->resultSet();
    }
}
