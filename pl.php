<?php
require_once 'api/db.php';

// Get period and date ranges
$period = $_GET['period'] ?? 'monthly';

// Define date range based on selected period
if ($period === 'quarterly') {
    // Current quarter
    $quarter = ceil(date('n') / 3);
    $year = date('Y');
    $from_date = date('Y-m-d', mktime(0, 0, 0, ($quarter - 1) * 3 + 1, 1, $year));
    $to_date = date('Y-m-d', mktime(0, 0, 0, $quarter * 3 + 1, 0, $year));
    $periodLabel = "Q" . $quarter . " " . $year;
    $scaleDays = 90;
} elseif ($period === 'yearly') {
    // Current year
    $from_date = date('Y-01-01');
    $to_date = date('Y-12-31');
    $periodLabel = date('Y');
    $scaleDays = 365;
} else {
    // Current month (default)
    $from_date = date('Y-m-01');
    $to_date = date('Y-m-t');
    $periodLabel = date('F Y');
    $scaleDays = 30;
}

// Custom date overrides
if (isset($_GET['from']) && isset($_GET['to'])) {
    $from_date = $_GET['from'];
    $to_date = $_GET['to'];
    $periodLabel = date('j M Y', strtotime($from_date)) . " – " . date('j M Y', strtotime($to_date));
    $scaleDays = (strtotime($to_date) - strtotime($from_date)) / (60 * 60 * 24);
    if ($scaleDays <= 0) $scaleDays = 1;
}

// 1. Fetch CRM Data
$crmTransactions = fetchCrmApi('transactions', 5000) ?? [];
$crmItems = fetchCrmApi('transaction_items', 25000) ?? [];
$products = fetchCrmApi('products') ?? [];
$damageLogs = fetchCrmApi('damage', 1000) ?? [];

// Index products for margin/price lookup
$productMap = [];
foreach ($products as $p) {
    $productMap[intval($p['id'])] = $p;
}

// Group transaction items by transaction ID
$itemsByTx = [];
foreach ($crmItems as $item) {
    $txId = $item['transection_id'] ?? 0;
    $itemsByTx[$txId][] = $item;
}

// 2. Perform Revenue and COGS calculations
$grossSales = 0;
$returnsDiscounts = 0;
$cogs = 0;

$monthlyTrends = []; // Grouped by month for chart

foreach ($crmTransactions as $tx) {
    $txId = $tx['id'];
    $txDate = $tx['transaction_date'] ?? '';
    
    // Check if within date range
    if ($txDate >= $from_date && $txDate <= $to_date) {
        $txType = $tx['transaction_type'] ?? 'out';
        $txItems = $itemsByTx[$txId] ?? [];
        $txTotal = 0;
        $txCogs = 0;

        foreach ($txItems as $item) {
            $qty = intval($item['out_qty'] ?? 0);
            if ($qty <= 0) $qty = intval($item['in_qty'] ?? 0); // fallback for returns
            $price = floatval($item['per_price'] ?? 0);
            $val = $qty * $price;
            $txTotal += $val;

            $pId = intval($item['product_id'] ?? 0);
            $p = $productMap[$pId] ?? null;
            $marginPct = floatval($p['percentage'] ?? 6.0); // default 6% if missing
            
            // Cost of product = selling price * (1 - margin_percentage / 100)
            $itemCost = $val * (1 - ($marginPct / 100));
            $txCogs += $itemCost;
        }

        if ($txType === 'out') {
            $grossSales += $txTotal;
            $cogs += $txCogs;
        } elseif ($txType === 'in') {
            $returnsDiscounts += $txTotal;
            $cogs -= $txCogs; // returns reduce COGS
        }
    }

    // Process all transactions for P&L chart trends (grouping by YYYY-MM)
    if ($txDate) {
        $txMonth = substr($txDate, 0, 7);
        $txType = $tx['transaction_type'] ?? 'out';
        $txItems = $itemsByTx[$txId] ?? [];
        $txTotal = 0;
        $txCogs = 0;

        foreach ($txItems as $item) {
            $qty = intval($item['out_qty'] ?? 0);
            if ($qty <= 0) $qty = intval($item['in_qty'] ?? 0);
            $price = floatval($item['per_price'] ?? 0);
            $txTotal += $qty * $price;

            $pId = intval($item['product_id'] ?? 0);
            $p = $productMap[$pId] ?? null;
            $marginPct = floatval($p['percentage'] ?? 6.0);
            $txCogs += ($qty * $price) * (1 - ($marginPct / 100));
        }

        if (!isset($monthlyTrends[$txMonth])) {
            $monthlyTrends[$txMonth] = ['revenue' => 0, 'cogs' => 0];
        }
        if ($txType === 'out') {
            $monthlyTrends[$txMonth]['revenue'] += $txTotal;
            $monthlyTrends[$txMonth]['cogs'] += $txCogs;
        } elseif ($txType === 'in') {
            $monthlyTrends[$txMonth]['revenue'] -= $txTotal;
            $monthlyTrends[$txMonth]['cogs'] -= $txCogs;
        }
    }
}

$netRevenue = $grossSales - $returnsDiscounts;
$grossProfit = $netRevenue - $cogs;
$grossMarginPct = $netRevenue > 0 ? ($grossProfit / $netRevenue) * 100 : 0;

// 3. Operating Expenses from existing database and damage logs only
// A. Local dealer withdrawals for the selected period
$dealerWithdrawalsSum = 0;
if ($pdo) {
    $stmt = $pdo->prepare("SELECT SUM(amount) FROM dealer_withdrawals WHERE withdrawal_date BETWEEN ? AND ?");
    $stmt->execute([$from_date, $to_date]);
    $dealerWithdrawalsSum = floatval($stmt->fetchColumn() ?: 0);
}

// B. Damaged Stock Expense
$damageExpense = 0;
foreach ($damageLogs as $d) {
    $dDate = $d['damage_date'] ?? '';
    if ($dDate >= $from_date && $dDate <= $to_date) {
        $damageExpense += floatval($d['damage_amount'] ?? 0);
    }
}

$totalExpenses = $dealerWithdrawalsSum + $damageExpense;
$netProfit = $grossProfit - $totalExpenses;
$netMarginPct = $netRevenue > 0 ? ($netProfit / $netRevenue) * 100 : 0;

// 4. Group Expenses by month for trend chart
ksort($monthlyTrends);
$chartMonths = array_slice(array_keys($monthlyTrends), -8);
$chartLabels = [];
$chartRevenue = [];
$chartExpenses = [];
$chartNetProfit = [];

foreach ($chartMonths as $m) {
    $dt = DateTime::createFromFormat('Y-m', $m);
    $chartLabels[] = $dt ? $dt->format('M Y') : $m;

    $rev = $monthlyTrends[$m]['revenue'] ?? 0;
    $cCost = $monthlyTrends[$m]['cogs'] ?? 0;
    $gProf = $rev - $cCost;

    // Get withdrawals for this month
    $mFrom = $m . "-01";
    $mTo = $m . "-31"; // simple end of month
    $mWithdrawals = 0;
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT SUM(amount) FROM dealer_withdrawals WHERE withdrawal_date BETWEEN ? AND ?");
        $stmt->execute([$mFrom, $mTo]);
        $mWithdrawals = floatval($stmt->fetchColumn() ?: 0);
    }
    
    // Get damage for this month
    $mDamage = 0;
    foreach ($damageLogs as $d) {
        $dDate = $d['damage_date'] ?? '';
        if (substr($dDate, 0, 7) === $m) {
            $mDamage += floatval($d['damage_amount'] ?? 0);
        }
    }

    $mExpenses = $mWithdrawals + $mDamage;

    $chartRevenue[] = $rev / 100000; // in Lakhs
    $chartExpenses[] = $mExpenses / 100000;
    $chartNetProfit[] = ($gProf - $mExpenses) / 100000;
}

include 'includes/header.php';
?>

<div class="max-w-7xl mx-auto space-y-6 animate-fade-in" style="animation-delay: 0.1s;">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Profit & Loss Engine</h2>
            <p class="text-gray-550 dark:text-gray-400 mt-1">Accurate accounting metrics combining local database and HappyCRM API data.</p>
        </div>
        <div class="flex items-center bg-gray-200 dark:bg-gray-800 p-0.5 rounded-lg border border-gray-300 dark:border-gray-700 shadow-sm self-start">
            <a href="?period=monthly" class="px-3 py-1.5 text-xs font-semibold rounded-md transition-all <?= $period === 'monthly' ? 'bg-white dark:bg-dark-card shadow-sm border border-gray-200 dark:border-gray-750 text-primary-600 dark:text-primary-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700' ?>">Monthly</a>
            <a href="?period=quarterly" class="px-3 py-1.5 text-xs font-semibold rounded-md transition-all <?= $period === 'quarterly' ? 'bg-white dark:bg-dark-card shadow-sm border border-gray-200 dark:border-gray-750 text-primary-600 dark:text-primary-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700' ?>">Quarterly</a>
            <a href="?period=yearly" class="px-3 py-1.5 text-xs font-semibold rounded-md transition-all <?= $period === 'yearly' ? 'bg-white dark:bg-dark-card shadow-sm border border-gray-200 dark:border-gray-750 text-primary-600 dark:text-primary-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700' ?>">Yearly</a>
        </div>
    </div>

    <!-- Date Range Selection Toolbar -->
    <div class="bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border rounded-xl p-4 shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date Filters:</span>
            <input type="date" name="from" value="<?= $from_date ?>" class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 px-3 py-1.5 rounded-lg focus:outline-none focus:border-primary-500 text-xs">
            <span class="text-xs text-gray-400">to</span>
            <input type="date" name="to" value="<?= $to_date ?>" class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 px-3 py-1.5 rounded-lg focus:outline-none focus:border-primary-500 text-xs">
            <button type="submit" class="px-4 py-1.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-xs font-semibold transition-colors shadow-sm">
                Apply Custom Range
            </button>
        </form>
        <div class="text-xs font-bold text-primary-650 dark:text-primary-400 bg-primary-50 dark:bg-primary-950/20 px-3.5 py-1.5 rounded-full border border-primary-200/50 dark:border-primary-900/30">
            Active Period: <?= htmlspecialchars($periodLabel) ?>
        </div>
    </div>

    <!-- P&L Metric Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-dark-card border border-blue-100 dark:border-dark-border rounded-xl p-4 shadow-sm hover-card relative overflow-hidden group">
            <div class="absolute right-3 top-3 text-3xl opacity-10 dark:opacity-20 text-blue-500"><i class="ph ph-handshake"></i></div>
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Net Sales Revenue</span>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-2">৳<?= number_format($netRevenue, 2) ?></p>
        </div>

        <div class="bg-white dark:bg-dark-card border border-blue-100 dark:border-dark-border rounded-xl p-4 shadow-sm hover-card relative overflow-hidden group">
            <div class="absolute right-3 top-3 text-3xl opacity-10 dark:opacity-20 text-emerald-500"><i class="ph ph-trend-up"></i></div>
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Gross Profit</span>
            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-450 mt-2">৳<?= number_format($grossProfit, 2) ?></p>
            <div class="text-[10px] text-gray-400 dark:text-gray-500 mt-1">Margin: <?= number_format($grossMarginPct, 2) ?>%</div>
        </div>

        <div class="bg-white dark:bg-dark-card border border-blue-100 dark:border-dark-border rounded-xl p-4 shadow-sm hover-card relative overflow-hidden group">
            <div class="absolute right-3 top-3 text-3xl opacity-10 dark:opacity-20 text-rose-500"><i class="ph ph-receipt"></i></div>
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Expenses</span>
            <p class="text-2xl font-bold text-rose-600 dark:text-rose-455 mt-2">৳<?= number_format($totalExpenses, 2) ?></p>
        </div>

        <div class="bg-white dark:bg-dark-card border border-blue-100 dark:border-dark-border rounded-xl p-4 shadow-sm hover-card relative overflow-hidden group">
            <div class="absolute right-3 top-3 text-3xl opacity-10 dark:opacity-20 text-orange-500"><i class="ph ph-coins"></i></div>
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Net Operating Profit</span>
            <p class="text-2xl font-bold text-orange-600 dark:text-orange-450 mt-2">৳<?= number_format($netProfit, 2) ?></p>
            <div class="text-[10px] text-gray-400 dark:text-gray-500 mt-1">Margin: <?= number_format($netMarginPct, 2) ?>%</div>
        </div>
    </div>

    <!-- P&L Content Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- P&L Detailed Statement Table -->
        <div class="bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border rounded-xl shadow-sm overflow-hidden flex flex-col">
            <div class="bg-primary-600 text-white text-xs uppercase tracking-wider p-3.5 border-b border-primary-700 font-semibold">
                Income Statement (Profit & Loss Sheet)
            </div>
            <div class="p-5 space-y-4 text-sm text-gray-700 dark:text-gray-300">
                <!-- Revenue section -->
                <div>
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 dark:border-dark-border pb-1 mb-2">I. Operating Revenue</h4>
                    <div class="flex justify-between py-1.5 px-2"><span>Gross Sales Revenue (Dispatched)</span><span class="font-mono text-gray-800 dark:text-white">৳<?= number_format($grossSales, 2) ?></span></div>
                    <div class="flex justify-between py-1.5 px-2 text-rose-600 dark:text-rose-455"><span>Returns & Discounts</span><span class="font-mono">-৳<?= number_format($returnsDiscounts, 2) ?></span></div>
                    <div class="flex justify-between py-2 px-2 bg-gray-50 dark:bg-slate-800/40 rounded-lg font-bold">
                        <span>Net Sales Revenue</span>
                        <span class="font-mono text-emerald-600 dark:text-emerald-400">৳<?= number_format($netRevenue, 2) ?></span>
                    </div>
                </div>

                <!-- COGS section -->
                <div>
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 dark:border-dark-border pb-1 mb-2">II. Cost of Goods Sold</h4>
                    <div class="flex justify-between py-1.5 px-2 text-rose-600 dark:text-rose-455"><span>Cost of Products Sold (Catalog Margin Base)</span><span class="font-mono">-৳<?= number_format($cogs, 2) ?></span></div>
                    <div class="flex justify-between py-2 px-2 bg-gray-50 dark:bg-slate-800/40 rounded-lg font-bold">
                        <span>Gross Profit</span>
                        <span class="font-mono text-emerald-600 dark:text-emerald-400">৳<?= number_format($grossProfit, 2) ?></span>
                    </div>
                    <div class="text-[11px] text-gray-400 dark:text-gray-500 text-right mt-1">Gross Margin: <?= number_format($grossMarginPct, 2) ?>%</div>
                </div>

                <!-- Expenses section -->
                <div>
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 dark:border-dark-border pb-1 mb-2">III. Operating Expenses</h4>
                    <div class="flex justify-between py-1.5 px-2 text-rose-600 dark:text-rose-455"><span>Dealer Cash Withdrawals (DB)</span><span class="font-mono">-৳<?= number_format($dealerWithdrawalsSum, 2) ?></span></div>
                    <div class="flex justify-between py-1.5 px-2 text-rose-600 dark:text-rose-455"><span>Inventory Damage / Losses (API)</span><span class="font-mono">-৳<?= number_format($damageExpense, 2) ?></span></div>
                    <div class="flex justify-between py-2 px-2 bg-gray-50 dark:bg-slate-800/40 rounded-lg font-bold">
                        <span>Total Operating Expenses</span>
                        <span class="font-mono text-rose-600 dark:text-rose-455">৳<?= number_format($totalExpenses, 2) ?></span>
                    </div>
                </div>

                <!-- Net profit row -->
                <div class="pt-3 border-t-2 border-dashed border-gray-200 dark:border-dark-border flex justify-between items-center bg-emerald-50/30 dark:bg-emerald-950/10 p-3 rounded-xl border border-emerald-100 dark:border-emerald-900/20">
                    <span class="text-base font-bold text-gray-800 dark:text-white">Net Operating Profit</span>
                    <span class="text-xl font-black font-mono text-emerald-600 dark:text-emerald-455">৳<?= number_format($netProfit, 2) ?></span>
                </div>
            </div>
        </div>

        <!-- P&L Trend Chart & Summary -->
        <div class="space-y-6 flex flex-col">
            <!-- P&L Monthly Bar/Line Chart -->
            <div class="bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border rounded-xl shadow-sm overflow-hidden flex-1 flex flex-col">
                <div class="bg-primary-600 text-white text-xs uppercase tracking-wider p-3.5 border-b border-primary-700 font-semibold flex justify-between items-center">
                    <span>P&L Performance Trend</span>
                    <span class="text-[10px] bg-primary-700 px-2 py-0.5 rounded text-primary-200">Last 8 Months (Lakh ৳)</span>
                </div>
                <div class="p-4 relative flex-1 min-h-[18rem]">
                    <canvas id="plTrendChart"></canvas>
                </div>
            </div>

            <!-- ROI and Performance breakdown -->
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-white dark:bg-dark-card border border-blue-100 dark:border-dark-border rounded-xl p-4 shadow-sm hover-card">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Gross Profit Margin</span>
                    <p class="text-2xl font-black text-emerald-600 dark:text-emerald-455 mt-1"><?= number_format($grossMarginPct, 1) ?>%</p>
                    <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1">Average profitability base</p>
                </div>
                <div class="bg-white dark:bg-dark-card border border-blue-100 dark:border-dark-border rounded-xl p-4 shadow-sm hover-card">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Net profit Margin</span>
                    <p class="text-2xl font-black text-blue-600 dark:text-blue-400 mt-1"><?= number_format($netMarginPct, 1) ?>%</p>
                    <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1">Target threshold: 8.5%</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = () => document.documentElement.classList.contains('dark');
    
    const colors = {
        blue: '#3b82f6',
        emerald: '#10b981',
        rose: '#f43f5e',
        textDark: '#1e293b',
        textLight: '#cbd5e1',
        gridLight: '#f1f5f9',
        gridDark: '#334155'
    };

    const getTextColor = () => isDark() ? colors.textLight : colors.textDark;
    const getGridColor = () => isDark() ? colors.gridDark : colors.gridLight;

    // P&L Trend Chart
    const plCtx = document.getElementById('plTrendChart').getContext('2d');
    const plTrendChart = new Chart(plCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($chartLabels) ?>,
            datasets: [
                {
                    label: 'Net Revenue',
                    data: <?= json_encode($chartRevenue) ?>,
                    backgroundColor: 'rgba(59, 130, 246, 0.75)',
                    borderRadius: 4
                },
                {
                    label: 'Expenses',
                    data: <?= json_encode($chartExpenses) ?>,
                    backgroundColor: 'rgba(244, 63, 94, 0.65)',
                    borderRadius: 4
                },
                {
                    label: 'Net Profit',
                    data: <?= json_encode($chartNetProfit) ?>,
                    type: 'line',
                    borderColor: colors.emerald,
                    pointBackgroundColor: colors.emerald,
                    pointRadius: 4,
                    borderWidth: 2,
                    tension: 0.35,
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: { color: getTextColor(), font: { size: 10 } }
                }
            },
            scales: {
                x: {
                    grid: { color: 'transparent' },
                    ticks: { color: getTextColor(), font: { size: 9 } }
                },
                y: {
                    grid: { color: getGridColor() },
                    ticks: { color: getTextColor(), font: { size: 9 } }
                }
            }
        }
    });

    // Theme changes observer to update chart tick colors dynamically
    const observer = new MutationObserver(() => {
        const textColor = getTextColor();
        const gridColor = getGridColor();

        plTrendChart.options.plugins.legend.labels.color = textColor;
        plTrendChart.options.scales.x.ticks.color = textColor;
        plTrendChart.options.scales.y.ticks.color = textColor;
        plTrendChart.options.scales.y.grid.color = gridColor;
        plTrendChart.update();
    });
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
});
</script>

<?php include 'includes/footer.php'; ?>
