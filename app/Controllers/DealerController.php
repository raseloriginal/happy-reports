<?php
class Dealer extends Controller {
    private $dealerPaymentModel;
    
    public function __construct() {
        $this->dealerPaymentModel = $this->model('DealerPayment');
    }

    public function index() {
        $payments = $this->dealerPaymentModel->getPayments();
        $dealerStats = $this->dealerPaymentModel->getDealerStats();
        
        $data = [
            'payments' => $payments,
            'dealerStats' => $dealerStats
        ];

        $this->view('dealers/details', $data);
    }
}
