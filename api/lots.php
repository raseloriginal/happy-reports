<?php
/**
 * Lots CRUD API
 * Local DB table: lots (id, company_id, lot_date, total_amount, description, created_at)
 */
require_once __DIR__ . '/db.php';
header('Content-Type: application/json');

if (!$pdo) jsonResponse(['error' => 'Database connection failed'], 500);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // If query param 'company_id' is set, filter by that company
        $companyId = isset($_GET['company_id']) ? intval($_GET['company_id']) : null;
        if ($companyId) {
            $stmt = $pdo->prepare("SELECT l.*, c.company_name 
                                 FROM lots l 
                                 LEFT JOIN companies c ON l.company_id = c.id 
                                 WHERE l.company_id = ?
                                 ORDER BY l.lot_date DESC");
            $stmt->execute([$companyId]);
        } else {
            $stmt = $pdo->query("SELECT l.*, c.company_name 
                                 FROM lots l 
                                 LEFT JOIN companies c ON l.company_id = c.id 
                                 ORDER BY l.lot_date DESC");
        }
        $rows = $stmt->fetchAll();
        jsonResponse(['status' => 'success', 'data' => $rows]);
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) jsonResponse(['error' => 'Invalid JSON body'], 400);

        if (!isset($input['company_id']) || !isset($input['lot_date']) || !isset($input['total_amount'])) {
            jsonResponse(['error' => 'Missing required fields'], 400);
        }

        $stmt = $pdo->prepare("INSERT INTO lots (company_id, lot_date, total_amount, description) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            intval($input['company_id']),
            $input['lot_date'],
            floatval($input['total_amount']),
            $input['description'] ?? ''
        ]);
        jsonResponse(['status' => 'success', 'id' => $pdo->lastInsertId()], 201);
        break;

    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['id'])) jsonResponse(['error' => 'Missing id'], 400);

        if (!isset($input['company_id']) || !isset($input['lot_date']) || !isset($input['total_amount'])) {
            jsonResponse(['error' => 'Missing required fields'], 400);
        }

        $stmt = $pdo->prepare("UPDATE lots SET company_id=?, lot_date=?, total_amount=?, description=? WHERE id=?");
        $stmt->execute([
            intval($input['company_id']),
            $input['lot_date'],
            floatval($input['total_amount']),
            $input['description'] ?? '',
            intval($input['id'])
        ]);
        jsonResponse(['status' => 'success']);
        break;

    case 'DELETE':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['id'])) jsonResponse(['error' => 'Missing id'], 400);

        $stmt = $pdo->prepare("DELETE FROM lots WHERE id=?");
        $stmt->execute([intval($input['id'])]);
        jsonResponse(['status' => 'success']);
        break;

    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}
