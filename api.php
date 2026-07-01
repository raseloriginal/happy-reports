<?php
require_once __DIR__ . '/includes/init.php';

$action = $_GET['action'] ?? '';

$lotModel = new Lot();
$transactionModel = new Transaction();
$depositModel = new Deposit();
$dealerPaymentModel = new DealerPayment();
$lotItemModel = new LotItem();
$transactionItemModel = new TransactionItem();

switch ($action) {
    // ── LEGACY FORM UPLOADS ─────────────
    // Removed uploadLots and uploadTransactions

    // ── JSON API: BATCH INSERT FROM CSV PREVIEW ─────────────────────
    // Removed uploadLotsJson and uploadTransactionsJson

    // ── JSON API: UPDATE / DELETE LOTS ──────────────────────────────
    case 'updateLot':
        header('Content-Type: application/json');
        $body = json_decode(file_get_contents('php://input'), true);
        $id   = intval($body['id'] ?? 0);
        if (!$id) { echo json_encode(['success' => false, 'error' => 'Missing id']); exit; }
        $data = [
            'crm_id'           => trim($body['crm_id'] ?? ''),
            'warehouse_crm_id' => trim($body['warehouse_crm_id'] ?? ''),
            'warehouse_name'   => trim($body['warehouse_name'] ?? ''),
            'company_crm_id'   => trim($body['company_crm_id'] ?? ''),
            'company_name'     => trim($body['company_name'] ?? ''),
            'dealer_crm_id'    => trim($body['dealer_crm_id'] ?? ''),
            'dealer_name'      => trim($body['dealer_name'] ?? ''),
            'lot_value'        => floatval($body['lot_value'] ?? 0),
            'lot_date'         => date('Y-m-d', strtotime($body['lot_date'] ?? 'now'))
        ];
        $ok = $lotModel->updateLot($id, $data);
        echo json_encode(['success' => (bool)$ok]);
        break;

    case 'deleteLot':
        header('Content-Type: application/json');
        $body = json_decode(file_get_contents('php://input'), true);
        $id   = intval($body['id'] ?? 0);
        if (!$id) { echo json_encode(['success' => false, 'error' => 'Missing id']); exit; }
        // Also delete lot items
        $lot = $lotModel->getLots();
        foreach ($lot as $l) {
            if ($l->id == $id) {
                $lotItemModel->deleteItemsByLotCrmId($l->crm_id);
                break;
            }
        }
        $ok = $lotModel->deleteLot($id);
        echo json_encode(['success' => (bool)$ok]);
        break;

    // ── JSON API: UPDATE / DELETE DEPOSITS ──────────────────────────
    case 'updateDeposit':
        header('Content-Type: application/json');
        $body = json_decode(file_get_contents('php://input'), true);
        $id   = intval($body['id'] ?? 0);
        if (!$id) { echo json_encode(['success' => false, 'error' => 'Missing id']); exit; }
        $data = [
            'company_crm_id' => trim($body['company_crm_id'] ?? ''),
            'company_name'   => trim($body['company_name'] ?? ''),
            'amount'         => floatval($body['amount'] ?? 0),
            'deposit_date'   => date('Y-m-d', strtotime($body['deposit_date'] ?? 'now'))
        ];
        $ok = $depositModel->updateDeposit($id, $data);
        echo json_encode(['success' => (bool)$ok]);
        break;

    case 'deleteDeposit':
        header('Content-Type: application/json');
        $body = json_decode(file_get_contents('php://input'), true);
        $id   = intval($body['id'] ?? 0);
        if (!$id) { echo json_encode(['success' => false, 'error' => 'Missing id']); exit; }
        $ok = $depositModel->deleteDeposit($id);
        echo json_encode(['success' => (bool)$ok]);
        break;

    // ── JSON API: UPDATE / DELETE TRANSACTIONS ───────────────────────
    case 'updateTransaction':
        header('Content-Type: application/json');
        $body = json_decode(file_get_contents('php://input'), true);
        $id   = intval($body['id'] ?? 0);
        if (!$id) { echo json_encode(['success' => false, 'error' => 'Missing id']); exit; }
        $data = [
            'crm_ids'          => trim($body['crm_ids'] ?? ''),
            'company_crm_id'   => trim($body['company_crm_id'] ?? ''),
            'company_name'     => trim($body['company_name'] ?? ''),
            'total_out_value'  => floatval($body['total_out_value'] ?? 0),
            'total_in_value'   => floatval($body['total_in_value'] ?? 0),
            'transaction_date' => date('Y-m-d', strtotime($body['transaction_date'] ?? 'now'))
        ];
        $ok = $transactionModel->updateTransaction($id, $data);
        echo json_encode(['success' => (bool)$ok]);
        break;

    case 'deleteTransaction':
        header('Content-Type: application/json');
        $body = json_decode(file_get_contents('php://input'), true);
        $id   = intval($body['id'] ?? 0);
        if (!$id) { echo json_encode(['success' => false, 'error' => 'Missing id']); exit; }
        $ok = $transactionModel->deleteTransaction($id);
        echo json_encode(['success' => (bool)$ok]);
        break;

    // ── MANUAL ENTRY: DEPOSITS ───────────────────────────────────────
    case 'addDeposit':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['company_crm_id']) && is_array($_POST['company_crm_id'])) {
                $count = count($_POST['company_crm_id']);
                for ($i = 0; $i < $count; $i++) {
                    if (!empty($_POST['company_crm_id'][$i]) && !empty($_POST['amount'][$i])) {
                        $data = [
                            'company_crm_id' => trim($_POST['company_crm_id'][$i]),
                            'company_name'   => trim($_POST['company_name'][$i]),
                            'amount'         => floatval($_POST['amount'][$i]),
                            'deposit_date'   => trim($_POST['deposit_date'][$i])
                        ];
                        $depositModel->addDeposit($data);
                    }
                }
            } else {
                $data = [
                    'company_crm_id' => trim($_POST['company_crm_id']),
                    'company_name'   => trim($_POST['company_name']),
                    'amount'         => floatval($_POST['amount']),
                    'deposit_date'   => trim($_POST['deposit_date'])
                ];
                $depositModel->addDeposit($data);
            }
            header('Location: ' . URLROOT . '/ledger.php');
        }
        break;

    case 'addPayment':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'dealer_crm_id' => trim($_POST['dealer_crm_id']),
                'dealer_name'   => trim($_POST['dealer_name']),
                'amount'        => floatval($_POST['amount']),
                'payment_date'  => trim($_POST['payment_date'])
            ];
            $dealerPaymentModel->addPayment($data);
            header('Location: ' . URLROOT . '/dealers.php');
        }
        break;

    // ── ZIP IMPORT FOR LOTS & ITEMS ─────────────────────────────────
    case 'importLotsZip':
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'POST required']);
            exit;
        }
        
        if (!isset($_FILES['zipfile']) || $_FILES['zipfile']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'error' => 'No ZIP file uploaded or upload error']);
            exit;
        }
        
        $zipPath = $_FILES['zipfile']['tmp_name'];
        $zip = new ZipArchive();
        
        if ($zip->open($zipPath) !== true) {
            echo json_encode(['success' => false, 'error' => 'Failed to open ZIP file']);
            exit;
        }
        
        // Extract to temp dir
        $tmpDir = sys_get_temp_dir() . '/happyreports_import_' . uniqid();
        mkdir($tmpDir, 0777, true);
        $zip->extractTo($tmpDir);
        $zip->close();
        
        // Find CSV files (may be in root or subdirectory)
        $lotsFile = null;
        $itemsFile = null;
        
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tmpDir));
        foreach ($iterator as $file) {
            $basename = strtolower(basename($file));
            if ($basename === 'lots.csv') $lotsFile = $file->getPathname();
            if ($basename === 'lot_items.csv') $itemsFile = $file->getPathname();
        }
        
        if (!$lotsFile || !$itemsFile) {
            // Cleanup
            array_map('unlink', glob("$tmpDir/*"));
            @rmdir($tmpDir);
            echo json_encode(['success' => false, 'error' => 'ZIP must contain lots.csv and lot_items.csv']);
            exit;
        }
        
        // Parse lots.csv
        $lotsData = [];
        if (($handle = fopen($lotsFile, 'r')) !== false) {
            $headers = fgetcsv($handle);
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) < 8 || empty(trim($row[0]))) continue;
                $lotsData[] = [
                    'lot_id'         => trim($row[0]),
                    'company_name'   => trim($row[1]),
                    'company_id'     => trim($row[2]),
                    'lot_date'       => trim($row[3]),
                    'dealer_name'    => trim($row[4]),
                    'dealer_id'      => trim($row[5]),
                    'warehouse_name' => trim($row[6]),
                    'warehouse_id'   => trim($row[7])
                ];
            }
            fclose($handle);
        }
        
        // Parse lot_items.csv
        $itemsData = [];
        if (($handle = fopen($itemsFile, 'r')) !== false) {
            $headers = fgetcsv($handle);
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) < 7 || empty(trim($row[0]))) continue;
                $itemsData[] = [
                    'lot_id'       => trim($row[0]),
                    'company_id'   => trim($row[1]),
                    'dealer_id'    => trim($row[2]),
                    'warehouse_id' => trim($row[3]),
                    'item_name'    => trim($row[4]),
                    'item_id'      => trim($row[5]),
                    'item_price'   => floatval($row[6])
                ];
            }
            fclose($handle);
        }
        
        // Check for duplicates
        $existingCrmIds = $lotModel->getAllCrmIds();
        $duplicates = [];
        foreach ($lotsData as $lot) {
            if (in_array($lot['lot_id'], $existingCrmIds)) {
                $duplicates[] = $lot['lot_id'];
            }
        }
        
        if (!empty($duplicates)) {
            // Cleanup
            array_map('unlink', glob("$tmpDir/*"));
            @rmdir($tmpDir);
            echo json_encode([
                'success'    => false,
                'error'      => 'Duplicate Lot IDs found: ' . implode(', ', $duplicates),
                'duplicates' => $duplicates
            ]);
            exit;
        }
        
        // Calculate lot values from items
        $lotValues = [];
        foreach ($itemsData as $item) {
            $lid = $item['lot_id'];
            if (!isset($lotValues[$lid])) $lotValues[$lid] = 0;
            $lotValues[$lid] += $item['item_price'];
        }
        
        // Insert lots
        $lotsInserted = 0;
        $itemsInserted = 0;
        
        foreach ($lotsData as $lot) {
            $lotValue = $lotValues[$lot['lot_id']] ?? 0;
            $data = [
                'crm_id'           => $lot['lot_id'],
                'warehouse_crm_id' => $lot['warehouse_id'],
                'warehouse_name'   => $lot['warehouse_name'],
                'company_crm_id'   => $lot['company_id'],
                'company_name'     => $lot['company_name'],
                'dealer_crm_id'    => $lot['dealer_id'],
                'dealer_name'      => $lot['dealer_name'],
                'lot_value'        => $lotValue,
                'lot_date'         => date('Y-m-d', strtotime($lot['lot_date']))
            ];
            $lotModel->addLot($data);
            $lotsInserted++;
        }
        
        // Insert lot items
        foreach ($itemsData as $item) {
            $data = [
                'lot_crm_id'  => $item['lot_id'],
                'item_crm_id' => $item['item_id'],
                'item_name'   => $item['item_name'],
                'item_qty'    => 1,
                'item_price'  => $item['item_price']
            ];
            $lotItemModel->addLotItem($data);
            $itemsInserted++;
        }
        
        // Cleanup temp files
        $cleanupIterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($tmpDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($cleanupIterator as $f) {
            if ($f->isDir()) rmdir($f->getPathname());
            else unlink($f->getPathname());
        }
        @rmdir($tmpDir);
        
        echo json_encode([
            'success'       => true,
            'lots_inserted' => $lotsInserted,
            'items_inserted' => $itemsInserted
        ]);
        break;

    // ── ZIP IMPORT FOR TRANSACTIONS ──────────────────────────────────
    case 'importTransactionsZip':
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'POST required']);
            exit;
        }
        
        if (!isset($_FILES['zipfile']) || $_FILES['zipfile']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'error' => 'No ZIP file uploaded or upload error']);
            exit;
        }
        
        $zipPath = $_FILES['zipfile']['tmp_name'];
        $zip = new ZipArchive();
        
        if ($zip->open($zipPath) !== true) {
            echo json_encode(['success' => false, 'error' => 'Failed to open ZIP file']);
            exit;
        }
        
        // Extract to temp dir
        $tmpDir = sys_get_temp_dir() . '/happyreports_txn_import_' . uniqid();
        mkdir($tmpDir, 0777, true);
        $zip->extractTo($tmpDir);
        $zip->close();
        
        // Find CSV files
        $summaryFile = null;
        $detailsFile = null;
        
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tmpDir));
        foreach ($iterator as $file) {
            $basename = strtolower(basename($file));
            if ($basename === 'transactions_summary.csv') $summaryFile = $file->getPathname();
            if ($basename === 'transaction_details.csv') $detailsFile = $file->getPathname();
        }
        
        if (!$summaryFile || !$detailsFile) {
            array_map('unlink', glob("$tmpDir/*"));
            @rmdir($tmpDir);
            echo json_encode(['success' => false, 'error' => 'ZIP must contain transactions_summary.csv and transaction_details.csv']);
            exit;
        }
        
        // Parse transactions_summary.csv
        $summaryData = [];
        if (($handle = fopen($summaryFile, 'r')) !== false) {
            $headers = fgetcsv($handle);
            $rowIndex = 0;
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) < 7 || empty(trim($row[0]))) continue;
                $summaryData[] = [
                    'index'          => $rowIndex,
                    'txn_date'       => trim($row[0]),
                    'company_name'   => trim($row[1]),
                    'company_id'     => trim($row[2]),
                    'dealer_name'    => trim($row[3]),
                    'dealer_id'      => trim($row[4]),
                    'warehouse_id'   => trim($row[5]),
                    'warehouse_name' => trim($row[6])
                ];
                $rowIndex++;
            }
            fclose($handle);
        }
        
        // Parse transaction_details.csv
        $detailsData = [];
        if (($handle = fopen($detailsFile, 'r')) !== false) {
            $headers = fgetcsv($handle);
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) < 15 || empty(trim($row[0]))) continue;
                $detailsData[] = [
                    'txn_date'       => trim($row[0]),
                    'company_name'   => trim($row[1]),
                    'company_id'     => trim($row[2]),
                    'dealer_name'    => trim($row[3]),
                    'dealer_id'      => trim($row[4]),
                    'warehouse_id'   => trim($row[5]),
                    'warehouse_name' => trim($row[6]),
                    'item_name'      => trim($row[7]),
                    'item_id'        => trim($row[8]),
                    'item_out_qty'   => intval($row[9]),
                    'item_in_qty'    => intval($row[10]),
                    'item_sell_qty'  => intval($row[11]),
                    'item_out_value' => floatval($row[12]),
                    'item_in_value'  => floatval($row[13]),
                    'item_sell_value'=> floatval($row[14])
                ];
            }
            fclose($handle);
        }
        
        // Deduplicate summary rows by composite key
        $uniqueSummaryData = [];
        foreach ($summaryData as $summary) {
            $key = $summary['txn_date'] . '|' . $summary['company_id'] . '|' . $summary['dealer_id'] . '|' . $summary['warehouse_id'];
            if (!isset($uniqueSummaryData[$key])) {
                $uniqueSummaryData[$key] = $summary;
            }
        }
        $uniqueSummaryData = array_values($uniqueSummaryData);
        
        $txnsInserted = 0;
        $itemsInserted = 0;
        
        foreach ($uniqueSummaryData as $idx => $summary) {
            // Calculate totals from matching details
            $matchKey = $summary['txn_date'] . '|' . $summary['company_id'] . '|' . $summary['dealer_id'] . '|' . $summary['warehouse_id'];
            
            $totalOut = 0;
            $totalIn = 0;
            $matchedDetails = [];
            
            foreach ($detailsData as $detail) {
                $detailKey = $detail['txn_date'] . '|' . $detail['company_id'] . '|' . $detail['dealer_id'] . '|' . $detail['warehouse_id'];
                if ($detailKey === $matchKey) {
                    $totalOut += $detail['item_out_value'];
                    $totalIn += $detail['item_in_value'];
                    $matchedDetails[] = $detail;
                }
            }
            
            // Insert transaction
            $txnData = [
                'crm_ids'          => '',
                'company_crm_id'   => $summary['company_id'],
                'company_name'     => $summary['company_name'],
                'dealer_crm_id'    => $summary['dealer_id'],
                'dealer_name'      => $summary['dealer_name'],
                'warehouse_crm_id' => $summary['warehouse_id'],
                'warehouse_name'   => $summary['warehouse_name'],
                'total_out_value'  => $totalOut,
                'total_in_value'   => $totalIn,
                'transaction_date' => date('Y-m-d', strtotime($summary['txn_date']))
            ];
            
            $transactionModel->addTransaction($txnData);
            $txnId = $transactionModel->getLastInsertId();
            $txnsInserted++;
            
            // Insert detail items for this transaction
            foreach ($matchedDetails as $detail) {
                $itemData = [
                    'transaction_id'  => $txnId,
                    'item_crm_id'     => $detail['item_id'],
                    'item_name'       => $detail['item_name'],
                    'item_out_qty'    => $detail['item_out_qty'],
                    'item_in_qty'     => $detail['item_in_qty'],
                    'item_sell_qty'   => $detail['item_sell_qty'],
                    'item_out_value'  => $detail['item_out_value'],
                    'item_in_value'   => $detail['item_in_value'],
                    'item_sell_value' => $detail['item_sell_value']
                ];
                $transactionItemModel->addTransactionItem($itemData);
                $itemsInserted++;
            }
            
            // Remove matched details so duplicate summary rows don't double-count
            $detailsData = array_filter($detailsData, function($d) use ($matchKey) {
                $k = $d['txn_date'] . '|' . $d['company_id'] . '|' . $d['dealer_id'] . '|' . $d['warehouse_id'];
                return $k !== $matchKey;
            });
            $detailsData = array_values($detailsData);
        }
        
        // Cleanup temp files
        $cleanupIterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($tmpDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($cleanupIterator as $f) {
            if ($f->isDir()) rmdir($f->getPathname());
            else unlink($f->getPathname());
        }
        @rmdir($tmpDir);
        
        echo json_encode([
            'success'         => true,
            'txns_inserted'   => $txnsInserted,
            'items_inserted'  => $itemsInserted
        ]);
        break;

    // ── CHECK DUPLICATE LOT IDS ─────────────────────────────────────
    case 'checkDuplicateLots':
        header('Content-Type: application/json');
        $body = json_decode(file_get_contents('php://input'), true);
        $crmIds = $body['crm_ids'] ?? [];
        $existingCrmIds = $lotModel->getAllCrmIds();
        $duplicates = array_values(array_intersect($crmIds, $existingCrmIds));
        echo json_encode(['duplicates' => $duplicates]);
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Action not found']);
        break;
}
