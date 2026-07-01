<?php
class DealerPayment {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function addPayment($data) {
        $this->db->query('INSERT INTO dealer_payments (dealer_crm_id, dealer_name, amount, payment_date) VALUES (:dealer_crm_id, :dealer_name, :amount, :payment_date)');
        
        $this->db->bind(':dealer_crm_id', $data['dealer_crm_id']);
        $this->db->bind(':dealer_name', $data['dealer_name']);
        $this->db->bind(':amount', $data['amount']);
        $this->db->bind(':payment_date', $data['payment_date']);

        return $this->db->execute();
    }

    public function getPayments() {
        $this->db->query('SELECT * FROM dealer_payments ORDER BY payment_date DESC');
        return $this->db->resultSet();
    }

    public function getTotalPayments() {
        $this->db->query('SELECT SUM(amount) as total FROM dealer_payments');
        $result = $this->db->single();
        return $result->total ? $result->total : 0;
    }

    public function getDealerStats() {
        $this->db->query('SELECT dealer_name, SUM(amount) as total_paid FROM dealer_payments GROUP BY dealer_crm_id, dealer_name');
        return $this->db->resultSet();
    }
}
