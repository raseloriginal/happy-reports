<?php include 'includes/header.php'; ?>

<div class="max-w-7xl mx-auto space-y-6 animate-fade-in pb-12" style="animation-delay: 0.1s;">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Advanced Custom Reports</h2>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Multi-perspective local database visualizations.</p>
        </div>
    </div>

    <!-- Section 1: Financial Perspectives -->
    <h3 class="text-lg font-bold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700 pb-2 mt-8 mb-4">Financial Perspectives</h3>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Monthly Comparison -->
        <div class="bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border rounded-none shadow-sm flex flex-col">
            <div class="bg-primary-600 text-white text-xs uppercase tracking-wider p-2 border-b border-primary-700 font-semibold flex flex-col md:flex-row justify-between items-center gap-2">
                <span>Monthly Comparison</span>
                <div class="flex items-center space-x-2">
                    <input type="date" id="from-monthly" class="text-gray-800 text-xs p-1 border border-gray-300 rounded-sm focus:outline-none">
                    <span>to</span>
                    <input type="date" id="to-monthly" class="text-gray-800 text-xs p-1 border border-gray-300 rounded-sm focus:outline-none">
                    <button onclick="updateMonthly()" class="bg-white text-primary-600 px-2 py-1 rounded-sm shadow-sm hover:bg-gray-100 transition-colors"><i class="ph ph-arrows-clockwise font-bold"></i></button>
                </div>
            </div>
            <div class="p-4 relative min-h-[18rem] flex items-center justify-center">
                <div id="loader-monthly" class="absolute inset-0 bg-white/80 dark:bg-dark-card/80 flex items-center justify-center z-10 hidden"><i class="ph ph-spinner animate-spin text-2xl text-primary-500"></i></div>
                <canvas id="compChart"></canvas>
            </div>
        </div>

        <!-- Breakdown -->
        <div class="bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border rounded-none shadow-sm flex flex-col">
            <div class="bg-primary-600 text-white text-xs uppercase tracking-wider p-2 border-b border-primary-700 font-semibold flex flex-col md:flex-row justify-between items-center gap-2">
                <span>Expense Breakdown</span>
                <div class="flex items-center space-x-2">
                    <input type="date" id="from-doughnut" class="text-gray-800 text-xs p-1 border border-gray-300 rounded-sm focus:outline-none">
                    <span>to</span>
                    <input type="date" id="to-doughnut" class="text-gray-800 text-xs p-1 border border-gray-300 rounded-sm focus:outline-none">
                    <button onclick="updateDoughnut()" class="bg-white text-primary-600 px-2 py-1 rounded-sm shadow-sm hover:bg-gray-100 transition-colors"><i class="ph ph-arrows-clockwise font-bold"></i></button>
                </div>
            </div>
            <div class="p-4 relative min-h-[18rem] flex items-center justify-center">
                <div id="loader-doughnut" class="absolute inset-0 bg-white/80 dark:bg-dark-card/80 flex items-center justify-center z-10 hidden"><i class="ph ph-spinner animate-spin text-2xl text-primary-500"></i></div>
                <canvas id="doughChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Section 2: Detailed Insights -->
    <h3 class="text-lg font-bold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700 pb-2 mt-8 mb-4">Insights & Trends</h3>
    <div class="grid grid-cols-1 gap-6">
        <!-- Top Dealers -->
        <div class="bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border rounded-none shadow-sm flex flex-col">
            <div class="bg-primary-600 text-white text-xs uppercase tracking-wider p-2 border-b border-primary-700 font-semibold flex flex-col md:flex-row justify-between items-center gap-2">
                <span>Top Dealers by Withdrawal</span>
                <div class="flex items-center space-x-2">
                    <input type="date" id="from-dealers" class="text-gray-800 text-xs p-1 border border-gray-300 rounded-sm focus:outline-none">
                    <span>to</span>
                    <input type="date" id="to-dealers" class="text-gray-800 text-xs p-1 border border-gray-300 rounded-sm focus:outline-none">
                    <button onclick="updateDealers()" class="bg-white text-primary-600 px-2 py-1 rounded-sm shadow-sm hover:bg-gray-100 transition-colors"><i class="ph ph-arrows-clockwise font-bold"></i></button>
                </div>
            </div>
            <div class="p-4 relative min-h-[18rem]">
                <div id="loader-dealers" class="absolute inset-0 bg-white/80 dark:bg-dark-card/80 flex items-center justify-center z-10 hidden"><i class="ph ph-spinner animate-spin text-2xl text-primary-500"></i></div>
                <canvas id="dealersChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Daily Trend -->
    <div class="grid grid-cols-1 gap-6 mt-6">
        <div class="bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border rounded-none shadow-sm flex flex-col">
            <div class="bg-primary-600 text-white text-xs uppercase tracking-wider p-2 border-b border-primary-700 font-semibold flex flex-col md:flex-row justify-between items-center gap-2">
                <span>Daily Financial Trend</span>
                <div class="flex items-center space-x-2">
                    <input type="date" id="from-trend" class="text-gray-800 text-xs p-1 border border-gray-300 rounded-sm focus:outline-none">
                    <span>to</span>
                    <input type="date" id="to-trend" class="text-gray-800 text-xs p-1 border border-gray-300 rounded-sm focus:outline-none">
                    <button onclick="updateTrend()" class="bg-white text-primary-600 px-2 py-1 rounded-sm shadow-sm hover:bg-gray-100 transition-colors"><i class="ph ph-arrows-clockwise font-bold"></i></button>
                </div>
            </div>
            <div class="p-4 relative min-h-[20rem]">
                <div id="loader-trend" class="absolute inset-0 bg-white/80 dark:bg-dark-card/80 flex items-center justify-center z-10 hidden"><i class="ph ph-spinner animate-spin text-2xl text-primary-500"></i></div>
                <canvas id="trendChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Section 3: Detailed Ledger -->
    <h3 class="text-lg font-bold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700 pb-2 mt-8 mb-4">Detailed Ledger</h3>
    <div class="bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border shadow-sm overflow-hidden rounded-none flex flex-col">
        <div class="bg-primary-600 text-white text-xs uppercase tracking-wider p-2 border-b border-primary-700 font-semibold flex flex-col md:flex-row justify-between items-center gap-2">
            <span>Transaction Ledger</span>
            <div class="flex items-center space-x-2">
                <input type="date" id="from-ledger" class="text-gray-800 text-xs p-1 border border-gray-300 rounded-sm focus:outline-none">
                <span>to</span>
                <input type="date" id="to-ledger" class="text-gray-800 text-xs p-1 border border-gray-300 rounded-sm focus:outline-none">
                <button onclick="updateLedger()" class="bg-white text-primary-600 px-2 py-1 rounded-sm shadow-sm hover:bg-gray-100 transition-colors"><i class="ph ph-arrows-clockwise font-bold"></i></button>
            </div>
        </div>
        <div class="overflow-x-auto relative min-h-[10rem]">
            <div id="loader-ledger" class="absolute inset-0 bg-white/80 dark:bg-dark-card/80 flex items-center justify-center z-10 hidden"><i class="ph ph-spinner animate-spin text-2xl text-primary-500"></i></div>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-primary-50 dark:bg-gray-800 text-xs uppercase tracking-wider border-b border-blue-200 dark:border-dark-border text-gray-600 dark:text-gray-300">
                        <th class="p-2 font-semibold border-r border-blue-200 dark:border-dark-border">Date</th>
                        <th class="p-2 font-semibold border-r border-blue-200 dark:border-dark-border">Description</th>
                        <th class="p-2 font-semibold border-r border-blue-200 dark:border-dark-border">Category</th>
                        <th class="p-2 font-semibold text-right">Amount</th>
                    </tr>
                </thead>
                <tbody id="ledger-body" class="text-sm text-gray-700 dark:text-gray-300">
                    <tr><td colspan="4" class="p-4 text-center text-gray-400">Loading data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// --- Globals & Helpers ---
const API = 'api/reports.php';
const charts = {};
const isDark = document.documentElement.classList.contains('dark');
const textColor = isDark ? '#9ca3af' : '#4b5563';
const gridColor = isDark ? '#334155' : '#e5e7eb';

const commonChartOpts = {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { labels: { color: textColor, usePointStyle: true, boxWidth: 8 } } },
    scales: {
        x: { grid: { display: false }, ticks: { color: textColor } },
        y: { grid: { color: gridColor, drawBorder: false }, ticks: { color: textColor } }
    }
};

const commonDoughnutOpts = {
    responsive: true, maintainAspectRatio: false, cutout: '75%',
    plugins: { legend: { position: 'right', labels: { color: textColor, usePointStyle: true, padding: 20 } } }
};

function floatval(val) { const p = parseFloat(val); return isNaN(p) ? 0 : p; }

function showLoader(id) { document.getElementById('loader-' + id).classList.remove('hidden'); }
function hideLoader(id) { document.getElementById('loader-' + id).classList.add('hidden'); }

function initDates() {
    const today = new Date();
    const startOfYear = new Date(today.getFullYear(), 0, 1).toISOString().split('T')[0];
    const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
    const endOfToday = today.toISOString().split('T')[0];
    
    ['monthly', 'doughnut', 'dealers', 'trend', 'ledger'].forEach(id => {
        if(document.getElementById('from-'+id)) {
            // Use start of month for trend to avoid cramped charts
            document.getElementById('from-'+id).value = (id === 'trend' ? startOfMonth : startOfYear);
        }
        if(document.getElementById('to-'+id)) document.getElementById('to-'+id).value = endOfToday;
    });
}

// --- Module Updaters ---

function updateMonthly() {
    showLoader('monthly');
    const f = document.getElementById('from-monthly').value;
    const t = document.getElementById('to-monthly').value;
    fetch(`${API}?dataset=monthly&from=${f}&to=${t}`).then(r=>r.json()).then(res => {
        const months = Object.keys(res.monthly).sort();
        const labels = months.map(m => { const [y,mo] = m.split('-'); return new Date(y, mo-1).toLocaleString('default',{month:'short',year:'2-digit'}); });
        const dep = months.map(m => res.monthly[m].deposits || 0);
        const wdr = months.map(m => res.monthly[m].withdrawals || 0);
 
        if(charts.comp) charts.comp.destroy();
        charts.comp = new Chart(document.getElementById('compChart').getContext('2d'), {
            type: 'bar',
            data: { labels, datasets: [
                { label: 'Deposits', data: dep, backgroundColor: '#10b981', borderRadius: 2 },
                { label: 'Withdrawals', data: wdr, backgroundColor: '#f43f5e', borderRadius: 2 }
            ]},
            options: { ...commonChartOpts, scales: { x: commonChartOpts.scales.x, y: { ...commonChartOpts.scales.y, callback: v => '৳'+v/1000+'k' } } }
        });
        hideLoader('monthly');
    });
}

function updateDoughnut() {
    showLoader('doughnut');
    const f = document.getElementById('from-doughnut').value;
    const t = document.getElementById('to-doughnut').value;
    fetch(`${API}?dataset=monthly&from=${f}&to=${t}`).then(r=>r.json()).then(res => {
        let totalDep = 0, totalWdr = 0;
        Object.values(res.monthly).forEach(m => {
            totalDep += m.deposits || 0;
            totalWdr += m.withdrawals || 0;
        });
 
        if(charts.dough) charts.dough.destroy();
        charts.dough = new Chart(document.getElementById('doughChart').getContext('2d'), {
            type: 'doughnut',
            data: { labels: ['Deposits', 'Withdrawals'], datasets: [{ data: [totalDep, totalWdr], backgroundColor: ['#10b981', '#f43f5e'], borderWidth: 0 }] },
            options: commonDoughnutOpts
        });
        hideLoader('doughnut');
    });
}

function updateDealers() {
    showLoader('dealers');
    const f = document.getElementById('from-dealers').value;
    const t = document.getElementById('to-dealers').value;
    fetch(`${API}?dataset=ledger&from=${f}&to=${t}`).then(r=>r.json()).then(res => {
        const dealers = {};
        (res.records.withdrawals||[]).forEach(w => {
            const name = w.dealer_name || 'Unknown';
            dealers[name] = (dealers[name] || 0) + floatval(w.amount);
        });
        
        // Sort and get top 5
        const sorted = Object.entries(dealers).sort((a,b) => b[1] - a[1]).slice(0, 5);
        const labels = sorted.map(i => i[0]);
        const data = sorted.map(i => i[1]);

        if(charts.dealers) charts.dealers.destroy();
        charts.dealers = new Chart(document.getElementById('dealersChart').getContext('2d'), {
            type: 'bar',
            data: { labels, datasets: [{ label: 'Withdrawal Total', data, backgroundColor: '#f43f5e', borderRadius: 2 }] },
            options: { ...commonChartOpts, indexAxis: 'y', scales: { x: commonChartOpts.scales.y, y: commonChartOpts.scales.x } }
        });
        hideLoader('dealers');
    });
}

function updateTrend() {
    showLoader('trend');
    const f = document.getElementById('from-trend').value;
    const t = document.getElementById('to-trend').value;
    fetch(`${API}?dataset=ledger&from=${f}&to=${t}`).then(r=>r.json()).then(res => {
        const daily = {};
        
        // Aggregate by date
        (res.records.deposits||[]).forEach(d => {
            if(!daily[d.date]) daily[d.date] = {d:0, w:0};
            daily[d.date].d += floatval(d.amount);
        });
        (res.records.withdrawals||[]).forEach(w => {
            if(!daily[w.date]) daily[w.date] = {d:0, w:0};
            daily[w.date].w += floatval(w.amount);
        });
        
        const sortedDates = Object.keys(daily).sort();
        const depData = sortedDates.map(d => daily[d].d);
        const wdrData = sortedDates.map(d => daily[d].w);
 
        if(charts.trend) charts.trend.destroy();
        charts.trend = new Chart(document.getElementById('trendChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: sortedDates,
                datasets: [
                    { label: 'Deposits', data: depData, borderColor: '#10b981', backgroundColor: 'transparent', tension: 0.1, borderWidth: 2 },
                    { label: 'Withdrawals', data: wdrData, borderColor: '#f43f5e', backgroundColor: 'transparent', tension: 0.1, borderWidth: 2 }
                ]
            },
            options: commonChartOpts
        });
        hideLoader('trend');
    });
}

function updateLedger() {
    showLoader('ledger');
    const f = document.getElementById('from-ledger').value;
    const t = document.getElementById('to-ledger').value;
    fetch(`${API}?dataset=ledger&from=${f}&to=${t}`).then(r=>r.json()).then(res => {
        const allRows = [];
        (res.records.deposits||[]).forEach(r => allRows.push({...r, cat: 'Deposit', type: 'in'}));
        (res.records.withdrawals||[]).forEach(r => allRows.push({...r, description: r.dealer_name, cat: 'Withdrawal', type: 'out'}));
        allRows.sort((a,b) => new Date(b.date) - new Date(a.date));

        const tbody = document.getElementById('ledger-body');
        if(!allRows.length) { tbody.innerHTML = '<tr><td colspan="4" class="p-4 text-center text-gray-500">No data found for this period.</td></tr>'; hideLoader('ledger'); return; }
        
        tbody.innerHTML = allRows.map((row, i) => {
            let catColor = '';
            if(row.cat === 'Deposit') catColor = 'text-emerald-600 dark:text-emerald-400 font-semibold';
            else catColor = 'text-rose-600 dark:text-rose-400 font-semibold';
            
            const prefix = row.type === 'in' ? '+' : '-';
            const bgClass = i % 2 !== 0 ? 'bg-primary-50/50 dark:bg-primary-900/10' : 'bg-white dark:bg-dark-card';
            return `
                <tr class="${bgClass} hover:bg-blue-100/50 dark:hover:bg-gray-800 transition-colors border-b border-blue-100 dark:border-dark-border">
                    <td class="p-2 border-r border-blue-100 dark:border-dark-border">${row.date}</td>
                    <td class="p-2 border-r border-blue-100 dark:border-dark-border font-medium text-gray-800 dark:text-white">${row.description || '-'}</td>
                    <td class="p-2 border-r border-blue-100 dark:border-dark-border ${catColor}">${row.cat}</td>
                    <td class="p-2 text-right font-medium ${row.type==='in'?'text-emerald-600 dark:text-emerald-400':'text-rose-600 dark:text-rose-400'}">${prefix} ৳${floatval(row.amount).toLocaleString()}</td>
                </tr>
            `;
        }).join('');
        hideLoader('ledger');
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initDates();
    updateMonthly();
    updateDoughnut();
    updateDealers();
    updateTrend();
    updateLedger();
});
</script>

<?php include 'includes/footer.php'; ?>
