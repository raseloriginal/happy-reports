<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/schema_parser.php';

header('Content-Type: application/json');

$table = $_GET['table'] ?? null;
$xAxis = $_GET['x_axis'] ?? null;
$yAxis = $_GET['y_axis'] ?? null;
$aggregate = $_GET['aggregate'] ?? 'sum'; // count, sum, avg
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 2000;

if (!$table) {
    jsonResponse(['error' => 'Table is required'], 400);
}

// Ensure the table exists in our schema to prevent injection or invalid requests
$schemaTables = parseSchema();
$tableSchema = null;
foreach ($schemaTables as $t) {
    if ($t['name'] === $table) {
        $tableSchema = $t;
        break;
    }
}

if (!$tableSchema) {
    jsonResponse(['error' => 'Invalid table'], 400);
}

// Fetch raw data from CRM API
$data = fetchCrmApi($table, $limit);

if ($data === null) {
    jsonResponse(['error' => 'Failed to fetch data from CRM API'], 500);
}

// If no specific axes are requested, return raw data
if (!$xAxis && !$yAxis) {
    jsonResponse([
        'status' => 'success',
        'table' => $table,
        'count' => count($data),
        'data' => $data
    ]);
}

// Aggregation logic
$aggregated = [];
$counts = []; // For average calculations

foreach ($data as $row) {
    $xValue = isset($row[$xAxis]) ? $row[$xAxis] : 'Unknown';
    
    // If X axis is a date, let's group by date (YYYY-MM-DD)
    if (preg_match('/^\d{4}-\d{2}-\d{2}/', $xValue, $m)) {
        $xValue = $m[0];
    }
    
    $yValue = isset($row[$yAxis]) ? floatval($row[$yAxis]) : 0;

    if (!isset($aggregated[$xValue])) {
        $aggregated[$xValue] = 0;
        $counts[$xValue] = 0;
    }

    if ($aggregate === 'count') {
        $aggregated[$xValue]++;
    } elseif ($aggregate === 'sum' || $aggregate === 'avg') {
        $aggregated[$xValue] += $yValue;
    }
    $counts[$xValue]++;
}

if ($aggregate === 'avg') {
    foreach ($aggregated as $k => $v) {
        $aggregated[$k] = $v / $counts[$k];
    }
}

// Sort by X-axis logically (e.g. dates)
uksort($aggregated, function($a, $b) {
    return strcmp($a, $b);
});

// Convert associative array to chart-friendly format
$labels = [];
$values = [];
foreach ($aggregated as $k => $v) {
    $labels[] = $k;
    $values[] = round($v, 2);
}

jsonResponse([
    'status' => 'success',
    'table' => $table,
    'x_axis' => $xAxis,
    'y_axis' => $yAxis,
    'aggregate' => $aggregate,
    'labels' => $labels,
    'values' => $values
]);
