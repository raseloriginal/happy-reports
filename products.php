<?php
require_once 'api/db.php';

// Fetch data
$products = fetchCrmApi('products');
$companies = fetchCrmApi('companies') ?? [];
$categories = fetchCrmApi('product_category') ?? [];

$error = null;
if ($products === null) {
    $error = "Failed to retrieve products from HappyCRM API.";
    $products = [];
}

// Index companies and categories for easy lookup
$companyMap = [];
foreach ($companies as $c) {
    $companyMap[$c['id']] = $c['company_name'];
}

$categoryMap = [];
foreach ($categories as $cat) {
    $categoryMap[$cat['id']] = $cat['category_name'];
}

// Compute metrics
$totalProducts = count($products);
$outOfStock = 0;
$lowStock = 0;
$totalVal = 0;

foreach ($products as $p) {
    $stock = intval($p['stock'] ?? 0);
    $price = floatval($p['price'] ?? 0);
    if ($stock === 0) {
        $outOfStock++;
    } elseif ($stock <= 10) {
        $lowStock++;
    }
    $totalVal += $price;
}

$avgPrice = $totalProducts > 0 ? $totalVal / $totalProducts : 0;

include 'includes/header.php';
?>
<style>
.lazy-hidden {
    display: none !important;
}
</style>

<div class="max-w-7xl mx-auto space-y-6 animate-fade-in pb-12" style="animation-delay: 0.1s;">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">CRM Products Catalog</h2>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Live listing and inventory of products from HappyCRM.</p>
        </div>
        <!-- View Toggle -->
        <div class="flex items-center bg-gray-200 dark:bg-gray-800 p-0.5 rounded-lg border border-gray-300 dark:border-gray-700 shadow-sm self-start md:self-auto">
            <button onclick="setView('grid')" id="btn-view-grid" class="flex items-center space-x-1.5 px-3 py-1.5 text-xs font-semibold rounded-md transition-all text-gray-700 dark:text-gray-300 bg-white dark:bg-dark-card shadow-sm border border-gray-200 dark:border-gray-750">
                <i class="ph ph-squares-four"></i> <span>Grid</span>
            </button>
            <button onclick="setView('list')" id="btn-view-list" class="flex items-center space-x-1.5 px-3 py-1.5 text-xs font-semibold rounded-md transition-all text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                <i class="ph ph-table"></i> <span>Spreadsheet</span>
            </button>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/50 text-red-700 dark:text-red-400 p-4 rounded-xl flex items-center">
            <i class="ph ph-warning-circle text-2xl mr-3"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <!-- Metric Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Card 1 -->
        <div class="bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border rounded-xl p-4 shadow-sm hover-card">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Products</span>
                <div class="w-8 h-8 bg-amber-50 dark:bg-amber-950/30 border border-amber-100 dark:border-amber-900/55 rounded-lg flex items-center justify-center text-amber-500">
                    <i class="ph ph-package text-lg"></i>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-gray-850 dark:text-white"><?= number_format($totalProducts) ?></p>
            <p class="text-xs text-gray-400 mt-1">Items in active inventory</p>
        </div>

        <!-- Card 2 -->
        <div class="bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border rounded-xl p-4 shadow-sm hover-card">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Out of Stock</span>
                <div class="w-8 h-8 bg-rose-50 dark:bg-rose-950/30 border border-rose-100 dark:border-rose-900/55 rounded-lg flex items-center justify-center text-rose-500">
                    <i class="ph ph-x-circle text-lg"></i>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-rose-600 dark:text-rose-450"><?= number_format($outOfStock) ?></p>
            <p class="text-xs text-gray-400 mt-1">Requires immediate refill</p>
        </div>

        <!-- Card 3 -->
        <div class="bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border rounded-xl p-4 shadow-sm hover-card">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Low Stock</span>
                <div class="w-8 h-8 bg-orange-50 dark:bg-orange-950/30 border border-orange-100 dark:border-orange-900/55 rounded-lg flex items-center justify-center text-orange-500">
                    <i class="ph ph-warning-diamond text-lg"></i>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-orange-600 dark:text-orange-450"><?= number_format($lowStock) ?></p>
            <p class="text-xs text-gray-400 mt-1">Stock count ≤ 10 units</p>
        </div>

        <!-- Card 4 -->
        <div class="bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border rounded-xl p-4 shadow-sm hover-card">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Average Price</span>
                <div class="w-8 h-8 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/55 rounded-lg flex items-center justify-center text-emerald-500">
                    <i class="ph ph-currency-circle-bdt text-lg"></i>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-gray-850 dark:text-white">৳<?= number_format($avgPrice, 2) ?></p>
            <p class="text-xs text-gray-400 mt-1">Base cost average</p>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border rounded-xl p-4 shadow-sm flex flex-col lg:flex-row gap-4 items-stretch lg:items-center justify-between">
        <!-- Search bar -->
        <div class="relative flex-1 max-w-md">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                <i class="ph ph-magnifying-glass text-lg"></i>
            </span>
            <input type="text" id="prodSearch" oninput="applyFilters(true)" placeholder="Search products by name..." class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 pl-10 pr-4 py-2 rounded-lg focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors text-sm">
        </div>
        
        <!-- Dropdowns -->
        <div class="flex flex-col sm:flex-row gap-3">
            <select id="filterCompany" onchange="applyFilters(true)" class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 py-2 px-3 rounded-lg focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors text-sm">
                <option value="">All Companies</option>
                <?php foreach ($companies as $c): ?>
                    <option value="<?= htmlspecialchars($c['id']) ?>"><?= htmlspecialchars($c['company_name']) ?></option>
                <?php endforeach; ?>
            </select>

            <select id="filterCategory" onchange="applyFilters(true)" class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 py-2 px-3 rounded-lg focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors text-sm">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat['id']) ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                <?php endforeach; ?>
            </select>

            <select id="filterStock" onchange="applyFilters(true)" class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 py-2 px-3 rounded-lg focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors text-sm">
                <option value="">All Stock Levels</option>
                <option value="in">In Stock (11+)</option>
                <option value="low">Low Stock (1-10)</option>
                <option value="out">Out of Stock (0)</option>
            </select>
        </div>

        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 flex items-center justify-end">
            Showing <span id="visibleCount" class="font-bold text-amber-600 dark:text-amber-400 mx-1"><?= $totalProducts ?></span> of <?= $totalProducts ?> items
        </div>
    </div>

    <!-- Dual Layout container -->
    <div>
        <!-- GRID VIEW -->
        <div id="grid-view" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($products as $prod): 
                $compId = $prod['company_id'] ?? 0;
                $catId = $prod['product_category_id'] ?? 0;
                $compName = $companyMap[$compId] ?? 'Unknown Company';
                $catName = $categoryMap[$catId] ?? 'General';
                $stock = intval($prod['stock'] ?? 0);
            ?>
                <div class="product-item bg-white dark:bg-dark-card border border-blue-100 dark:border-dark-border rounded-xl shadow-sm overflow-hidden flex flex-col hover-card" 
                     data-name="<?= htmlspecialchars(strtolower($prod['product_name'])) ?>"
                     data-company="<?= htmlspecialchars($compId) ?>"
                     data-category="<?= htmlspecialchars($catId) ?>"
                     data-stock="<?= $stock ?>">
                    
                    <!-- Product Image Container -->
                    <div class="relative bg-gray-100 dark:bg-slate-800 h-44 w-full flex items-center justify-center border-b border-gray-100 dark:border-dark-border">
                        <?php if (!empty($prod['product_image'])): ?>
                            <img src="https://happycrm.site/assets/images/<?= htmlspecialchars($prod['product_image']) ?>" 
                                 alt="<?= htmlspecialchars($prod['product_name']) ?>" 
                                 class="w-full h-full object-contain p-2 opacity-0 transition-opacity duration-300"
                                 loading="lazy"
                                 onload="this.classList.remove('opacity-0')"
                                 onerror="showPlaceholder(this)">
                        <?php else: ?>
                            <div class="text-gray-400 flex flex-col items-center">
                                <i class="ph ph-package text-3xl mb-1"></i>
                                <span class="text-[10px] font-medium uppercase tracking-wider">No Image</span>
                            </div>
                        <?php endif; ?>

                        <!-- Low Stock Badge -->
                        <?php if ($stock === 0): ?>
                            <span class="absolute top-2 right-2 px-2 py-0.5 text-[10px] font-bold uppercase rounded bg-rose-500 text-white shadow-sm">Out Of Stock</span>
                        <?php elseif ($stock <= 10): ?>
                            <span class="absolute top-2 right-2 px-2 py-0.5 text-[10px] font-bold uppercase rounded bg-orange-500 text-white shadow-sm">Low Stock (<?= $stock ?>)</span>
                        <?php endif; ?>
                    </div>

                    <div class="p-4 flex-1 flex flex-col">
                        <!-- Company & Category Label -->
                        <div class="flex items-center space-x-1.5 text-[10px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-450 mb-1.5">
                            <span><?= htmlspecialchars($compName) ?></span>
                            <span>•</span>
                            <span class="text-gray-400 dark:text-gray-550"><?= htmlspecialchars($catName) ?></span>
                        </div>

                        <!-- Name -->
                        <h4 class="font-bold text-gray-800 dark:text-white text-sm mb-3 flex-1 line-clamp-2"><?= htmlspecialchars($prod['product_name']) ?></h4>
                        
                        <!-- Details Row -->
                        <div class="flex items-end justify-between mt-auto">
                            <div>
                                <span class="block text-[10px] text-gray-400">Price</span>
                                <span class="font-bold text-gray-850 dark:text-white text-base">৳<?= number_format($prod['price'], 2) ?></span>
                            </div>
                            <div class="text-right">
                                <span class="block text-[10px] text-gray-400">Stock</span>
                                <span class="font-semibold text-sm <?= $stock === 0 ? 'text-rose-500' : ($stock <= 10 ? 'text-orange-500' : 'text-gray-700 dark:text-gray-300') ?>"><?= number_format($stock) ?> units</span>
                            </div>
                        </div>

                        <!-- Commission info -->
                        <div class="mt-3 pt-2.5 border-t border-gray-150 dark:border-dark-border/80 flex items-center justify-between text-[10px] text-gray-455 dark:text-gray-500 font-mono">
                            <span>Sales Commission</span>
                            <span><?= htmlspecialchars($prod['percentage'] ?? '0') ?>%</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- SPREADSHEET LIST VIEW -->
        <div id="list-view" class="hidden bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border shadow-sm rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <!-- Excel Style Spreadsheet Table -->
                <table class="w-full text-left border-collapse border-spacing-0">
                    <thead>
                        <tr class="bg-primary-50 dark:bg-gray-800/80 text-xs font-semibold text-gray-600 dark:text-gray-300 border-b border-blue-200 dark:border-dark-border">
                            <th class="p-2 border-r border-blue-100 dark:border-dark-border text-center w-12 font-medium">ID</th>
                            <th class="p-2 border-r border-blue-100 dark:border-dark-border font-semibold">Product Name</th>
                            <th class="p-2 border-r border-blue-100 dark:border-dark-border font-semibold">Company</th>
                            <th class="p-2 border-r border-blue-100 dark:border-dark-border font-semibold">Category</th>
                            <th class="p-2 border-r border-blue-100 dark:border-dark-border text-right font-semibold">Price</th>
                            <th class="p-2 border-r border-blue-100 dark:border-dark-border text-right font-semibold">Comm %</th>
                            <th class="p-2 border-r border-blue-100 dark:border-dark-border text-right font-semibold">Stock</th>
                            <th class="p-2 text-center font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs text-gray-700 dark:text-gray-300 divide-y divide-gray-100 dark:divide-dark-border/80">
                        <?php foreach ($products as $index => $prod): 
                            $compId = $prod['company_id'] ?? 0;
                            $catId = $prod['product_category_id'] ?? 0;
                            $compName = $companyMap[$compId] ?? 'Unknown Company';
                            $catName = $categoryMap[$catId] ?? 'General';
                            $stock = intval($prod['stock'] ?? 0);
                        ?>
                            <tr class="product-item-row hover:bg-amber-50/30 dark:hover:bg-amber-900/10 transition-colors" 
                                 data-name="<?= htmlspecialchars(strtolower($prod['product_name'])) ?>"
                                 data-company="<?= htmlspecialchars($compId) ?>"
                                 data-category="<?= htmlspecialchars($catId) ?>"
                                 data-stock="<?= $stock ?>">
                                <td class="p-2 border-r border-gray-100 dark:border-dark-border/60 text-center font-mono text-gray-400"><?= htmlspecialchars($prod['id']) ?></td>
                                <td class="p-2 border-r border-gray-100 dark:border-dark-border/60 font-semibold text-gray-800 dark:text-white text-sm"><?= htmlspecialchars($prod['product_name']) ?></td>
                                <td class="p-2 border-r border-gray-100 dark:border-dark-border/60"><?= htmlspecialchars($compName) ?></td>
                                <td class="p-2 border-r border-gray-100 dark:border-dark-border/60 text-gray-450"><?= htmlspecialchars($catName) ?></td>
                                <td class="p-2 border-r border-gray-100 dark:border-dark-border/60 text-right font-mono font-medium">৳<?= number_format($prod['price'], 2) ?></td>
                                <td class="p-2 border-r border-gray-100 dark:border-dark-border/60 text-right font-mono text-gray-400"><?= htmlspecialchars($prod['percentage'] ?? '0') ?>%</td>
                                <td class="p-2 border-r border-gray-100 dark:border-dark-border/60 text-right font-mono font-medium <?= $stock === 0 ? 'text-rose-500 font-bold' : ($stock <= 10 ? 'text-orange-500 font-bold' : '') ?>"><?= number_format($stock) ?></td>
                                <td class="p-2 text-center font-medium">
                                    <?php if ($stock === 0): ?>
                                        <span class="px-2 py-0.5 text-[9px] font-bold rounded bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-450 border border-rose-200 dark:border-rose-800/40">OUT OF STOCK</span>
                                    <?php elseif ($stock <= 10): ?>
                                        <span class="px-2 py-0.5 text-[9px] font-bold rounded bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-450 border border-orange-200 dark:border-orange-800/40">LOW STOCK</span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 text-[9px] font-bold rounded bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-450 border border-emerald-200 dark:border-emerald-800/40">ACTIVE</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Empty search state -->
    <div id="emptySearchState" class="hidden text-center py-16 bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border rounded-xl shadow-sm">
        <i class="ph ph-binoculars text-5xl text-gray-400 mb-3"></i>
        <h4 class="text-lg font-bold text-gray-700 dark:text-gray-300">No Products Found</h4>
        <p class="text-sm text-gray-400 mt-1">Try resetting filters or adjusting search queries.</p>
    </div>

    <!-- Scroll Sentinel for Infinite Loading -->
    <div id="scroll-sentinel" class="h-16 w-full flex items-center justify-center py-4 mt-6">
        <div class="flex items-center space-x-2 text-amber-600 dark:text-amber-450 text-xs font-semibold animate-pulse">
            <i class="ph ph-spinner animate-spin text-lg font-bold"></i>
            <span>Loading more products...</span>
        </div>
    </div>
</div>

<script>
let currentView = 'grid';
let visibleLimit = 20;

function setView(view) {
    currentView = view;
    
    const gridDiv = document.getElementById('grid-view');
    const listDiv = document.getElementById('list-view');
    const btnGrid = document.getElementById('btn-view-grid');
    const btnList = document.getElementById('btn-view-list');
    
    const activeBtnClasses = ['bg-white', 'dark:bg-dark-card', 'shadow-sm', 'border', 'border-gray-200', 'dark:border-gray-750', 'text-gray-700', 'dark:text-gray-300'];
    const inactiveBtnClasses = ['text-gray-500', 'dark:text-gray-400', 'hover:text-gray-700', 'dark:hover:text-gray-300'];

    if (view === 'grid') {
        gridDiv.classList.remove('hidden');
        listDiv.classList.add('hidden');
        
        btnGrid.className = "flex items-center space-x-1.5 px-3 py-1.5 text-xs font-semibold rounded-md transition-all " + activeBtnClasses.join(' ');
        btnList.className = "flex items-center space-x-1.5 px-3 py-1.5 text-xs font-semibold rounded-md transition-all " + inactiveBtnClasses.join(' ');
    } else {
        gridDiv.classList.add('hidden');
        listDiv.classList.remove('hidden');
        
        btnList.className = "flex items-center space-x-1.5 px-3 py-1.5 text-xs font-semibold rounded-md transition-all " + activeBtnClasses.join(' ');
        btnGrid.className = "flex items-center space-x-1.5 px-3 py-1.5 text-xs font-semibold rounded-md transition-all " + inactiveBtnClasses.join(' ');
    }
    
    applyFilters(true);
}

function showPlaceholder(img) {
    const parent = img.parentNode;
    parent.innerHTML = `
        <div class="text-gray-400 flex flex-col items-center">
            <i class="ph ph-package text-3xl mb-1 text-amber-500/80"></i>
            <span class="text-[9px] font-bold uppercase tracking-wider text-gray-500">Image Load Error</span>
        </div>
    `;
}

function checkItemMatch(item, query, filterCo, filterCat, filterStk) {
    const name = item.getAttribute('data-name');
    const coId = item.getAttribute('data-company');
    const catId = item.getAttribute('data-category');
    const stock = parseInt(item.getAttribute('data-stock'));

    let matchText = name.includes(query);
    let matchCo = filterCo === "" || coId === filterCo;
    let matchCat = filterCat === "" || catId === filterCat;
    
    let matchStock = true;
    if (filterStk === 'out') {
        matchStock = (stock === 0);
    } else if (filterStk === 'low') {
        matchStock = (stock > 0 && stock <= 10);
    } else if (filterStk === 'in') {
        matchStock = (stock > 10);
    }

    return matchText && matchCo && matchCat && matchStock;
}

function applyFilters(resetLimit = false) {
    if (resetLimit) {
        visibleLimit = 20;
    }

    const query = document.getElementById('prodSearch').value.toLowerCase().trim();
    const filterCo = document.getElementById('filterCompany').value;
    const filterCat = document.getElementById('filterCategory').value;
    const filterStk = document.getElementById('filterStock').value;
    
    const gridItems = document.querySelectorAll('.product-item');
    const listRows = document.querySelectorAll('.product-item-row');
    
    let totalMatching = 0;
    
    // Filter grid items
    gridItems.forEach(item => {
        const match = checkItemMatch(item, query, filterCo, filterCat, filterStk);
        if (match) {
            totalMatching++;
            if (currentView === 'grid') {
                if (totalMatching <= visibleLimit) {
                    item.classList.remove('hidden', 'lazy-hidden');
                } else {
                    item.classList.remove('hidden');
                    item.classList.add('lazy-hidden');
                }
            } else {
                item.classList.add('hidden');
                item.classList.remove('lazy-hidden');
            }
        } else {
            item.classList.add('hidden');
            item.classList.remove('lazy-hidden');
        }
    });

    // Filter list rows
    let rowMatching = 0;
    listRows.forEach(item => {
        const match = checkItemMatch(item, query, filterCo, filterCat, filterStk);
        if (match) {
            rowMatching++;
            if (currentView === 'list') {
                if (rowMatching <= visibleLimit) {
                    item.classList.remove('hidden', 'lazy-hidden');
                } else {
                    item.classList.remove('hidden');
                    item.classList.add('lazy-hidden');
                }
            } else {
                item.classList.add('hidden');
                item.classList.remove('lazy-hidden');
            }
        } else {
            item.classList.add('hidden');
            item.classList.remove('lazy-hidden');
        }
    });

    const visibleCount = currentView === 'grid' ? totalMatching : rowMatching;
    const currentRenderedCount = Math.min(visibleCount, visibleLimit);
    
    document.getElementById('visibleCount').textContent = currentRenderedCount;
    const emptyState = document.getElementById('emptySearchState');
    
    const gridDiv = document.getElementById('grid-view');
    const listDiv = document.getElementById('list-view');
    const sentinel = document.getElementById('scroll-sentinel');

    if (visibleCount === 0) {
        emptyState.classList.remove('hidden');
        gridDiv.classList.add('hidden');
        listDiv.classList.add('hidden');
        if (sentinel) sentinel.classList.add('hidden');
    } else {
        emptyState.classList.add('hidden');
        if (currentView === 'grid') {
            gridDiv.classList.remove('hidden');
        } else {
            listDiv.classList.remove('hidden');
        }
        
        if (sentinel) {
            if (visibleCount > visibleLimit) {
                sentinel.classList.remove('hidden');
            } else {
                sentinel.classList.add('hidden');
            }
        }
    }
}

function loadMore() {
    visibleLimit += 20;
    applyFilters(false);
}

document.addEventListener('DOMContentLoaded', () => {
    // Infinite scroll observer setup
    const mainContainer = document.querySelector('main');
    const sentinel = document.getElementById('scroll-sentinel');
    
    if (sentinel) {
        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting) {
                loadMore();
            }
        }, {
            root: mainContainer,
            rootMargin: '150px' // Load 150px before scroll hits sentinel
        });
        
        observer.observe(sentinel);
    }
    
    applyFilters(true);
});
</script>

<?php include 'includes/footer.php'; ?>
