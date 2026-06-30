<?php
class Ledger extends Controller {
    private $lotModel;
    private $depositModel;

    public function __construct() {
        $this->lotModel = $this->model('Lot');
        $this->depositModel = $this->model('Deposit');
    }

    public function index() {
        $lots = $this->lotModel->getLots();
        $deposits = $this->depositModel->getDeposits();
        
        $data = [
            'lots' => $lots,
            'deposits' => $deposits
        ];
        
        $this->view('ledger/index', $data);
    }
}
