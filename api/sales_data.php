<?php
/**
 * Sales Data API
 * Aggregates transactions, items, products, and companies for async rendering
 */
require_once __DIR__ . '/db.php';
header('Content-Type: application/json');

if (!$pdo) jsonResponse(['error' => 'Database connection failed'], 500);

if (isset($_GET['refresh'])) {
    $caches = [
        'cache_transactions_5000_none.json',
        'cache_transaction_items_25000_none.json',
        'cache_products_all_none.json',
        'cache_companies_all_none.json'
    ];
    foreach ($caches as $c) {
        $path = __DIR__ . '/' . $c;
        if (file_exists($path)) {
            @unlink($path);
        }
    }
}

// Fetch CRM data (using caching)
$transactions = fetchCrmApi('transactions', 5000) ?? [];
$transaction_items = fetchCrmApi('transaction_items', 25000) ?? [];
$products = fetchCrmApi('products') ?? [];
$companies = fetchCrmApi('companies') ?? [];

// Map products for company lookup
$productMap = [];
foreach ($products as $p) {
    $productMap[$p['id']] = $p;
}

// Group transaction items by transaction ID
$itemsByTx = [];
foreach ($transaction_items as $item) {
    $txId = $item['transection_id'] ?? 0;
    if (!isset($itemsByTx[$txId])) {
        $itemsByTx[$txId] = [];
    }
    $itemsByTx[$txId][] = $item;
}

// Process transactions
$processedTransactions = [];
$totalSalesValue = 0;
$totalItemsSold = 0;
$productSalesQty = [];

foreach ($transactions as $tx) {
    $txId = $tx['id'];
    $txDate = $tx['transaction_date'] ?? 'Unknown';
    $txType = $tx['transaction_type'] ?? 'out';
    
    $txItems = $itemsByTx[$txId] ?? [];
    $totalValue = 0;
    $totalQty = 0;
    
    $itemsDetails = [];
    $txCompanyIds = [];
    $txProductIds = [];

    foreach ($txItems as $item) {
        $pId = $item['product_id'] ?? 0;
        $qty = intval($item['out_qty'] ?? 0);
        $price = floatval($item['per_price'] ?? 0);
        
        $itemTotal = $qty * $price;
        $totalValue += $itemTotal;
        $totalQty += $qty;
        
        $pName = $productMap[$pId]['product_name'] ?? 'Product #' . $pId;
        $itemsDetails[] = [
            'name' => $pName,
            'qty' => $qty,
            'price' => $price,
            'total' => $itemTotal
        ];

        $txProductIds[] = $pId;
        $p = $productMap[$pId] ?? null;
        if ($p && isset($p['company_id'])) {
            $txCompanyIds[] = intval($p['company_id']);
        }

        if ($qty > 0) {
            if (!isset($productSalesQty[$pName])) {
                $productSalesQty[$pName] = 0;
            }
            $productSalesQty[$pName] += $qty;
        }
    }
    
    $processedTransactions[] = [
        'id' => intval($txId),
        'date' => $txDate,
        'type' => $txType,
        'items_count' => count($txItems),
        'total_qty' => $totalQty,
        'total_value' => $totalValue,
        'companies' => array_values(array_unique($txCompanyIds)),
        'products' => array_values(array_unique($txProductIds)),
        'items' => $itemsDetails
    ];

    $totalSalesValue += $totalValue;
    $totalItemsSold += $totalQty;
}

// Sort transactions descending (newest first)
usort($processedTransactions, function($a, $b) {
    $dateCompare = strcmp($b['date'], $a['date']);
    if ($dateCompare === 0) {
        return $b['id'] - $a['id'];
    }
    return $dateCompare;
});

// Get top 5 products by quantity
arsort($productSalesQty);
$topProducts = [];
$topProductsSlice = array_slice($productSalesQty, 0, 5, true);
foreach ($topProductsSlice as $name => $qty) {
    $topProducts[] = [
        'name' => $name,
        'qty' => $qty
    ];
}

jsonResponse([
    'status' => 'success',
    'data' => [
        'transactions' => $processedTransactions,
        'companies' => $companies,
        'products' => $products,
        'top_products' => $topProducts,
        'summary' => [
            'total_revenue' => $totalSalesValue,
            'total_items_sold' => $totalItemsSold,
            'avg_ticket' => count($processedTransactions) > 0 ? $totalSalesValue / count($processedTransactions) : 0,
            'tx_count' => count($processedTransactions)
        ]
    ]
]);
