<?php
class Inventory extends Controller {
    private $transactionModel;

    public function __construct() {
        $this->transactionModel = $this->model('Transaction');
    }

    public function index() {
        $transactions = $this->transactionModel->getTransactions();
        $data = [
            'transactions' => $transactions
        ];
        $this->view('inventory/index', $data);
    }
}
