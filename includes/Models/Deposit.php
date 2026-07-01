<?php
class Deposit {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function addDeposit($data) {
        $this->db->query('INSERT INTO deposits (company_crm_id, company_name, amount, deposit_date) VALUES (:company_crm_id, :company_name, :amount, :deposit_date)');
        
        $this->db->bind(':company_crm_id', $data['company_crm_id']);
        $this->db->bind(':company_name', $data['company_name']);
        $this->db->bind(':amount', $data['amount']);
        $this->db->bind(':deposit_date', $data['deposit_date']);

        return $this->db->execute();
    }

    // Update a single Deposit by ID (partial — only touches the specified row)
    public function updateDeposit($id, $data) {
        $this->db->query('UPDATE deposits SET company_crm_id = :company_crm_id, company_name = :company_name, amount = :amount, deposit_date = :deposit_date WHERE id = :id');
        $this->db->bind(':id', intval($id));
        $this->db->bind(':company_crm_id', $data['company_crm_id']);
        $this->db->bind(':company_name', $data['company_name']);
        $this->db->bind(':amount', $data['amount']);
        $this->db->bind(':deposit_date', $data['deposit_date']);
        return $this->db->execute();
    }

    // Delete a single Deposit by ID (partial — does NOT affect other rows)
    public function deleteDeposit($id) {
        $this->db->query('DELETE FROM deposits WHERE id = :id');
        $this->db->bind(':id', intval($id));
        return $this->db->execute();
    }

    public function getDeposits() {
        $this->db->query('SELECT * FROM deposits ORDER BY deposit_date DESC');
        return $this->db->resultSet();
    }

    public function getTotalDeposits() {
        $this->db->query('SELECT SUM(amount) as total FROM deposits');
        $result = $this->db->single();
        return $result->total ? $result->total : 0;
    }

    public function getDepositStatsByCompany() {
        $this->db->query('SELECT company_crm_id, company_name, SUM(amount) as total_deposits FROM deposits GROUP BY company_crm_id, company_name');
        return $this->db->resultSet();
    }
}

