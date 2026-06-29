<?php
class Import extends Controller {
    private $lotModel;
    private $transactionModel;
    private $depositModel;
    private $dealerPaymentModel;
    
    public function __construct() {
        $this->lotModel = $this->model('Lot');
        $this->transactionModel = $this->model('Transaction');
        $this->depositModel = $this->model('Deposit');
        $this->dealerPaymentModel = $this->model('DealerPayment');
    }

    public function index() {
        $this->view('import/index');
    }

    public function uploadLots() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['lots_csv'])) {
            $file = $_FILES['lots_csv']['tmp_name'];
            if (($handle = fopen($file, "r")) !== FALSE) {
                fgetcsv($handle); // Skip header
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $lotData = [
                        'crm_id' => $data[0],
                        'warehouse_crm_id' => $data[1],
                        'warehouse_name' => $data[2],
                        'company_crm_id' => $data[3],
                        'company_name' => $data[4],
                        'lot_value' => floatval($data[5]),
                        'lot_date' => date('Y-m-d', strtotime($data[6]))
                    ];
                    $this->lotModel->addLot($lotData);
                }
                fclose($handle);
                header('Location: ' . URLROOT . '/dashboard');
            }
        }
    }

    public function uploadTransactions() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['transactions_csv'])) {
            $file = $_FILES['transactions_csv']['tmp_name'];
            if (($handle = fopen($file, "r")) !== FALSE) {
                fgetcsv($handle); // Skip header
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $transData = [
                        'crm_ids' => $data[0],
                        'company_crm_id' => $data[1],
                        'company_name' => $data[2],
                        'total_out_value' => floatval($data[3]),
                        'total_in_value' => floatval($data[4]),
                        'transaction_date' => date('Y-m-d', strtotime($data[5]))
                    ];
                    $this->transactionModel->addTransaction($transData);
                }
                fclose($handle);
                header('Location: ' . URLROOT . '/company');
            }
        }
    }

    public function addDeposit() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['company_crm_id']) && is_array($_POST['company_crm_id'])) {
                $count = count($_POST['company_crm_id']);
                for ($i = 0; $i < $count; $i++) {
                    if (!empty($_POST['company_crm_id'][$i]) && !empty($_POST['amount'][$i])) {
                        $data = [
                            'company_crm_id' => trim($_POST['company_crm_id'][$i]),
                            'company_name' => trim($_POST['company_name'][$i]),
                            'amount' => floatval($_POST['amount'][$i]),
                            'deposit_date' => trim($_POST['deposit_date'][$i])
                        ];
                        $this->depositModel->addDeposit($data);
                    }
                }
            } else {
                // Fallback for single deposit just in case
                $data = [
                    'company_crm_id' => trim($_POST['company_crm_id']),
                    'company_name' => trim($_POST['company_name']),
                    'amount' => floatval($_POST['amount']),
                    'deposit_date' => trim($_POST['deposit_date'])
                ];
                $this->depositModel->addDeposit($data);
            }
            header('Location: ' . URLROOT . '/company');
        }
    }

    public function addPayment() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'dealer_crm_id' => trim($_POST['dealer_crm_id']),
                'dealer_name' => trim($_POST['dealer_name']),
                'amount' => floatval($_POST['amount']),
                'payment_date' => trim($_POST['payment_date'])
            ];
            $this->dealerPaymentModel->addPayment($data);
            header('Location: ' . URLROOT . '/dealer');
        }
    }
}
