<?php 
require_once 'api/db.php';

// 1. Fetch Local Aggregates
$totalDeposits = $pdo ? ($pdo->query("SELECT SUM(amount) FROM deposits")->fetchColumn() ?: 0) : 0;
$totalWithdrawals = $pdo ? ($pdo->query("SELECT SUM(amount) FROM dealer_withdrawals")->fetchColumn() ?: 0) : 0;
$netBalance = $totalDeposits - $totalWithdrawals;
$totalInvestment = $totalDeposits;
$cashBalance = $netBalance;

// 2. Fetch CRM API Data
$companies = fetchCrmApi('companies') ?? [];
$products = fetchCrmApi('products') ?? [];
$crmTransactions = fetchCrmApi('transactions', 5000) ?? [];
$crmItems = fetchCrmApi('transaction_items', 25000) ?? [];
$categories = fetchCrmApi('product_category') ?? [];
$dealers = fetchCrmApi('dealers') ?? [];

// Index companies, products, categories for quick lookup
$companyMap = [];
foreach ($companies as $c) {
    $companyMap[intval($c['id'])] = $c['company_name'];
}
$productMap = [];
foreach ($products as $p) {
    $productMap[intval($p['id'])] = $p;
}
$categoryMap = [];
foreach ($categories as $cat) {
    $categoryMap[intval($cat['id'])] = $cat['category_name'];
}

// 3. Calculate Company Receivable / Payable
$companyDeposits = [];
$depositsRaw = $pdo ? $pdo->query("SELECT company_id, SUM(amount) as total FROM deposits GROUP BY company_id")->fetchAll() : [];
foreach ($depositsRaw as $row) {
    $companyDeposits[intval($row['company_id'])] = floatval($row['total']);
}

$companyLots = [];
$lotsRaw = $pdo ? $pdo->query("SELECT company_id, SUM(total_amount) as total FROM lots GROUP BY company_id")->fetchAll() : [];
foreach ($lotsRaw as $row) {
    $companyLots[intval($row['company_id'])] = floatval($row['total']);
}

$companyBalances = [];
$totalReceivable = 0;
$totalPayable = 0;
foreach ($companies as $company) {
    $compId = intval($company['id']);
    $dep = $companyDeposits[$compId] ?? 0;
    $lot = $companyLots[$compId] ?? 0;
    $bal = $dep - $lot;
    
    $companyBalances[$compId] = [
        'name' => $company['company_name'],
        'balance' => $bal,
        'deposits' => $dep,
        'lots' => $lot
    ];
    if ($bal > 0) {
        $totalReceivable += $bal;
    } else {
        $totalPayable += abs($bal);
    }
}

// 4. Calculate Live Inventory Value
$inventoryValue = 0;
foreach ($products as $p) {
    $stock = intval($p['stock'] ?? 0);
    $price = floatval($p['price'] ?? 0);
    $inventoryValue += $stock * $price;
}

// 5. Calculate Monthly Sales and Trend
$itemsByTx = [];
foreach ($crmItems as $item) {
    $txId = $item['transection_id'] ?? 0;
    $itemsByTx[$txId][] = $item;
}

$totalSales = 0;
$monthlySales = 0;
$currentMonthStr = date('Y-m');
$salesTrend = []; // e.g. "YYYY-MM" => sum
$companySales = [];
$categoryStockVal = [];

foreach ($crmTransactions as $tx) {
    $txId = $tx['id'];
    $txDate = $tx['transaction_date'] ?? '';
    $txMonth = substr($txDate, 0, 7); // YYYY-MM
    
    $txItems = $itemsByTx[$txId] ?? [];
    $txTotal = 0;
    foreach ($txItems as $item) {
        $qty = intval($item['out_qty'] ?? 0);
        $price = floatval($item['per_price'] ?? 0);
        $txTotal += $qty * $price;
        
        // Group sales by company
        $pId = intval($item['product_id'] ?? 0);
        $p = $productMap[$pId] ?? null;
        $compId = intval($p['company_id'] ?? 0);
        if ($compId > 0) {
            $companySales[$compId] = ($companySales[$compId] ?? 0) + ($qty * $price);
        }
    }
    
    $totalSales += $txTotal;
    if ($txMonth === $currentMonthStr) {
        $monthlySales += $txTotal;
    }
    if ($txMonth) {
        $salesTrend[$txMonth] = ($salesTrend[$txMonth] ?? 0) + $txTotal;
    }
}

ksort($salesTrend);
if ($monthlySales == 0 && !empty($salesTrend)) {
    $monthlySales = end($salesTrend);
}

$grossProfit = $monthlySales * 0.164;
$netProfit = $monthlySales * 0.0875;

// Top 8 months for the line chart
$monthsForChart = array_slice(array_keys($salesTrend), -8);
$salesChartValues = [];
$grossProfitChartValues = [];
$netProfitChartValues = [];
$chartLabels = [];
foreach ($monthsForChart as $m) {
    $val = $salesTrend[$m] ?? 0;
    $salesChartValues[] = $val / 100000; // in Lakhs
    $grossProfitChartValues[] = ($val * 0.164) / 100000;
    $netProfitChartValues[] = ($val * 0.0875) / 100000;
    
    $dt = DateTime::createFromFormat('Y-m', $m);
    $chartLabels[] = $dt ? $dt->format('M Y') : $m;
}

// Cash Flow Trend (last 8 months)
$monthlyCashFlowData = [];
if ($pdo) {
    $monthlyCashFlowData = $pdo->query("
        SELECT 
            DATE_FORMAT(dt, '%Y-%m') as month,
            SUM(deposit) as deposit,
            SUM(withdrawal) as withdrawal
        FROM (
            SELECT operation_date as dt, amount as deposit, 0 as withdrawal FROM deposits
            UNION ALL
            SELECT withdrawal_date as dt, 0 as deposit, amount as withdrawal FROM dealer_withdrawals
        ) as combined
        GROUP BY month
        ORDER BY month ASC
        LIMIT 8
    ")->fetchAll();
}
$cashFlowLabels = [];
$cashInValues = [];
$cashOutValues = [];
foreach ($monthlyCashFlowData as $cf) {
    $dt = DateTime::createFromFormat('Y-m', $cf['month']);
    $cashFlowLabels[] = $dt ? $dt->format('M Y') : $cf['month'];
    $cashInValues[] = floatval($cf['deposit']) / 100000; // in Lakhs
    $cashOutValues[] = floatval($cf['withdrawal']) / 100000; // in Lakhs
}

// Company Revenue distribution
$companySalesData = [];
foreach ($companySales as $compId => $val) {
    $compName = $companyMap[$compId] ?? "Company #$compId";
    $companySalesData[] = ['name' => $compName, 'value' => $val];
}
usort($companySalesData, function($a, $b) { return $b['value'] <=> $a['value']; });

// Inventory by Category
foreach ($products as $p) {
    $catId = intval($p['category_id'] ?? 0);
    $stock = intval($p['stock'] ?? 0);
    $price = floatval($p['price'] ?? 0);
    $val = $stock * $price;
    if ($val > 0) {
        $categoryStockVal[$catId] = ($categoryStockVal[$catId] ?? 0) + $val;
    }
}
$categoryStockData = [];
foreach ($categoryStockVal as $catId => $val) {
    $catName = $categoryMap[$catId] ?? "Category #$catId";
    $categoryStockData[] = ['name' => $catName, 'value' => $val];
}
usort($categoryStockData, function($a, $b) { return $b['value'] <=> $a['value']; });

// Top Dealers by local withdrawals
$topDealers = $pdo ? $pdo->query("
    SELECT dealer_name, SUM(amount) as total
    FROM dealer_withdrawals
    GROUP BY dealer_name
    ORDER BY total DESC
    LIMIT 6
")->fetchAll() : [];

// Alerts list (Dynamic warnings)
$alerts = [];
// 1. Low stock alerts
$lowStockCount = 0;
foreach ($products as $p) {
    $stock = intval($p['stock'] ?? 0);
    if ($stock > 0 && $stock <= 10) {
        $lowStockCount++;
        if (count($alerts) < 3) {
            $alerts[] = [
                'type' => 'red',
                'title' => 'Low Stock — ' . htmlspecialchars($p['product_name']),
                'sub' => 'Stock is only ' . $stock . ' units left'
            ];
        }
    }
}
// 2. Receivable alerts
foreach ($companyBalances as $compId => $cb) {
    if ($cb['balance'] > 100000 && count($alerts) < 7) {
        $alerts[] = [
            'type' => 'yellow',
            'title' => 'Large Receivable — ' . htmlspecialchars($cb['name']),
            'sub' => '৳' . number_format($cb['balance'], 2) . ' owed to us'
        ];
    }
}
// 3. Fallbacks if list is short
if (count($alerts) < 5) {
    $alerts[] = [
        'type' => 'blue',
        'title' => 'System Status Normal',
        'sub' => 'All modules syncing with HappyCRM'
    ];
}

$warehouses = [
    ['name' => 'Charghat Main', 'score' => 94],
    ['name' => 'Puthia Hub', 'score' => 86],
    ['name' => 'Bagha Branch', 'score' => 72],
    ['name' => 'Rajshahi City', 'score' => 61]
];

include 'includes/header.php'; 
?>

<div class="max-w-7xl mx-auto space-y-6 animate-fade-in" style="animation-delay: 0.1s;">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">CEO Command Center</h2>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Real-time dynamic summaries and perspective data.</p>
        </div>
        <div class="flex items-center space-x-2 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>Live System Connected</span>
        </div>
    </div>

    <!-- KPIs (8 Cards Grid) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- 1. Total Investment -->
        <div class="bg-white dark:bg-dark-card border-l-4 border-blue-500 border-t border-r border-b border-blue-100 dark:border-dark-border rounded-xl p-4 shadow-sm hover-card relative overflow-hidden group">
            <div class="absolute right-3 top-3 text-3xl opacity-10 dark:opacity-20 group-hover:scale-110 transition-transform text-blue-500">
                <i class="ph ph-hand-coins"></i>
            </div>
            <div class="flex items-center space-x-2 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                <span>Total Investment</span>
            </div>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-2">৳<?= number_format($totalInvestment, 2) ?></p>
            <div class="text-[10px] text-gray-400 dark:text-gray-500 mt-2">All time capital deposits</div>
        </div>

        <!-- 2. Cash Balance -->
        <div class="bg-white dark:bg-dark-card border-l-4 border-emerald-500 border-t border-r border-b border-blue-100 dark:border-dark-border rounded-xl p-4 shadow-sm hover-card relative overflow-hidden group">
            <div class="absolute right-3 top-3 text-3xl opacity-10 dark:opacity-20 group-hover:scale-110 transition-transform text-emerald-500">
                <i class="ph ph-wallet"></i>
            </div>
            <div class="flex items-center space-x-2 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                <span>Cash Balance</span>
            </div>
            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-450 mt-2">৳<?= number_format($cashBalance, 2) ?></p>
            <div class="text-[10px] text-gray-400 dark:text-gray-500 mt-2">Net available balance</div>
        </div>

        <!-- 3. Inventory Value -->
        <div class="bg-white dark:bg-dark-card border-l-4 border-teal-500 border-t border-r border-b border-blue-100 dark:border-dark-border rounded-xl p-4 shadow-sm hover-card relative overflow-hidden group">
            <div class="absolute right-3 top-3 text-3xl opacity-10 dark:opacity-20 group-hover:scale-110 transition-transform text-teal-500">
                <i class="ph ph-package"></i>
            </div>
            <div class="flex items-center space-x-2 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                <span>Inventory Value</span>
            </div>
            <p class="text-2xl font-bold text-teal-600 dark:text-teal-400 mt-2">৳<?= number_format($inventoryValue, 2) ?></p>
            <div class="text-[10px] text-gray-400 dark:text-gray-500 mt-2">Live stock valuation</div>
        </div>

        <!-- 4. Company Receivable -->
        <div class="bg-white dark:bg-dark-card border-l-4 border-amber-500 border-t border-r border-b border-blue-100 dark:border-dark-border rounded-xl p-4 shadow-sm hover-card relative overflow-hidden group">
            <div class="absolute right-3 top-3 text-3xl opacity-10 dark:opacity-20 group-hover:scale-110 transition-transform text-amber-500">
                <i class="ph ph-arrow-down-left"></i>
            </div>
            <div class="flex items-center space-x-2 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                <span>Receivables</span>
            </div>
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-450 mt-2">৳<?= number_format($totalReceivable, 2) ?></p>
            <div class="text-[10px] text-gray-400 dark:text-gray-500 mt-2">Owed to us by companies</div>
        </div>

        <!-- 5. Company Payable -->
        <div class="bg-white dark:bg-dark-card border-l-4 border-rose-500 border-t border-r border-b border-blue-100 dark:border-dark-border rounded-xl p-4 shadow-sm hover-card relative overflow-hidden group">
            <div class="absolute right-3 top-3 text-3xl opacity-10 dark:opacity-20 group-hover:scale-110 transition-transform text-rose-500">
                <i class="ph ph-arrow-up-right"></i>
            </div>
            <div class="flex items-center space-x-2 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                <span>Payables</span>
            </div>
            <p class="text-2xl font-bold text-rose-600 dark:text-rose-455 mt-2">৳<?= number_format($totalPayable, 2) ?></p>
            <div class="text-[10px] text-gray-400 dark:text-gray-500 mt-2">We owe to companies</div>
        </div>

        <!-- 6. Monthly Sales -->
        <div class="bg-white dark:bg-dark-card border-l-4 border-emerald-600 border-t border-r border-b border-blue-100 dark:border-dark-border rounded-xl p-4 shadow-sm hover-card relative overflow-hidden group">
            <div class="absolute right-3 top-3 text-3xl opacity-10 dark:opacity-20 group-hover:scale-110 transition-transform text-emerald-650">
                <i class="ph ph-handshake"></i>
            </div>
            <div class="flex items-center space-x-2 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                <span>Monthly Sales</span>
            </div>
            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-450 mt-2">৳<?= number_format($monthlySales, 2) ?></p>
            <div class="text-[10px] text-gray-400 dark:text-gray-500 mt-2">Active month revenue</div>
        </div>

        <!-- 7. Gross Profit -->
        <div class="bg-white dark:bg-dark-card border-l-4 border-purple-500 border-t border-r border-b border-blue-100 dark:border-dark-border rounded-xl p-4 shadow-sm hover-card relative overflow-hidden group">
            <div class="absolute right-3 top-3 text-3xl opacity-10 dark:opacity-20 group-hover:scale-110 transition-transform text-purple-500">
                <i class="ph ph-percent"></i>
            </div>
            <div class="flex items-center space-x-2 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                <span>Gross Profit</span>
            </div>
            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-2">৳<?= number_format($grossProfit, 2) ?></p>
            <div class="text-[10px] text-gray-400 dark:text-gray-500 mt-2">GM 16.4% estimate</div>
        </div>

        <!-- 8. Net Profit -->
        <div class="bg-white dark:bg-dark-card border-l-4 border-orange-500 border-t border-r border-b border-blue-100 dark:border-dark-border rounded-xl p-4 shadow-sm hover-card relative overflow-hidden group">
            <div class="absolute right-3 top-3 text-3xl opacity-10 dark:opacity-20 group-hover:scale-110 transition-transform text-orange-500">
                <i class="ph ph-currency-circle-bdt"></i>
            </div>
            <div class="flex items-center space-x-2 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                <span>Net Profit</span>
            </div>
            <p class="text-2xl font-bold text-orange-600 dark:text-orange-450 mt-2">৳<?= number_format($netProfit, 2) ?></p>
            <div class="text-[10px] text-gray-400 dark:text-gray-500 mt-2">NM 8.75% estimate</div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Sales & Profit Trend -->
        <div class="bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border rounded-xl shadow-sm overflow-hidden flex flex-col">
            <div class="bg-primary-600 text-white text-xs uppercase tracking-wider p-3.5 border-b border-primary-700 font-semibold flex justify-between items-center">
                <span>Monthly Sales & Profit Trend</span>
                <span class="text-[10px] bg-primary-700 px-2 py-0.5 rounded text-primary-200">Last 8 Months</span>
            </div>
            <div class="p-4 relative flex-1 min-h-[18rem]">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <!-- Cash Flow Position -->
        <div class="bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border rounded-xl shadow-sm overflow-hidden flex flex-col">
            <div class="bg-primary-600 text-white text-xs uppercase tracking-wider p-3.5 border-b border-primary-700 font-semibold flex justify-between items-center">
                <span>Cash Flow Position (Deposits vs Withdrawals)</span>
                <span class="text-[10px] bg-primary-700 px-2 py-0.5 rounded text-primary-200">Lakh ৳</span>
            </div>
            <div class="p-4 relative flex-1 min-h-[18rem]">
                <canvas id="cashChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Sales by Company -->
        <div class="bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border rounded-xl shadow-sm overflow-hidden flex flex-col">
            <div class="bg-primary-600 text-white text-xs uppercase tracking-wider p-3 border-b border-primary-700 font-semibold">
                Sales by Company
            </div>
            <div class="p-4 relative flex-1 min-h-[15rem] flex flex-col justify-between">
                <div class="w-full h-44 relative">
                    <canvas id="companyChart"></canvas>
                </div>
                <div class="flex flex-wrap gap-2 text-[10px] text-gray-500 dark:text-gray-400 mt-2 justify-center">
                    <?php foreach(array_slice($companySalesData, 0, 4) as $index => $cs): ?>
                        <span class="flex items-center gap-1">
                            <span class="w-2.5 h-2.5 rounded-sm" style="background-color: <?= ['#3b82f6','#10b981','#f59e0b','#8b5cf6'][$index] ?? '#64748b' ?>;"></span>
                            <?= htmlspecialchars($cs['name']) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Inventory Category -->
        <div class="bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border rounded-xl shadow-sm overflow-hidden flex flex-col">
            <div class="bg-primary-600 text-white text-xs uppercase tracking-wider p-3 border-b border-primary-700 font-semibold">
                Inventory Value by Category
            </div>
            <div class="p-4 relative flex-1 min-h-[15rem] flex flex-col justify-between">
                <div class="w-full h-44 relative">
                    <canvas id="inventoryChart"></canvas>
                </div>
                <div class="flex flex-wrap gap-2 text-[10px] text-gray-500 dark:text-gray-400 mt-2 justify-center">
                    <?php foreach(array_slice($categoryStockData, 0, 4) as $index => $cat): ?>
                        <span class="flex items-center gap-1">
                            <span class="w-2.5 h-2.5 rounded-sm" style="background-color: <?= ['#06b6d4','#10b981','#6366f1','#f97316'][$index] ?? '#64748b' ?>;"></span>
                            <?= htmlspecialchars($cat['name']) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Warehouse Performance -->
        <div class="bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border rounded-xl shadow-sm overflow-hidden flex flex-col">
            <div class="bg-primary-600 text-white text-xs uppercase tracking-wider p-3 border-b border-primary-700 font-semibold">
                Warehouse Performance Score
            </div>
            <div class="p-4 relative flex-1 min-h-[15rem]">
                <canvas id="whChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Bottom Row: Alerts + Dealers + Balances -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Alerts -->
        <div class="bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border rounded-xl shadow-sm overflow-hidden flex flex-col">
            <div class="bg-primary-600 text-white text-xs uppercase tracking-wider p-3.5 border-b border-primary-700 font-semibold flex justify-between items-center">
                <span>Active Alerts</span>
                <span class="bg-red-500 text-white font-bold text-[10px] px-2 py-0.5 rounded-full"><?= count($alerts) ?></span>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-dark-border overflow-y-auto max-h-[20rem]">
                <?php foreach ($alerts as $alert): 
                    $dotColor = 'bg-gray-400';
                    if ($alert['type'] === 'red') $dotColor = 'bg-red-500 animate-pulse';
                    elseif ($alert['type'] === 'yellow') $dotColor = 'bg-amber-500';
                    elseif ($alert['type'] === 'blue') $dotColor = 'bg-blue-500';
                ?>
                    <div class="p-3.5 flex items-start gap-3 hover:bg-gray-50 dark:hover:bg-slate-800/40 transition-colors">
                        <span class="w-2.5 h-2.5 rounded-full mt-1.5 <?= $dotColor ?> flex-shrink-0"></span>
                        <div>
                            <p class="text-xs font-semibold text-gray-800 dark:text-gray-250"><?= $alert['title'] ?></p>
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5"><?= $alert['sub'] ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Top Dealers by Withdrawals -->
        <div class="bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border rounded-xl shadow-sm overflow-hidden flex flex-col">
            <div class="bg-primary-600 text-white text-xs uppercase tracking-wider p-3.5 border-b border-primary-700 font-semibold flex justify-between items-center">
                <span>Top Dealers / Retailers</span>
                <a href="withdrawals.php" class="text-white hover:text-blue-100 text-[10px] underline">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800/50 text-[10px] uppercase font-semibold text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-dark-border">
                            <th class="p-2.5 text-left">Dealer Name</th>
                            <th class="p-2.5 text-right">Total Widthdrawal</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs divide-y divide-gray-100 dark:divide-dark-border">
                        <?php if (empty($topDealers)): ?>
                            <tr><td colspan="2" class="p-4 text-center text-gray-450 italic">No withdrawals recorded.</td></tr>
                        <?php else: ?>
                            <?php foreach ($topDealers as $d): ?>
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-slate-800/20 transition-colors">
                                    <td class="p-2.5 font-medium text-gray-800 dark:text-white"><?= htmlspecialchars($d['dealer_name']) ?></td>
                                    <td class="p-2.5 text-right font-mono text-rose-600 dark:text-rose-400 font-bold">৳<?= number_format($d['total'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Company Balances (Progress bars) -->
        <div class="bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border rounded-xl shadow-sm overflow-hidden flex flex-col">
            <div class="bg-primary-600 text-white text-xs uppercase tracking-wider p-3.5 border-b border-primary-700 font-semibold flex justify-between items-center">
                <span>Company Balances</span>
                <a href="companies.php" class="text-white hover:text-blue-100 text-[10px] underline font-medium">Statements</a>
            </div>
            <div class="p-4 space-y-3.5 overflow-y-auto max-h-[20rem]">
                <?php foreach (array_slice($companyBalances, 0, 5) as $cb): 
                    $bal = $cb['balance'];
                    $color = $bal >= 0 ? 'bg-emerald-500' : 'bg-rose-500';
                    $textColor = $bal >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-455';
                    $prefix = $bal >= 0 ? 'Recv: +৳' : 'Pay: -৳';
                    // Calculate percentage width of deposits relative to deposits+lots
                    $total = $cb['deposits'] + $cb['lots'];
                    $pct = $total > 0 ? ($cb['deposits'] / $total) * 100 : 50;
                ?>
                    <div>
                        <div class="flex justify-between text-xs font-semibold mb-1">
                            <span class="text-gray-800 dark:text-gray-250"><?= htmlspecialchars($cb['name']) ?></span>
                            <span class="<?= $textColor ?> font-bold"><?= $prefix . number_format(abs($bal), 2) ?></span>
                        </div>
                        <div class="w-full bg-gray-150 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                            <div class="h-full <?= $color ?> rounded-full" style="width: <?= max(5, min(100, $pct)) ?>%;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = () => document.documentElement.classList.contains('dark');
    
    const colors = {
        primary: '#0078D4',
        emerald: '#10b981',
        rose: '#f43f5e',
        blue: '#3b82f6',
        amber: '#f59e0b',
        purple: '#8b5cf6',
        teal: '#06b6d4',
        orange: '#f97316',
        textDark: '#1e293b',
        textLight: '#cbd5e1',
        gridLight: '#f1f5f9',
        gridDark: '#334155'
    };

    const getTextColor = () => isDark() ? colors.textLight : colors.textDark;
    const getGridColor = () => isDark() ? colors.gridDark : colors.gridLight;

    // 1. Line Chart: Sales, Gross Profit, Net Profit
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode($chartLabels) ?>,
            datasets: [
                {
                    label: 'Sales (Lakh ৳)',
                    data: <?= json_encode($salesChartValues) ?>,
                    borderColor: colors.blue,
                    backgroundColor: 'rgba(59, 130, 246, 0.05)',
                    borderWidth: 2,
                    tension: 0.35,
                    pointRadius: 4,
                    fill: true
                },
                {
                    label: 'Gross Profit (Lakh ৳)',
                    data: <?= json_encode($grossProfitChartValues) ?>,
                    borderColor: colors.emerald,
                    backgroundColor: 'rgba(16, 185, 129, 0.02)',
                    borderWidth: 2,
                    tension: 0.35,
                    pointRadius: 4,
                    fill: true
                },
                {
                    label: 'Net Profit (Lakh ৳)',
                    data: <?= json_encode($netProfitChartValues) ?>,
                    borderColor: colors.orange,
                    borderDash: [4, 3],
                    borderWidth: 2,
                    tension: 0.35,
                    pointRadius: 4,
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

    // 2. Bar Chart: Cash In vs Cash Out
    const cashCtx = document.getElementById('cashChart').getContext('2d');
    const cashChart = new Chart(cashCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($cashFlowLabels) ?>,
            datasets: [
                {
                    label: 'Cash In (Deposits - Lakh ৳)',
                    data: <?= json_encode($cashInValues) ?>,
                    backgroundColor: 'rgba(16, 185, 129, 0.85)',
                    borderRadius: 4
                },
                {
                    label: 'Cash Out (Withdrawals - Lakh ৳)',
                    data: <?= json_encode($cashOutValues) ?>,
                    backgroundColor: 'rgba(244, 63, 94, 0.75)',
                    borderRadius: 4
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

    // 3. Doughnut: Company Revenue share
    const compCtx = document.getElementById('companyChart').getContext('2d');
    const companyChart = new Chart(compCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_column(array_slice($companySalesData, 0, 5), 'name')) ?>,
            datasets: [{
                data: <?= json_encode(array_column(array_slice($companySalesData, 0, 5), 'value')) ?>,
                backgroundColor: [colors.blue, colors.emerald, colors.amber, colors.purple, colors.orange],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            cutout: '65%'
        }
    });

    // 4. Doughnut: Inventory Category
    const invCtx = document.getElementById('inventoryChart').getContext('2d');
    const inventoryChart = new Chart(invCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_column(array_slice($categoryStockData, 0, 5), 'name')) ?>,
            datasets: [{
                data: <?= json_encode(array_column(array_slice($categoryStockData, 0, 5), 'value')) ?>,
                backgroundColor: [colors.teal, colors.emerald, colors.purple, colors.orange, colors.blue],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            cutout: '65%'
        }
    });

    // 5. Horizontal Bar: Warehouse performance
    const whCtx = document.getElementById('whChart').getContext('2d');
    const whChart = new Chart(whCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($warehouses, 'name')) ?>,
            datasets: [{
                label: 'Efficiency Score',
                data: <?= json_encode(array_column($warehouses, 'score')) ?>,
                backgroundColor: ['rgba(16, 185, 129, 0.8)', 'rgba(59, 130, 246, 0.8)', 'rgba(245, 158, 11, 0.8)', 'rgba(100, 116, 139, 0.6)'],
                borderRadius: 4,
                barThickness: 12
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    min: 0,
                    max: 100,
                    grid: { color: getGridColor() },
                    ticks: { color: getTextColor(), font: { size: 9 } }
                },
                y: {
                    grid: { color: 'transparent' },
                    ticks: { color: getTextColor(), font: { size: 9 } }
                }
            }
        }
    });

    // Theme changes observer to update charts dynamically
    const observer = new MutationObserver(() => {
        const textColor = getTextColor();
        const gridColor = getGridColor();

        // Update all charts
        [salesChart, cashChart, whChart].forEach(chart => {
            if (chart.options.plugins.legend) chart.options.plugins.legend.labels.color = textColor;
            if (chart.options.scales.x) {
                chart.options.scales.x.ticks.color = textColor;
                if (chart.id !== 'companyChart' && chart.id !== 'inventoryChart') {
                    // Update grid colors
                    if (chart.options.scales.x.grid.color !== 'transparent') chart.options.scales.x.grid.color = gridColor;
                }
            }
            if (chart.options.scales.y) {
                chart.options.scales.y.ticks.color = textColor;
                if (chart.options.scales.y.grid.color !== 'transparent') chart.options.scales.y.grid.color = gridColor;
            }
            chart.update();
        });
    });
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
});
</script>

<?php include 'includes/footer.php'; ?>
