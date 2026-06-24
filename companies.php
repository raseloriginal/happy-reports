<?php
require_once 'api/db.php';

// Fetch companies and products
$companies = fetchCrmApi('companies');
$products = fetchCrmApi('products');

$error = null;
if ($companies === null) {
    $error = "Failed to retrieve companies from HappyCRM API.";
    $companies = [];
}
if ($products === null) {
    $products = [];
}

// Fetch local deposits and lots
$deposits = [];
$lots = [];
$companyLotsSum = [];
if ($pdo) {
    try {
        $deposits = $pdo->query("SELECT * FROM deposits")->fetchAll();
        $lots = $pdo->query("SELECT * FROM lots")->fetchAll();
        foreach ($lots as $lot) {
            $compId = intval($lot['company_id']);
            if (!isset($companyLotsSum[$compId])) {
                $companyLotsSum[$compId] = 0;
            }
            $companyLotsSum[$compId] += floatval($lot['total_amount']);
        }
    } catch (Exception $e) {
        $error = "Failed to retrieve local financial data: " . $e->getMessage();
    }
}

// Calculate product counts per company
$companyProductCounts = [];
$companyProducts = [];
foreach ($products as $p) {
    $compId = $p['company_id'] ?? 0;
    if (!isset($companyProductCounts[$compId])) {
        $companyProductCounts[$compId] = 0;
        $companyProducts[$compId] = [];
    }
    $companyProductCounts[$compId]++;
    $companyProducts[$compId][] = $p;
}

// Calculate deposits sum per company
$companyDepositsSum = [];
foreach ($deposits as $dep) {
    $compId = intval($dep['company_id']);
    if (!isset($companyDepositsSum[$compId])) {
        $companyDepositsSum[$compId] = 0;
    }
    $companyDepositsSum[$compId] += floatval($dep['amount']);
}

$totalCompanies = count($companies);
$totalProducts = count($products);
$avgProducts = $totalCompanies > 0 ? round($totalProducts / $totalCompanies, 1) : 0;

include 'includes/header.php';
?>

<div class="max-w-7xl mx-auto space-y-6 animate-fade-in pb-12" style="animation-delay: 0.1s;">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">CRM Companies</h2>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Directory of registered companies and product portfolios in HappyCRM.</p>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/50 text-red-700 dark:text-red-400 p-4 rounded-xl flex items-center">
            <i class="ph ph-warning-circle text-2xl mr-3"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Card 1 -->
        <div class="bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border rounded-xl p-5 shadow-sm hover-card">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Total Companies</span>
                <div class="w-9 h-9 bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/50 rounded-lg flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                    <i class="ph ph-buildings text-xl"></i>
                </div>
            </div>
            <p class="text-3xl font-extrabold text-gray-850 dark:text-white"><?= number_format($totalCompanies) ?></p>
            <p class="text-xs text-gray-400 mt-1">Unique brand entities</p>
        </div>

        <!-- Card 2 -->
        <div class="bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border rounded-xl p-5 shadow-sm hover-card">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Total Products</span>
                <div class="w-9 h-9 bg-amber-50 dark:bg-amber-950/40 border border-amber-100 dark:border-amber-900/50 rounded-lg flex items-center justify-center text-amber-600 dark:text-amber-400">
                    <i class="ph ph-package text-xl"></i>
                </div>
            </div>
            <p class="text-3xl font-extrabold text-gray-850 dark:text-white"><?= number_format($totalProducts) ?></p>
            <p class="text-xs text-gray-400 mt-1">Items linked across companies</p>
        </div>

        <!-- Card 3 -->
        <div class="bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border rounded-xl p-5 shadow-sm hover-card">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Avg. Products / Co</span>
                <div class="w-9 h-9 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-100 dark:border-emerald-900/50 rounded-lg flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                    <i class="ph ph-chart-bar text-xl"></i>
                </div>
            </div>
            <p class="text-3xl font-extrabold text-gray-850 dark:text-white"><?= $avgProducts ?></p>
            <p class="text-xs text-gray-400 mt-1">Average items per catalog</p>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border rounded-xl p-4 shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
        <div class="relative w-full md:w-96">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                <i class="ph ph-magnifying-glass text-lg"></i>
            </span>
            <input type="text" id="companySearch" oninput="filterCompanies()" placeholder="Search companies..." class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 pl-10 pr-4 py-2.5 rounded-lg focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors text-sm">
        </div>
        <div class="text-xs font-medium text-gray-500 dark:text-gray-400">
            Showing <span id="visibleCount" class="font-bold text-indigo-650 dark:text-indigo-400"><?= $totalCompanies ?></span> of <?= $totalCompanies ?> companies
        </div>
    </div>

    <!-- Grid Layout of Companies -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="companiesGrid">
        <?php foreach ($companies as $company): 
            $compId = $company['id'];
            $pCount = $companyProductCounts[$compId] ?? 0;
            $pList = $companyProducts[$compId] ?? [];
        ?>
            <div class="company-card bg-white dark:bg-dark-card border border-blue-100 dark:border-dark-border rounded-xl shadow-sm overflow-hidden flex flex-col hover-card" data-name="<?= htmlspecialchars(strtolower($company['company_name'])) ?>">
                <!-- Accent top bar -->
                <div class="h-1.5 bg-gradient-to-r from-indigo-500 to-blue-500"></div>
                
                <div class="p-5 flex-1 flex flex-col">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold border border-indigo-100 dark:border-indigo-800/50">
                                <i class="ph ph-factory text-xl"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800 dark:text-white text-base leading-tight"><?= htmlspecialchars($company['company_name']) ?></h4>
                                <p class="text-xs text-gray-400 mt-0.5">ID: <?= htmlspecialchars($compId) ?></p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 border border-indigo-100/50 dark:border-indigo-800/30">
                            <?= $pCount ?> Products
                        </span>
                    </div>

                    <hr class="border-gray-100 dark:border-dark-border mb-4">

                    <!-- Mini catalog preview -->
                    <div class="flex-1">
                        <h5 class="text-xs font-semibold text-gray-450 dark:text-gray-500 uppercase tracking-wider mb-2">Featured Products</h5>
                        <?php if (empty($pList)): ?>
                            <p class="text-xs text-gray-400 italic">No products found for this company.</p>
                        <?php else: ?>
                            <ul class="space-y-2 max-h-40 overflow-y-auto pr-1">
                                <?php foreach (array_slice($pList, 0, 4) as $prod): ?>
                                    <li class="flex items-center justify-between text-xs py-1 px-1.5 rounded hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                        <span class="text-gray-700 dark:text-gray-300 font-medium truncate max-w-[150px]"><?= htmlspecialchars($prod['product_name']) ?></span>
                                        <span class="text-gray-400 dark:text-gray-500">৳<?= number_format($prod['price'], 2) ?></span>
                                    </li>
                                <?php endforeach; ?>
                                <?php if (count($pList) > 4): ?>
                                    <li class="text-[11px] text-indigo-500 dark:text-indigo-400 pt-1 font-medium italic text-right">
                                        +<?= count($pList) - 4 ?> more products
                                    </li>
                                <?php endif; ?>
                            </ul>
                        <?php endif; ?>
                    </div>

                    <?php
                    $balance = ($companyDepositsSum[$compId] ?? 0) - ($companyLotsSum[$compId] ?? 0);
                    ?>

                    <!-- Financial Status -->
                    <div class="mt-4 pt-3 border-t border-gray-100 dark:border-dark-border flex items-center justify-between">
                        <div>
                            <span class="block text-[10px] text-gray-400 dark:text-gray-500 uppercase tracking-wider font-semibold">Balance</span>
                            <span class="font-bold text-sm <?= $balance >= 0 ? 'text-emerald-600 dark:text-emerald-450' : 'text-rose-600 dark:text-rose-450' ?>">
                                ৳<?= number_format($balance, 2) ?>
                            </span>
                        </div>
                        <a href="company_ledger.php?id=<?= $compId ?>" class="flex items-center space-x-1.5 py-1.5 px-3 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-750 dark:bg-indigo-950/40 dark:hover:bg-indigo-900/40 dark:text-indigo-400 font-semibold text-xs border border-indigo-100/50 dark:border-indigo-900/50 transition-colors shadow-sm">
                            <i class="ph ph-receipt"></i>
                            <span>Ledger Statement</span>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Empty search state -->
    <div id="emptySearchState" class="hidden text-center py-16 bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border rounded-xl shadow-sm">
        <i class="ph ph-detective text-5xl text-gray-400 mb-3"></i>
        <h4 class="text-lg font-bold text-gray-700 dark:text-gray-300">No Companies Found</h4>
        <p class="text-sm text-gray-400 mt-1">Try adjusting your search terms.</p>
    </div>
</div>

<script>
function filterCompanies() {
    const query = document.getElementById('companySearch').value.toLowerCase().trim();
    const cards = document.querySelectorAll('.company-card');
    let visibleCount = 0;

    cards.forEach(card => {
        const name = card.getAttribute('data-name');
        if (name.includes(query)) {
            card.classList.remove('hidden');
            visibleCount++;
        } else {
            card.classList.add('hidden');
        }
    });

    document.getElementById('visibleCount').textContent = visibleCount;
    const emptyState = document.getElementById('emptySearchState');
    if (visibleCount === 0) {
        emptyState.classList.remove('hidden');
        document.getElementById('companiesGrid').classList.add('hidden');
    } else {
        emptyState.classList.add('hidden');
        document.getElementById('companiesGrid').classList.remove('hidden');
    }
}
</script>

<?php include 'includes/footer.php'; ?>
