<?php
class LotItem {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    // Add Lot Item
    public function addLotItem($data) {
        $this->db->query('INSERT INTO lot_items (lot_crm_id, item_crm_id, item_name, item_qty, item_price) VALUES (:lot_crm_id, :item_crm_id, :item_name, :item_qty, :item_price)');
        
        $this->db->bind(':lot_crm_id', $data['lot_crm_id']);
        $this->db->bind(':item_crm_id', $data['item_crm_id'] ?? '');
        $this->db->bind(':item_name', $data['item_name']);
        $this->db->bind(':item_qty', $data['item_qty'] ?? 1);
        $this->db->bind(':item_price', $data['item_price']);

        return $this->db->execute();
    }

    // Get Items by Lot CRM ID
    public function getItemsByLotCrmId($lot_crm_id) {
        $this->db->query('SELECT * FROM lot_items WHERE lot_crm_id = :lot_crm_id');
        $this->db->bind(':lot_crm_id', $lot_crm_id);
        return $this->db->resultSet();
    }

    // Delete items by Lot CRM ID
    public function deleteItemsByLotCrmId($lot_crm_id) {
        $this->db->query('DELETE FROM lot_items WHERE lot_crm_id = :lot_crm_id');
        $this->db->bind(':lot_crm_id', $lot_crm_id);
        return $this->db->execute();
    }
}
