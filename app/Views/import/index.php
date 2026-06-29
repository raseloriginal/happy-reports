<script>document.getElementById('pageTitle').innerText = 'Data Imports & Manual Entries';</script>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Import Lots -->
    <div class="glass-panel rounded-2xl p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">
            <i class="fa-solid fa-file-csv text-blue-500 mr-2"></i> Import Lots
        </h3>
        <p class="text-sm text-gray-500 mb-2">Upload a CSV file containing lot data. Ensure headers are: CRM_ID, Warehouse_CRM_ID, Warehouse_Name, Company_CRM_ID, Company_Name, Lot_Value, Lot_Date.</p>
        <a href="<?php echo URLROOT; ?>/templates/lots_template.csv" class="text-xs text-blue-600 hover:underline mb-4 inline-block" download><i class="fa-solid fa-download"></i> Download Lots Template</a>
        <form action="<?php echo URLROOT; ?>/import/uploadLots" method="POST" enctype="multipart/form-data" class="space-y-4">
            <div>
                <input type="file" name="lots_csv" accept=".csv" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-gray-300 rounded-lg bg-gray-50">
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition">Upload Lots</button>
        </form>
    </div>

    <!-- Import Transactions -->
    <div class="glass-panel rounded-2xl p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">
            <i class="fa-solid fa-file-csv text-green-500 mr-2"></i> Import Transactions
        </h3>
        <p class="text-sm text-gray-500 mb-2">Upload a CSV file containing transactions. Headers: CRM_IDs, Company_CRM_ID, Company_Name, Total_Out, Total_In, Transaction_Date.</p>
        <a href="<?php echo URLROOT; ?>/templates/transactions_template.csv" class="text-xs text-green-600 hover:underline mb-4 inline-block" download><i class="fa-solid fa-download"></i> Download Transactions Template</a>
        <form action="<?php echo URLROOT; ?>/import/uploadTransactions" method="POST" enctype="multipart/form-data" class="space-y-4">
            <div>
                <input type="file" name="transactions_csv" accept=".csv" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 cursor-pointer border border-gray-300 rounded-lg bg-gray-50">
            </div>
            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition">Upload Transactions</button>
        </form>
    </div>

    <!-- Manual Deposit -->
    <div class="glass-panel rounded-2xl p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">
            <i class="fa-solid fa-building-columns text-primary mr-2"></i> Add Company Deposit(s)
        </h3>
        <form action="<?php echo URLROOT; ?>/import/addDeposit" method="POST" class="space-y-4" id="deposit_form">
            <div id="deposit_rows">
                <div class="deposit-row mb-4 relative pb-4 border-b border-gray-100">
                    <button type="button" class="remove-deposit absolute top-0 right-0 text-red-500 hover:text-red-700 hidden" title="Remove"><i class="fa-solid fa-times"></i></button>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Company</label>
                            <select name="company_select" required class="company-select mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 p-2 border">
                                <option value="">Select Company</option>
                            </select>
                            <input type="hidden" name="company_name[]" class="company-name-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Company CRM ID</label>
                            <input type="text" name="company_crm_id[]" readonly required class="company-crm-id-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 p-2 border bg-gray-100 cursor-not-allowed">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Amount (৳)</label>
                            <input type="number" step="0.01" name="amount[]" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 p-2 border">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date</label>
                            <input type="date" name="deposit_date[]" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 p-2 border">
                        </div>
                    </div>
                </div>
            </div>
            <button type="button" id="add_deposit_btn" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-2 px-4 rounded-lg transition border border-gray-300 mb-2">
                <i class="fa-solid fa-plus mr-2"></i> Add Another Deposit
            </button>
            <button type="submit" class="w-full bg-primary hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg transition">Save Deposit(s)</button>
        </form>
    </div>

    <!-- Manual Dealer Payment -->
    <div class="glass-panel rounded-2xl p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">
            <i class="fa-solid fa-hand-holding-dollar text-orange-500 mr-2"></i> Add Dealer Payment
        </h3>
        <form action="<?php echo URLROOT; ?>/import/addPayment" method="POST" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Dealer</label>
                    <select id="dealer_select" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50 p-2 border">
                        <option value="">Select Dealer</option>
                    </select>
                    <input type="hidden" name="dealer_name" id="dealer_name_input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Dealer CRM ID</label>
                    <input type="text" name="dealer_crm_id" id="dealer_crm_id_input" readonly required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50 p-2 border bg-gray-100 cursor-not-allowed">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Amount (৳)</label>
                    <input type="number" step="0.01" name="amount" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50 p-2 border">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Date</label>
                    <input type="date" name="payment_date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50 p-2 border">
                </div>
            </div>
            <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-medium py-2 px-4 rounded-lg transition">Save Payment</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Determine the base API URL (pointing to live CRM API)
    const apiUrlBase = 'https://happycrm.site/happyreports_api/index.php?table=';

    // Global variable for company data
    let companyOptions = [];

    // Fetch Companies
    fetch(apiUrlBase + 'companies')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                companyOptions = data.data;
                const companySelects = document.querySelectorAll('.company-select');
                companySelects.forEach(select => {
                    companyOptions.forEach(company => {
                        const option = document.createElement('option');
                        option.value = company.id;
                        option.textContent = company.company_name;
                        select.appendChild(option);
                    });
                });
            }
        })
        .catch(error => console.error('Error fetching companies:', error));

    // Handle Company Selection using event delegation
    document.getElementById('deposit_rows').addEventListener('change', function(e) {
        if (e.target.classList.contains('company-select')) {
            const row = e.target.closest('.deposit-row');
            const selectedOption = e.target.options[e.target.selectedIndex];
            if (selectedOption.value) {
                row.querySelector('.company-crm-id-input').value = selectedOption.value;
                row.querySelector('.company-name-input').value = selectedOption.textContent;
            } else {
                row.querySelector('.company-crm-id-input').value = '';
                row.querySelector('.company-name-input').value = '';
            }
        }
    });

    // Handle Add Another Deposit
    document.getElementById('add_deposit_btn').addEventListener('click', function() {
        const depositRows = document.getElementById('deposit_rows');
        const firstRow = depositRows.querySelector('.deposit-row');
        const newRow = firstRow.cloneNode(true);
        
        // Clear inputs in the new row
        newRow.querySelectorAll('input').forEach(input => input.value = '');
        newRow.querySelector('.company-select').selectedIndex = 0;
        
        // Show remove button
        newRow.querySelector('.remove-deposit').classList.remove('hidden');
        
        depositRows.appendChild(newRow);
        
        // Also ensure remove buttons are visible if there is more than 1 row
        const allRows = depositRows.querySelectorAll('.deposit-row');
        if (allRows.length > 1) {
            allRows.forEach(row => row.querySelector('.remove-deposit').classList.remove('hidden'));
        }
    });

    // Handle Remove Deposit Row
    document.getElementById('deposit_rows').addEventListener('click', function(e) {
        if (e.target.closest('.remove-deposit') || e.target.closest('.remove-deposit i')) {
            const depositRows = document.getElementById('deposit_rows');
            const allRows = depositRows.querySelectorAll('.deposit-row');
            if (allRows.length > 1) {
                e.target.closest('.deposit-row').remove();
            }
            // Hide remove button if only 1 row left
            const remainingRows = depositRows.querySelectorAll('.deposit-row');
            if (remainingRows.length === 1) {
                remainingRows[0].querySelector('.remove-deposit').classList.add('hidden');
            }
        }
    });

    // Fetch Dealers
    fetch(apiUrlBase + 'dealers')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const dealerSelect = document.getElementById('dealer_select');
                data.data.forEach(dealer => {
                    const option = document.createElement('option');
                    option.value = dealer.id;
                    option.textContent = dealer.dealer_name;
                    dealerSelect.appendChild(option);
                });
            }
        })
        .catch(error => console.error('Error fetching dealers:', error));

    // Handle Dealer Selection
    document.getElementById('dealer_select').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            document.getElementById('dealer_crm_id_input').value = selectedOption.value;
            document.getElementById('dealer_name_input').value = selectedOption.textContent;
        } else {
            document.getElementById('dealer_crm_id_input').value = '';
            document.getElementById('dealer_name_input').value = '';
        }
    });
});
</script>
