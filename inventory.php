<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = 'Inventory — Transactions';

$transactionModel = new Transaction();
$lotModel = new Lot();

$transactionItemModel = new TransactionItem();
$transactions  = $transactionModel->getTransactions();
foreach ($transactions as $tx) {
    $tx->items = $transactionItemModel->getItemsByTransactionId($tx->id);
}
$totalStockIn  = $lotModel->getTotalLotValue();          // All lots imported
$totalSalesOut = $transactionModel->getTotalSalesOutValue(); // All sales out
$floorStock    = $totalStockIn - $totalSalesOut;               // Remaining stock

$data = [
    'transactions'  => $transactions,
    'totalStockIn'  => $totalStockIn,
    'totalSalesOut' => $totalSalesOut,
    'floorStock'    => $floorStock
];

require_once __DIR__ . '/includes/header.php';
?>
<style>
/* ── INCREASED PADDING OVERRIDES ── */
.xl-panel-header { padding: 16px 20px !important; }
.xl-table thead th { padding: 12px 16px !important; font-size: 12px !important; }
.xl-table tbody td { padding: 10px 16px !important; font-size: 13px !important; }
.xl-pagination { padding: 12px 16px !important; }

/* ── FLOOR STOCK STRIP ── */
#floor-stock-strip {
    display: flex;
    background: #fff;
    border-bottom: 1px solid var(--border);
    gap: 0;
}
.fs-cell {
    flex: 1;
    padding: 14px 22px;
    border-right: 1px solid var(--border);
    position: relative;
}
.fs-cell:last-child { border-right: none; }
.fs-label {
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-muted);
    margin-bottom: 4px;
}
.fs-value {
    font-size: 18px;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
}
.fs-sub { font-size: 10.5px; color: var(--text-muted); margin-top: 2px; }
.fs-cell.stock-in  .fs-value { color: #166534; }
.fs-cell.stock-out .fs-value { color: #b91c1c; }
.fs-cell.floor-stock .fs-value { color: #92400e; }
.fs-cell.floor-stock { background: #fffbeb; border-left: 3px solid #f59e0b; }
.fs-cell.floor-stock.negative .fs-value { color: #b91c1c; }
.fs-cell.floor-stock.negative { background: #fef2f2; border-left-color: #ef4444; }

.toggle-details-btn {
    background: none;
    border: none;
    color: var(--accent);
    cursor: pointer;
    width: 24px;
    height: 24px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: background 0.2s, transform 0.2s;
}
.toggle-details-btn:hover {
    background: rgba(26, 115, 232, 0.1);
}
.details-row td {
    background-color: #fafaf9;
    border-bottom: 1px solid var(--border) !important;
}

/* ── CSV PREVIEW TABLE ── */
#csv-preview-wrap {
    display: none;
    flex-direction: column;
    max-height: 420px;
}
#csv-preview-wrap.active { display: flex; }
#csv-preview-table-wrap {
    overflow: auto;
    border: 1px solid var(--border);
    border-radius: 4px;
    flex: 1;
}
.csv-preview-tbl {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}
.csv-preview-tbl thead th {
    background: #eaeae4;
    padding: 8px 10px;
    text-align: left;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    color: #555;
    border-bottom: 1px solid var(--border);
    white-space: nowrap;
    position: sticky;
    top: 0;
}
.csv-preview-tbl tbody td {
    padding: 6px 8px;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
}
.csv-preview-tbl tbody tr:hover { background: #f7f7ff; }
.csv-cell-edit {
    width: 100%;
    border: 1px solid transparent;
    background: transparent;
    padding: 3px 5px;
    font-size: 12px;
    font-family: inherit;
    color: inherit;
    border-radius: 3px;
    min-width: 60px;
}
.csv-cell-edit:focus {
    outline: none;
    border-color: var(--accent);
    background: #fff;
}
.csv-row-remove {
    background: none;
    border: none;
    color: #ccc;
    cursor: pointer;
    font-size: 14px;
    padding: 2px 6px;
    border-radius: 3px;
    transition: color 0.15s, background 0.15s;
}
.csv-row-remove:hover { color: #ef4444; background: #fee2e2; }
#csv-preview-info {
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 8px;
}
#csv-stage1 { display: block; }
#csv-stage2 { display: none; }

/* ── EDIT MODAL ── */
#edit-txn-modal .form-row { display:flex; gap:10px; }
#edit-txn-modal .form-group { flex:1; }

/* ── ACTION BUTTONS IN TABLE ── */
.act-btn {
    background: none;
    border: 1px solid transparent;
    border-radius: 3px;
    cursor: pointer;
    padding: 3px 7px;
    font-size: 12px;
    transition: all 0.15s;
    line-height: 1;
}
.act-btn-edit  { color: var(--accent); }
.act-btn-edit:hover  { background: #dbeafe; border-color: #93c5fd; }
.act-btn-del   { color: #dc2626; }
.act-btn-del:hover   { background: #fee2e2; border-color: #fca5a5; }
.act-btn-del.confirming { background: #dc2626; color: #fff; border-color: #dc2626; }

/* ── DELETE CONFIRM INLINE ── */
.del-confirm-wrap { display:flex; align-items:center; gap:6px; }
.del-yes { background:#dc2626;color:#fff;border:none;border-radius:3px;padding:3px 8px;cursor:pointer;font-size:11px; }
.del-no  { background:#e5e7eb;color:#374151;border:none;border-radius:3px;padding:3px 8px;cursor:pointer;font-size:11px; }

/* Row fade out */
@keyframes rowFadeOut {
    from { opacity:1; background:#fee2e2; }
    to   { opacity:0; height:0; padding:0; }
}
.row-removing { animation: rowFadeOut 0.4s ease forwards; }
</style>

<!-- IMPORT TRANSACTIONS MODAL -->
<div class="modal-overlay" id="import-txn-modal">
    <div class="modal-box" style="max-width:960px;width:96vw;" id="import-txn-box">
        <div class="modal-header">
            <span><i class="fa-solid fa-file-archive" style="color:var(--accent);margin-right:7px;"></i> Import Transactions (ZIP)</span>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <!-- Stage 1: file picker -->
            <div id="txn-import-s1">
                <div style="background:#e8f0fe;border:1px solid #93c5fd;padding:8px 10px;margin-bottom:12px;font-size:11.5px;color:#1e40af;border-radius:2px;">
                    <strong>Required ZIP contents:</strong><br>
                    <span class="mono" style="font-size:11px;color:#1a1a1a;">transactions_summary.csv and transaction_details.csv</span>
                </div>
                <div class="form-group" id="txn-drop-zone" style="border: 2px dashed #93c5fd; border-radius: 6px; padding: 30px; text-align: center; cursor: pointer; background: #f0f4ff;">
                    <i class="fa-solid fa-cloud-arrow-up" style="font-size:32px; color:var(--accent); margin-bottom:10px;"></i>
                    <div style="font-size: 14px; color: #444; font-weight: 500;">Click or drag a .zip file here</div>
                    <input type="file" id="txn-import-file" accept=".zip" style="display:none;">
                </div>
                <div id="txn-import-loading" style="display:none; text-align:center; padding:20px;">
                    <i class="fa-solid fa-spinner fa-spin" style="font-size:24px; color:var(--accent);"></i>
                    <p style="margin-top:10px; font-size:13px; color:#555;">Processing ZIP file...</p>
                </div>
                <p id="txn-import-error" style="color:#dc2626;font-size:12px;margin-top:6px;display:none;"></p>
            </div>
            <!-- Stage 2: preview -->
            <div id="txn-import-s2" style="display:none;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                    <span style="font-size:13px;font-weight:600;color:#1a1a1a;">
                        <i class="fa-solid fa-table" style="color:var(--accent);margin-right:6px;"></i>
                        Preview — review data before importing
                    </span>
                    <button onclick="resetTxnImport()" class="btn btn-sm" style="font-size:11px;">
                        <i class="fa-solid fa-arrow-left"></i> Back to File
                    </button>
                </div>
                <div style="display:flex; gap: 15px; margin-bottom: 15px;">
                    <div style="background:#e8f0fe; border:1px solid #93c5fd; padding:10px 15px; border-radius:4px; flex:1;">
                        <div style="font-size:11px; color:#1e40af; font-weight:bold; text-transform:uppercase;">Total Transactions</div>
                        <div id="txnImportSummaryCount" style="font-size:20px; font-weight:bold; color:#1e3a8a;">0</div>
                    </div>
                    <div style="background:#f0fdf4; border:1px solid #dcfce7; padding:10px 15px; border-radius:4px; flex:1;">
                        <div style="font-size:11px; color:#166534; font-weight:bold; text-transform:uppercase;">Total Items</div>
                        <div id="txnImportSummaryItems" style="font-size:20px; font-weight:bold; color:#14532d;">0</div>
                    </div>
                    <div style="background:#fef2f2; border:1px solid #fecaca; padding:10px 15px; border-radius:4px; flex:1;">
                        <div style="font-size:11px; color:#b91c1c; font-weight:bold; text-transform:uppercase;">Total Out Value</div>
                        <div id="txnImportSummaryOut" style="font-size:20px; font-weight:bold; color:#991b1b;">৳0</div>
                    </div>
                </div>
                <div id="txn-import-preview" style="overflow-y:auto; max-height:400px; padding-right:5px;"></div>
            </div>
        </div>
        <div class="modal-footer" id="txn-import-footer-s1">
            <button type="button" class="btn modal-close">Cancel</button>
        </div>
        <div class="modal-footer" id="txn-import-footer-s2" style="display:none;">
            <button type="button" class="btn modal-close">Cancel</button>
            <button type="button" id="txn-confirm-btn"
                class="btn btn-primary"
                onclick="confirmTxnImport()">
                <i class="fa-solid fa-upload"></i> Confirm Import
            </button>
        </div>
    </div>
</div>

<!-- ── EDIT TRANSACTION MODAL ── -->
<div class="modal-overlay" id="edit-txn-modal">
    <div class="modal-box" style="max-width:560px;" id="edit-txn-box">
        <div class="modal-header">
            <span><i class="fa-solid fa-pen-to-square" style="color:var(--accent);margin-right:6px;"></i> Edit Transaction</span>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="edit-txn-id">
            <div class="form-group" style="margin-bottom:10px;">
                <label class="form-label">CRM IDs</label>
                <input type="text" id="edit-txn-crm-ids" class="form-control" placeholder="Comma-separated CRM IDs">
            </div>
            <div class="form-row" style="margin-bottom:10px;">
                <div class="form-group">
                    <label class="form-label">Company CRM ID</label>
                    <input type="text" id="edit-txn-co-id" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Company Name</label>
                    <input type="text" id="edit-txn-co-name" class="form-control">
                </div>
            </div>
            <div class="form-row" style="margin-bottom:10px;">
                <div class="form-group">
                    <label class="form-label">Total Out (৳)</label>
                    <input type="number" step="0.01" id="edit-txn-out" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Total In (৳)</label>
                    <input type="number" step="0.01" id="edit-txn-in" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Date</label>
                    <input type="date" id="edit-txn-date" class="form-control">
                </div>
            </div>
            <p id="edit-txn-error" style="color:#dc2626;font-size:12px;display:none;"></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn modal-close">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="saveEditTxn()">
                <i class="fa-solid fa-floppy-disk"></i> Save Changes
            </button>
        </div>
    </div>
</div>

<!-- ── FLOOR STOCK STRIP ── -->
<?php
$floorClass    = $floorStock >= 0 ? '' : 'negative';
?>
<div id="floor-stock-strip">
    <div class="fs-cell stock-in">
        <div class="fs-label"><i class="fa-solid fa-boxes-stacked" style="margin-right:4px;"></i>Stock In (Total Lots)</div>
        <div class="fs-value">৳<?php echo number_format($totalStockIn, 2); ?></div>
        <div class="fs-sub">All imported lot value</div>
    </div>
    <div class="fs-cell stock-out">
        <div class="fs-label"><i class="fa-solid fa-cart-arrow-down" style="margin-right:4px;"></i>Sold Out (Sales)</div>
        <div class="fs-value">৳<?php echo number_format($totalSalesOut, 2); ?></div>
        <div class="fs-sub">Total out from transactions</div>
    </div>
    <div class="fs-cell floor-stock <?php echo $floorClass; ?>">
        <div class="fs-label"><i class="fa-solid fa-warehouse" style="margin-right:4px;"></i>Current Floor Stock</div>
        <div class="fs-value"><?php echo $floorStock >= 0 ? '' : '-'; ?>৳<?php echo number_format(abs($floorStock), 2); ?></div>
        <div class="fs-sub"><?php echo $floorStock >= 0 ? 'Remaining inventory value' : 'Stock deficit'; ?></div>
    </div>
</div>

<!-- ── MAIN PANEL ── -->
<div class="xl-panel" style="margin:12px;margin-top:0;">
    <div class="xl-panel-header">
        <div class="header-left">
            <i class="fa-solid fa-boxes-stacked" style="color:var(--accent)"></i>
            Transaction Records
            <span class="td-muted" style="font-weight:400;font-size:11px;" id="inv-count"></span>
        </div>
        <div class="header-right">
            <button class="btn btn-sm" style="background:var(--accent);color:#fff;border-color:var(--accent-dark);" onclick="openModal('import-txn-modal')">
                <i class="fa-solid fa-file-import"></i> Import Transactions
            </button>
            <div class="divider-v"></div>
            <label style="font-size:11.5px;color:var(--text-muted);">From</label>
            <input type="date" id="f-from" style="height:26px;padding:3px 6px;border:1px solid var(--border-dark);border-radius:2px;font-size:12px;">
            <label style="font-size:11.5px;color:var(--text-muted);">To</label>
            <input type="date" id="f-to" style="height:26px;padding:3px 6px;border:1px solid var(--border-dark);border-radius:2px;font-size:12px;">
            <button class="btn btn-sm" onclick="applyFilter()"><i class="fa-solid fa-filter"></i> Filter</button>
            <button class="btn btn-sm" onclick="clearFilter()">Clear</button>
        </div>
    </div>
        <table class="xl-table">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th style="width:60px;"># ID</th>
                    <th style="width:110px;">Date</th>
                    <th>Company</th>
                    <th>Dealer</th>
                    <th>Warehouse</th>
                    <th class="text-right" style="width:120px;">Total Out (৳)</th>
                    <th class="text-right" style="width:120px;">Sale Value (৳)</th>
                    <th class="text-right" style="width:120px;">Total In (৳)</th>
                    <th style="width:80px;text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody id="inv-tbody">
                <?php if (!empty($data['transactions'])): ?>
                    <?php foreach ($data['transactions'] as $tx): ?>
                    <tr data-date="<?php echo $tx->transaction_date; ?>"
                        data-id="<?php echo $tx->id; ?>"
                        data-crm-ids="<?php echo htmlspecialchars($tx->crm_ids ?? ''); ?>"
                        data-co-id="<?php echo htmlspecialchars($tx->company_crm_id ?? ''); ?>"
                        data-co-name="<?php echo htmlspecialchars($tx->company_name ?? ''); ?>"
                        data-dealer-id="<?php echo htmlspecialchars($tx->dealer_crm_id ?? ''); ?>"
                        data-dealer-name="<?php echo htmlspecialchars($tx->dealer_name ?? ''); ?>"
                        data-warehouse-id="<?php echo htmlspecialchars($tx->warehouse_crm_id ?? ''); ?>"
                        data-warehouse-name="<?php echo htmlspecialchars($tx->warehouse_name ?? ''); ?>"
                        data-out="<?php echo $tx->total_out_value; ?>"
                        data-sale="<?php echo $tx->total_sale_value; ?>"
                        data-in="<?php echo $tx->total_in_value; ?>">
                        <td style="text-align:center;">
                            <button class="toggle-details-btn" onclick="toggleDetails(this, <?php echo $tx->id; ?>)" title="View products">
                                <i class="fa-solid fa-chevron-right"></i>
                            </button>
                        </td>
                        <td class="mono td-muted">#<?php echo $tx->id; ?></td>
                        <td><?php echo date('d M Y', strtotime($tx->transaction_date)); ?></td>
                        <td class="fw600"><?php echo htmlspecialchars($tx->company_name ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($tx->dealer_name ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($tx->warehouse_name ?? '—'); ?></td>
                        <td class="td-num td-red"><?php echo $tx->total_out_value > 0 ? number_format($tx->total_out_value, 2) : '—'; ?></td>
                        <td class="td-num td-blue"><?php echo $tx->total_sale_value > 0 ? number_format($tx->total_sale_value, 2) : '—'; ?></td>
                        <td class="td-num td-green"><?php echo $tx->total_in_value > 0 ? number_format($tx->total_in_value, 2) : '—'; ?></td>
                        <td style="text-align:center;white-space:nowrap;">
                            <button class="act-btn act-btn-edit" title="Edit" onclick="openEditTxn(this)">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button class="act-btn act-btn-del" title="Delete" onclick="initDeleteTxn(this)">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <tr id="details-row-<?php echo $tx->id; ?>" class="details-row" style="display:none;">
                        <td colspan="10" style="padding: 12px 20px !important;">
                            <div style="border-left: 3px solid var(--accent); padding-left: 16px;">
                                <h4 style="margin-bottom:8px; font-size:12px; text-transform:uppercase; color:#666; font-weight:700;">Products Sells Detail</h4>
                                <table class="xl-table" style="width:100%; border: 1px solid var(--border); font-size:12px; margin-bottom:0; background:#fff;">
                                    <thead>
                                        <tr style="background:#e8e8e2;">
                                            <th style="text-align:left; padding:6px 10px !important;">Product Name</th>
                                            <th style="text-align:right; width:100px; padding:6px 10px !important;">Qty Out</th>
                                            <th style="text-align:right; width:100px; padding:6px 10px !important;">Qty Sale</th>
                                            <th style="text-align:right; width:100px; padding:6px 10px !important;">Qty In</th>
                                            <th style="text-align:right; width:120px; padding:6px 10px !important;">Current Stock</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($tx->items)): ?>
                                            <?php foreach ($tx->items as $item): ?>
                                            <tr>
                                                <td style="padding:6px 10px !important;"><?php echo htmlspecialchars($item->item_name); ?></td>
                                                <td style="text-align:right; padding:6px 10px !important;"><?php echo number_format($item->item_out_qty); ?></td>
                                                <td style="text-align:right; padding:6px 10px !important;"><?php echo number_format($item->item_sell_qty); ?></td>
                                                <td style="text-align:right; padding:6px 10px !important;"><?php echo number_format($item->item_in_qty); ?></td>
                                                <td style="text-align:right; padding:6px 10px !important; font-weight:600; color:<?php echo $item->current_stock < 0 ? 'var(--red)' : '#166534'; ?>;">
                                                    <?php echo number_format($item->current_stock); ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="5" style="text-align:center; color:#999; padding: 10px;">No product details found for this transaction.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="10">
                        <div class="empty-state">
                            <i class="fa-solid fa-inbox" style="display:block;font-size:32px;margin-bottom:10px;opacity:0.2;"></i>
                            No transactions found.
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
// ── COUNT ──
const invRows = document.querySelectorAll('#inv-tbody tr[data-date]');
document.getElementById('inv-count').textContent = '(' + invRows.length + ' records)';

// ── FILTER ──
function applyFilter() {
    const from = document.getElementById('f-from').value;
    const to   = document.getElementById('f-to').value;
    let visible = 0;
    invRows.forEach(row => {
        const d = row.getAttribute('data-date');
        let show = true;
        if (from && d < from) show = false;
        if (to   && d > to)   show = false;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    document.getElementById('inv-count').textContent = '(' + visible + ' shown)';
}
function clearFilter() {
    document.getElementById('f-from').value = '';
    document.getElementById('f-to').value   = '';
    invRows.forEach(r => r.style.display = '');
    document.getElementById('inv-count').textContent = '(' + invRows.length + ' records)';
}

const URLROOT_JS = '<?php echo URLROOT; ?>';

// ── EDIT TRANSACTION ─────────────────────────────────────────────
function openEditTxn(btn) {
    const tr = btn.closest('tr');
    document.getElementById('edit-txn-id').value      = tr.dataset.id;
    document.getElementById('edit-txn-crm-ids').value = tr.dataset.crmIds;
    document.getElementById('edit-txn-co-id').value   = tr.dataset.coId;
    document.getElementById('edit-txn-co-name').value = tr.dataset.coName;
    document.getElementById('edit-txn-out').value     = tr.dataset.out;
    document.getElementById('edit-txn-in').value      = tr.dataset.in;
    document.getElementById('edit-txn-date').value    = tr.dataset.date;
    document.getElementById('edit-txn-error').style.display = 'none';
    openModal('edit-txn-modal');
}

async function saveEditTxn() {
    const id = document.getElementById('edit-txn-id').value;
    const payload = {
        id:               parseInt(id),
        crm_ids:          document.getElementById('edit-txn-crm-ids').value,
        company_crm_id:   document.getElementById('edit-txn-co-id').value,
        company_name:     document.getElementById('edit-txn-co-name').value,
        total_out_value:  parseFloat(document.getElementById('edit-txn-out').value) || 0,
        total_in_value:   parseFloat(document.getElementById('edit-txn-in').value) || 0,
        transaction_date: document.getElementById('edit-txn-date').value
    };
    const btn = document.querySelector('#edit-txn-box .btn-primary');
    btn.disabled = true; btn.textContent = 'Saving…';
    try {
        const res  = await fetch(URLROOT_JS + '/api.php?action=updateTransaction', {
            method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload)
        });
        const json = await res.json();
        if (json.success) {
            closeModal('edit-txn-modal');
            location.reload();
        } else {
            document.getElementById('edit-txn-error').textContent = 'Save failed. Please try again.';
            document.getElementById('edit-txn-error').style.display = 'block';
        }
    } catch (e) {
        document.getElementById('edit-txn-error').textContent = 'Network error.';
        document.getElementById('edit-txn-error').style.display = 'block';
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Save Changes';
    }
}

// ── DELETE TRANSACTION ───────────────────────────────────────────
function initDeleteTxn(btn) {
    const tr = btn.closest('tr');
    const actCell = btn.parentElement;
    // Replace buttons with inline confirm
    actCell.innerHTML = `
        <div class="del-confirm-wrap">
            <span style="font-size:11px;color:#dc2626;white-space:nowrap;">Delete?</span>
            <button class="del-yes" onclick="confirmDeleteTxn(this, ${tr.dataset.id})">Yes</button>
            <button class="del-no"  onclick="cancelDeleteTxn(this, ${tr.dataset.id})">No</button>
        </div>`;
}

function cancelDeleteTxn(btn, id) {
    const tr = btn.closest('tr');
    const actCell = btn.closest('td');
    actCell.innerHTML = `
        <button class="act-btn act-btn-edit" title="Edit"   onclick="openEditTxn(this)"><i class="fa-solid fa-pen"></i></button>
        <button class="act-btn act-btn-del"  title="Delete" onclick="initDeleteTxn(this)"><i class="fa-solid fa-trash"></i></button>`;
}

async function confirmDeleteTxn(btn, id) {
    btn.disabled = true; btn.textContent = '…';
    try {
        const res  = await fetch(URLROOT_JS + '/api.php?action=deleteTransaction', {
            method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({id})
        });
        const json = await res.json();
        if (json.success) {
            const tr = btn.closest('tr');
            tr.classList.add('row-removing');
            setTimeout(() => { tr.remove(); recountInv(); }, 420);
        }
    } catch(e) { alert('Delete failed.'); }
}

function recountInv() {
    const remaining = document.querySelectorAll('#inv-tbody tr[data-date]').length;
    document.getElementById('inv-count').textContent = '(' + remaining + ' records)';
}

function esc(s) {
    return String(s ?? '').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

// Modal helpers (may already be in layout)
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

// ── TRANSACTION ZIP IMPORT LOGIC ──────────────────────────────────
(function() {
    const dropZone = document.getElementById('txn-drop-zone');
    const fileInput = document.getElementById('txn-import-file');
    const stage1 = document.getElementById('txn-import-s1');
    const stage2 = document.getElementById('txn-import-s2');
    const footer1 = document.getElementById('txn-import-footer-s1');
    const footer2 = document.getElementById('txn-import-footer-s2');
    const errorEl = document.getElementById('txn-import-error');
    const loadingEl = document.getElementById('txn-import-loading');

    // Click to open file dialog
    dropZone.addEventListener('click', () => fileInput.click());

    // Drag & drop
    dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.style.borderColor = 'var(--accent)'; dropZone.style.background = '#dbeafe'; });
    dropZone.addEventListener('dragleave', () => { dropZone.style.borderColor = '#93c5fd'; dropZone.style.background = '#f0f4ff'; });
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.style.borderColor = '#93c5fd';
        dropZone.style.background = '#f0f4ff';
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            handleTxnZip(e.dataTransfer.files[0]);
        }
    });

    fileInput.addEventListener('change', () => {
        if (fileInput.files.length) handleTxnZip(fileInput.files[0]);
    });

    async function handleTxnZip(file) {
        if (!file.name.toLowerCase().endsWith('.zip')) {
            showTxnError('Please select a .zip file');
            return;
        }
        errorEl.style.display = 'none';
        loadingEl.style.display = 'block';
        dropZone.style.display = 'none';

        try {
            const JSZip = window.JSZip || await loadJSZipLib();
            const zip = await JSZip.loadAsync(file);

            let summaryCSV = null, detailsCSV = null;
            zip.forEach((path, entry) => {
                const name = path.split('/').pop().toLowerCase();
                if (name === 'transactions_summary.csv') summaryCSV = entry;
                if (name === 'transaction_details.csv') detailsCSV = entry;
            });

            if (!summaryCSV || !detailsCSV) {
                showTxnError('ZIP must contain transactions_summary.csv and transaction_details.csv');
                loadingEl.style.display = 'none';
                dropZone.style.display = 'block';
                return;
            }

            const summaryTxt = await summaryCSV.async('string');
            const detailsTxt = await detailsCSV.async('string');

            const summaryRows = parseTxnCSV(summaryTxt);
            const detailRows = parseTxnCSV(detailsTxt);

            if (!summaryRows.length) {
                showTxnError('transactions_summary.csv is empty or has no data rows');
                loadingEl.style.display = 'none';
                dropZone.style.display = 'block';
                return;
            }

            loadingEl.style.display = 'none';
            showTxnPreview(summaryRows, detailRows);

        } catch (err) {
            showTxnError('Error reading ZIP: ' + err.message);
            loadingEl.style.display = 'none';
            dropZone.style.display = 'block';
        }
    }

    function parseTxnCSV(text) {
        const lines = text.split('\n').map(l => l.trim()).filter(l => l);
        if (lines.length < 2) return [];
        const rows = [];
        for (let i = 1; i < lines.length; i++) {
            const row = parseTxnCSVLine(lines[i]);
            if (row.length > 1 && row[0].trim()) rows.push(row);
        }
        return rows;
    }

    function parseTxnCSVLine(line) {
        const result = [];
        let current = '';
        let inQuotes = false;
        for (let i = 0; i < line.length; i++) {
            const ch = line[i];
            if (inQuotes) {
                if (ch === '"') {
                    if (i + 1 < line.length && line[i + 1] === '"') { current += '"'; i++; }
                    else inQuotes = false;
                } else current += ch;
            } else {
                if (ch === '"') inQuotes = true;
                else if (ch === ',') { result.push(current); current = ''; }
                else current += ch;
            }
        }
        result.push(current);
        return result;
    }

    function showTxnPreview(summaryRows, detailRows) {
        stage1.style.display = 'none';
        stage2.style.display = 'block';
        footer1.style.display = 'none';
        footer2.style.display = 'flex';

        // Deduplicate summary rows
        const uniqueSummaryRows = [];
        const seenKeys = new Set();
        summaryRows.forEach(summary => {
            const key = summary[0] + '|' + summary[2] + '|' + summary[4] + '|' + summary[5];
            if (!seenKeys.has(key)) {
                seenKeys.add(key);
                uniqueSummaryRows.push(summary);
            }
        });

        document.getElementById('txnImportSummaryCount').textContent = uniqueSummaryRows.length;
        document.getElementById('txnImportSummaryItems').textContent = detailRows.length;

        // Calculate total out value
        let totalOut = 0;
        detailRows.forEach(r => totalOut += parseFloat(r[14] || 0)); // Index 14 is item_sell_value
        document.getElementById('txnImportSummaryOut').textContent = '৳' + totalOut.toLocaleString('en-BD', {minimumFractionDigits:2});

        const container = document.getElementById('txn-import-preview');
        let html = '';

        uniqueSummaryRows.forEach((summary, idx) => {
            const txnDate = summary[0];
            const companyName = summary[1];
            const companyId = summary[2];
            const dealerName = summary[3];
            const dealerId = summary[4];
            const warehouseId = summary[5];
            const warehouseName = summary[6];

            // Match details
            const matchKey = txnDate + '|' + companyId + '|' + dealerId + '|' + warehouseId;
            const txnDetails = detailRows.filter(d => {
                const dk = d[0] + '|' + d[2] + '|' + d[4] + '|' + d[5];
                return dk === matchKey;
            });

            let txnTotalOut = 0;
            let txnSaleValue = 0;
            txnDetails.forEach(d => {
                txnTotalOut += parseFloat(d[12] || 0);
                txnSaleValue += parseFloat(d[14] || 0);
            });

            html += `<div style="border:1px solid #d0d0c8; border-radius:6px; margin-bottom:12px; overflow:hidden; background:#fff;">`;

            // Transaction header
            html += `<div style="padding:10px 14px; background:#e8f0fe; border-bottom:1px solid #d0d0c8; display:flex; justify-content:space-between; align-items:center;">`;
            html += `<div>`;
            html += `<strong style="font-size:13px;">Transaction ${idx+1}</strong>`;
            html += ` <span style="font-size:11px; color:#666; margin-left:8px;">${companyName} | ${dealerName}</span>`;
            html += ` <span style="font-size:11px; color:#888; margin-left:8px;">Date: ${txnDate}</span>`;
            html += `</div>`;
            html += `<div style="font-size:13px; font-weight:bold; color:#b91c1c;">Out: ৳${txnTotalOut.toLocaleString('en-BD', {minimumFractionDigits:2})} | Sale: ৳${txnSaleValue.toLocaleString('en-BD', {minimumFractionDigits:2})}</div>`;
            html += `</div>`;

            // Items table
            if (txnDetails.length) {
                html += `<table class="csv-preview-tbl"><thead><tr><th>Item Name</th><th>Item ID</th><th>Out Qty</th><th>In Qty</th><th>Sell Qty</th><th style="text-align:right;">Out Value</th><th style="text-align:right;">In Value</th><th style="text-align:right;">Sell Value</th></tr></thead><tbody>`;
                txnDetails.forEach(d => {
                    html += `<tr>`;
                    html += `<td>${d[7]}</td>`;
                    html += `<td style="color:#888;">${d[8]}</td>`;
                    html += `<td style="text-align:center;">${d[9]}</td>`;
                    html += `<td style="text-align:center;">${d[10]}</td>`;
                    html += `<td style="text-align:center;">${d[11]}</td>`;
                    html += `<td style="text-align:right; color:#b91c1c; font-weight:600;">${parseFloat(d[12]).toLocaleString('en-BD',{minimumFractionDigits:2})}</td>`;
                    html += `<td style="text-align:right; color:#166534; font-weight:600;">${parseFloat(d[13] || 0) > 0 ? parseFloat(d[13]).toLocaleString('en-BD',{minimumFractionDigits:2}) : '—'}</td>`;
                    html += `<td style="text-align:right; font-weight:600;">${parseFloat(d[14]).toLocaleString('en-BD',{minimumFractionDigits:2})}</td>`;
                    html += `</tr>`;
                });
                html += `</tbody></table>`;
            } else {
                html += `<div style="padding:15px; text-align:center; color:#999; font-size:12px;">No matching detail items found</div>`;
            }
            html += `</div>`;
        });

        container.innerHTML = html;

        const confirmBtn = document.getElementById('txn-confirm-btn');
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = `<i class="fa-solid fa-upload"></i> Confirm Import (${summaryRows.length} Transactions)`;
    }

    function showTxnError(msg) {
        errorEl.textContent = msg;
        errorEl.style.display = 'block';
    }

    window.resetTxnImport = function() {
        stage1.style.display = 'block';
        stage2.style.display = 'none';
        footer1.style.display = 'flex';
        footer2.style.display = 'none';
        dropZone.style.display = 'block';
        loadingEl.style.display = 'none';
        errorEl.style.display = 'none';
        fileInput.value = '';
    };

    window.confirmTxnImport = async function() {
        const confirmBtn = document.getElementById('txn-confirm-btn');
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Importing...';

        try {
            const file = fileInput.files[0];
            if (!file) {
                showTxnError('No file selected. Please re-select the ZIP file.');
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="fa-solid fa-upload"></i> Confirm Import';
                return;
            }

            const formData = new FormData();
            formData.append('zipfile', file);

            const res = await fetch(URLROOT_JS + '/api.php?action=importTransactionsZip', {
                method: 'POST',
                body: formData
            });

            const json = await res.json();

            if (json.success) {
                const container = document.getElementById('txn-import-preview');
                container.innerHTML = `
                    <div style="text-align:center; padding:40px;">
                        <i class="fa-solid fa-circle-check" style="font-size:48px; color:#166534; margin-bottom:15px;"></i>
                        <div style="font-size:16px; font-weight:bold; color:#166534; margin-bottom:8px;">Import Successful!</div>
                        <div style="font-size:13px; color:#555;">${json.txns_inserted} transaction(s) and ${json.items_inserted} item(s) imported.</div>
                    </div>`;
                confirmBtn.style.display = 'none';
                setTimeout(() => location.reload(), 1500);
            } else {
                showTxnError(json.error || 'Import failed');
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="fa-solid fa-upload"></i> Confirm Import';
            }
        } catch (err) {
            showTxnError('Network error: ' + err.message);
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="fa-solid fa-upload"></i> Confirm Import';
        }
    };

    function loadJSZipLib() {
        return new Promise((resolve, reject) => {
            if (window.JSZip) return resolve(window.JSZip);
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js';
            script.onload = () => resolve(window.JSZip);
            script.onerror = () => reject(new Error('Failed to load JSZip'));
            document.head.appendChild(script);
        });
    }
})();

window.toggleDetails = function(btn, id) {
    const row = document.getElementById('details-row-' + id);
    const icon = btn.querySelector('i');
    if (row.style.display === 'none') {
        row.style.display = 'table-row';
        icon.className = 'fa-solid fa-chevron-down';
        btn.style.transform = 'rotate(90deg)';
    } else {
        row.style.display = 'none';
        icon.className = 'fa-solid fa-chevron-right';
        btn.style.transform = 'none';
    }
};
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
