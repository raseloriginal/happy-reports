<?php
class Inventory extends Controller {
    private $lotModel;
    
    public function __construct() {
        $this->lotModel = $this->model('Lot');
    }

    public function index() {
        // Fetch local warehouse inventory stats from Lots
        $warehouseStats = $this->lotModel->getInventoryStats();
        
        // Fetch SKU and product details from CRM API
        // For testing/fallback if API is unreachable, we mock the detailed SKU response.
        $context = stream_context_create(['http' => ['timeout' => 3]]);
        $apiUrl = 'https://happycrm.site/happyreports_api/index.php?table=inventory';
        $apiResponse = @file_get_contents($apiUrl, false, $context);
        
        $apiData = [];
        if ($apiResponse) {
            $parsed = json_decode($apiResponse, true);
            if ($parsed && isset($parsed['status']) && $parsed['status'] === 'success') {
                $apiData = $parsed['data'];
            }
        }
        
        if (empty($apiData)) {
            // Mock Data for Visuals (total sku, values, top selling products by qty, revenue value, profit value)
            $apiData = [
                'total_skus' => 1245,
                'total_inventory_value' => 840500.00,
                'top_products' => [
                    ['name' => 'Premium Widget A', 'qty' => 450, 'revenue' => 45000, 'profit' => 15000],
                    ['name' => 'Standard Gadget B', 'qty' => 820, 'revenue' => 32800, 'profit' => 8200],
                    ['name' => 'Industrial Gizmo C', 'qty' => 150, 'revenue' => 75000, 'profit' => 25000],
                    ['name' => 'Eco-Friendly Pack D', 'qty' => 1100, 'revenue' => 22000, 'profit' => 11000],
                ]
            ];
        }

        $data = [
            'warehouseStats' => $warehouseStats,
            'apiData' => $apiData
        ];

        $this->view('inventory/index', $data);
    }
}
