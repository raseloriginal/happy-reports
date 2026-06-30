<script>document.getElementById('pageTitle').textContent = 'Dealers';</script>

<div class="xl-panel" style="margin:12px;">
    <div class="xl-panel-header">
        <div class="header-left">
            <i class="fa-solid fa-handshake" style="color:var(--accent)"></i>
            Dealers
            <span class="td-muted" style="font-weight:400;font-size:11px;" id="dl-count"></span>
        </div>
        <div class="header-right">
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

<script>
(async function() {
    const tbody = document.getElementById('dl-tbody');
    const countEl = document.getElementById('dl-count');
    try {
        const res = await fetch(CRM_API + '?table=dealers&limit=1000');
        const json = await res.json();
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
