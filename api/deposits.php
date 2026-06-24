<?php
/**
 * Deposits CRUD API
 * Local DB table: deposits (id, operation_date, company_id, amount, note, created_at, updated_at)
 */
require_once __DIR__ . '/db.php';
header('Content-Type: application/json');

if (!$pdo) jsonResponse(['error' => 'Database connection failed'], 500);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $stmt = $pdo->query("SELECT d.*, c.company_name 
                             FROM deposits d 
                             LEFT JOIN companies c ON d.company_id = c.id 
                             ORDER BY d.operation_date DESC");
        // Try local first, fallback to CRM API
        $rows = $stmt->fetchAll();
        if (empty($rows)) {
            $apiData = fetchCrmApi('deposits');
            if ($apiData) jsonResponse(['status' => 'success', 'source' => 'api', 'data' => $apiData]);
        }
        jsonResponse(['status' => 'success', 'source' => 'local', 'data' => $rows]);
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) jsonResponse(['error' => 'Invalid JSON body'], 400);

        $stmt = $pdo->prepare("INSERT INTO deposits (operation_date, company_id, amount, note) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $input['operation_date'],
            $input['company_id'] ?? 1,
            $input['amount'],
            $input['note'] ?? ''
        ]);
        jsonResponse(['status' => 'success', 'id' => $pdo->lastInsertId()], 201);
        break;

    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['id'])) jsonResponse(['error' => 'Missing id'], 400);

        $stmt = $pdo->prepare("UPDATE deposits SET operation_date=?, company_id=?, amount=?, note=? WHERE id=?");
        $stmt->execute([
            $input['operation_date'],
            $input['company_id'] ?? 1,
            $input['amount'],
            $input['note'] ?? '',
            $input['id']
        ]);
        jsonResponse(['status' => 'success']);
        break;

    case 'DELETE':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['id'])) jsonResponse(['error' => 'Missing id'], 400);

        $stmt = $pdo->prepare("DELETE FROM deposits WHERE id=?");
        $stmt->execute([$input['id']]);
        jsonResponse(['status' => 'success']);
        break;

    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}
