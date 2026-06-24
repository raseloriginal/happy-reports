<?php
/**
 * Reports API - returns filtered data for charts and tables
 * Supports ?type=deposits|withdrawals|all
 *          ?from=YYYY-MM-DD  &to=YYYY-MM-DD
 *          ?dataset=monthly|ledger|all
 */
require_once __DIR__ . '/db.php';
header('Content-Type: application/json');

if (!$pdo) jsonResponse(['error' => 'Database connection failed'], 500);

$type = $_GET['type'] ?? 'all';
$from = $_GET['from'] ?? date('Y-01-01');
$to   = $_GET['to']   ?? date('Y-m-d');
$dataset = $_GET['dataset'] ?? 'all';

$result = [];
$monthly = [];

// ─── Local DB data (Ledger) ───────────────────────────────────
if ($dataset === 'all' || $dataset === 'ledger') {
    if ($type === 'all' || $type === 'deposits') {
        $stmt = $pdo->prepare("SELECT id, operation_date as date, amount, note as description, 'deposit' as category FROM deposits WHERE operation_date BETWEEN ? AND ? ORDER BY operation_date DESC");
        $stmt->execute([$from, $to]);
        $result['deposits'] = $stmt->fetchAll();
    }
    if ($type === 'all' || $type === 'withdrawals') {
        $stmt = $pdo->prepare("SELECT id, withdrawal_date as date, amount, dealer_name, description, 'withdrawal' as category FROM dealer_withdrawals WHERE withdrawal_date BETWEEN ? AND ? ORDER BY withdrawal_date DESC");
        $stmt->execute([$from, $to]);
        $result['withdrawals'] = $stmt->fetchAll();
    }

}

// ─── Monthly aggregations ────────────────────────────────────
if ($dataset === 'all' || $dataset === 'monthly') {
    if ($type === 'all' || $type === 'deposits') {
        $stmt = $pdo->prepare("SELECT DATE_FORMAT(operation_date, '%Y-%m') as month, SUM(amount) as total FROM deposits WHERE operation_date BETWEEN ? AND ? GROUP BY month ORDER BY month");
        $stmt->execute([$from, $to]);
        foreach ($stmt->fetchAll() as $row) {
            $monthly[$row['month']]['deposits'] = floatval($row['total']);
        }
    }
    if ($type === 'all' || $type === 'withdrawals') {
        $stmt = $pdo->prepare("SELECT DATE_FORMAT(withdrawal_date, '%Y-%m') as month, SUM(amount) as total FROM dealer_withdrawals WHERE withdrawal_date BETWEEN ? AND ? GROUP BY month ORDER BY month");
        $stmt->execute([$from, $to]);
        foreach ($stmt->fetchAll() as $row) {
            $monthly[$row['month']]['withdrawals'] = floatval($row['total']);
        }
    }

}

jsonResponse([
    'status' => 'success',
    'filters' => ['type' => $type, 'from' => $from, 'to' => $to, 'dataset' => $dataset],
    'monthly' => $monthly,
    'records' => $result
]);
