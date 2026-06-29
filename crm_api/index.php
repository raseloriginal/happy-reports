<?php
// API for HappyReports Website
header("Access-Control-Allow-Origin: *"); // Allows any website, like HappyReports, to access this
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config/db.php'; 

if (!$conn) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

if (!isset($_GET['table']) || empty($_GET['table'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Please specify a table by adding ?table=your_table_name to the URL"]);
    exit;
}

$table = $_GET['table'];

// Security: Validate the table name to prevent SQL injection!
if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid table name format"]);
    exit;
}

// Set a limit for the number of records returned to avoid crashing the server.
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 1000;

$whereClause = "";
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);
    $whereClause = " WHERE id = $id ";
}

// Build the final query
$query = "SELECT * FROM `$table` $whereClause LIMIT $limit";
$result = mysqli_query($conn, $query);

if ($result) {
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    
    echo json_encode([
        "status" => "success",
        "count" => count($data),
        "data" => $data
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Error executing query: " . mysqli_error($conn)
    ]);
}

mysqli_close($conn);
?>
