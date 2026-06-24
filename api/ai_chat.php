<?php
/**
 * AI Chat API - Ultra Token-Efficient
 * 
 * Features:
 *  - Chat history stored in DB
 *  - Loads last N messages for conversation context
 *  - Smart CRM API data injection based on user question
 *  - Strict system prompt, max 200 output tokens
 */
require_once __DIR__ . '/db.php';
header('Content-Type: application/json');

if (!$pdo) jsonResponse(['error' => 'Database connection failed'], 500);

$method = $_SERVER['REQUEST_METHOD'];

// ─── GET: Load chat history ──────────────────────────────────
if ($method === 'GET') {
    $limit = intval($_GET['limit'] ?? 50);
    $stmt = $pdo->prepare("SELECT id, role, message, tokens_used, created_at FROM chat_history ORDER BY id DESC LIMIT ?");
    $stmt->execute([$limit]);
    $rows = array_reverse($stmt->fetchAll());
    jsonResponse(['status' => 'success', 'data' => $rows]);
}

// ─── DELETE: Clear chat history ──────────────────────────────
if ($method === 'DELETE') {
    $pdo->exec("DELETE FROM chat_history");
    jsonResponse(['status' => 'success']);
}

if ($method !== 'POST') {
    jsonResponse(['error' => 'POST, GET, or DELETE only'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$userMessage = trim($input['message'] ?? '');

if (empty($userMessage)) {
    jsonResponse(['error' => 'Empty message'], 400);
}

// ─── Save user message to DB ─────────────────────────────────
$stmt = $pdo->prepare("INSERT INTO chat_history (role, message) VALUES ('user', ?)");
$stmt->execute([$userMessage]);

// ─── Build data context ──────────────────────────────────────
$ctx = [];

// Local DB aggregates
try {
    $r = $pdo->query("SELECT COALESCE(SUM(amount),0) as t, COUNT(*) as c FROM deposits")->fetch();
    $ctx['dep'] = ['sum' => floatval($r['t']), 'cnt' => intval($r['c'])];

    $r = $pdo->query("SELECT COALESCE(SUM(amount),0) as t, COUNT(*) as c FROM dealer_withdrawals")->fetch();
    $ctx['wdr'] = ['sum' => floatval($r['t']), 'cnt' => intval($r['c'])];

    $ctx['bal'] = $ctx['dep']['sum'] - $ctx['wdr']['sum'];

    // Last 5 of each
    $ctx['last_dep'] = $pdo->query("SELECT operation_date as d, amount as a, note as n FROM deposits ORDER BY operation_date DESC LIMIT 5")->fetchAll();
    $ctx['last_wdr'] = $pdo->query("SELECT withdrawal_date as d, dealer_name as dn, amount as a FROM dealer_withdrawals ORDER BY withdrawal_date DESC LIMIT 5")->fetchAll();
} catch (Exception $e) {
    $ctx['db_error'] = true;
}

// ─── Smart injection: no longer fetching CRM data ────────
$msgLower = mb_strtolower($userMessage);

// Compact JSON
$contextJson = json_encode($ctx, JSON_UNESCAPED_UNICODE);

// ─── Load last 6 messages for conversation context ───────────
$historyMessages = [];
$historyRows = $pdo->query("SELECT role, message FROM (SELECT role, message, id FROM chat_history ORDER BY id DESC LIMIT 6) sub ORDER BY id ASC")->fetchAll();
foreach ($historyRows as $row) {
    // Skip the current user message (already at the end)
    $historyMessages[] = ['role' => $row['role'], 'content' => $row['message']];
}

// ─── System prompt ───────────────────────────────────────────
$systemPrompt = "You are the Happy Bangladesh AI assistant. Your data comes ONLY from the local DB.\n"
    . "Rules:\n"
    . "1. You ONLY have access to local data: deposits and withdrawals. If the user asks about CRM API data (like dealers, products, lots, transactions), clearly tell them you do not have access to that data.\n"
    . "2. For numerical or financial questions, answer ONLY using the JSON DATA below. Do not invent numbers.\n"
    . "3. Do not include greetings or conversational filler.\n"
    . "4. Use ৳ for currency.\n"
    . "DATA:\n" . $contextJson;

// ─── Build messages array (system + history) ─────────────────
$messages = [['role' => 'system', 'content' => $systemPrompt]];

// Add conversation history (last few turns for context, excluding current msg which is already last)
// We pop the last one since it's the current user message
if (count($historyMessages) > 1) {
    // Add all but the last (current user msg) from history
    $prevMessages = array_slice($historyMessages, 0, -1);
    // Only include up to 4 previous messages to save tokens
    $prevMessages = array_slice($prevMessages, -4);
    foreach ($prevMessages as $hm) {
        $messages[] = $hm;
    }
}
// Current user message
$messages[] = ['role' => 'user', 'content' => $userMessage];

// ─── Call OpenAI ─────────────────────────────────────────────
$payload = [
    'model' => OPENAI_MODEL,
    'messages' => $messages,
    'max_tokens' => 200,
    'temperature' => 0.1,
];

$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . OPENAI_API_KEY,
    ],
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_SSL_VERIFYPEER => false,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    $err = json_decode($response, true);
    jsonResponse([
        'status' => 'error',
        'message' => $err['error']['message'] ?? 'OpenAI API error',
        'code' => $httpCode
    ], 500);
}

$result = json_decode($response, true);
$aiMessage = $result['choices'][0]['message']['content'] ?? 'No response received.';
$usage = $result['usage'] ?? [];
$totalTokens = $usage['total_tokens'] ?? 0;

// ─── Save AI response to DB ─────────────────────────────────
$stmt = $pdo->prepare("INSERT INTO chat_history (role, message, tokens_used) VALUES ('assistant', ?, ?)");
$stmt->execute([$aiMessage, $totalTokens]);

jsonResponse([
    'status' => 'success',
    'reply' => $aiMessage,
    'tokens' => [
        'prompt' => $usage['prompt_tokens'] ?? 0,
        'completion' => $usage['completion_tokens'] ?? 0,
        'total' => $totalTokens,
    ]
]);
