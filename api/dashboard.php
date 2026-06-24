<?php
/**
 * Dashboard Summary API
 * Combines local DB aggregates + CRM API data into one response
 */
require_once __DIR__ . '/db.php';
header('Content-Type: application/json');

$summary = [
    'local' => [
        'total_deposits' => 0,
        'total_withdrawals' => 0,
        'net_balance' => 0,
        'deposit_count' => 0,
        'withdrawal_count' => 0,
    ],
    'crm' => [
        'total_dealers' => 0,
        'total_products' => 0,
        'total_companies' => 0,
        'total_transactions' => 0,
        'total_dsrs' => 0,
    ],
    'recent_deposits' => [],
    'recent_withdrawals' => [],
];

// ─── Local DB summaries ──────────────────────────────────────
if ($pdo) {
    try {
        $r = $pdo->query("SELECT COALESCE(SUM(amount),0) as total, COUNT(*) as cnt FROM deposits")->fetch();
        $summary['local']['total_deposits'] = floatval($r['total']);
        $summary['local']['deposit_count'] = intval($r['cnt']);

        $r = $pdo->query("SELECT COALESCE(SUM(amount),0) as total, COUNT(*) as cnt FROM dealer_withdrawals")->fetch();
        $summary['local']['total_withdrawals'] = floatval($r['total']);
        $summary['local']['withdrawal_count'] = intval($r['cnt']);

        $summary['local']['net_balance'] = $summary['local']['total_deposits'] - $summary['local']['total_withdrawals'];

        $summary['local']['lot_count'] = intval($pdo->query("SELECT COUNT(*) FROM lots")->fetchColumn() ?: 0);

        // Recent items (last 5)
        $summary['recent_deposits'] = $pdo->query("SELECT * FROM deposits ORDER BY operation_date DESC LIMIT 5")->fetchAll();
        $summary['recent_withdrawals'] = $pdo->query("SELECT * FROM dealer_withdrawals ORDER BY withdrawal_date DESC LIMIT 5")->fetchAll();
    } catch (Exception $e) {
        // continue even if local DB fails
    }
}

// ─── CRM API Data (For metrics & charts) ────────────────────────
$summary['crm_data'] = [];

// Fetch standard entities for counts
$dealers = fetchCrmApi('dealers');
if ($dealers !== null) {
    $summary['crm']['total_dealers'] = count($dealers);
    $summary['crm_data']['dealers'] = $dealers;
}

$products = fetchCrmApi('products');
if ($products !== null) {
    $summary['crm']['total_products'] = count($products);
    $summary['crm_data']['products'] = $products;
}

$companies = fetchCrmApi('companies');
if ($companies !== null) {
    $summary['crm']['total_companies'] = count($companies);
    $summary['crm_data']['companies'] = $companies;
}

$transactions = fetchCrmApi('transactions', 2000);
if ($transactions !== null) {
    $summary['crm']['total_transactions'] = count($transactions);
    $summary['crm_data']['transactions'] = $transactions;
}

$dsrs = fetchCrmApi('dsrs');
if ($dsrs !== null) {
    $summary['crm']['total_dsrs'] = count($dsrs);
    $summary['crm_data']['dsrs'] = $dsrs;
}

// Additional data for new charts
$categories = fetchCrmApi('product_category');
if ($categories !== null) $summary['crm_data']['categories'] = $categories;

$scanLogs = fetchCrmApi('scan_logs', 1000);
if ($scanLogs !== null) $summary['crm_data']['scan_logs'] = $scanLogs;

$transactionActivity = fetchCrmApi('transaction_activity', 1000);
if ($transactionActivity !== null) $summary['crm_data']['transaction_activity'] = $transactionActivity;

$damage = fetchCrmApi('damage', 1000);
if ($damage !== null) $summary['crm_data']['damage'] = $damage;

$salesReps = fetchCrmApi('sales_rep');
if ($salesReps !== null) $summary['crm']['total_sales_reps'] = count($salesReps);
jsonResponse(['status' => 'success', 'data' => $summary]);
