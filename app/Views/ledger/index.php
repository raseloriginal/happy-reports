<script>document.getElementById('pageTitle').textContent = 'Company Ledger';</script>

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
#ledger-action-bar .co-title {
    font-size: 15px;
    font-weight: 700;
    color: #1a1a1a;
}
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
</style>

<!-- ── ADD LOT MODAL ─────────────────────────────── -->
<div class="modal-overlay" id="lot-modal">
    <div class="modal-box" style="max-width:500px;">
        <div class="modal-header">
            <span><i class="fa-solid fa-file-csv" style="color:#5b21b6;margin-right:7px;"></i> Import Lot (CSV)</span>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <div style="background:#f7f4ff;border:1px solid #c4b5fd;padding:8px 10px;margin-bottom:12px;font-size:11.5px;color:#5b21b6;border-radius:2px;">
                <strong>Required CSV headers:</strong><br>
                <span class="mono" style="font-size:11px;color:#1a1a1a;">CRM_ID, Warehouse_CRM_ID, Warehouse_Name, Company_CRM_ID, Company_Name, Lot_Value, Lot_Date</span>
                <br><a href="<?php echo URLROOT; ?>/templates/lots_template.csv" download style="color:var(--accent);font-size:11px;margin-top:4px;display:inline-block;"><i class="fa-solid fa-download"></i> Download Lot Template</a>
            </div>
            <form action="<?php echo URLROOT; ?>/import/uploadLots" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="form-label">Select CSV File</label>
                    <input type="file" name="lots_csv" accept=".csv" required>
                </div>
                <div class="modal-footer" style="margin:12px -14px -14px;padding:10px 14px;">
                    <button type="button" class="btn modal-close">Cancel</button>
                    <button type="submit" class="btn" style="background:#5b21b6;color:#fff;border-color:#4c1d95;"><i class="fa-solid fa-upload"></i> Import Lot</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── ADD DEPOSIT MODAL ─────────────────────────── -->
<div class="modal-overlay" id="dep-modal">
    <div class="modal-box" style="max-width:580px;">
        <div class="modal-header">
            <span><i class="fa-solid fa-building-columns" style="color:#1a73e8;margin-right:7px;"></i> Add Deposit(s)</span>
            <button class="modal-close">&times;</button>
        </div>
        <form action="<?php echo URLROOT; ?>/import/addDeposit" method="POST">
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

    <!-- Company selector tabs -->
    <div id="co-list-bar">
        <div class="co-item active" data-co-id="all" data-co-name="All Companies">All Companies</div>
        <!-- JS will populate -->
    </div>

    <!-- Action bar -->
    <div id="ledger-action-bar">
        <div class="co-title" id="ledger-co-title">All Companies — Transaction Ledger</div>
        <div class="action-btns">
            <button class="btn" style="background:#ede9fe;color:#5b21b6;border-color:#c4b5fd;" onclick="openModal('lot-modal')">
                <i class="fa-solid fa-box-open"></i> Add Lot
            </button>
            <button class="btn btn-primary" onclick="openModal('dep-modal')">
                <i class="fa-solid fa-building-columns"></i> Add Deposit
            </button>
        </div>
    </div>

    <!-- Summary strip -->
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

    <!-- Ledger table -->
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
                </tr>
            </thead>
            <tbody id="ledger-tbody">
                <tr class="loading-row"><td colspan="9" style="text-align:center;padding:30px;color:#888;"><i class="fa-solid fa-spinner fa-spin"></i> Loading ledger data…</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- PHP data injected for JS -->
<script>
// Inject local DB data
window.DB_LOTS = <?php echo json_encode(array_map(function($l) {
    return [
        'id'          => $l->id,
        'company_id'  => $l->company_crm_id,
        'company_name'=> $l->company_name ?? '',
        'lot_date'    => $l->lot_date,
        'total_amount'=> $l->lot_value
    ];
}, $data['lots'] ?? [])); ?>;

window.DB_DEPOSITS = <?php echo json_encode(array_map(function($d) {
    return [
        'id'          => $d->id,
        'company_id'  => $d->company_crm_id,
        'company_name'=> $d->company_name ?? '',
        'operation_date'=> $d->deposit_date,
        'amount'      => $d->amount,
        'note'        => $d->note ?? ''
    ];
}, $data['deposits'] ?? [])); ?>;

// ── MAIN LEDGER LOGIC ────────────────────────────────────────────
(async function() {
    const tbody = document.getElementById('ledger-tbody');
    let allRows = [];     // merged ledger entries
    let companies = [];   // [{id, name}]
    let activeCoId = 'all';

    // 1) Fetch companies from CRM API
    try {
        const res = await fetch(CRM_API + '?table=companies&limit=1000');
        const json = await res.json();
        if (json.status === 'success') {
            companies = json.data.map(c => ({id: String(c.id), name: c.company_name}));
            populateCompanyTabs(companies);
            populateDepositDropdowns(companies);
        }
    } catch(e) { console.error('CRM fetch failed:', e); document.getElementById('ledger-tbody').innerHTML = `<tr><td colspan="8" class="text-center" style="color:var(--red);padding:20px">Failed to load CRM companies: ${e.message}</td></tr>`; }

    // 2) Build ledger from local DB lots + deposits
    buildLedger();

    function buildLedger() {
        allRows = [];

        // Lots → DEBIT (money owed / cost)
        (window.DB_LOTS || []).forEach(l => {
            let parsedCoId = String(l.company_id);
            if (parsedCoId.startsWith('C-')) parsedCoId = parsedCoId.substring(2);
            
            allRows.push({
                date:      l.lot_date,
                txnId:     'LOT-' + String(l.id).padStart(3, '0'),
                type:      'lot',
                typeLabel: 'Lot Received',
                coName:    l.company_name || resolveCompany(parsedCoId),
                desc:      'Imported Lot',
                debit:     0,
                credit:    parseFloat(l.total_amount) || 0,
                status:    'Delivered',
                coId:      parsedCoId
            });
        });

        // Deposits → CREDIT (money received)
        (window.DB_DEPOSITS || []).forEach(d => {
            const note = d.note || '—';
            allRows.push({
                date:      d.operation_date,
                txnId:     'DEP-' + String(d.id).padStart(3, '0'),
                type:      'deposit',
                typeLabel: 'Deposit',
                coName:    d.company_name || resolveCompany(d.company_id),
                desc:      note,
                debit:     parseFloat(d.amount) || 0,
                credit:    0,
                status:    'Settled',
                coId:      String(d.company_id)
            });
        });

        // Sort by date ascending
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
            tbody.innerHTML = `<tr><td colspan="9">
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
            // deposit = money IN (debit from company side), lot = cost OUT (credit side)
            balance += row.debit;
            balance -= row.credit;
            totalDebit += row.debit;
            totalCredit += row.credit;

            const balClass = balance >= 0 ? 'col-bal-pos' : 'col-bal-neg';
            const balPrefix = balance >= 0 ? '+' : '';
            const balStr = balPrefix + fmt2(balance);

            const typeHtml = getTypeBadge(row.type, row.typeLabel);
            const statusHtml = getStatusBadge(row.status);

            const debitCell  = row.debit  > 0 ? `<td class="col-debit">${fmt2(row.debit)}</td>`  : `<td class="dash-cell">—</td>`;
            const creditCell = row.credit > 0 ? `<td class="col-credit">${fmt2(row.credit)}</td>` : `<td class="dash-cell">—</td>`;

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
            </tr>`;
        });

        // Running total footer row
        rowsHtml.push(`<tr class="ledger-total-row">
            <td colspan="5">Running Total</td>
            <td class="col-debit">${fmt2(totalDebit)}</td>
            <td class="col-credit">${fmt2(totalCredit)}</td>
            <td class="${balance >= 0 ? 'col-bal-pos' : 'col-bal-neg'}">${(balance >= 0 ? 'Recv: +' : 'Owed: ') + fmt2(Math.abs(balance))}</td>
            <td></td>
        </tr>`);

        tbody.innerHTML = rowsHtml.join('');
        updateSummary(filtered, totalDebit, totalCredit);
    }

    function updateSummary(rows, totalDep, totalLot) {
        const net = totalDep - totalLot;
        document.getElementById('sum-deposit').textContent = '৳' + fmt2(totalDep);
        document.getElementById('sum-lot').textContent = '৳' + fmt2(totalLot);
        const balEl = document.getElementById('sum-balance');
        balEl.textContent = (net >= 0 ? '+' : '') + '৳' + fmt2(net);
        balEl.style.color = net >= 0 ? '#166534' : '#b91c1c';
        document.getElementById('sum-entries').textContent = rows.filter(r => r.txnId !== 'total').length;
    }

    function fmt2(n) {
        return parseFloat(n||0).toLocaleString('en-BD', {minimumFractionDigits:2, maximumFractionDigits:2});
    }

    function getTypeBadge(type, label) {
        const cls = {deposit:'type-deposit', lot:'type-lot', credit:'type-credit', txn:'type-txn'}[type] || 'type-txn';
        return `<span class="type-badge ${cls}">${label}</span>`;
    }

    function getStatusBadge(status) {
        const s = (status||'').toLowerCase();
        const cls = {settled:'status-settled', delivered:'status-delivered', approved:'status-approved', pending:'status-pending'}[s] || 'status-pending';
        return `<span class="status-badge ${cls}">${status}</span>`;
    }

    function populateCompanyTabs(cos) {
        const bar = document.getElementById('co-list-bar');
        cos.forEach(c => {
            const el = document.createElement('div');
            el.className = 'co-item';
            el.dataset.coId = c.id;
            el.dataset.coName = c.name;
            el.textContent = c.name;
            bar.appendChild(el);
        });

        bar.addEventListener('click', function(e) {
            const item = e.target.closest('.co-item');
            if (!item) return;
            bar.querySelectorAll('.co-item').forEach(i => i.classList.remove('active'));
            item.classList.add('active');
            const coId = item.dataset.coId;
            const coName = item.dataset.coName;
            document.getElementById('ledger-co-title').textContent = coName + ' — Transaction Ledger';
            renderLedger(coId);
        });
    }

    function populateDepositDropdowns(cos) {
        document.querySelectorAll('.company-select').forEach(sel => {
            cos.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = c.name;
                sel.appendChild(opt);
            });
        });
    }

    // ── PRINT ──
    window.printLedger = function() { window.print(); };

    // ── EXPORT CSV ──
    window.exportCSV = function() {
        const filtered = activeCoId === 'all' ? allRows : allRows.filter(r => r.coId === activeCoId);
        if (!filtered.length) return;
        let balance = 0;
        let csv = 'Date,TXN ID,Type,Company,Description,Debit,Credit,Balance,Status\n';
        filtered.forEach(row => {
            balance += row.debit - row.credit;
            csv += [row.date, row.txnId, row.typeLabel, '"'+row.coName.replace(/"/g,'""')+'"', '"'+row.desc.replace(/"/g,'""')+'"',
                    row.debit||'', row.credit||'', balance.toFixed(2), row.status].join(',') + '\n';
        });
        const a = document.createElement('a');
        a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
        a.download = 'ledger_export.csv';
        a.click();
    };

})();

// ── DEPOSIT ROW ADD/REMOVE ──────────────────────────────────────
let companyOptionsCache = [];

document.addEventListener('DOMContentLoaded', function() {
    // Cache loaded options after CRM fetch
    const observer = new MutationObserver(() => {
        const firstSel = document.querySelector('.company-select');
        if (firstSel && firstSel.options.length > 1) {
            companyOptionsCache = Array.from(firstSel.options).slice(1).map(o => ({id:o.value, name:o.textContent}));
            observer.disconnect();
        }
    });
    observer.observe(document.getElementById('dep-rows'), {childList:true, subtree:true, characterData:false});

    // Company select → auto-fill CRM ID + name hidden
    document.getElementById('dep-rows').addEventListener('change', function(e) {
        if (e.target.classList.contains('company-select')) {
            const row = e.target.closest('.deposit-row-item');
            const opt = e.target.options[e.target.selectedIndex];
            row.querySelector('.company-crm-id-input').value = opt.value;
            row.querySelector('.company-name-input').value = opt.textContent;
        }
    });

    // Add row
    document.getElementById('add-dep-row').addEventListener('click', function() {
        const rows = document.getElementById('dep-rows');
        const tpl = rows.querySelector('.deposit-row-item');
        const clone = tpl.cloneNode(true);
        clone.querySelectorAll('input').forEach(i => {
            if (i.type === 'date') i.value = new Date().toISOString().split('T')[0];
            else i.value = '';
        });
        // Repopulate select
        const sel = clone.querySelector('.company-select');
        while (sel.options.length > 1) sel.remove(1);
        companyOptionsCache.forEach(c => {
            const o = document.createElement('option');
            o.value = c.id; o.textContent = c.name; sel.appendChild(o);
        });
        clone.querySelector('.dep-remove').style.display = 'block';
        rows.appendChild(clone);
        updateRemoveBtns();
    });

    // Remove row
    document.getElementById('dep-rows').addEventListener('click', function(e) {
        const btn = e.target.closest('.dep-remove');
        if (!btn) return;
        btn.closest('.deposit-row-item').remove();
        updateRemoveBtns();
    });

    function updateRemoveBtns() {
        const rows = document.querySelectorAll('#dep-rows .deposit-row-item');
        rows.forEach((r, i) => {
            const b = r.querySelector('.dep-remove');
            b.style.display = rows.length > 1 ? 'block' : 'none';
        });
    }
});
</script>
