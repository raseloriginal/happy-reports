<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = 'Dealers';

$dealerPaymentModel = new DealerPayment();

$payments = $dealerPaymentModel->getPayments();
$dealerStats = $dealerPaymentModel->getDealerStats();

require_once __DIR__ . '/includes/header.php';
?>
<div class="xl-panel" style="margin:12px;">
    <div class="xl-panel-header">
        <div class="header-left">
            <i class="fa-solid fa-handshake" style="color:var(--accent)"></i>
            Dealers
            <span class="td-muted" style="font-weight:400;font-size:11px;" id="dl-count"></span>
        </div>
        <div class="header-right">
            <button class="btn btn-primary" onclick="openModal('payment-modal')" style="margin-right:10px;">
                <i class="fa-solid fa-hand-holding-dollar"></i> Add Payment
            </button>
            <input type="text" id="dl-search" class="form-control" style="width:180px;height:26px;padding:3px 8px;font-size:12px;" placeholder="Search dealers…">
        </div>
    </div>
    <div style="overflow-x:auto;">
        <table class="xl-table" id="dl-table">
            <thead>
                <tr>
                    <th style="width:50px;">#</th>
                    <th style="width:70px;">CRM ID</th>
                    <th>Dealer Name</th>
                    <th>Phone</th>
                    <th style="width:120px;">Happy %</th>
                </tr>
            </thead>
            <tbody id="dl-tbody">
                <tr class="loading-row"><td colspan="5"><i class="fa-solid fa-spinner fa-spin"></i> Loading from CRM…</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ── ADD PAYMENT MODAL ──────────────────────────── -->
<div class="modal-overlay" id="payment-modal">
    <div class="modal-box" style="max-width:500px;">
        <div class="modal-header">
            <span><i class="fa-solid fa-hand-holding-dollar" style="color:var(--orange);margin-right:7px;"></i> Add Dealer Payment</span>
            <button class="modal-close">&times;</button>
        </div>
        <form action="<?php echo URLROOT; ?>/api.php?action=addPayment" method="POST">
        <div class="modal-body">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Dealer</label>
                    <select id="dealer_select" class="form-control" required>
                        <option value="">Select Dealer…</option>
                    </select>
                    <input type="hidden" name="dealer_name" id="dealer_name_input">
                </div>
                <div class="form-group">
                    <label class="form-label">Dealer CRM ID</label>
                    <input type="text" name="dealer_crm_id" id="dealer_crm_id_input" class="form-control" readonly required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Amount (৳)</label>
                    <input type="number" step="0.01" name="amount" class="form-control" required placeholder="0.00">
                </div>
                <div class="form-group">
                    <label class="form-label">Date</label>
                    <input type="date" name="payment_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn modal-close">Cancel</button>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Payment</button>
        </div>
        </form>
    </div>
</div>

<script>
(async function() {
    const tbody = document.getElementById('dl-tbody');
    const countEl = document.getElementById('dl-count');
    const dealerSelect = document.getElementById('dealer_select');
    document.getElementById('dealer_select').addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (opt.value) {
            document.getElementById('dealer_crm_id_input').value = opt.value;
            document.getElementById('dealer_name_input').value = opt.textContent;
        } else {
            document.getElementById('dealer_crm_id_input').value = '';
            document.getElementById('dealer_name_input').value = '';
        }
    });

    try {
        const res = await fetch(window.CRM_API + '?table=dealers&limit=1000');
        const json = await res.json();
        
        if (json.status === 'success') {
            json.data.forEach(d => {
                const opt = document.createElement('option');
                opt.value = d.id;
                opt.textContent = d.dealer_name;
                dealerSelect.appendChild(opt);
            });
        }

        if (json.status !== 'success' || !json.data.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center td-muted" style="padding:20px">No dealers found.</td></tr>';
            return;
        }
        let rows = json.data;
        countEl.textContent = '(' + rows.length + ' records)';

        function render(data) {
            tbody.innerHTML = data.map((d, i) => `
                <tr>
                    <td class="td-muted">${i + 1}</td>
                    <td class="mono">${d.id}</td>
                    <td class="fw600">${d.dealer_name}</td>
                    <td class="td-muted">${d.phone_number || '—'}</td>
                    <td>${d.happy_percentage ? `<span class="td-badge badge-blue">${parseFloat(d.happy_percentage).toFixed(2)}%</span>` : '—'}</td>
                </tr>
            `).join('');
        }
        render(rows);

        document.getElementById('dl-search').addEventListener('input', function() {
            const q = this.value.toLowerCase();
            render(q ? rows.filter(d => d.dealer_name.toLowerCase().includes(q) || (d.phone_number||'').includes(q)) : rows);
        });
    } catch(e) {
        console.error("CRM Fetch Error:", e);
        tbody.innerHTML = `<tr><td colspan="5" class="text-center" style="color:var(--red);padding:20px"><i class="fa-solid fa-triangle-exclamation"></i> Failed to load CRM data: ${e.message}</td></tr>`;
    }
})();
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
