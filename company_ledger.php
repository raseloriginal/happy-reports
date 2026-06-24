<?php
require_once 'api/db.php';

$companyId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$companyId) {
    header("Location: companies.php");
    exit;
}

// Fetch companies
$companies = fetchCrmApi('companies') ?? [];
$company = null;
foreach ($companies as $c) {
    if (intval($c['id']) === $companyId) {
        $company = $c;
        break;
    }
}

if (!$company) {
    include 'includes/header.php';
    echo '<div class="max-w-4xl mx-auto mt-12 p-6 bg-white dark:bg-dark-card border border-red-200 rounded-xl text-center shadow-sm">
            <i class="ph ph-warning-circle text-5xl text-red-500 mb-4"></i>
            <h3 class="text-xl font-bold text-gray-800 dark:text-white">Company Not Found</h3>
            <p class="text-gray-500 dark:text-gray-400 mt-1">The company ID you requested could not be resolved in HappyCRM.</p>
            <a href="companies.php" class="mt-4 inline-block bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">Back to Companies</a>
          </div>';
    include 'includes/footer.php';
    exit;
}

// Fetch local deposits for this company
$companyDeposits = [];
if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM deposits WHERE company_id = ? ORDER BY operation_date DESC");
        $stmt->execute([$companyId]);
        $companyDeposits = $stmt->fetchAll();
    } catch (Exception $e) {
        // ignore
    }
}

// Fetch local lots for this company
$companyLots = [];
if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM lots WHERE company_id = ? ORDER BY lot_date DESC");
        $stmt->execute([$companyId]);
        $companyLots = $stmt->fetchAll();
    } catch (Exception $e) {
        // ignore
    }
}

// Calculate sums
$totalDeposits = 0;
foreach ($companyDeposits as $dep) {
    $totalDeposits += floatval($dep['amount']);
}

$totalLots = 0;
foreach ($companyLots as $lot) {
    $totalLots += floatval($lot['total_amount']);
}

$netBalance = $totalDeposits - $totalLots;

// Build chronological unified ledger
$ledger = [];
foreach ($companyDeposits as $d) {
    $ledger[] = [
        'type' => 'deposit',
        'id' => $d['id'],
        'date' => $d['operation_date'],
        'credit' => floatval($d['amount']),
        'debit' => 0.0,
        'description' => $d['note'] ?: 'Cash Deposit / Capital Input'
    ];
}
foreach ($companyLots as $l) {
    $ledger[] = [
        'type' => 'lot',
        'id' => $l['id'],
        'date' => $l['lot_date'],
        'credit' => 0.0,
        'debit' => floatval($l['total_amount']),
        'description' => $l['description'] ?: 'Manual Lot Allocation'
    ];
}

// Sort oldest first for calculating running balance
usort($ledger, function($a, $b) {
    $dateCompare = strcmp($a['date'], $b['date']);
    if ($dateCompare === 0) {
        // deposits first if on same date
        return $a['type'] === 'deposit' ? -1 : 1;
    }
    return $dateCompare;
});

$runningBalance = 0;
foreach ($ledger as &$entry) {
    $runningBalance += ($entry['credit'] - $entry['debit']);
    $entry['balance'] = $runningBalance;
}
unset($entry);

// Reverse for display (newest first)
$ledger = array_reverse($ledger);

include 'includes/header.php';
?>

<div class="max-w-7xl mx-auto space-y-6 animate-fade-in pb-12" style="animation-delay: 0.1s;">
    <!-- Page Header & Navigation back -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <a href="companies.php" class="text-indigo-600 dark:text-indigo-400 hover:underline flex items-center text-sm font-semibold transition-all">
                    <i class="ph ph-arrow-left mr-1"></i> Back to Companies
                </a>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white mt-2">Ledger Statement: <?= htmlspecialchars($company['company_name']) ?></h2>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Audit comparison of cash deposits (credits) and manual lots (debits).</p>
        </div>
    </div>

    <!-- Financial Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Net Balance Card -->
        <div class="bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border rounded-xl p-5 shadow-sm hover-card">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Current Balance</span>
                <div class="w-9 h-9 <?= $netBalance >= 0 ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-450 border-emerald-100 dark:border-emerald-900/50' : 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-450 border-rose-100 dark:border-rose-900/50' ?> border rounded-lg flex items-center justify-center">
                    <i class="ph ph-scales text-xl"></i>
                </div>
            </div>
            <p class="text-3xl font-extrabold <?= $netBalance >= 0 ? 'text-emerald-600 dark:text-emerald-450' : 'text-rose-600 dark:text-rose-450' ?>">
                ৳<?= number_format($netBalance, 2) ?>
            </p>
            <p class="text-xs text-gray-400 mt-1">Deposits minus Lot value</p>
        </div>

        <!-- Total Deposits Card -->
        <div class="bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border rounded-xl p-5 shadow-sm hover-card">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Deposits (Credit)</span>
                <div class="w-9 h-9 bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/50 rounded-lg flex items-center justify-center text-indigo-650 dark:text-indigo-400">
                    <i class="ph ph-arrow-down-left text-xl"></i>
                </div>
            </div>
            <p class="text-3xl font-extrabold text-gray-850 dark:text-white">৳<?= number_format($totalDeposits, 2) ?></p>
            <p class="text-xs text-gray-400 mt-1">Sum of <?= count($companyDeposits) ?> cash additions</p>
        </div>

        <!-- Total Lots Card -->
        <div class="bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border rounded-xl p-5 shadow-sm hover-card">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Lots (Debit)</span>
                <div class="w-9 h-9 bg-rose-50 dark:bg-rose-950/40 border border-rose-100 dark:border-rose-900/50 rounded-lg flex items-center justify-center text-rose-600 dark:text-rose-400">
                    <i class="ph ph-truck text-xl"></i>
                </div>
            </div>
            <p class="text-3xl font-extrabold text-gray-850 dark:text-white">৳<?= number_format($totalLots, 2) ?></p>
            <p class="text-xs text-gray-400 mt-1">Sum of <?= count($companyLots) ?> allocated lots</p>
        </div>
    </div>

    <!-- Search Panel -->
    <div class="bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border rounded-xl p-4 shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
        <div class="relative w-full md:w-96">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                <i class="ph ph-magnifying-glass text-lg"></i>
            </span>
            <input type="text" id="ledgerSearch" oninput="filterLedger()" placeholder="Search date, description..." class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 pl-10 pr-4 py-2.5 rounded-lg focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors text-sm">
        </div>
        <div class="text-xs font-medium text-gray-500 dark:text-gray-400">
            Total of <span class="font-bold text-indigo-650 dark:text-indigo-400"><?= count($ledger) ?></span> ledger actions
        </div>
    </div>

    <!-- Side-by-Side Comparative Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Deposits Table (Left) -->
        <div class="bg-white dark:bg-dark-card border border-emerald-250 dark:border-dark-border shadow-sm rounded-xl overflow-hidden flex flex-col">
            <div class="h-1.5 bg-emerald-500"></div>
            <div class="p-4 border-b border-gray-100 dark:border-dark-border bg-emerald-50/20 dark:bg-emerald-950/5 flex items-center justify-between">
                <h4 class="font-bold text-sm text-emerald-800 dark:text-emerald-400 flex items-center">
                    <i class="ph ph-arrow-down-left mr-2 text-lg"></i> Deposits Table (Credit)
                </h4>
                <span class="px-2 py-0.5 text-xs rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 font-semibold border border-emerald-100/50">
                    +৳<?= number_format($totalDeposits, 2) ?>
                </span>
            </div>
            <div class="overflow-x-auto flex-1 max-h-[400px] overflow-y-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800/80 text-[10px] uppercase font-semibold text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-dark-border">
                            <th class="p-2.5 w-12 text-center border-r border-gray-100 dark:border-dark-border/40">ID</th>
                            <th class="p-2.5 border-r border-gray-100 dark:border-dark-border/40">Date</th>
                            <th class="p-2.5 border-r border-gray-100 dark:border-dark-border/40">Note</th>
                            <th class="p-2.5 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs text-gray-700 dark:text-gray-300 divide-y divide-gray-100 dark:divide-dark-border/80">
                        <?php if (empty($companyDeposits)): ?>
                            <tr><td colspan="4" class="p-6 text-center text-gray-400 italic">No deposits found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($companyDeposits as $dep): ?>
                                <tr class="deposit-row hover:bg-emerald-50/10 dark:hover:bg-emerald-900/5 transition-colors" data-desc="<?= htmlspecialchars(strtolower($dep['note'])) ?>" data-date="<?= htmlspecialchars(strtolower($dep['operation_date'])) ?>">
                                    <td class="p-2.5 text-center font-mono text-gray-400 border-r border-gray-100 dark:border-dark-border/40">#<?= $dep['id'] ?></td>
                                    <td class="p-2.5 border-r border-gray-100 dark:border-dark-border/40 font-medium"><?= htmlspecialchars($dep['operation_date']) ?></td>
                                    <td class="p-2.5 border-r border-gray-100 dark:border-dark-border/40 truncate max-w-[180px]" title="<?= htmlspecialchars($dep['note']) ?>"><?= htmlspecialchars($dep['note'] ?: '-') ?></td>
                                    <td class="p-2.5 text-right font-mono text-emerald-600 dark:text-emerald-450 font-bold">+৳<?= number_format($dep['amount'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Lots Table (Right) -->
        <div class="bg-white dark:bg-dark-card border border-rose-250 dark:border-dark-border shadow-sm rounded-xl overflow-hidden flex flex-col">
            <div class="h-1.5 bg-rose-500"></div>
            <div class="p-4 border-b border-gray-100 dark:border-dark-border bg-rose-50/20 dark:bg-rose-950/5 flex items-center justify-between">
                <h4 class="font-bold text-sm text-rose-800 dark:text-rose-455 flex items-center">
                    <i class="ph ph-truck mr-2 text-lg"></i> Local Lots Table (Debit)
                </h4>
                <span class="px-2 py-0.5 text-xs rounded-full bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-400 font-semibold border border-rose-100/50">
                    -৳<?= number_format($totalLots, 2) ?>
                </span>
            </div>
            <div class="overflow-x-auto flex-1 max-h-[400px] overflow-y-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800/80 text-[10px] uppercase font-semibold text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-dark-border">
                            <th class="p-2.5 w-12 text-center border-r border-gray-100 dark:border-dark-border/40">ID</th>
                            <th class="p-2.5 border-r border-gray-100 dark:border-dark-border/40">Date</th>
                            <th class="p-2.5 border-r border-gray-100 dark:border-dark-border/40">Description</th>
                            <th class="p-2.5 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs text-gray-700 dark:text-gray-300 divide-y divide-gray-100 dark:divide-dark-border/80">
                        <?php if (empty($companyLots)): ?>
                            <tr><td colspan="4" class="p-6 text-center text-gray-400 italic">No lots found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($companyLots as $lot): ?>
                                <tr class="lot-row hover:bg-rose-50/10 dark:hover:bg-rose-900/5 transition-colors" data-desc="<?= htmlspecialchars(strtolower($lot['description'])) ?>" data-date="<?= htmlspecialchars(strtolower($lot['lot_date'])) ?>">
                                    <td class="p-2.5 text-center font-mono text-gray-400 border-r border-gray-100 dark:border-dark-border/40">#<?= $lot['id'] ?></td>
                                    <td class="p-2.5 border-r border-gray-100 dark:border-dark-border/40 font-medium"><?= htmlspecialchars($lot['lot_date']) ?></td>
                                    <td class="p-2.5 border-r border-gray-100 dark:border-dark-border/40 truncate max-w-[180px]" title="<?= htmlspecialchars($lot['description']) ?>"><?= htmlspecialchars($lot['description'] ?: '-') ?></td>
                                    <td class="p-2.5 text-right font-mono text-rose-600 dark:text-rose-455 font-bold">-৳<?= number_format($lot['total_amount'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Unified Chronological Statement (Full Width) -->
    <div class="bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border shadow-sm rounded-xl overflow-hidden">
        <div class="p-4 border-b border-gray-100 dark:border-dark-border bg-gray-50/50 dark:bg-gray-800/40 flex items-center">
            <h4 class="font-bold text-sm text-gray-800 dark:text-white flex items-center">
                <i class="ph ph-receipt mr-2 text-lg text-indigo-500"></i> Unified Ledger Statement (Running Balance)
            </h4>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/80 text-xs font-semibold text-gray-600 dark:text-gray-300 border-b border-blue-200 dark:border-dark-border">
                        <th class="p-3 border-r border-blue-100 dark:border-dark-border text-center font-medium w-16">No</th>
                        <th class="p-3 border-r border-blue-100 dark:border-dark-border w-28">Date</th>
                        <th class="p-3 border-r border-blue-100 dark:border-dark-border w-24">Type</th>
                        <th class="p-3 border-r border-blue-100 dark:border-dark-border">Note/Description</th>
                        <th class="p-3 border-r border-blue-100 dark:border-dark-border text-right w-36">Credit (+)</th>
                        <th class="p-3 border-r border-blue-100 dark:border-dark-border text-right w-36">Debit (-)</th>
                        <th class="p-3 text-right w-44">Running Balance</th>
                    </tr>
                </thead>
                <tbody class="text-xs text-gray-700 dark:text-gray-300 divide-y divide-gray-100 dark:divide-dark-border/80" id="ledgerTableBody">
                    <?php if (empty($ledger)): ?>
                        <tr><td colspan="7" class="p-8 text-center text-gray-400 italic">No ledger activity found for this company.</td></tr>
                    <?php else: ?>
                        <?php foreach ($ledger as $idx => $entry): ?>
                            <tr class="ledger-row hover:bg-indigo-50/15 dark:hover:bg-slate-800/40 transition-colors border-b border-gray-100 dark:border-dark-border" data-desc="<?= htmlspecialchars(strtolower($entry['description'])) ?>" data-date="<?= htmlspecialchars(strtolower($entry['date'])) ?>">
                                <td class="p-3 border-r border-gray-100 dark:border-dark-border/60 text-center font-mono text-gray-400"><?= count($ledger) - $idx ?></td>
                                <td class="p-3 border-r border-gray-100 dark:border-dark-border/60 font-semibold text-gray-800 dark:text-white"><?= htmlspecialchars($entry['date']) ?></td>
                                <td class="p-3 border-r border-gray-100 dark:border-dark-border/60 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase <?= $entry['type'] === 'deposit' ? 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/30' : 'bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-400 border border-rose-200/30' ?>">
                                        <?= $entry['type'] === 'deposit' ? 'Deposit' : 'Lot' ?>
                                    </span>
                                </td>
                                <td class="p-3 border-r border-gray-100 dark:border-dark-border/60"><?= htmlspecialchars($entry['description']) ?></td>
                                <td class="p-3 border-r border-gray-100 dark:border-dark-border/60 text-right font-mono <?= $entry['credit'] > 0 ? 'text-emerald-600 dark:text-emerald-450 font-semibold' : 'text-gray-400' ?>">
                                    <?= $entry['credit'] > 0 ? '+৳' . number_format($entry['credit'], 2) : '-' ?>
                                </td>
                                <td class="p-3 border-r border-gray-100 dark:border-dark-border/60 text-right font-mono <?= $entry['debit'] > 0 ? 'text-rose-600 dark:text-rose-455 font-semibold' : 'text-gray-400' ?>">
                                    <?= $entry['debit'] > 0 ? '-৳' . number_format($entry['debit'], 2) : '-' ?>
                                </td>
                                <td class="p-3 text-right font-mono font-semibold <?= $entry['balance'] >= 0 ? 'text-emerald-600 dark:text-emerald-450' : 'text-rose-600 dark:text-rose-455' ?>">
                                    ৳<?= number_format($entry['balance'], 2) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function filterLedger() {
    const query = document.getElementById('ledgerSearch').value.toLowerCase().trim();
    
    // Filter Unified Table
    const ledgerRows = document.querySelectorAll('.ledger-row');
    ledgerRows.forEach(row => {
        const desc = row.getAttribute('data-desc');
        const date = row.getAttribute('data-date');
        if (desc.includes(query) || date.includes(query)) {
            row.classList.remove('hidden');
        } else {
            row.classList.add('hidden');
        }
    });

    // Filter Deposits Table
    const depositRows = document.querySelectorAll('.deposit-row');
    depositRows.forEach(row => {
        const desc = row.getAttribute('data-desc');
        const date = row.getAttribute('data-date');
        if (desc.includes(query) || date.includes(query)) {
            row.classList.remove('hidden');
        } else {
            row.classList.add('hidden');
        }
    });

    // Filter Lots Table
    const lotRows = document.querySelectorAll('.lot-row');
    lotRows.forEach(row => {
        const desc = row.getAttribute('data-desc');
        const date = row.getAttribute('data-date');
        if (desc.includes(query) || date.includes(query)) {
            row.classList.remove('hidden');
        } else {
            row.classList.add('hidden');
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>
