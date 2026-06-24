<?php include 'includes/header.php'; ?>

<div class="max-w-7xl mx-auto space-y-6 animate-fade-in" style="animation-delay: 0.1s;">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Dealer Withdrawals</h2>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Manage fund withdrawals by registered dealers.</p>
        </div>
        <button onclick="openAddModal()" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded text-sm font-medium transition-colors shadow-sm flex items-center border border-primary-700">
            <i class="ph ph-plus font-bold mr-2"></i> New Withdrawal
        </button>
    </div>

    <div class="bg-white dark:bg-dark-card border border-blue-200 dark:border-dark-border shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-primary-600 text-white text-xs uppercase tracking-wider border-b border-primary-700">
                        <th class="p-3 font-semibold border-r border-primary-500/50">ID</th>
                        <th class="p-3 font-semibold border-r border-primary-500/50">Date</th>
                        <th class="p-3 font-semibold border-r border-primary-500/50">Dealer Name</th>
                        <th class="p-3 font-semibold border-r border-primary-500/50">Description</th>
                        <th class="p-3 font-semibold text-right border-r border-primary-500/50">Amount</th>
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

<!-- Modal -->
<div id="wd-modal" class="fixed inset-0 bg-gray-900/50 dark:bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="modal-content bg-white dark:bg-dark-card w-full max-w-md rounded-2xl shadow-xl transform scale-95 opacity-0 transition-all duration-300 border border-gray-100 dark:border-dark-border">
        <div class="p-5 border-b border-gray-100 dark:border-dark-border flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white" id="modal-title">New Withdrawal</h3>
            <button onclick="closeModal('wd-modal')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors"><i class="ph ph-x text-xl"></i></button>
        </div>
        <form id="wd-form" class="p-5 space-y-4">
            <input type="hidden" id="edit-id">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date</label>
                <input type="date" id="f-date" required class="w-full bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 py-2.5 px-3 rounded-lg focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500 transition-colors text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dealer Name</label>
                <select id="f-dealer" required class="w-full bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 py-2.5 px-3 rounded-lg focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500 transition-colors text-sm">
                    <option value="">Select dealer...</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount (৳)</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-medium">৳</span>
                    <input type="number" step="0.01" id="f-amount" required placeholder="0.00" class="w-full bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 py-2.5 pl-8 pr-3 rounded-lg focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500 transition-colors text-sm">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                <input type="text" id="f-desc" placeholder="Purpose..." class="w-full bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 py-2.5 px-3 rounded-lg focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500 transition-colors text-sm">
            </div>
            <div class="pt-2 flex space-x-3">
                <button type="button" onclick="closeModal('wd-modal')" class="flex-1 py-2.5 border border-gray-200 dark:border-gray-700 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Cancel</button>
                <button type="submit" class="flex-1 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm font-medium transition-colors shadow-sm">Confirm</button>
            </div>
        </form>
    </div>
</div>

<script>
const API = 'api/withdrawals.php';
let allData = [];
let dealers = [];

function init() {
    fetch('https://happycrm.site/happyreports_api/index.php?table=dealers')
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                dealers = res.data || [];
                populateDealerSelect();
            }
            loadData();
        })
        .catch(err => {
            console.error('Error fetching dealers:', err);
            loadData();
        });
}

function populateDealerSelect(selectedName = '') {
    const select = document.getElementById('f-dealer');
    select.innerHTML = '<option value="">Select dealer...</option>';
    
    dealers.forEach(d => {
        const option = document.createElement('option');
        option.value = d.dealer_name;
        option.textContent = d.dealer_name;
        select.appendChild(option);
    });

    if (selectedName && !dealers.some(d => d.dealer_name === selectedName)) {
        const option = document.createElement('option');
        option.value = selectedName;
        option.textContent = selectedName;
        select.appendChild(option);
    }
}

function loadData() {
    fetch(API).then(r => r.json()).then(res => { allData = res.data || []; renderTable(); });
}

function renderTable() {
    const tbody = document.getElementById('data-body');
    if (!allData.length) { tbody.innerHTML = '<tr><td colspan="6" class="p-8 text-center text-gray-400">No withdrawals found.</td></tr>'; return; }
    tbody.innerHTML = allData.map((row, index) => `
        <tr class="${index % 2 !== 0 ? 'bg-primary-50/50 dark:bg-primary-900/10' : 'bg-white dark:bg-dark-card'} hover:bg-blue-100/50 dark:hover:bg-gray-800 transition-colors border-b border-blue-100 dark:border-dark-border">
            <td class="p-3 text-gray-600 dark:text-gray-400 border-r border-blue-100 dark:border-dark-border text-sm">#${row.id}</td>
            <td class="p-3 border-r border-blue-100 dark:border-dark-border text-sm">${row.withdrawal_date}</td>
            <td class="p-3 font-medium text-gray-800 dark:text-white border-r border-blue-100 dark:border-dark-border text-sm">${row.dealer_name}</td>
            <td class="p-3 border-r border-blue-100 dark:border-dark-border text-sm">${row.description || '-'}</td>
            <td class="p-3 text-right text-gray-800 dark:text-gray-200 font-medium border-r border-blue-100 dark:border-dark-border text-sm">৳${parseFloat(row.amount).toLocaleString()}</td>
            <td class="p-3 text-right text-sm">
                <button onclick="editRow(${row.id})" class="text-primary-600 hover:text-primary-800 transition-colors p-1"><i class="ph ph-pencil-simple"></i></button>
                <button onclick="deleteRow(${row.id})" class="text-red-500 hover:text-red-700 transition-colors p-1 ml-1"><i class="ph ph-trash"></i></button>
            </td>
        </tr>
    `).join('');
}

function openAddModal() {
    document.getElementById('modal-title').textContent = 'New Withdrawal';
    document.getElementById('edit-id').value = '';
    document.getElementById('f-date').value = new Date().toISOString().split('T')[0];
    populateDealerSelect('');
    document.getElementById('f-dealer').value = '';
    document.getElementById('f-amount').value = '';
    document.getElementById('f-desc').value = '';
    openModal('wd-modal');
}

function editRow(id) {
    const row = allData.find(r => r.id == id);
    if (!row) return;
    document.getElementById('modal-title').textContent = 'Edit Withdrawal';
    document.getElementById('edit-id').value = id;
    document.getElementById('f-date').value = row.withdrawal_date;
    populateDealerSelect(row.dealer_name);
    document.getElementById('f-dealer').value = row.dealer_name;
    document.getElementById('f-amount').value = row.amount;
    document.getElementById('f-desc').value = row.description || '';
    openModal('wd-modal');
}

function deleteRow(id) {
    if (!confirm('Delete this withdrawal?')) return;
    fetch(API, { method: 'DELETE', headers: {'Content-Type':'application/json'}, body: JSON.stringify({id}) })
        .then(r => r.json()).then(() => loadData());
}

document.getElementById('wd-form').addEventListener('submit', e => {
    e.preventDefault();
    const editId = document.getElementById('edit-id').value;
    const body = {
        withdrawal_date: document.getElementById('f-date').value,
        dealer_name: document.getElementById('f-dealer').value,
        amount: document.getElementById('f-amount').value,
        description: document.getElementById('f-desc').value,
    };
    if (editId) { body.id = editId; fetch(API, {method:'PUT',headers:{'Content-Type':'application/json'},body:JSON.stringify(body)}).then(r=>r.json()).then(()=>{closeModal('wd-modal');loadData();}); }
    else { fetch(API, {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(body)}).then(r=>r.json()).then(()=>{closeModal('wd-modal');loadData();}); }
});

document.addEventListener('DOMContentLoaded', init);
</script>

<?php include 'includes/footer.php'; ?>
