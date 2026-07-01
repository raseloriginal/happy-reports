<?php
class TransactionItem {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    // Add single transaction item
    public function addTransactionItem($data) {
        $this->db->query('INSERT INTO transaction_items (transaction_id, item_crm_id, item_name, item_out_qty, item_in_qty, item_sell_qty, item_out_value, item_in_value, item_sell_value) VALUES (:transaction_id, :item_crm_id, :item_name, :item_out_qty, :item_in_qty, :item_sell_qty, :item_out_value, :item_in_value, :item_sell_value)');
        
        $this->db->bind(':transaction_id', $data['transaction_id']);
        $this->db->bind(':item_crm_id', $data['item_crm_id'] ?? '');
        $this->db->bind(':item_name', $data['item_name']);
        $this->db->bind(':item_out_qty', $data['item_out_qty'] ?? 0);
        $this->db->bind(':item_in_qty', $data['item_in_qty'] ?? 0);
        $this->db->bind(':item_sell_qty', $data['item_sell_qty'] ?? 0);
        $this->db->bind(':item_out_value', $data['item_out_value'] ?? 0);
        $this->db->bind(':item_in_value', $data['item_in_value'] ?? 0);
        $this->db->bind(':item_sell_value', $data['item_sell_value'] ?? 0);

        return $this->db->execute();
    }

    // Get items by transaction ID with dynamically calculated current stock
    public function getItemsByTransactionId($transaction_id) {
        $this->db->query('
            SELECT ti.*,
                   (
                       (SELECT COALESCE(SUM(li.item_qty), 0) FROM lot_items li WHERE li.item_crm_id = ti.item_crm_id) - 
                       (SELECT COALESCE(SUM(t2.item_out_qty), 0) FROM transaction_items t2 WHERE t2.item_crm_id = ti.item_crm_id)
                   ) AS current_stock
            FROM transaction_items ti 
            WHERE ti.transaction_id = :transaction_id
        ');
        $this->db->bind(':transaction_id', $transaction_id);
        return $this->db->resultSet();
    }

    // Delete items by transaction ID
    public function deleteItemsByTransactionId($transaction_id) {
        $this->db->query('DELETE FROM transaction_items WHERE transaction_id = :transaction_id');
        $this->db->bind(':transaction_id', $transaction_id);
        return $this->db->execute();
    }
}
