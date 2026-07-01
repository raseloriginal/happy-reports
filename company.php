<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = 'Companies';

$transactionModel = new Transaction();
$depositModel = new Deposit();
$lotModel = new Lot();

$companyStats = $transactionModel->getCompanyStats();
$transactions = $transactionModel->getTransactions();
$deposits = $depositModel->getDeposits();

$lotStats = $lotModel->getLotStatsByCompany();
$depositStats = $depositModel->getDepositStatsByCompany();

$combinedStats = [];

// Helper function to initialize company if not exists
$initCompany = function($id, $name) use (&$combinedStats) {
    if (!isset($combinedStats[$id])) {
        $combinedStats[$id] = [
            'company_name' => $name,
            'total_in' => 0,
            'total_out' => 0,
            'total_lots' => 0,
            'total_deposits' => 0
        ];
    }
};

foreach ($companyStats as $cs) {
    $initCompany($cs->company_crm_id, $cs->company_name);
    $combinedStats[$cs->company_crm_id]['total_in'] = $cs->total_in;
    $combinedStats[$cs->company_crm_id]['total_out'] = $cs->total_out;
}

foreach ($lotStats as $ls) {
    $initCompany($ls->company_crm_id, $ls->company_name);
    $combinedStats[$ls->company_crm_id]['total_lots'] = $ls->total_lots;
}

foreach ($depositStats as $ds) {
    $initCompany($ds->company_crm_id, $ds->company_name);
    $combinedStats[$ds->company_crm_id]['total_deposits'] = $ds->total_deposits;
}

require_once __DIR__ . '/includes/header.php';
?>
<div class="xl-panel" style="margin:12px;">
    <div class="xl-panel-header">
        <div class="header-left">
            <i class="fa-solid fa-building" style="color:var(--accent)"></i>
            Companies
            <span class="td-muted" style="font-weight:400;font-size:11px;" id="co-count"></span>
        </div>
        <div class="header-right">
            <input type="text" id="co-search" class="form-control" style="width:180px;height:26px;padding:3px 8px;font-size:12px;" placeholder="Search companies…">
        </div>
    </div>
    <div style="overflow-x:auto;">
        <table class="xl-table" id="co-table">
            <thead>
                <tr>
                    <th style="width:60px;">#</th>
                    <th style="width:60px;">CRM ID</th>
                    <th>Company Name</th>
                </tr>
            </thead>
            <tbody id="co-tbody">
                <tr class="loading-row"><td colspan="3"><i class="fa-solid fa-spinner fa-spin"></i> Loading from CRM…</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
(async function() {
    const tbody = document.getElementById('co-tbody');
    const countEl = document.getElementById('co-count');
    try {
        const res = await fetch(window.CRM_API + '?table=companies&limit=1000');
        const json = await res.json();
        if (json.status !== 'success' || !json.data.length) {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center td-muted" style="padding:20px">No companies found.</td></tr>';
            return;
        }
        let rows = json.data;
        countEl.textContent = '(' + rows.length + ' records)';

        function render(data) {
            tbody.innerHTML = data.map((c, i) => `
                <tr>
                    <td class="td-muted">${i + 1}</td>
                    <td class="mono">${c.id}</td>
                    <td class="fw600">${c.company_name}</td>
                </tr>
            `).join('');
        }
        render(rows);

        document.getElementById('co-search').addEventListener('input', function() {
            const q = this.value.toLowerCase();
            render(q ? rows.filter(c => c.company_name.toLowerCase().includes(q)) : rows);
        });
    } catch(e) {
        console.error("CRM Fetch Error:", e);
        tbody.innerHTML = `<tr><td colspan="3" class="text-center" style="color:var(--red);padding:20px"><i class="fa-solid fa-triangle-exclamation"></i> Failed to load CRM data: ${e.message}</td></tr>`;
    }
})();
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
