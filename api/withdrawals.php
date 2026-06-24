<?php
/**
 * Dealer Withdrawals CRUD API
 * Local DB table: dealer_withdrawals (id, dealer_name, amount, withdrawal_date, description, created_at)
 */
require_once __DIR__ . '/db.php';
header('Content-Type: application/json');

if (!$pdo) jsonResponse(['error' => 'Database connection failed'], 500);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $stmt = $pdo->query("SELECT * FROM dealer_withdrawals ORDER BY withdrawal_date DESC");
        $rows = $stmt->fetchAll();
        jsonResponse(['status' => 'success', 'data' => $rows]);
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) jsonResponse(['error' => 'Invalid JSON body'], 400);

        $stmt = $pdo->prepare("INSERT INTO dealer_withdrawals (dealer_name, amount, withdrawal_date, description) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $input['dealer_name'],
            $input['amount'],
            $input['withdrawal_date'],
            $input['description'] ?? ''
        ]);
        jsonResponse(['status' => 'success', 'id' => $pdo->lastInsertId()], 201);
        break;

    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['id'])) jsonResponse(['error' => 'Missing id'], 400);

        $stmt = $pdo->prepare("UPDATE dealer_withdrawals SET dealer_name=?, amount=?, withdrawal_date=?, description=? WHERE id=?");
        $stmt->execute([
            $input['dealer_name'],
            $input['amount'],
            $input['withdrawal_date'],
            $input['description'] ?? '',
            $input['id']
        ]);
        jsonResponse(['status' => 'success']);
        break;

    case 'DELETE':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['id'])) jsonResponse(['error' => 'Missing id'], 400);

        $stmt = $pdo->prepare("DELETE FROM dealer_withdrawals WHERE id=?");
        $stmt->execute([$input['id']]);
        jsonResponse(['status' => 'success']);
        break;

    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}
