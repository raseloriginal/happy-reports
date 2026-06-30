<script>document.getElementById('pageTitle').textContent = 'Inventory — Transactions';</script>

<style>
/* ── INCREASED PADDING OVERRIDES ── */
.xl-panel-header {
    padding: 16px 20px !important;
}
.xl-table thead th {
    padding: 12px 16px !important;
    font-size: 12px !important;
}
.xl-table tbody td {
    padding: 12px 16px !important;
    font-size: 13px !important;
}
.xl-pagination {
    padding: 12px 16px !important;
}
</style>

<!-- Import Modal -->
<div class="modal-overlay" id="import-modal">
    <div class="modal-box" style="max-width:480px;">
        <div class="modal-header">
            <span><i class="fa-solid fa-file-csv" style="color:var(--green);margin-right:6px;"></i> Import Transactions</span>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <div style="background:var(--row-alt);border:1px solid var(--border);padding:8px 10px;margin-bottom:12px;font-size:11.5px;color:var(--text-muted);">
                <strong>CSV Headers required:</strong><br>
                <span class="mono" style="font-size:11px;">CRM_IDs, Company_CRM_ID, Company_Name, Total_Out, Total_In, Transaction_Date</span>
                <br><a href="<?php echo URLROOT; ?>/templates/transactions_template.csv" download style="color:var(--accent);font-size:11px;"><i class="fa-solid fa-download"></i> Download Template</a>
            </div>
            <form action="<?php echo URLROOT; ?>/import/uploadTransactions" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="form-label">Select CSV File</label>
                    <input type="file" name="transactions_csv" accept=".csv" required>
                </div>
                <div class="modal-footer" style="margin:12px -14px -14px;padding:10px 14px;">
                    <button type="button" class="btn modal-close">Cancel</button>
                    <button type="submit" class="btn btn-green"><i class="fa-solid fa-upload"></i> Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Main Panel -->
<div class="xl-panel" style="margin:12px;">
    <div class="xl-panel-header">
        <div class="header-left">
            <i class="fa-solid fa-boxes-stacked" style="color:var(--accent)"></i>
            Transaction Records
            <span class="td-muted" style="font-weight:400;font-size:11px;" id="inv-count"></span>
        </div>
        <div class="header-right">
            <!-- Filter bar inline with header -->
            <label style="font-size:11.5px;color:var(--text-muted);">From</label>
            <input type="date" id="f-from" style="height:26px;padding:3px 6px;border:1px solid var(--border-dark);border-radius:2px;font-size:12px;">
            <label style="font-size:11.5px;color:var(--text-muted);">To</label>
            <input type="date" id="f-to" style="height:26px;padding:3px 6px;border:1px solid var(--border-dark);border-radius:2px;font-size:12px;">
            <button class="btn btn-sm" onclick="applyFilter()"><i class="fa-solid fa-filter"></i> Filter</button>
            <button class="btn btn-sm" onclick="clearFilter()">Clear</button>
            <div class="divider-v"></div>
            <button class="btn btn-primary btn-sm" onclick="openModal('import-modal')">
                <i class="fa-solid fa-file-import"></i> Import Transactions
            </button>
        </div>
    </div>
    <div style="overflow-x:auto;">
        <table class="xl-table">
            <thead>
                <tr>
                    <th style="width:60px;"># ID</th>
                    <th style="width:110px;">Date</th>
                    <th>Company</th>
                    <th>CRM IDs</th>
                    <th class="text-right" style="width:120px;">Total Out (৳)</th>
                    <th class="text-right" style="width:120px;">Total In (৳)</th>
                </tr>
            </thead>
            <tbody id="inv-tbody">
                <?php if (!empty($data['transactions'])): ?>
                    <?php foreach ($data['transactions'] as $tx): ?>
                    <tr data-date="<?php echo $tx->transaction_date; ?>">
                        <td class="mono td-muted">#<?php echo $tx->id; ?></td>
                        <td><?php echo date('d M Y', strtotime($tx->transaction_date)); ?></td>
                        <td class="fw600"><?php echo htmlspecialchars($tx->company_name ?? '—'); ?></td>
                        <td class="mono td-muted" style="font-size:11px;"><?php echo htmlspecialchars(substr($tx->crm_ids ?? '', 0, 40) . (strlen($tx->crm_ids ?? '') > 40 ? '…' : '')); ?></td>
                        <td class="td-num td-red"><?php echo $tx->total_out_value > 0 ? number_format($tx->total_out_value, 2) : '—'; ?></td>
                        <td class="td-num td-green"><?php echo $tx->total_in_value > 0 ? number_format($tx->total_in_value, 2) : '—'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7">
                        <div class="empty-state">
                            <i class="fa-solid fa-inbox" style="display:block;font-size:32px;margin-bottom:10px;opacity:0.2;"></i>
                            No transactions imported yet. Use the <strong>Import Transactions</strong> button above.
                        </div>
                    </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if (!empty($data['transactions'])): ?>
    <div class="xl-pagination">
        <span id="inv-info" style="font-size:11.5px;color:var(--text-muted);">
            Showing <?php echo count($data['transactions']); ?> records
        </span>
        <span></span>
    </div>
    <?php endif; ?>
</div>

<script>
// Set count
const invRows = document.querySelectorAll('#inv-tbody tr[data-date]');
document.getElementById('inv-count').textContent = '(' + invRows.length + ' records)';

function applyFilter() {
    const from = document.getElementById('f-from').value;
    const to = document.getElementById('f-to').value;
    let visible = 0;
    invRows.forEach(row => {
        const d = row.getAttribute('data-date');
        let show = true;
        if (from && d < from) show = false;
        if (to && d > to) show = false;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    document.getElementById('inv-count').textContent = '(' + visible + ' shown)';
}

function clearFilter() {
    document.getElementById('f-from').value = '';
    document.getElementById('f-to').value = '';
    invRows.forEach(r => r.style.display = '');
    document.getElementById('inv-count').textContent = '(' + invRows.length + ' records)';
}
</script>
