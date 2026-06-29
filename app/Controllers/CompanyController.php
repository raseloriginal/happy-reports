<?php
class Company extends Controller {
    private $transactionModel;
    private $depositModel;
    private $lotModel;
    
    public function __construct() {
        $this->transactionModel = $this->model('Transaction');
        $this->depositModel = $this->model('Deposit');
        $this->lotModel = $this->model('Lot');
    }

    public function index() {
        $companyStats = $this->transactionModel->getCompanyStats();
        $transactions = $this->transactionModel->getTransactions();
        $deposits = $this->depositModel->getDeposits();
        
        $lotStats = $this->lotModel->getLotStatsByCompany();
        $depositStats = $this->depositModel->getDepositStatsByCompany();

        $combinedStats = [];
        
        // Helper function to initialize company if not exists
        $initCompany = function($id, $name) use (&$combinedStats) {
            if (!isset($combinedStats[$id])) {
                $combinedStats[$id] = [
                    'company_name' => $name,
                    'total_in' => 0,
                    'total_out' => 0,
                    'total_lots' => 0,
                    'total_deposits' => 0
                ];
            }
        };

        foreach ($companyStats as $cs) {
            $initCompany($cs->company_crm_id, $cs->company_name);
            $combinedStats[$cs->company_crm_id]['total_in'] = $cs->total_in;
            $combinedStats[$cs->company_crm_id]['total_out'] = $cs->total_out;
        }

        foreach ($lotStats as $ls) {
            $initCompany($ls->company_crm_id, $ls->company_name);
            $combinedStats[$ls->company_crm_id]['total_lots'] = $ls->total_lots;
        }

        foreach ($depositStats as $ds) {
            $initCompany($ds->company_crm_id, $ds->company_name);
            $combinedStats[$ds->company_crm_id]['total_deposits'] = $ds->total_deposits;
        }
        
        $data = [
            'combinedStats' => $combinedStats,
            'transactions' => $transactions,
            'deposits' => $deposits
        ];

        $this->view('company/transactions', $data);
    }
}
