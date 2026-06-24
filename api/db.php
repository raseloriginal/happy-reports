<?php
require_once __DIR__ . '/config.php';

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    // Auto-create local lots table if not exists
    if ($pdo) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS lots (
            id INT AUTO_INCREMENT PRIMARY KEY,
            company_id INT NOT NULL,
            lot_date DATE NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL,
            description TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }
} catch (\PDOException $e) {
    $pdo = null;
}

/**
 * Fetch data from HappyCRM external API
 * @param string $table  Table name
 * @param int|null $limit  Optional limit
 * @param int|null $id     Optional specific record ID
 * @return array|null
 */
function fetchCrmApi($table, $limit = null, $id = null) {
    $cleanTable = preg_replace('/[^a-zA-Z0-9_-]/', '', $table);
    $cacheFile = __DIR__ . '/cache_' . $cleanTable . '_' . ($limit ?? 'all') . '_' . ($id ?? 'none') . '.json';
    $cacheLife = 300; // 5 minutes cache

    $dataToReturn = null;

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheLife) {
        $cachedContent = @file_get_contents($cacheFile);
        $cachedData = json_decode($cachedContent, true);
        if ($cachedData !== null) {
            $dataToReturn = $cachedData;
        }
    }

    if ($dataToReturn === null) {
        $url = CRM_API_BASE . '?table=' . urlencode($table);
        if ($limit !== null) $url .= '&limit=' . intval($limit);
        if ($id !== null)    $url .= '&id=' . intval($id);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            if ($data && isset($data['status']) && $data['status'] === 'success') {
                $extractedData = $data['data'] ?? [];
                // Cache the successful response
                @file_put_contents($cacheFile, json_encode($extractedData, JSON_UNESCAPED_UNICODE));
                $dataToReturn = $extractedData;
            }
        }
    }

    // Fallback to expired cache if we still have nothing
    if ($dataToReturn === null && file_exists($cacheFile)) {
        $cachedContent = @file_get_contents($cacheFile);
        $cachedData = json_decode($cachedContent, true);
        if ($cachedData !== null) {
            $dataToReturn = $cachedData;
        }
    }

    // Sync companies to local database if we have them
    if ($cleanTable === 'companies' && !empty($dataToReturn)) {
        global $pdo;
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("INSERT INTO companies (id, company_name) VALUES (?, ?) ON DUPLICATE KEY UPDATE company_name = VALUES(company_name)");
                foreach ($dataToReturn as $company) {
                    if (isset($company['id']) && isset($company['company_name'])) {
                        $stmt->execute([$company['id'], $company['company_name']]);
                    }
                }
            } catch (Exception $e) {
                // ignore sync errors
            }
        }
    }

    return $dataToReturn;
}

/**
 * Send JSON response and exit
 */
function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
