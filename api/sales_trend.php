<?php
/**
 * Daily Sales Revenue Trend API
 * Returns dynamic aggregates for Chart.js
 */
require_once __DIR__ . '/db.php';
header('Content-Type: application/json');

$from = $_GET['from'] ?? null;
$to = $_GET['to'] ?? null;
$type = $_GET['type'] ?? null;
$companyId = isset($_GET['company_id']) && $_GET['company_id'] !== '' ? intval($_GET['company_id']) : null;
$productId = isset($_GET['product_id']) && $_GET['product_id'] !== '' ? intval($_GET['product_id']) : null;
$minAmount = isset($_GET['min_amount']) && $_GET['min_amount'] !== '' ? floatval($_GET['min_amount']) : null;
$maxAmount = isset($_GET['max_amount']) && $_GET['max_amount'] !== '' ? floatval($_GET['max_amount']) : null;

// Fetch CRM data (with caching)
$transactions = fetchCrmApi('transactions', 5000) ?? [];
$transaction_items = fetchCrmApi('transaction_items', 25000) ?? [];
$products = fetchCrmApi('products') ?? [];

// Map products for company lookup
$productMap = [];
foreach ($products as $p) {
    $productMap[$p['id']] = $p;
}

// Map items by transaction ID (noting DB schema typo: transection_id)
$itemsByTx = [];
foreach ($transaction_items as $item) {
    $txId = $item['transection_id'] ?? 0;
    if (!isset($itemsByTx[$txId])) {
        $itemsByTx[$txId] = [];
    }
    $itemsByTx[$txId][] = $item;
}

// Aggregate daily sales
$dailySales = [];
foreach ($transactions as $tx) {
    $txId = $tx['id'];
    $txDate = $tx['transaction_date'] ?? 'Unknown';
    $txType = $tx['transaction_type'] ?? 'out';

    // Apply type filter
    if ($type !== null && $type !== '' && $txType !== $type) {
        continue;
    }

    // Apply date filters
    if ($txDate !== 'Unknown') {
        if ($from && $txDate < $from) continue;
        if ($to && $txDate > $to) continue;
    } else {
        if ($from || $to) continue;
    }

    $txItems = $itemsByTx[$txId] ?? [];
    $totalValue = 0;
    $txCompanyIds = [];
    $txProductIds = [];

    foreach ($txItems as $item) {
        $pId = $item['product_id'] ?? 0;
        $qty = intval($item['out_qty'] ?? 0);
        $price = floatval($item['per_price'] ?? 0);
        $totalValue += $qty * $price;

        $txProductIds[] = $pId;
        $p = $productMap[$pId] ?? null;
        if ($p && isset($p['company_id'])) {
            $txCompanyIds[] = intval($p['company_id']);
        }
    }

    // Apply company filter
    if ($companyId !== null && !in_array($companyId, $txCompanyIds)) {
        continue;
    }

    // Apply product filter
    if ($productId !== null && !in_array($productId, $txProductIds)) {
        continue;
    }

    // Apply amount range filters
    if ($minAmount !== null && $totalValue < $minAmount) {
        continue;
    }
    if ($maxAmount !== null && $totalValue > $maxAmount) {
        continue;
    }

    if ($txDate !== 'Unknown') {
        if (!isset($dailySales[$txDate])) {
            $dailySales[$txDate] = 0;
        }
        $dailySales[$txDate] += $totalValue;
    }
}

// Sort chronologically
uksort($dailySales, function($a, $b) {
    return strcmp($a, $b);
});

// Format for Chart.js
$labels = [];
$values = [];
foreach ($dailySales as $date => $val) {
    $labels[] = date('j M y', strtotime($date));
    $values[] = round($val, 2);
}

jsonResponse([
    'status' => 'success',
    'data' => [
        'labels' => $labels,
        'values' => $values
    ]
]);
