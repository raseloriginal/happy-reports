<?php include 'includes/header.php'; ?>

<div class="max-w-7xl mx-auto space-y-6 animate-fade-in pb-12" style="animation-delay: 0.1s;">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">CRM Sales Analytics</h2>
            <p class="text-gray-550 dark:text-gray-400 mt-1">Comprehensive sales summary, trends, and item-level ledger.</p>
        </div>
        <button onclick="refreshSalesData(this)" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-sm font-medium transition-colors shadow-sm flex items-center border border-emerald-700">
            <i class="ph ph-arrows-counter-clockwise mr-2" id="refresh-icon"></i> Sync CRM Data
        </button>
    </div>

    <!-- KPI Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6" id="kpi-metrics-container">
        <!-- Revenue Card -->
        <div class="bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border rounded-xl p-4 shadow-sm hover-card">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Revenue</span>
                <div class="w-8 h-8 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/55 rounded-lg flex items-center justify-center text-emerald-500">
                    <i class="ph ph-trend-up text-lg"></i>
                </div>
            </div>
            <p id="metric-revenue" class="text-2xl font-extrabold text-gray-850 dark:text-white"><span class="inline-block w-24 h-6 bg-gray-200 dark:bg-gray-700 animate-pulse rounded"></span></p>
            <p class="text-xs text-gray-400 mt-1">Accrued sales volume</p>
        </div>

        <!-- Transactions Card -->
        <div class="bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border rounded-xl p-4 shadow-sm hover-card">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Transactions</span>
                <div class="w-8 h-8 bg-indigo-50 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/55 rounded-lg flex items-center justify-center text-indigo-500">
                    <i class="ph ph-file-text text-lg"></i>
                </div>
            </div>
            <p id="metric-tx-count" class="text-2xl font-extrabold text-gray-850 dark:text-white"><span class="inline-block w-16 h-6 bg-gray-200 dark:bg-gray-700 animate-pulse rounded"></span></p>
            <p class="text-xs text-gray-400 mt-1">Processed invoices</p>
        </div>

        <!-- Average Ticket Card -->
        <div class="bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border rounded-xl p-4 shadow-sm hover-card">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Average Ticket</span>
                <div class="w-8 h-8 bg-blue-50 dark:bg-blue-950/30 border border-blue-100 dark:border-blue-900/55 rounded-lg flex items-center justify-center text-blue-500">
                    <i class="ph ph-receipt text-lg"></i>
                </div>
            </div>
            <p id="metric-avg-ticket" class="text-2xl font-extrabold text-gray-850 dark:text-white"><span class="inline-block w-20 h-6 bg-gray-200 dark:bg-gray-700 animate-pulse rounded"></span></p>
            <p class="text-xs text-gray-400 mt-1">Average invoice value</p>
        </div>

        <!-- Items Sold Card -->
        <div class="bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border rounded-xl p-4 shadow-sm hover-card">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Items Sold</span>
                <div class="w-8 h-8 bg-amber-50 dark:bg-amber-950/30 border border-amber-100 dark:border-amber-900/55 rounded-lg flex items-center justify-center text-amber-500">
                    <i class="ph ph-shopping-bag text-lg"></i>
                </div>
            </div>
            <p id="metric-items-sold" class="text-2xl font-extrabold text-gray-850 dark:text-white"><span class="inline-block w-16 h-6 bg-gray-200 dark:bg-gray-700 animate-pulse rounded"></span></p>
            <p class="text-xs text-gray-400 mt-1">Product units dispatched</p>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Sales Trend Line Chart -->
        <div class="bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border rounded-xl shadow-sm lg:col-span-2 flex flex-col overflow-hidden">
            <div class="bg-primary-600 text-white text-xs uppercase tracking-wider p-3 border-b border-primary-700 font-semibold">
                Daily Sales Revenue Trend
            </div>
            <div class="p-4 relative flex-1 min-h-[16rem] flex items-center justify-center">
                <div id="trend-spinner" class="absolute inset-0 flex items-center justify-center bg-white/50 dark:bg-dark-card/55 z-10">
                    <i class="ph ph-spinner animate-spin text-3xl text-emerald-500"></i>
                </div>
                <canvas id="salesTrendChart"></canvas>
            </div>
        </div>

        <!-- Top Products Bar Chart -->
        <div class="bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border rounded-xl shadow-sm flex flex-col overflow-hidden">
            <div class="bg-primary-600 text-white text-xs uppercase tracking-wider p-3 border-b border-primary-700 font-semibold">
                Top 5 Products by Sales Volume
            </div>
            <div class="p-4 relative flex-1 min-h-[16rem] flex items-center justify-center">
                <div id="products-spinner" class="absolute inset-0 flex items-center justify-center bg-white/50 dark:bg-dark-card/55 z-10">
                    <i class="ph ph-spinner animate-spin text-3xl text-blue-500"></i>
                </div>
                <canvas id="topProductsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Filter & Search Panel -->
    <div class="bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border rounded-xl p-4 shadow-sm flex flex-col xl:flex-row gap-4 items-stretch xl:items-center justify-between">
        <div class="relative w-full xl:w-72">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                <i class="ph ph-magnifying-glass text-lg"></i>
            </span>
            <input type="text" id="salesSearch" oninput="applySalesFilters()" placeholder="Search by ID or Date..." class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 pl-10 pr-4 py-2 rounded-lg focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors text-sm">
        </div>
        
        <div class="flex flex-wrap items-center gap-3 flex-1 justify-start xl:justify-end">
            <!-- Date range -->
            <div class="flex items-center space-x-1.5 text-xs text-gray-600 dark:text-gray-400">
                <span>From:</span>
                <input type="date" id="salesFrom" onchange="applySalesFilters()" class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 px-2 py-1 rounded-lg focus:outline-none focus:border-emerald-500 text-xs">
                <span>To:</span>
                <input type="date" id="salesTo" onchange="applySalesFilters()" class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 px-2 py-1 rounded-lg focus:outline-none focus:border-emerald-500 text-xs">
            </div>

            <!-- Type dropdown -->
            <select id="salesType" onchange="applySalesFilters()" class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 py-1.5 px-3 rounded-lg focus:outline-none focus:border-emerald-500 text-xs">
                <option value="">All Types</option>
                <option value="out">Sales Dispatch (out)</option>
                <option value="in">Returns (in)</option>
            </select>

            <!-- Company dropdown -->
            <select id="salesCompany" onchange="applySalesFilters()" class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 py-1.5 px-3 rounded-lg focus:outline-none focus:border-emerald-500 text-xs max-w-[140px]">
                <option value="">All Companies</option>
            </select>

            <!-- Product dropdown -->
            <select id="salesProduct" onchange="applySalesFilters()" class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 py-1.5 px-3 rounded-lg focus:outline-none focus:border-emerald-500 text-xs max-w-[140px]">
                <option value="">All Products</option>
            </select>

            <!-- Amount range -->
            <div class="flex items-center space-x-1 text-xs text-gray-600 dark:text-gray-400">
                <span>Value:</span>
                <input type="number" id="salesMinAmount" oninput="applySalesFilters()" placeholder="Min" class="w-14 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 px-1.5 py-1 rounded-lg focus:outline-none focus:border-emerald-500 text-xs">
                <span>-</span>
                <input type="number" id="salesMaxAmount" oninput="applySalesFilters()" placeholder="Max" class="w-14 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 px-1.5 py-1 rounded-lg focus:outline-none focus:border-emerald-500 text-xs">
            </div>
        </div>

        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 text-right min-w-[90px]">
            Showing <span id="visibleSalesCount" class="font-bold text-emerald-600 dark:text-emerald-450">0</span> of <span id="totalSalesCount">0</span>
        </div>
    </div>

    <!-- Sales Table Ledger -->
    <div class="bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border shadow-sm rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-primary-50 dark:bg-gray-800/80 text-xs font-semibold text-gray-600 dark:text-gray-300 border-b border-blue-200 dark:border-dark-border">
                        <th class="p-3 border-r border-blue-100 dark:border-dark-border w-12 text-center"></th>
                        <th class="p-3 border-r border-blue-100 dark:border-dark-border text-center font-medium w-16">Tx ID</th>
                        <th class="p-3 border-r border-blue-100 dark:border-dark-border w-44">Transaction Date</th>
                        <th class="p-3 border-r border-blue-100 dark:border-dark-border text-center w-28">Type</th>
                        <th class="p-3 border-r border-blue-100 dark:border-dark-border text-right w-36">Distinct Items</th>
                        <th class="p-3 border-r border-blue-100 dark:border-dark-border text-right w-36">Quantity</th>
                        <th class="p-3 text-right">Total Invoice Value</th>
                    </tr>
                </thead>
                <tbody class="text-xs text-gray-700 dark:text-gray-300 divide-y divide-gray-100 dark:divide-dark-border/80" id="salesTableBody">
                    <tr><td colspan="7" class="p-8 text-center text-gray-450"><i class="ph ph-spinner animate-spin text-2xl mr-2"></i>Loading sales transactional ledger...</td></tr>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination controls -->
        <div id="load-more-container" class="hidden p-4 bg-gray-50/50 dark:bg-dark-card border-t border-gray-100 dark:border-dark-border text-center">
            <button onclick="loadMoreRows()" class="px-5 py-2 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/20 dark:hover:bg-emerald-900/30 border border-emerald-200/50 dark:border-emerald-900/30 rounded-lg transition-colors shadow-sm">
                Load More Transactions (<span id="remaining-count">0</span> remaining)
            </button>
        </div>
    </div>
    
    <div id="emptySalesState" class="hidden text-center py-16 bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border rounded-xl shadow-sm">
        <i class="ph ph-receipt text-5xl text-gray-400 mb-3"></i>
        <h4 class="text-lg font-bold text-gray-700 dark:text-gray-300">No Sales Transactions Found</h4>
        <p class="text-sm text-gray-400 mt-1">Try resetting filters or adjusting search queries.</p>
    </div>
</div>

<script>
let allTransactions = [];
let filteredTransactions = [];
let displayedCount = 0;
const ROWS_PER_PAGE = 100;

// Chart references
let trendChartInstance = null;
let productsChartInstance = null;

// Toggle collapsible rows
function toggleDetails(txId) {
    const detailsRow = document.getElementById('details-row-' + txId);
    const caretIcon = document.getElementById('caret-icon-' + txId);
    
    if (detailsRow.classList.contains('hidden')) {
        detailsRow.classList.remove('hidden');
        caretIcon.classList.add('rotate-180');
    } else {
        detailsRow.classList.add('hidden');
        caretIcon.classList.remove('rotate-180');
    }
}

// Fetch sales data on load
function loadSalesDashboard() {
    fetch('api/sales_data.php')
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success' && res.data) {
                const data = res.data;
                allTransactions = data.transactions || [];
                
                // Populate KPIs
                document.getElementById('metric-revenue').textContent = '৳' + parseFloat(data.summary.total_revenue).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                document.getElementById('metric-tx-count').textContent = data.summary.tx_count.toLocaleString();
                document.getElementById('metric-avg-ticket').textContent = '৳' + parseFloat(data.summary.avg_ticket).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                document.getElementById('metric-items-sold').textContent = data.summary.total_items_sold.toLocaleString();
                
                // Populate Company Select
                const compSelect = document.getElementById('salesCompany');
                data.companies.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = c.company_name;
                    compSelect.appendChild(opt);
                });

                // Populate Product Select
                const prodSelect = document.getElementById('salesProduct');
                data.products.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.id;
                    opt.textContent = p.product_name;
                    prodSelect.appendChild(opt);
                });

                // Set up and draw charts
                document.getElementById('trend-spinner').classList.add('hidden');
                document.getElementById('products-spinner').classList.add('hidden');
                initializeCharts(data);

                // Run filters and render initial set
                applySalesFilters(true);
            } else {
                showError('Failed to parse API payload.');
            }
        })
        .catch(err => {
            console.error('Error fetching sales data:', err);
            showError('Unable to connect to sales aggregates API.');
        });
}

function showError(msg) {
    const tbody = document.getElementById('salesTableBody');
    tbody.innerHTML = `<tr><td colspan="7" class="p-8 text-center text-red-500 font-semibold"><i class="ph ph-warning-circle text-2xl mr-2 inline-block align-middle"></i> ${msg}</td></tr>`;
}

// Sales Ledger Filtering
function applySalesFilters(isInitial = false) {
    const query = document.getElementById('salesSearch').value.toLowerCase().trim();
    const dateFrom = document.getElementById('salesFrom').value;
    const dateTo = document.getElementById('salesTo').value;
    const typeFilter = document.getElementById('salesType').value;
    const companyFilter = document.getElementById('salesCompany').value;
    const productFilter = document.getElementById('salesProduct').value;
    const minAmount = parseFloat(document.getElementById('salesMinAmount').value) || 0;
    const maxAmount = parseFloat(document.getElementById('salesMaxAmount').value) || Infinity;
    
    // Perform filtering
    filteredTransactions = allTransactions.filter(tx => {
        let matchText = String(tx.id).includes(query) || tx.date.toLowerCase().includes(query) || tx.type.toLowerCase().includes(query);
        let matchType = typeFilter === "" || tx.type === typeFilter;
        
        let matchDate = true;
        if (dateFrom && tx.date !== 'Unknown') {
            matchDate = matchDate && (tx.date >= dateFrom);
        }
        if (dateTo && tx.date !== 'Unknown') {
            matchDate = matchDate && (tx.date <= dateTo);
        }

        let matchCompany = companyFilter === "" || tx.companies.includes(parseInt(companyFilter));
        let matchProduct = productFilter === "" || tx.products.includes(parseInt(productFilter));
        let matchAmount = tx.total_value >= minAmount && tx.total_value <= maxAmount;

        return matchText && matchType && matchDate && matchCompany && matchProduct && matchAmount;
    });

    document.getElementById('totalSalesCount').textContent = allTransactions.length.toLocaleString();
    document.getElementById('visibleSalesCount').textContent = filteredTransactions.length.toLocaleString();
    
    const emptyState = document.getElementById('emptySalesState');
    if (filteredTransactions.length === 0) {
        emptyState.classList.remove('hidden');
        document.getElementById('salesTableBody').innerHTML = '';
        document.getElementById('load-more-container').classList.add('hidden');
    } else {
        emptyState.classList.add('hidden');
        displayedCount = 0;
        renderRows(true);
    }

    // Update charts dynamically with filtered subset
    if (!isInitial) {
        updateChartsDynamic(filteredTransactions);
    }
}

// Render dynamic rows (with pagination to avoid blocking DOM rendering)
function renderRows(clear = false) {
    const tbody = document.getElementById('salesTableBody');
    if (clear) {
        tbody.innerHTML = '';
    }

    const end = Math.min(displayedCount + ROWS_PER_PAGE, filteredTransactions.length);
    const slice = filteredTransactions.slice(displayedCount, end);
    
    let html = '';
    slice.forEach(tx => {
        html += `
            <!-- Master Row -->
            <tr class="sales-row hover:bg-emerald-50/20 dark:hover:bg-emerald-950/10 transition-colors border-b border-gray-100 dark:border-dark-border/60">
                <td class="p-3 text-center border-r border-gray-100 dark:border-dark-border/60">
                    <button onclick="toggleDetails(${tx.id})" class="text-gray-400 hover:text-emerald-500 font-bold focus:outline-none" id="btn-toggle-${tx.id}">
                        <i class="ph ph-caret-down text-base transition-transform" id="caret-icon-${tx.id}"></i>
                    </button>
                </td>
                <td class="p-3 border-r border-gray-100 dark:border-dark-border/60 text-center font-mono font-medium text-gray-500">#${tx.id}</td>
                <td class="p-3 border-r border-gray-100 dark:border-dark-border/60 font-semibold text-gray-800 dark:text-white">${tx.date}</td>
                <td class="p-3 border-r border-gray-100 dark:border-dark-border/60 text-center">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold ${tx.type === 'out' ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-450 border border-emerald-200/30' : 'bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-455 border border-rose-200/30'}">
                        ${tx.type.toUpperCase()}
                    </span>
                </td>
                <td class="p-3 border-r border-gray-100 dark:border-dark-border/60 text-right font-mono">${tx.items_count}</td>
                <td class="p-3 border-r border-gray-100 dark:border-dark-border/60 text-right font-mono">${tx.total_qty.toLocaleString()}</td>
                <td class="p-3 text-right font-mono font-semibold text-sm text-gray-850 dark:text-white">৳${parseFloat(tx.total_value).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            </tr>
            
            <!-- Detail Items Row (Collapsible) -->
            <tr id="details-row-${tx.id}" class="hidden bg-gray-50/50 dark:bg-slate-900/40 select-none">
                <td colspan="7" class="p-4 border-b border-gray-200/60 dark:border-dark-border">
                    <div class="pl-8 py-2">
                        <h5 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 flex items-center">
                            <i class="ph ph-shopping-cart mr-1 text-sm text-emerald-500"></i> Invoice Items
                        </h5>
                        <table class="w-full max-w-3xl text-left border-collapse border border-gray-200 dark:border-dark-border/60 rounded-lg overflow-hidden shadow-inner bg-white dark:bg-dark-card">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-800 text-[10px] uppercase text-gray-500 font-semibold border-b border-gray-200 dark:border-dark-border">
                                    <th class="p-2 border-r border-gray-200 dark:border-dark-border">Item Name</th>
                                    <th class="p-2 border-r border-gray-200 dark:border-dark-border text-right w-24">Quantity</th>
                                    <th class="p-2 border-r border-gray-200 dark:border-dark-border text-right w-36">Price per Unit</th>
                                    <th class="p-2 text-right w-40">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-dark-border/40 text-[11px] text-gray-650 dark:text-gray-350">
                                ${tx.items.map(item => `
                                    <tr>
                                        <td class="p-2 border-r border-gray-100 dark:border-dark-border/40 font-medium text-gray-800 dark:text-gray-200">${item.name}</td>
                                        <td class="p-2 border-r border-gray-100 dark:border-dark-border/40 text-right font-mono">${item.qty}</td>
                                        <td class="p-2 border-r border-gray-100 dark:border-dark-border/40 text-right font-mono">৳${parseFloat(item.price).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                                        <td class="p-2 text-right font-mono font-semibold">৳${parseFloat(item.total).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                                    </tr>
                                `).join('')}
                                ${tx.items.length === 0 ? '<tr><td colspan="4" class="p-2 text-center text-gray-400 italic">No items found.</td></tr>' : ''}
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
        `;
    });
    
    // Append rows using a fast DOM injection
    const tempDiv = document.createElement('tbody');
    tempDiv.innerHTML = html;
    while (tempDiv.firstChild) {
        tbody.appendChild(tempDiv.firstChild);
    }
    
    displayedCount = end;
    
    // Render pagination load button
    const pagContainer = document.getElementById('load-more-container');
    const remaining = filteredTransactions.length - displayedCount;
    if (remaining > 0) {
        document.getElementById('remaining-count').textContent = remaining.toLocaleString();
        pagContainer.classList.remove('hidden');
    } else {
        pagContainer.classList.add('hidden');
    }
}

function loadMoreRows() {
    renderRows(false);
}

// Chart Initializations
function initializeCharts(data) {
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#9ca3af' : '#4b5563';
    const gridColor = isDark ? '#334155' : '#e5e7eb';

    // 1. Process Trend Line data (Initial)
    const dailySales = {};
    data.transactions.forEach(tx => {
        if (tx.date !== 'Unknown') {
            if (!dailySales[tx.date]) dailySales[tx.date] = 0;
            dailySales[tx.date] += tx.total_value;
        }
    });
    const dates = Object.keys(dailySales).sort();
    const trendLabels = dates.map(d => formatFriendlyDate(d)).slice(-15);
    const trendValues = dates.map(d => dailySales[d]).slice(-15);

    const trendCtx = document.getElementById('salesTrendChart').getContext('2d');
    trendChartInstance = new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: trendLabels,
            datasets: [{
                label: 'Sales Revenue (৳)',
                data: trendValues,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.08)',
                borderWidth: 2,
                fill: true,
                tension: 0.15,
                pointBackgroundColor: '#10b981',
                pointRadius: 2.5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: textColor, font: { size: 9 } }
                },
                y: {
                    grid: { color: gridColor },
                    ticks: { 
                        color: textColor,
                        font: { size: 9 },
                        callback: function(value) { return '৳' + (value >= 1000 ? (value/1000) + 'k' : value); }
                    }
                }
            }
        }
    });

    // 2. Products Bar data (Initial)
    const productLabels = data.top_products.map(p => p.name);
    const productValues = data.top_products.map(p => p.qty);

    const productsCtx = document.getElementById('topProductsChart').getContext('2d');
    productsChartInstance = new Chart(productsCtx, {
        type: 'bar',
        data: {
            labels: productLabels,
            datasets: [{
                data: productValues,
                backgroundColor: '#3b82f6',
                borderRadius: 4,
                barThickness: 14
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: {
                    grid: { color: gridColor },
                    ticks: { color: textColor, font: { size: 9 } }
                },
                y: {
                    grid: { display: false },
                    ticks: { 
                        color: textColor,
                        font: { size: 9 },
                        callback: function(val, index) {
                            const name = productLabels[index] || '';
                            return name.length > 13 ? name.substring(0, 13) + '...' : name;
                        }
                    }
                }
            }
        }
    });
}

// Update charts client-side instantly on filtering
function updateChartsDynamic(filtered) {
    if (!trendChartInstance && !productsChartInstance) return;

    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#9ca3af' : '#4b5563';

    // 1. Recalculate daily sales trend client-side
    const dailySales = {};
    filtered.forEach(tx => {
        if (tx.date !== 'Unknown') {
            if (!dailySales[tx.date]) dailySales[tx.date] = 0;
            dailySales[tx.date] += tx.total_value;
        }
    });
    const sortedDates = Object.keys(dailySales).sort();
    
    // Only slice if range is large, otherwise show complete range
    let sliceLen = -15;
    const fromVal = document.getElementById('salesFrom').value;
    const toVal = document.getElementById('salesTo').value;
    if (fromVal || toVal) {
        sliceLen = -sortedDates.length; // show all
    }

    const trendLabels = sortedDates.map(d => formatFriendlyDate(d)).slice(sliceLen);
    const trendValues = sortedDates.map(d => dailySales[d]).slice(sliceLen);

    if (trendChartInstance) {
        trendChartInstance.data.labels = trendLabels;
        trendChartInstance.data.datasets[0].data = trendValues;
        trendChartInstance.options.scales.x.ticks.color = textColor;
        trendChartInstance.options.scales.y.ticks.color = textColor;
        trendChartInstance.update();
    }

    // 2. Recalculate top products client-side
    const productSalesQty = {};
    filtered.forEach(tx => {
        tx.items.forEach(item => {
            if (item.qty > 0) {
                if (!productSalesQty[item.name]) productSalesQty[item.name] = 0;
                productSalesQty[item.name] += item.qty;
            }
        });
    });

    const productsSorted = Object.keys(productSalesQty).map(name => {
        return { name, qty: productSalesQty[name] };
    }).sort((a, b) => b.qty - a.qty).slice(0, 5);

    const productLabels = productsSorted.map(p => p.name);
    const productValues = productsSorted.map(p => p.qty);

    if (productsChartInstance) {
        productsChartInstance.data.labels = productLabels;
        productsChartInstance.data.datasets[0].data = productValues;
        productsChartInstance.options.scales.x.ticks.color = textColor;
        productsChartInstance.options.scales.y.ticks.color = textColor;
        productsChartInstance.update();
    }
}

function formatFriendlyDate(d) {
    const parts = d.split('-');
    if (parts.length === 3) {
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const year = parts[0].substring(2);
        const month = months[parseInt(parts[1]) - 1];
        const day = parseInt(parts[2]);
        return `${day} ${month} ${year}`;
    }
    return d;
}

// Watch Theme changes to adapt chart tick colors
const observer = new MutationObserver(() => {
    if (allTransactions.length > 0) {
        updateChartsDynamic(filteredTransactions);
    }
});
observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

function refreshSalesData(btn) {
    const icon = document.getElementById('refresh-icon');
    icon.classList.add('animate-spin');
    btn.disabled = true;
    
    // Clear sessionStorage badge counts
    sessionStorage.removeItem('crm_badge_data');
    
    fetch('api/sales_data.php?refresh=1')
        .then(res => res.json())
        .then(res => {
            icon.classList.remove('animate-spin');
            btn.disabled = false;
            
            if (res.status === 'success' && res.data) {
                const data = res.data;
                allTransactions = data.transactions || [];
                
                // Repopulate KPIs
                document.getElementById('metric-revenue').textContent = '৳' + parseFloat(data.summary.total_revenue).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                document.getElementById('metric-tx-count').textContent = data.summary.tx_count.toLocaleString();
                document.getElementById('metric-avg-ticket').textContent = '৳' + parseFloat(data.summary.avg_ticket).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                document.getElementById('metric-items-sold').textContent = data.summary.total_items_sold.toLocaleString();
                
                // Reinitialize Charts
                if (trendChartInstance) trendChartInstance.destroy();
                if (productsChartInstance) productsChartInstance.destroy();
                initializeCharts(data);
                
                // Reapply filters
                applySalesFilters(true);
                
                // Refresh global CRM badges if function exists
                if (typeof fetchCrmBadges === 'function') {
                    fetchCrmBadges();
                }
            }
        })
        .catch(err => {
            icon.classList.remove('animate-spin');
            btn.disabled = false;
            console.error('Failed to sync CRM data:', err);
        });
}

document.addEventListener('DOMContentLoaded', loadSalesDashboard);
</script>

<?php include 'includes/footer.php'; ?>
