<?php include 'includes/header.php'; ?>

<div class="max-w-7xl mx-auto space-y-6 animate-fade-in" style="animation-delay: 0.1s;">
    
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Lots Management</h2>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Manage company lots manually to calculate accurate financial balances.</p>
        </div>
        <button onclick="openAddModal()" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded text-sm font-medium transition-colors shadow-sm flex items-center border border-rose-700">
            <i class="ph ph-plus font-bold mr-2"></i> Add Lot
        </button>
    </div>

    <!-- Data Table -->
    <div class="bg-white dark:bg-dark-card border border-rose-200 dark:border-dark-border shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-rose-600 text-white text-xs uppercase tracking-wider border-b border-rose-700">
                        <th class="p-3 font-semibold border-r border-rose-500/50">ID</th>
                        <th class="p-3 font-semibold border-r border-rose-500/50">Date</th>
                        <th class="p-3 font-semibold border-r border-rose-500/50">Company</th>
                        <th class="p-3 font-semibold border-r border-rose-500/50">Notes / Description</th>
                        <th class="p-3 font-semibold text-right border-r border-rose-500/50">Total Amount</th>
                        <th class="p-3 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-dark-border text-sm text-gray-700 dark:text-gray-300" id="data-body">
                    <tr><td colspan="6" class="p-8 text-center text-gray-400"><i class="ph ph-spinner animate-spin text-2xl"></i></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="lot-modal" class="fixed inset-0 bg-gray-900/50 dark:bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="modal-content bg-white dark:bg-dark-card w-full max-w-md rounded-2xl shadow-xl transform scale-95 opacity-0 transition-all duration-300 border border-gray-100 dark:border-dark-border">
        <div class="p-5 border-b border-gray-100 dark:border-dark-border flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white" id="modal-title">Add Lot</h3>
            <button onclick="closeModal('lot-modal')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors"><i class="ph ph-x text-xl"></i></button>
        </div>
        <form id="lot-form" class="p-5 space-y-4">
            <input type="hidden" id="edit-id">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Lot Date</label>
                <input type="date" id="f-date" required class="w-full bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 py-2.5 px-3 rounded-lg focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500 transition-colors text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company</label>
                <select id="f-company" required class="w-full bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 py-2.5 px-3 rounded-lg focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500 transition-colors text-sm">
                    <option value="">Select Company...</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Total Amount (৳)</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-medium">৳</span>
                    <input type="number" step="0.01" id="f-amount" required placeholder="0.00" class="w-full bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 py-2.5 pl-8 pr-3 rounded-lg focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500 transition-colors text-sm">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes / Description</label>
                <textarea rows="3" id="f-desc" placeholder="Lot details, items, or specifications..." class="w-full bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 py-2.5 px-3 rounded-lg focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500 transition-colors text-sm resize-none"></textarea>
            </div>
            <div class="pt-2 flex space-x-3">
                <button type="button" onclick="closeModal('lot-modal')" class="flex-1 py-2.5 border border-gray-200 dark:border-gray-700 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Cancel</button>
                <button type="submit" class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-sm font-medium transition-colors shadow-sm">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
const API = 'api/lots.php';
let allData = [];
let companyMap = {};

function init() {
    // Fetch companies to map names and populate select option
    fetch('https://happycrm.site/happyreports_api/index.php?table=companies')
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                const select = document.getElementById('f-company');
                select.innerHTML = '<option value="">Select Company...</option>';
                res.data.forEach(c => {
                    companyMap[c.id] = c.company_name;
                    const option = document.createElement('option');
                    option.value = c.id;
                    option.textContent = c.company_name;
                    select.appendChild(option);
                });
            }
            loadData();
        })
        .catch(err => {
            console.error('Error fetching companies:', err);
            loadData();
        });
}

function loadData() {
    fetch(API).then(r => r.json()).then(res => {
        allData = res.data || [];
        renderTable();
    });
}

function renderTable() {
    const tbody = document.getElementById('data-body');
    if (!allData.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="p-8 text-center text-gray-400">No lots found. Click "Add Lot" to create one.</td></tr>';
        return;
    }
    tbody.innerHTML = allData.map((row, index) => `
        <tr class="${index % 2 !== 0 ? 'bg-rose-50/10 dark:bg-rose-900/5' : 'bg-white dark:bg-dark-card'} hover:bg-rose-100/30 dark:hover:bg-rose-900/10 transition-colors border-b border-rose-100 dark:border-dark-border">
            <td class="p-3 text-gray-600 dark:text-gray-400 border-r border-rose-100 dark:border-dark-border text-sm">#${row.id}</td>
            <td class="p-3 border-r border-rose-100 dark:border-dark-border text-sm">${row.lot_date}</td>
            <td class="p-3 border-r border-rose-100 dark:border-dark-border text-sm font-medium text-gray-800 dark:text-white">${companyMap[row.company_id] || row.company_name || 'Company #' + row.company_id}</td>
            <td class="p-3 text-gray-800 dark:text-gray-300 border-r border-rose-100 dark:border-dark-border text-sm">${row.description || '-'}</td>
            <td class="p-3 text-right text-gray-800 dark:text-gray-200 font-medium border-r border-rose-100 dark:border-dark-border text-sm">৳${parseFloat(row.total_amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            <td class="p-3 text-right text-sm">
                <button onclick="editRow(${row.id})" class="text-rose-600 hover:text-rose-800 transition-colors p-1"><i class="ph ph-pencil-simple"></i></button>
                <button onclick="deleteRow(${row.id})" class="text-red-500 hover:text-red-700 transition-colors p-1 ml-1"><i class="ph ph-trash"></i></button>
            </td>
        </tr>
    `).join('');
}

function openAddModal() {
    document.getElementById('modal-title').textContent = 'Add Lot';
    document.getElementById('edit-id').value = '';
    document.getElementById('f-date').value = new Date().toISOString().split('T')[0];
    document.getElementById('f-company').value = '';
    document.getElementById('f-amount').value = '';
    document.getElementById('f-desc').value = '';
    openModal('lot-modal');
}

function editRow(id) {
    const row = allData.find(r => r.id == id);
    if (!row) return;
    document.getElementById('modal-title').textContent = 'Edit Lot';
    document.getElementById('edit-id').value = id;
    document.getElementById('f-date').value = row.lot_date;
    document.getElementById('f-company').value = row.company_id || '';
    document.getElementById('f-amount').value = row.total_amount;
    document.getElementById('f-desc').value = row.description || '';
    openModal('lot-modal');
}

function deleteRow(id) {
    if (!confirm('Delete this lot?')) return;
    fetch(API, { method: 'DELETE', headers: {'Content-Type':'application/json'}, body: JSON.stringify({id}) })
        .then(r => r.json())
        .then(() => {
            sessionStorage.removeItem('crm_badge_data');
            // Check if fetchCrmBadges function exists and call it
            if (typeof fetchCrmBadges === 'function') {
                fetchCrmBadges();
            } else {
                // If it's scoped, dispatching standard dashboard reload or location reload could work, but simply refreshing page or letting navigation reload it is fine.
                // We also have local reload.
            }
            loadData();
        });
}

document.getElementById('lot-form').addEventListener('submit', e => {
    e.preventDefault();
    const editId = document.getElementById('edit-id').value;
    const body = {
        company_id: document.getElementById('f-company').value,
        lot_date: document.getElementById('f-date').value,
        total_amount: document.getElementById('f-amount').value,
        description: document.getElementById('f-desc').value,
    };

    if (editId) {
        body.id = editId;
        fetch(API, { method: 'PUT', headers: {'Content-Type':'application/json'}, body: JSON.stringify(body) })
            .then(r => r.json())
            .then(() => { 
                closeModal('lot-modal'); 
                sessionStorage.removeItem('crm_badge_data');
                loadData(); 
            });
    } else {
        fetch(API, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(body) })
            .then(r => r.json())
            .then(() => { 
                closeModal('lot-modal'); 
                sessionStorage.removeItem('crm_badge_data');
                loadData(); 
            });
    }
});

document.addEventListener('DOMContentLoaded', init);
</script>

<?php include 'includes/footer.php'; ?>
