<?php
class Lot {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    // Add Lot
    public function addLot($data) {
        $this->db->query('INSERT INTO lots (crm_id, warehouse_crm_id, warehouse_name, company_crm_id, company_name, lot_value, lot_date) VALUES (:crm_id, :warehouse_crm_id, :warehouse_name, :company_crm_id, :company_name, :lot_value, :lot_date)');
        
        $this->db->bind(':crm_id', $data['crm_id']);
        $this->db->bind(':warehouse_crm_id', $data['warehouse_crm_id']);
        $this->db->bind(':warehouse_name', $data['warehouse_name']);
        $this->db->bind(':company_crm_id', $data['company_crm_id']);
        $this->db->bind(':company_name', $data['company_name']);
        $this->db->bind(':lot_value', $data['lot_value']);
        $this->db->bind(':lot_date', $data['lot_date']);

        return $this->db->execute();
    }

    // Get All Lots
    public function getLots() {
        $this->db->query('SELECT * FROM lots ORDER BY lot_date DESC');
        return $this->db->resultSet();
    }
    
    public function getTotalLotValue() {
        $this->db->query('SELECT SUM(lot_value) as total FROM lots');
        $result = $this->db->single();
        return $result->total ? $result->total : 0;
    }

    public function getInventoryStats() {
        $this->db->query('SELECT warehouse_name, COUNT(*) as lot_count, SUM(lot_value) as total_value FROM lots GROUP BY warehouse_crm_id, warehouse_name ORDER BY total_value DESC');
        return $this->db->resultSet();
    }

    public function getLotStatsByCompany() {
        $this->db->query('SELECT company_crm_id, company_name, SUM(lot_value) as total_lots FROM lots GROUP BY company_crm_id, company_name');
        return $this->db->resultSet();
    }
}
