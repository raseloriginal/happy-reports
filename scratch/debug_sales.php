<?php
require_once __DIR__ . '/../api/db.php';

// Clear cache for limit 100000
$cacheFile = __DIR__ . '/../api/cache_transaction_items_100000_none.json';
if (file_exists($cacheFile)) {
    unlink($cacheFile);
}

$items = fetchCrmApi('transaction_items', 100000) ?? [];
echo "Total transaction items in CRM (limit 100000): " . count($items) . "\n";

if (!empty($items)) {
    $last = end($items);
    echo "Last item ID: " . ($last['id'] ?? 'N/A') . " (Transaction ID: " . ($last['transection_id'] ?? 'N/A') . ")\n";
}
