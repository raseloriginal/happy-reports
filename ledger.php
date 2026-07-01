<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = 'Company Ledger';

$lotModel = new Lot();
$depositModel = new Deposit();

$lots = $lotModel->getLots();
$deposits = $depositModel->getDeposits();

$data = [
    'lots' => $lots,
    'deposits' => $deposits
];

require_once __DIR__ . '/includes/header.php';
?>
<style>
/* ── LEDGER-SPECIFIC LIGHT STYLES ──────────────────────── */
#ledger-wrap { display:flex; flex-direction:column; height:100%; }

/* Company list bar */
#co-list-bar {
    background: #fff;
    border-bottom: 1px solid #d0d0c8;
    padding: 8px 16px;
    display: flex;
    align-items: center;
    gap: 8px;
    overflow-x: auto;
    flex-shrink: 0;
}
#co-list-bar::-webkit-scrollbar { height: 4px; }
.co-item {
    padding: 10px 20px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    color: #666;
    border-bottom: 2px solid transparent;
    margin-bottom: -1px;
    white-space: nowrap;
    transition: all 0.15s;
    flex-shrink: 0;
}
.co-item:hover { color: #1a73e8; background: #f0f4ff; }
.co-item.active { color: #1a3a6e; border-bottom-color: #1a73e8; font-weight: 600; background: #fff; }

/* Action bar */
#ledger-action-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    background: #f7f7f3;
    border-bottom: 1px solid #d0d0c8;
    flex-shrink: 0;
}
#ledger-action-bar .co-title { font-size: 15px; font-weight: 700; color: #1a1a1a; }
#ledger-action-bar .action-btns { display:flex; gap:10px; align-items:center; }

/* Summary strip */
#ledger-summary {
    display: flex;
    background: #fff;
    border-bottom: 1px solid #d0d0c8;
    flex-shrink: 0;
}
.ledger-sum-cell {
    flex: 1;
    padding: 16px 24px;
    border-right: 1px solid #d0d0c8;
    font-size: 13px;
}
.ledger-sum-cell:last-child { border-right: none; }
.ledger-sum-cell .ls-label { font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 4px; }
.ledger-sum-cell .ls-val { font-size: 16px; font-weight: 700; font-variant-numeric: tabular-nums; }

/* Ledger table */
#ledger-table-wrap { flex: 1; overflow-y: auto; }

.ledger-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.ledger-table thead tr {
    background: #eaeae4;
    position: sticky;
    top: 0;
    z-index: 5;
}
.ledger-table thead th {
    padding: 12px 16px;
    text-align: left;
    font-size: 11.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #555;
    border-bottom: 2px solid #b8b8b0;
    border-right: 1px solid #d0d0c8;
    white-space: nowrap;
}
.ledger-table thead th:last-child { border-right: none; }
.ledger-table thead th.text-right { text-align: right; }

.ledger-table tbody tr {
    border-bottom: 1px solid #e4e4dc;
    transition: background 0.1s;
}
.ledger-table tbody tr:nth-child(even) { background: #fafaf7; }
.ledger-table tbody tr:hover { background: #eef3ff; }

.ledger-table tbody td {
    padding: 12px 16px;
    border-right: 1px solid #e4e4dc;
    vertical-align: middle;
}
.ledger-table tbody td:last-child { border-right: none; }

/* Running total row */
.ledger-total-row {
    background: #eaeae4 !important;
    border-top: 2px solid #a0a098 !important;
}
.ledger-total-row td {
    font-weight: 700;
    font-size: 13px;
    padding: 14px 16px;
    border-right: 1px solid #c8c8c0 !important;
}
.ledger-total-row td:last-child { border-right: none !important; }

/* Type badges */
.type-badge {
    display: inline-block;
    padding: 2px 9px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}
.type-deposit  { background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }
.type-lot      { background: #ede9fe; color: #5b21b6; border: 1px solid #c4b5fd; }
.type-credit   { background: #fef9c3; color: #854d0e; border: 1px solid #fde68a; }
.type-txn      { background: #dcfce7; color: #166534; border: 1px solid #86efac; }

/* Status badges */
.status-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 3px;
    font-size: 10.5px;
    font-weight: 600;
    border: 1px solid;
}
.status-settled   { color: #166534; border-color: #86efac; background: #dcfce7; }
.status-delivered { color: #166534; border-color: #86efac; background: #dcfce7; }
.status-approved  { color: #166534; border-color: #86efac; background: #dcfce7; }
.status-pending   { color: #92400e; border-color: #fcd34d; background: #fef9c3; }

/* Numeric cols */
.ledger-table .col-debit  { text-align: right; color: #1e40af; font-variant-numeric: tabular-nums; font-weight: 600; }
.ledger-table .col-credit { text-align: right; color: #b91c1c; font-variant-numeric: tabular-nums; font-weight: 600; }
.ledger-table .col-bal-pos { text-align: right; color: #166534; font-variant-numeric: tabular-nums; font-weight: 700; }
.ledger-table .col-bal-neg { text-align: right; color: #b91c1c; font-variant-numeric: tabular-nums; font-weight: 700; }
.dash-cell { color: #aaa; text-align: right; }

/* ── ACTION BUTTONS ── */
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
.act-btn-edit:hover  { background: #dbeafe; border-color: #93c5fd; color: #1e40af; }
.act-btn-del { color: #dc2626; }
.act-btn-del:hover   { background: #fee2e2; border-color: #fca5a5; }

/* Delete inline confirm */
.del-confirm-wrap { display:flex; align-items:center; gap:5px; }
.del-yes { background:#dc2626;color:#fff;border:none;border-radius:3px;padding:2px 7px;cursor:pointer;font-size:11px; }
.del-no  { background:#e5e7eb;color:#374151;border:none;border-radius:3px;padding:2px 7px;cursor:pointer;font-size:11px; }

/* Row fade out */
@keyframes rowFadeOut { from{opacity:1;background:#fee2e2;} to{opacity:0;} }
.row-removing { animation: rowFadeOut 0.4s ease forwards; pointer-events:none; }

/* ── CSV PREVIEW STYLES ── */
#lot-csv-preview-wrap { display:none; flex-direction:column; max-height:380px; }
#lot-csv-preview-wrap.active { display:flex; }
.csv-preview-table-outer {
    overflow: auto;
    border: 1px solid #d0d0c8;
    border-radius: 3px;
    flex: 1;
}
.csv-prev-tbl {
    width:100%;
    border-collapse:collapse;
    font-size:12px;
}
.csv-prev-tbl thead th {
    background:#eaeae4;
    padding:7px 9px;
    text-align:left;
    font-size:10.5px;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:0.4px;
    color:#555;
    border-bottom:1px solid #d0d0c8;
    white-space:nowrap;
    position:sticky;
    top:0;
}
.csv-prev-tbl tbody td {
    padding:5px 7px;
    border-bottom:1px solid #eee;
}
.csv-prev-tbl tbody tr:hover { background:#f7f7ff; }
.csv-cell-edit {
    width:100%;
    border:1px solid transparent;
    background:transparent;
    padding:3px 5px;
    font-size:12px;
    font-family:inherit;
    border-radius:3px;
    min-width:55px;
}
.csv-cell-edit:focus { outline:none; border-color:#5b21b6; background:#fff; }
.csv-row-remove {
    background:none;
    border:none;
    color:#ccc;
    cursor:pointer;
    font-size:13px;
    padding:2px 5px;
    border-radius:3px;
}
.csv-row-remove:hover { color:#ef4444; background:#fee2e2; }
</style>

<!-- ── IMPORT LOT MODAL (ZIP) ──────────────────── -->
<div class="modal-overlay" id="lot-modal">
    <div class="modal-box" style="max-width:960px;width:96vw;" id="lot-modal-box">
        <div class="modal-header">
            <span><i class="fa-solid fa-file-archive" style="color:#5b21b6;margin-right:7px;"></i> Import Lots & Items (ZIP)</span>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <!-- Stage 1: file picker -->
            <div id="lot-csv-s1">
                <div style="background:#f7f4ff;border:1px solid #c4b5fd;padding:8px 10px;margin-bottom:12px;font-size:11.5px;color:#5b21b6;border-radius:2px;">
                    <strong>Required ZIP contents:</strong><br>
                    <span class="mono" style="font-size:11px;color:#1a1a1a;">lots.csv and lot_items.csv</span>
                </div>
                <div class="form-group" id="zip-drop-zone" style="border: 2px dashed #c4b5fd; border-radius: 6px; padding: 30px; text-align: center; cursor: pointer; background: #faf5ff;">
                    <i class="fa-solid fa-cloud-arrow-up" style="font-size:32px; color:#5b21b6; margin-bottom:10px;"></i>
                    <div style="font-size: 14px; color: #444; font-weight: 500;">Click or drag a .zip file here</div>
                    <input type="file" id="lot-csv-file" accept=".zip" style="display:none;">
                </div>
                
                <div id="zip-loading" style="display:none; text-align:center; padding:20px;">
                    <i class="fa-solid fa-spinner fa-spin" style="font-size:24px; color:#5b21b6;"></i>
                    <p style="margin-top:10px; font-size:13px; color:#555;">Processing ZIP file...</p>
                </div>
                
                <p id="lot-csv-error" style="color:#dc2626;font-size:12px;margin-top:6px;display:none;"></p>
            </div>
            <!-- Stage 2: preview table -->
            <div id="lot-csv-s2" style="display:none;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                    <span style="font-size:13px;font-weight:600;color:#1a1a1a;">
                        <i class="fa-solid fa-table" style="color:#5b21b6;margin-right:6px;"></i>
                        Preview — review data before importing
                    </span>
                    <button onclick="resetLotCsv()" class="btn btn-sm" style="font-size:11px;">
                        <i class="fa-solid fa-arrow-left"></i> Back to File
                    </button>
                </div>
                
                <div style="display:flex; gap: 15px; margin-bottom: 15px;">
                    <div style="background:#f0f4ff; border:1px solid #dbeafe; padding:10px 15px; border-radius:4px; flex:1;">
                        <div style="font-size:11px; color:#1e40af; font-weight:bold; text-transform:uppercase;">Total Lots</div>
                        <div id="importSummaryLots" style="font-size:20px; font-weight:bold; color:#1e3a8a;">0</div>
                    </div>
                    <div style="background:#f0fdf4; border:1px solid #dcfce7; padding:10px 15px; border-radius:4px; flex:1;">
                        <div style="font-size:11px; color:#166534; font-weight:bold; text-transform:uppercase;">Total Items</div>
                        <div id="importSummaryItems" style="font-size:20px; font-weight:bold; color:#14532d;">0</div>
                    </div>
                </div>

                <div id="lot-csv-preview-wrap" class="active" style="display:flex; flex-direction:column; gap:10px; overflow-y:auto; max-height:400px; padding-right:5px;">
                    <div id="importLotsContainer"></div>
                </div>
            </div>
        </div>
        <div class="modal-footer" id="lot-modal-footer-s1">
            <button type="button" class="btn modal-close">Cancel</button>
        </div>
        <div class="modal-footer" id="lot-modal-footer-s2" style="display:none;">
            <button type="button" class="btn modal-close">Cancel</button>
            <button type="button" id="lot-confirm-btn"
                class="btn" style="background:#5b21b6;color:#fff;border-color:#4c1d95;"
                onclick="confirmLotImport()">
                <i class="fa-solid fa-upload"></i> Confirm Import
            </button>
        </div>
    </div>
</div>

<!-- ── EDIT LOT MODAL ────────────────────────────── -->
<div class="modal-overlay" id="edit-lot-modal">
    <div class="modal-box" style="max-width:540px;">
        <div class="modal-header">
            <span><i class="fa-solid fa-pen-to-square" style="color:#5b21b6;margin-right:6px;"></i> Edit Lot</span>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="el-id">
            <div class="form-row" style="margin-bottom:10px;">
                <div class="form-group">
                    <label class="form-label">CRM ID</label>
                    <input type="text" id="el-crm-id" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Warehouse CRM ID</label>
                    <input type="text" id="el-wh-id" class="form-control">
                </div>
            </div>
            <div class="form-row" style="margin-bottom:10px;">
                <div class="form-group">
                    <label class="form-label">Warehouse Name</label>
                    <input type="text" id="el-wh-name" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Company CRM ID</label>
                    <input type="text" id="el-co-id" class="form-control">
                </div>
            </div>
            <div class="form-row" style="margin-bottom:10px;">
                <div class="form-group">
                    <label class="form-label">Company Name</label>
                    <input type="text" id="el-co-name" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Lot Value (৳)</label>
                    <input type="number" step="0.01" id="el-value" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Lot Date</label>
                    <input type="date" id="el-date" class="form-control">
                </div>
            </div>
            <p id="el-error" style="color:#dc2626;font-size:12px;display:none;"></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn modal-close">Cancel</button>
            <button type="button" class="btn" style="background:#5b21b6;color:#fff;" onclick="saveLotEdit()">
                <i class="fa-solid fa-floppy-disk"></i> Save Changes
            </button>
        </div>
    </div>
</div>

<!-- ── EDIT DEPOSIT MODAL ─────────────────────────── -->
<div class="modal-overlay" id="edit-dep-modal">
    <div class="modal-box" style="max-width:460px;">
        <div class="modal-header">
            <span><i class="fa-solid fa-pen-to-square" style="color:#1a73e8;margin-right:6px;"></i> Edit Deposit</span>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="ed-id">
            <div class="form-row" style="margin-bottom:10px;">
                <div class="form-group">
                    <label class="form-label">Company CRM ID</label>
                    <input type="text" id="ed-co-id" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Company Name</label>
                    <input type="text" id="ed-co-name" class="form-control">
                </div>
            </div>
            <div class="form-row" style="margin-bottom:10px;">
                <div class="form-group">
                    <label class="form-label">Amount (৳)</label>
                    <input type="number" step="0.01" id="ed-amount" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Date</label>
                    <input type="date" id="ed-date" class="form-control">
                </div>
            </div>
            <p id="ed-error" style="color:#dc2626;font-size:12px;display:none;"></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn modal-close">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="saveDepEdit()">
                <i class="fa-solid fa-floppy-disk"></i> Save Changes
            </button>
        </div>
    </div>
</div>

<!-- ── ADD DEPOSIT MODAL ──────────────────────────── -->
<div class="modal-overlay" id="dep-modal">
    <div class="modal-box" style="max-width:580px;">
        <div class="modal-header">
            <span><i class="fa-solid fa-building-columns" style="color:#1a73e8;margin-right:7px;"></i> Add Deposit(s)</span>
            <button class="modal-close">&times;</button>
        </div>
        <form action="<?php echo URLROOT; ?>/api.php?action=addDeposit" method="POST">
        <div class="modal-body">
            <div id="dep-rows">
                <div class="deposit-row-item">
                    <button type="button" class="deposit-row-remove dep-remove" title="Remove" style="display:none;">&times;</button>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Company</label>
                            <select class="form-control company-select" name="company_select" required>
                                <option value="">Select Company…</option>
                            </select>
                            <input type="hidden" name="company_name[]" class="company-name-input">
                        </div>
                        <div class="form-group" style="max-width:110px;">
                            <label class="form-label">CRM ID</label>
                            <input type="text" class="form-control company-crm-id-input" name="company_crm_id[]" readonly required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Amount (৳)</label>
                            <input type="number" step="0.01" name="amount[]" class="form-control" required placeholder="0.00">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Date</label>
                            <input type="date" name="deposit_date[]" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Note (optional)</label>
                            <input type="text" name="note[]" class="form-control" placeholder="e.g. Bank Transfer">
                        </div>
                    </div>
                </div>
            </div>
            <button type="button" id="add-dep-row" class="btn" style="width:100%;margin-top:6px;justify-content:center;">
                <i class="fa-solid fa-plus"></i> Add Another Deposit
            </button>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn modal-close">Cancel</button>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Deposit(s)</button>
        </div>
        </form>
    </div>
</div>

<!-- ── LEDGER MAIN LAYOUT ─────────────────────────── -->
<div id="ledger-wrap">
    <div id="co-list-bar">
        <div class="co-item active" data-co-id="all" data-co-name="All Companies">All Companies</div>
    </div>

    <div id="ledger-action-bar">
        <div class="co-title" id="ledger-co-title">All Companies — Transaction Ledger</div>
        <div class="action-btns">
            <button class="btn" style="background:#5b21b6;color:#fff;border-color:#4c1d95;" onclick="openModal('lot-modal')">
                <i class="fa-solid fa-file-archive"></i> Import Lots
            </button>
            <button class="btn btn-primary" onclick="openModal('dep-modal')">
                <i class="fa-solid fa-building-columns"></i> Add Deposit
            </button>
        </div>
    </div>

    <div id="ledger-summary">
        <div class="ledger-sum-cell">
            <div class="ls-label">Total Deposits (Credit)</div>
            <div class="ls-val" id="sum-deposit" style="color:#1e40af;">৳0.00</div>
        </div>
        <div class="ledger-sum-cell">
            <div class="ls-label">Total Lots (Debit)</div>
            <div class="ls-val" id="sum-lot" style="color:#b91c1c;">৳0.00</div>
        </div>
        <div class="ledger-sum-cell">
            <div class="ls-label">Net Balance</div>
            <div class="ls-val" id="sum-balance">৳0.00</div>
        </div>
        <div class="ledger-sum-cell">
            <div class="ls-label">Total Entries</div>
            <div class="ls-val" id="sum-entries" style="color:#555;">0</div>
        </div>
    </div>

    <div id="ledger-table-wrap">
        <table class="ledger-table" id="ledger-tbl">
            <thead>
                <tr>
                    <th style="width:110px;">Date</th>
                    <th style="width:100px;">TXN ID</th>
                    <th style="width:120px;">Type</th>
                    <th>Company</th>
                    <th>Description</th>
                    <th class="text-right" style="width:130px;">Debit (৳)</th>
                    <th class="text-right" style="width:130px;">Credit (৳)</th>
                    <th class="text-right" style="width:140px;">Balance (৳)</th>
                    <th style="width:100px;text-align:center;">Status</th>
                    <th style="width:80px;text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody id="ledger-tbody">
                <tr class="loading-row"><td colspan="10" style="text-align:center;padding:30px;color:#888;"><i class="fa-solid fa-spinner fa-spin"></i> Loading ledger data…</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- PHP data injected for JS -->
<script>
const URLROOT_JS = '<?php echo URLROOT; ?>';

window.DB_LOTS = <?php echo json_encode(array_map(function($l) {
    return [
        'id'               => $l->id,
        'company_id'       => $l->company_crm_id,
        'company_name'     => $l->company_name ?? '',
        'lot_date'         => $l->lot_date,
        'total_amount'     => $l->lot_value,
        'crm_id'           => $l->crm_id ?? '',
        'warehouse_crm_id' => $l->warehouse_crm_id ?? '',
        'warehouse_name'   => $l->warehouse_name ?? ''
    ];
}, $data['lots'] ?? [])); ?>;

window.DB_DEPOSITS = <?php echo json_encode(array_map(function($d) {
    return [
        'id'             => $d->id,
        'company_id'     => $d->company_crm_id,
        'company_name'   => $d->company_name ?? '',
        'operation_date' => $d->deposit_date,
        'amount'         => $d->amount,
        'note'           => $d->note ?? ''
    ];
}, $data['deposits'] ?? [])); ?>;

// ── MAIN LEDGER LOGIC ─────────────────────────────────────────────
(async function() {
    const tbody = document.getElementById('ledger-tbody');
    let allRows   = [];
    let companies = [];
    let activeCoId = 'all';

    // 1) Fetch companies from CRM API
    try {
        const res = await fetch(window.CRM_API + '?table=companies&limit=1000');
        const json = await res.json();
        if (json.status === 'success') {
            companies = json.data.map(c => ({id: String(c.id), name: c.company_name}));
            populateCompanyTabs(companies);
            populateDepositDropdowns(companies);
        }
    } catch(e) {
        console.error('CRM fetch failed:', e);
        tbody.innerHTML = `<tr><td colspan="10" class="text-center" style="color:var(--red);padding:20px">Failed to load CRM companies: ${e.message}</td></tr>`;
    }

    // 2) Build ledger
    buildLedger();

    function buildLedger() {
        allRows = [];

        (window.DB_LOTS || []).forEach(l => {
            let parsedCoId = String(l.company_id);
            if (parsedCoId.startsWith('C-')) parsedCoId = parsedCoId.substring(2);
            allRows.push({
                date:        l.lot_date,
                txnId:       'LOT-' + String(l.id).padStart(3, '0'),
                type:        'lot',
                typeLabel:   'Lot Received',
                coName:      l.company_name || resolveCompany(parsedCoId),
                desc:        'Imported Lot',
                debit:       0,
                credit:      parseFloat(l.total_amount) || 0,
                status:      'Delivered',
                coId:        parsedCoId,
                recordId:    l.id,
                recordType:  'lot',
                rawData:     l
            });
        });

        (window.DB_DEPOSITS || []).forEach(d => {
            const note = d.note || '—';
            allRows.push({
                date:        d.operation_date,
                txnId:       'DEP-' + String(d.id).padStart(3, '0'),
                type:        'deposit',
                typeLabel:   'Deposit',
                coName:      d.company_name || resolveCompany(d.company_id),
                desc:        note,
                debit:       parseFloat(d.amount) || 0,
                credit:      0,
                status:      'Settled',
                coId:        String(d.company_id),
                recordId:    d.id,
                recordType:  'deposit',
                rawData:     d
            });
        });

        allRows.sort((a, b) => a.date.localeCompare(b.date));
        renderLedger(activeCoId);
    }

    function resolveCompany(id) {
        const c = companies.find(c => c.id === String(id));
        return c ? c.name : `Company #${id}`;
    }

    function renderLedger(coId) {
        activeCoId = coId;
        const filtered = coId === 'all' ? allRows : allRows.filter(r => r.coId === coId);

        if (!filtered.length) {
            tbody.innerHTML = `<tr><td colspan="10">
                <div class="empty-state" style="padding:40px;text-align:center;color:#999;">
                    <i class="fa-solid fa-book" style="font-size:32px;opacity:0.2;display:block;margin-bottom:10px;"></i>
                    No ledger entries for this company yet.
                </div>
            </td></tr>`;
            updateSummary([], 0, 0);
            return;
        }

        let balance = 0;
        let totalDebit = 0, totalCredit = 0;
        const rowsHtml = filtered.map(row => {
            balance += row.debit;
            balance -= row.credit;
            totalDebit  += row.debit;
            totalCredit += row.credit;

            const balClass  = balance >= 0 ? 'col-bal-pos' : 'col-bal-neg';
            const balPrefix = balance >= 0 ? '+' : '';
            const balStr    = balPrefix + fmt2(balance);
            const typeHtml  = getTypeBadge(row.type, row.typeLabel);
            const statusHtml = getStatusBadge(row.status);

            const debitCell  = row.debit  > 0 ? `<td class="col-debit">${fmt2(row.debit)}</td>`  : `<td class="dash-cell">—</td>`;
            const creditCell = row.credit > 0 ? `<td class="col-credit">${fmt2(row.credit)}</td>` : `<td class="dash-cell">—</td>`;

            // Actions — encode raw data as JSON in data attrs
            const raw = JSON.stringify(row.rawData).replace(/"/g, '&quot;');
            const actionsHtml = `
                <td style="text-align:center;white-space:nowrap;" class="ledger-act-cell"
                    data-record-id="${row.recordId}"
                    data-record-type="${row.recordType}"
                    data-raw="${raw}">
                    <button class="act-btn act-btn-edit" title="Edit" style="color:#5b21b6;" onclick="openLedgerEdit(this)">
                        <i class="fa-solid fa-pen"></i>
                    </button>
                    <button class="act-btn act-btn-del" title="Delete" onclick="initLedgerDelete(this)">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </td>`;

            return `<tr>
                <td>${fmtDate(row.date)}</td>
                <td class="mono" style="color:#555;font-size:11.5px;">${row.txnId}</td>
                <td>${typeHtml}</td>
                <td class="fw600">${row.coName}</td>
                <td>${row.desc}</td>
                ${debitCell}
                ${creditCell}
                <td class="${balClass}">${balStr}</td>
                <td style="text-align:center;">${statusHtml}</td>
                ${actionsHtml}
            </tr>`;
        });

        rowsHtml.push(`<tr class="ledger-total-row">
            <td colspan="5">Running Total</td>
            <td class="col-debit">${fmt2(totalDebit)}</td>
            <td class="col-credit">${fmt2(totalCredit)}</td>
            <td class="${balance >= 0 ? 'col-bal-pos' : 'col-bal-neg'}">${(balance >= 0 ? 'Recv: +' : 'Owed: ') + fmt2(Math.abs(balance))}</td>
            <td></td>
            <td></td>
        </tr>`);

        tbody.innerHTML = rowsHtml.join('');
        updateSummary(filtered, totalDebit, totalCredit);
    }

    function updateSummary(rows, totalDep, totalLot) {
        const net = totalDep - totalLot;
        document.getElementById('sum-deposit').textContent = '৳' + fmt2(totalDep);
        document.getElementById('sum-lot').textContent     = '৳' + fmt2(totalLot);
        const balEl = document.getElementById('sum-balance');
        balEl.textContent = (net >= 0 ? '+' : '') + '৳' + fmt2(net);
        balEl.style.color = net >= 0 ? '#166534' : '#b91c1c';
        document.getElementById('sum-entries').textContent = rows.filter(r => r.txnId !== 'total').length;
    }

    function fmt2(n) {
        return parseFloat(n||0).toLocaleString('en-BD', {minimumFractionDigits:2, maximumFractionDigits:2});
    }
    function getTypeBadge(type, label) {
        const cls = {deposit:'type-deposit',lot:'type-lot',credit:'type-credit',txn:'type-txn'}[type] || 'type-txn';
        return `<span class="type-badge ${cls}">${label}</span>`;
    }
    function getStatusBadge(status) {
        const s = (status||'').toLowerCase();
        const cls = {settled:'status-settled',delivered:'status-delivered',approved:'status-approved',pending:'status-pending'}[s] || 'status-pending';
        return `<span class="status-badge ${cls}">${status}</span>`;
    }

    function populateCompanyTabs(cos) {
        const bar = document.getElementById('co-list-bar');
        cos.forEach(c => {
            const el = document.createElement('div');
            el.className   = 'co-item';
            el.dataset.coId   = c.id;
            el.dataset.coName = c.name;
            el.textContent = c.name;
            bar.appendChild(el);
        });
        bar.addEventListener('click', function(e) {
            const item = e.target.closest('.co-item');
            if (!item) return;
            bar.querySelectorAll('.co-item').forEach(i => i.classList.remove('active'));
            item.classList.add('active');
            document.getElementById('ledger-co-title').textContent = item.dataset.coName + ' — Transaction Ledger';
            renderLedger(item.dataset.coId);
        });
    }

    function populateDepositDropdowns(cos) {
        document.querySelectorAll('.company-select').forEach(sel => {
            // Clear existing options (keep placeholder)
            while (sel.options.length > 1) sel.remove(1);
            cos.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = c.name;
                sel.appendChild(opt);
            });
        });
        // Attach change handlers
        attachDepositSelectHandlers();
    }

    function attachDepositSelectHandlers() {
        document.querySelectorAll('.company-select').forEach(sel => {
            // Remove old handler to prevent duplicates
            sel.onchange = function() {
                const row = this.closest('.deposit-row-item');
                const selectedOpt = this.options[this.selectedIndex];
                const crmIdInput = row.querySelector('.company-crm-id-input');
                const nameInput = row.querySelector('.company-name-input');
                if (this.value) {
                    crmIdInput.value = this.value;
                    nameInput.value = selectedOpt.textContent;
                } else {
                    crmIdInput.value = '';
                    nameInput.value = '';
                }
            };
        });
    }

    // ── ADD ANOTHER DEPOSIT ROW ──
    window._depositCompanies = companies;

    document.getElementById('add-dep-row')?.addEventListener('click', function() {
        const container = document.getElementById('dep-rows');
        const firstRow = container.querySelector('.deposit-row-item');
        const clone = firstRow.cloneNode(true);

        // Clear values
        clone.querySelector('.company-select').value = '';
        clone.querySelector('.company-crm-id-input').value = '';
        clone.querySelector('.company-name-input').value = '';
        clone.querySelector('input[name="amount[]"]').value = '';
        clone.querySelector('input[name="deposit_date[]"]').value = new Date().toISOString().split('T')[0];
        const noteInput = clone.querySelector('input[name="note[]"]');
        if (noteInput) noteInput.value = '';

        // Show remove button
        const removeBtn = clone.querySelector('.dep-remove');
        if (removeBtn) removeBtn.style.display = 'block';

        container.appendChild(clone);
        attachDepositSelectHandlers();
    });

    // ── REMOVE DEPOSIT ROW ──
    document.getElementById('dep-rows')?.addEventListener('click', function(e) {
        if (e.target.closest('.dep-remove')) {
            const row = e.target.closest('.deposit-row-item');
            const container = document.getElementById('dep-rows');
            if (container.querySelectorAll('.deposit-row-item').length > 1) {
                row.remove();
            }
        }
    });

    window.printLedger  = () => window.print();
    window.exportCSV    = function() {
        const filtered = activeCoId === 'all' ? allRows : allRows.filter(r => r.coId === activeCoId);
        if (!filtered.length) return;
        let balance = 0;
        let csv = 'Date,TXN ID,Type,Company,Description,Debit,Credit,Balance,Status\n';
        filtered.forEach(row => {
            balance += row.debit - row.credit;
            csv += [row.date, row.txnId, row.typeLabel, '"'+row.coName.replace(/"/g,'""')+'"',
                    '"'+row.desc.replace(/"/g,'""')+'"', row.debit||'', row.credit||'',
                    balance.toFixed(2), row.status].join(',') + '\n';
        });
        const a = document.createElement('a');
        a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
        a.download = 'ledger_export.csv';
        a.click();
    };

    function fmtDate(d) {
        const dt = new Date(d);
        if (isNaN(dt)) return d;
        return dt.toLocaleDateString('en-GB', {day:'numeric', month:'short', year:'numeric'});
    }

})();

// ── LEDGER ROW: EDIT ─────────────────────────────────────────────
function openLedgerEdit(btn) {
    const cell   = btn.closest('.ledger-act-cell');
    const type   = cell.dataset.recordType;
    const raw    = JSON.parse(cell.dataset.raw.replace(/&quot;/g, '"'));
    const id     = cell.dataset.recordId;

    if (type === 'lot') {
        document.getElementById('el-id').value       = id;
        document.getElementById('el-crm-id').value   = raw.crm_id || '';
        document.getElementById('el-wh-id').value    = raw.warehouse_crm_id || '';
        document.getElementById('el-wh-name').value  = raw.warehouse_name || '';
        document.getElementById('el-co-id').value    = raw.company_id || '';
        document.getElementById('el-co-name').value  = raw.company_name || '';
        document.getElementById('el-value').value    = raw.total_amount || 0;
        document.getElementById('el-date').value     = raw.lot_date || '';
        document.getElementById('el-error').style.display = 'none';
        openModal('edit-lot-modal');
    } else {
        document.getElementById('ed-id').value       = id;
        document.getElementById('ed-co-id').value    = raw.company_id || '';
        document.getElementById('ed-co-name').value  = raw.company_name || '';
        document.getElementById('ed-amount').value   = raw.amount || 0;
        document.getElementById('ed-date').value     = raw.operation_date || '';
        document.getElementById('ed-error').style.display = 'none';
        openModal('edit-dep-modal');
    }
}

async function saveLotEdit() {
    const payload = {
        id:               parseInt(document.getElementById('el-id').value),
        crm_id:           document.getElementById('el-crm-id').value,
        warehouse_crm_id: document.getElementById('el-wh-id').value,
        warehouse_name:   document.getElementById('el-wh-name').value,
        company_crm_id:   document.getElementById('el-co-id').value,
        company_name:     document.getElementById('el-co-name').value,
        lot_value:        parseFloat(document.getElementById('el-value').value) || 0,
        lot_date:         document.getElementById('el-date').value
    };
    const btn = document.querySelector('#edit-lot-modal .btn');
    btn.disabled = true; btn.textContent = 'Saving…';
    try {
        const res = await fetch(URLROOT_JS + '/api.php?action=updateLot', {
            method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload)
        });
        const json = await res.json();
        if (json.success) location.reload();
        else { document.getElementById('el-error').textContent = 'Error: ' + json.error; document.getElementById('el-error').style.display='block'; }
    } catch(e) { document.getElementById('el-error').textContent = 'Network error.'; document.getElementById('el-error').style.display='block'; }
    finally { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Save Changes'; }
}

async function saveDepEdit() {
    const payload = {
        id:             parseInt(document.getElementById('ed-id').value),
        company_crm_id: document.getElementById('ed-co-id').value,
        company_name:   document.getElementById('ed-co-name').value,
        amount:         parseFloat(document.getElementById('ed-amount').value) || 0,
        deposit_date:   document.getElementById('ed-date').value
    };
    const btn = document.querySelector('#edit-dep-modal .btn-primary');
    btn.disabled = true; btn.textContent = 'Saving…';
    try {
        const res = await fetch(URLROOT_JS + '/api.php?action=updateDeposit', {
            method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload)
        });
        const json = await res.json();
        if (json.success) location.reload();
        else { document.getElementById('ed-error').textContent = 'Error: ' + json.error; document.getElementById('ed-error').style.display='block'; }
    } catch(e) { document.getElementById('ed-error').textContent = 'Network error.'; document.getElementById('ed-error').style.display='block'; }
    finally { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Save Changes'; }
}

// ── LEDGER ROW: DELETE ───────────────────────────────────────────
function initLedgerDelete(btn) {
    const cell = btn.closest('td');
    cell.innerHTML = `
        <div class="del-confirm-wrap">
            <span style="font-size:11px;color:#dc2626;">Delete?</span>
            <button class="del-yes" onclick="confirmLedgerDelete(this)">Yes</button>
            <button class="del-no"  onclick="cancelLedgerDelete(this)">No</button>
        </div>`;
}

function cancelLedgerDelete(btn) {
    const cell = btn.closest('td');
    cell.innerHTML = `
        <button class="act-btn act-btn-edit" title="Edit" style="color:#5b21b6;" onclick="openLedgerEdit(this)"><i class="fa-solid fa-pen"></i></button>
        <button class="act-btn act-btn-del" title="Delete" onclick="initLedgerDelete(this)"><i class="fa-solid fa-trash"></i></button>`;
}

async function confirmLedgerDelete(btn) {
    const cell = btn.closest('td');
    const type = cell.dataset.recordType;
    const id   = parseInt(cell.dataset.recordId);
    
    btn.disabled = true; btn.textContent = '…';
    const endpoint = type === 'lot' ? '/api.php?action=deleteLot' : '/api.php?action=deleteDeposit';
    
    try {
        const res = await fetch(URLROOT_JS + endpoint, {
            method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({id})
        });
        const json = await res.json();
        if (json.success) {
            const tr = cell.closest('tr');
            tr.classList.add('row-removing');
            setTimeout(() => location.reload(), 400); // reload to recalc balances
        } else alert('Delete failed: ' + json.error);
    } catch(e) { alert('Network error'); }
}

// Modal helpers
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

// ── ZIP IMPORT LOGIC ──────────────────────────────────────────────
(function() {
    const dropZone = document.getElementById('zip-drop-zone');
    const fileInput = document.getElementById('lot-csv-file');
    const stage1 = document.getElementById('lot-csv-s1');
    const stage2 = document.getElementById('lot-csv-s2');
    const footer1 = document.getElementById('lot-modal-footer-s1');
    const footer2 = document.getElementById('lot-modal-footer-s2');
    const errorEl = document.getElementById('lot-csv-error');
    const loadingEl = document.getElementById('zip-loading');

    let parsedLots = [];
    let parsedItems = [];

    // Click to open file dialog
    dropZone.addEventListener('click', () => fileInput.click());

    // Drag & drop
    dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.style.borderColor = '#5b21b6'; dropZone.style.background = '#ede9fe'; });
    dropZone.addEventListener('dragleave', () => { dropZone.style.borderColor = '#c4b5fd'; dropZone.style.background = '#faf5ff'; });
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.style.borderColor = '#c4b5fd';
        dropZone.style.background = '#faf5ff';
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            handleZipFile(e.dataTransfer.files[0]);
        }
    });

    fileInput.addEventListener('change', () => {
        if (fileInput.files.length) handleZipFile(fileInput.files[0]);
    });

    async function handleZipFile(file) {
        if (!file.name.toLowerCase().endsWith('.zip')) {
            showError('Please select a .zip file');
            return;
        }
        errorEl.style.display = 'none';
        loadingEl.style.display = 'block';
        dropZone.style.display = 'none';

        try {
            // Use JSZip to read locally
            const JSZip = window.JSZip || await loadJSZip();
            const zip = await JSZip.loadAsync(file);

            let lotsCSV = null, itemsCSV = null;
            zip.forEach((path, entry) => {
                const name = path.split('/').pop().toLowerCase();
                if (name === 'lots.csv') lotsCSV = entry;
                if (name === 'lot_items.csv') itemsCSV = entry;
            });

            if (!lotsCSV || !itemsCSV) {
                showError('ZIP must contain lots.csv and lot_items.csv');
                loadingEl.style.display = 'none';
                dropZone.style.display = 'block';
                return;
            }

            const lotsTxt = await lotsCSV.async('string');
            const itemsTxt = await itemsCSV.async('string');

            parsedLots = parseCSV(lotsTxt);
            parsedItems = parseCSV(itemsTxt);

            if (!parsedLots.length) {
                showError('lots.csv is empty or has no data rows');
                loadingEl.style.display = 'none';
                dropZone.style.display = 'block';
                return;
            }

            // Check duplicates via API
            const lotIds = parsedLots.map(r => r[0]);
            const dupRes = await fetch(URLROOT_JS + '/api.php?action=checkDuplicateLots', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ crm_ids: lotIds })
            });
            const dupData = await dupRes.json();

            loadingEl.style.display = 'none';

            // Show preview
            showPreview(parsedLots, parsedItems, dupData.duplicates || []);

        } catch (err) {
            showError('Error reading ZIP: ' + err.message);
            loadingEl.style.display = 'none';
            dropZone.style.display = 'block';
        }
    }

    function parseCSV(text) {
        const lines = text.split('\n').map(l => l.trim()).filter(l => l);
        if (lines.length < 2) return [];
        // Skip header
        const rows = [];
        for (let i = 1; i < lines.length; i++) {
            const row = parseCSVLine(lines[i]);
            if (row.length > 1 && row[0].trim()) rows.push(row);
        }
        return rows;
    }

    function parseCSVLine(line) {
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

    function showPreview(lots, items, duplicates) {
        stage1.style.display = 'none';
        stage2.style.display = 'block';
        footer1.style.display = 'none';
        footer2.style.display = 'flex';

        document.getElementById('importSummaryLots').textContent = lots.length;
        document.getElementById('importSummaryItems').textContent = items.length;

        const container = document.getElementById('importLotsContainer');
        let html = '';

        const dupSet = new Set(duplicates.map(String));
        let hasDuplicates = false;

        lots.forEach((lot, idx) => {
            const lotId = lot[0];
            const isDup = dupSet.has(String(lotId));
            if (isDup) hasDuplicates = true;

            const lotItems = items.filter(item => String(item[0]) === String(lotId));
            const totalValue = lotItems.reduce((sum, item) => sum + parseFloat(item[6] || 0), 0);

            const borderColor = isDup ? '#ef4444' : '#d0d0c8';
            const bgColor = isDup ? '#fef2f2' : '#fff';

            html += `<div style="border:1px solid ${borderColor}; border-radius:6px; margin-bottom:12px; overflow:hidden; background:${bgColor};">`;

            // Lot header
            html += `<div style="padding:10px 14px; background:${isDup ? '#fee2e2' : '#f7f4ff'}; border-bottom:1px solid ${borderColor}; display:flex; justify-content:space-between; align-items:center;">`;
            html += `<div>`;
            html += `<strong style="font-size:13px;">Lot #${lotId}</strong>`;
            html += ` <span style="font-size:11px; color:#666; margin-left:8px;">${lot[1]} | ${lot[4]}</span>`;
            html += ` <span style="font-size:11px; color:#888; margin-left:8px;">Date: ${lot[3]}</span>`;
            html += `</div>`;
            html += `<div style="font-size:13px; font-weight:bold; color:#5b21b6;">৳${totalValue.toLocaleString('en-BD', {minimumFractionDigits:2})}</div>`;
            html += `</div>`;

            // Duplicate warning
            if (isDup) {
                html += `<div style="padding:8px 14px; background:#fef2f2; border-bottom:1px solid #fca5a5; display:flex; align-items:center; gap:8px;">`;
                html += `<i class="fa-solid fa-triangle-exclamation" style="color:#dc2626;"></i>`;
                html += `<span style="font-size:12px; color:#dc2626; font-weight:600;">⚠ Lot ID ${lotId} already exists in database — this lot will NOT be imported</span>`;
                html += `</div>`;
            }

            // Items table
            if (lotItems.length) {
                html += `<table class="csv-prev-tbl"><thead><tr><th>Item Name</th><th>Item ID</th><th>Price (৳)</th></tr></thead><tbody>`;
                lotItems.forEach(item => {
                    html += `<tr><td>${item[4]}</td><td style="color:#888;">${item[5]}</td><td style="text-align:right; font-weight:600;">${parseFloat(item[6]).toLocaleString('en-BD',{minimumFractionDigits:2})}</td></tr>`;
                });
                html += `</tbody></table>`;
            }
            html += `</div>`;
        });

        container.innerHTML = html;

        // Update confirm button state
        const confirmBtn = document.getElementById('lot-confirm-btn');
        if (hasDuplicates) {
            // Check if ALL are duplicates
            const nonDupCount = lots.filter(l => !dupSet.has(String(l[0]))).length;
            if (nonDupCount === 0) {
                confirmBtn.disabled = true;
                confirmBtn.innerHTML = '<i class="fa-solid fa-ban"></i> All Lots Already Exist';
                confirmBtn.style.background = '#9ca3af';
                confirmBtn.style.borderColor = '#6b7280';
            } else {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = `<i class="fa-solid fa-upload"></i> Import ${nonDupCount} Lot(s) (Skip Duplicates)`;
                confirmBtn.style.background = '#5b21b6';
                confirmBtn.style.borderColor = '#4c1d95';
            }
        } else {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = `<i class="fa-solid fa-upload"></i> Confirm Import (${lots.length} Lots)`;
            confirmBtn.style.background = '#5b21b6';
            confirmBtn.style.borderColor = '#4c1d95';
        }
    }

    function showError(msg) {
        errorEl.textContent = msg;
        errorEl.style.display = 'block';
    }

    window.resetLotCsv = function() {
        stage1.style.display = 'block';
        stage2.style.display = 'none';
        footer1.style.display = 'flex';
        footer2.style.display = 'none';
        dropZone.style.display = 'block';
        loadingEl.style.display = 'none';
        errorEl.style.display = 'none';
        fileInput.value = '';
        parsedLots = [];
        parsedItems = [];
    };

    window.confirmLotImport = async function() {
        const confirmBtn = document.getElementById('lot-confirm-btn');
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Importing...';

        try {
            // Re-upload the original ZIP file to the server
            const file = fileInput.files[0];
            if (!file) {
                showError('No file selected. Please re-select the ZIP file.');
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="fa-solid fa-upload"></i> Confirm Import';
                return;
            }

            const formData = new FormData();
            formData.append('zipfile', file);

            const res = await fetch(URLROOT_JS + '/api.php?action=importLotsZip', {
                method: 'POST',
                body: formData
            });

            const json = await res.json();

            if (json.success) {
                // Show success and reload
                const container = document.getElementById('importLotsContainer');
                container.innerHTML = `
                    <div style="text-align:center; padding:40px;">
                        <i class="fa-solid fa-circle-check" style="font-size:48px; color:#166534; margin-bottom:15px;"></i>
                        <div style="font-size:16px; font-weight:bold; color:#166534; margin-bottom:8px;">Import Successful!</div>
                        <div style="font-size:13px; color:#555;">${json.lots_inserted} lot(s) and ${json.items_inserted} item(s) imported.</div>
                    </div>`;
                confirmBtn.style.display = 'none';
                setTimeout(() => location.reload(), 1500);
            } else {
                showError(json.error || 'Import failed');
                // If duplicates, show in preview
                if (json.duplicates) {
                    showPreview(parsedLots, parsedItems, json.duplicates);
                }
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="fa-solid fa-upload"></i> Confirm Import';
            }
        } catch (err) {
            showError('Network error: ' + err.message);
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="fa-solid fa-upload"></i> Confirm Import';
        }
    };

    // Load JSZip dynamically
    function loadJSZip() {
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
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
