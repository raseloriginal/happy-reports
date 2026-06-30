<script>document.getElementById('pageTitle').textContent = 'Sales Reps';</script>

<div class="xl-panel" style="margin:12px;">
    <div class="xl-panel-header">
        <div class="header-left">
            <i class="fa-solid fa-user-tie" style="color:var(--accent)"></i>
            Sales Representatives
            <span class="td-muted" style="font-weight:400;font-size:11px;" id="sr-count"></span>
        </div>
        <div class="header-right">
            <input type="text" id="sr-search" class="form-control" style="width:180px;height:26px;padding:3px 8px;font-size:12px;" placeholder="Search reps…">
        </div>
    </div>
    <div style="overflow-x:auto;">
        <table class="xl-table">
            <thead>
                <tr>
                    <th style="width:50px;">#</th>
                    <th style="width:70px;">SR ID</th>
                    <th>SR Name</th>
                    <th style="width:90px;">Dealer ID</th>
                    <th>Dealer Name</th>
                </tr>
            </thead>
            <tbody id="sr-tbody">
                <tr class="loading-row"><td colspan="5"><i class="fa-solid fa-spinner fa-spin"></i> Loading from CRM…</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
(async function() {
    const tbody = document.getElementById('sr-tbody');
    const countEl = document.getElementById('sr-count');
    try {
        const [srRes, dlRes] = await Promise.all([
            fetch(CRM_API + '?table=sales_rep&limit=1000'),
            fetch(CRM_API + '?table=dealers&limit=1000')
        ]);
        const srJson = await srRes.json();
        const dlJson = await dlRes.json();

        const dealerMap = {};
        if (dlJson.status === 'success') dlJson.data.forEach(d => dealerMap[d.id] = d.dealer_name);

        if (srJson.status !== 'success' || !srJson.data.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center td-muted" style="padding:20px">No sales reps found.</td></tr>';
            return;
        }
        let rows = srJson.data;
        countEl.textContent = '(' + rows.length + ' records)';

        function render(data) {
            tbody.innerHTML = data.map((s, i) => `
                <tr>
                    <td class="td-muted">${i + 1}</td>
                    <td class="mono">${s.id}</td>
                    <td class="fw600">${s.sr_name}</td>
                    <td class="mono td-muted">${s.dealer_id}</td>
                    <td>${dealerMap[s.dealer_id] || '—'}</td>
                </tr>
            `).join('');
        }
        render(rows);

        document.getElementById('sr-search').addEventListener('input', function() {
            const q = this.value.toLowerCase();
            render(q ? rows.filter(s => s.sr_name.toLowerCase().includes(q) || (dealerMap[s.dealer_id]||'').toLowerCase().includes(q)) : rows);
        });
    } catch(e) {
        console.error("CRM Fetch Error:", e);
        tbody.innerHTML = `<tr><td colspan="5" class="text-center" style="color:var(--red);padding:20px"><i class="fa-solid fa-triangle-exclamation"></i> Failed to load CRM data: ${e.message}</td></tr>`;
    }
})();
</script>
