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
            <i class="fa-solid fa-building-columns text-primary mr-2"></i> Add Company Deposit
        </h3>
        <form action="<?php echo URLROOT; ?>/import/addDeposit" method="POST" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Company</label>
                    <select id="company_select" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 p-2 border">
                        <option value="">Select Company</option>
                    </select>
                    <input type="hidden" name="company_name" id="company_name_input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Company CRM ID</label>
                    <input type="text" name="company_crm_id" id="company_crm_id_input" readonly required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 p-2 border bg-gray-100 cursor-not-allowed">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Amount ($)</label>
                    <input type="number" step="0.01" name="amount" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 p-2 border">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Date</label>
                    <input type="date" name="deposit_date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 p-2 border">
                </div>
            </div>
            <button type="submit" class="w-full bg-primary hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg transition">Save Deposit</button>
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
                    <label class="block text-sm font-medium text-gray-700">Amount ($)</label>
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

    // Fetch Companies
    fetch(apiUrlBase + 'companies')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const companySelect = document.getElementById('company_select');
                data.data.forEach(company => {
                    const option = document.createElement('option');
                    option.value = company.id;
                    option.textContent = company.company_name;
                    companySelect.appendChild(option);
                });
            }
        })
        .catch(error => console.error('Error fetching companies:', error));

    // Handle Company Selection
    document.getElementById('company_select').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            document.getElementById('company_crm_id_input').value = selectedOption.value;
            document.getElementById('company_name_input').value = selectedOption.textContent;
        } else {
            document.getElementById('company_crm_id_input').value = '';
            document.getElementById('company_name_input').value = '';
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
