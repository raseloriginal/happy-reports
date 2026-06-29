<?php
class Transaction {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function addTransaction($data) {
        $this->db->query('INSERT INTO transactions (crm_ids, company_crm_id, company_name, total_out_value, total_in_value, transaction_date) VALUES (:crm_ids, :company_crm_id, :company_name, :total_out_value, :total_in_value, :transaction_date)');
        
        $this->db->bind(':crm_ids', $data['crm_ids']);
        $this->db->bind(':company_crm_id', $data['company_crm_id']);
        $this->db->bind(':company_name', $data['company_name']);
        $this->db->bind(':total_out_value', $data['total_out_value']);
        $this->db->bind(':total_in_value', $data['total_in_value']);
        $this->db->bind(':transaction_date', $data['transaction_date']);

        return $this->db->execute();
    }

    public function getTransactions() {
        $this->db->query('SELECT * FROM transactions ORDER BY transaction_date DESC');
        return $this->db->resultSet();
    }

    public function getTotalRevenue() {
        $this->db->query('SELECT SUM(total_in_value) as revenue FROM transactions');
        $result = $this->db->single();
        return $result->revenue ? $result->revenue : 0;
    }

    public function getCompanyStats() {
        $this->db->query('SELECT company_crm_id, company_name, SUM(total_in_value) as total_in, SUM(total_out_value) as total_out FROM transactions GROUP BY company_crm_id, company_name');
        return $this->db->resultSet();
    }
}
