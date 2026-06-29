<script>document.getElementById('pageTitle').innerText = 'Inventory Monitor';</script>

<!-- Top Level Metrics -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="glass-panel rounded-2xl p-6 border-l-4 border-blue-500 hover:shadow-lg transition">
        <p class="text-sm text-gray-500 font-medium mb-1">Total SKUs</p>
        <h3 class="text-2xl font-bold text-gray-800" id="dyn-total-skus"><i class="fas fa-spinner fa-spin text-sm"></i></h3>
    </div>
    <div class="glass-panel rounded-2xl p-6 border-l-4 border-green-500 hover:shadow-lg transition">
        <p class="text-sm text-gray-500 font-medium mb-1">Current Stock Value</p>
        <h3 class="text-2xl font-bold text-gray-800" id="dyn-total-value"><i class="fas fa-spinner fa-spin text-sm"></i></h3>
    </div>
    <div class="glass-panel rounded-2xl p-6 border-l-4 border-red-500 hover:shadow-lg transition">
        <p class="text-sm text-gray-500 font-medium mb-1">Last 30 Days Damage</p>
        <h3 class="text-2xl font-bold text-gray-800" id="dyn-damage-value"><i class="fas fa-spinner fa-spin text-sm"></i></h3>
    </div>
    <div class="glass-panel rounded-2xl p-6 border-l-4 border-gray-500 hover:shadow-lg transition">
        <p class="text-sm text-gray-500 font-medium mb-1">Total Dead SKUs</p>
        <h3 class="text-2xl font-bold text-gray-800" id="dyn-dead-skus"><i class="fas fa-spinner fa-spin text-sm"></i></h3>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Inventory Value by Category -->
    <div class="glass-panel rounded-2xl p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Inventory Value by Category</h3>
        <div class="h-64">
            <canvas id="categoryChart"></canvas>
        </div>
    </div>

    <!-- Top Selling Products -->
    <div class="glass-panel rounded-2xl p-6">
        <div class="mb-4 border-b pb-2">
            <h3 class="text-lg font-bold text-gray-800 flex justify-between items-center">
                Top Selling Products
                <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded-full">By Revenue</span>
            </h3>
            <p class="text-xs text-gray-500 mt-1">Overall selling value</p>
        </div>
        <div id="dyn-top-products-body" class="flex flex-col gap-1">
            <div class="text-center py-4 text-gray-500"><i class="fas fa-spinner fa-spin"></i> Loading data from API...</div>
        </div>
    </div>
</div>

<div class="mt-6">
    <div class="glass-panel rounded-2xl p-6 border-l-4 border-amber-500 hover:shadow-lg transition">
        <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2 flex items-center gap-2">
            <i class="fas fa-exclamation-triangle text-amber-500"></i> Low Stock Alerts
        </h3>
        <div id="dyn-low-stock" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="col-span-full text-center py-4 text-gray-500"><i class="fas fa-spinner fa-spin"></i> Loading data from API...</div>
        </div>
    </div>
</div>

<script>

async function fetchCrmData() {
    const API_URL = 'https://happycrm.site/happyreports_api/index.php';
    try {
        const [productsRes, damageRes, opDamageRes, txRes, catRes] = await Promise.all([
            fetch(`${API_URL}?table=products&limit=10000`),
            fetch(`${API_URL}?table=damage&limit=10000`),
            fetch(`${API_URL}?table=operations_damage&limit=10000`),
            fetch(`${API_URL}?table=transaction_items&limit=10000`),
            fetch(`${API_URL}?table=product_category&limit=1000`)
        ]);

        const productsData = await productsRes.json();
        const damageData = await damageRes.json();
        const opDamageData = await opDamageRes.json();
        const txData = await txRes.json();
        const catData = await catRes.json();

        const categoryMap = {};
        if (catData.status === 'success' && catData.data) {
            catData.data.forEach(c => {
                categoryMap[c.id] = c.category_name;
            });
        }

        let totalSkus = 0;
        let totalStockValue = 0;
        let deadSkus = 0;
        const productsMap = {};
        const categoryValue = {};
        const lowStockProducts = [];

        if (productsData.status === 'success' && productsData.data) {
            totalSkus = productsData.data.length;
            productsData.data.forEach(p => {
                const stock = parseInt(p.stock) || 0;
                const price = parseFloat(p.price) || 0;
                const val = stock * price;
                
                totalStockValue += val;
                if (stock <= 0) deadSkus++;
                productsMap[p.id] = p.product_name;

                const catId = p.product_category_id;
                const catName = categoryMap[catId] || `Unknown (${catId})`;
                if(!categoryValue[catName]) categoryValue[catName] = 0;
                categoryValue[catName] += val;

                if (stock > 0 && stock <= 20) {
                    lowStockProducts.push({ name: p.product_name, stock: stock, price: price });
                }
            });
        }

        let thirtyDaysDamage = 0;
        const thirtyDaysAgo = new Date();
        thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);

        if (damageData.status === 'success' && damageData.data) {
            damageData.data.forEach(d => {
                if (new Date(d.damage_date) >= thirtyDaysAgo) {
                    thirtyDaysDamage += parseFloat(d.damage_amount || 0);
                }
            });
        }
        
        if (opDamageData.status === 'success' && opDamageData.data) {
            opDamageData.data.forEach(d => {
                if (new Date(d.operation_date) >= thirtyDaysAgo) {
                    thirtyDaysDamage += parseFloat(d.damage_amount || 0);
                }
            });
        }

        const productSales = {};
        if (txData.status === 'success' && txData.data) {
            txData.data.forEach(t => {
                const outQty = parseInt(t.out_qty) || 0;
                if (outQty > 0) {
                    const price = parseFloat(t.per_price) || 0;
                    const revenue = outQty * price;
                    const pId = t.product_id;
                    if (!productSales[pId]) {
                        productSales[pId] = { qty: 0, revenue: 0, name: productsMap[pId] || `Unknown (${pId})` };
                    }
                    productSales[pId].qty += outQty;
                    productSales[pId].revenue += revenue;
                }
            });
        }

        const topProducts = Object.values(productSales)
            .sort((a, b) => b.revenue - a.revenue)
            .slice(0, 10);

        document.getElementById('dyn-total-skus').innerText = totalSkus.toLocaleString();
        document.getElementById('dyn-total-value').innerText = '৳' + totalStockValue.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('dyn-damage-value').innerText = '৳' + thirtyDaysDamage.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('dyn-dead-skus').innerText = deadSkus.toLocaleString();

        const listContainer = document.getElementById('dyn-top-products-body');
        if (topProducts.length > 0) {
            const maxRevenue = topProducts[0].revenue;
            const colors = ['bg-blue-500', 'bg-emerald-500', 'bg-purple-500', 'bg-amber-500', 'bg-rose-500', 'bg-cyan-500', 'bg-pink-500', 'bg-indigo-500', 'bg-teal-500', 'bg-orange-500'];
            const iconColors = ['bg-blue-50 text-blue-500', 'bg-emerald-50 text-emerald-500', 'bg-purple-50 text-purple-500', 'bg-amber-50 text-amber-500', 'bg-rose-50 text-rose-500', 'bg-cyan-50 text-cyan-500', 'bg-pink-50 text-pink-500', 'bg-indigo-50 text-indigo-500', 'bg-teal-50 text-teal-500', 'bg-orange-50 text-orange-500'];
            
            listContainer.innerHTML = topProducts.map((p, i) => {
                const percentage = Math.round((p.revenue / maxRevenue) * 100);
                const barColor = colors[i % colors.length];
                const iconColor = iconColors[i % iconColors.length];
                
                let revText = '';
                if (p.revenue >= 100000) {
                    revText = (p.revenue / 100000).toFixed(2) + 'L';
                } else if (p.revenue >= 1000) {
                    revText = (p.revenue / 1000).toFixed(2) + 'k';
                } else {
                    revText = p.revenue.toFixed(2);
                }

                return `
                <div class="flex items-center py-3 px-2 border-b border-gray-50 last:border-0 hover:bg-gray-50 transition rounded-xl group">
                    <div class="w-12 h-12 rounded-xl ${iconColor} flex items-center justify-center shrink-0 mr-4 shadow-sm group-hover:scale-105 transition-transform">
                        <i class="fas fa-box text-lg"></i>
                    </div>
                    <div class="flex-grow">
                        <div class="flex justify-between items-start mb-1">
                            <h4 class="font-bold text-gray-800 text-sm md:text-base leading-tight">${p.name}</h4>
                            <span class="font-bold text-gray-800 text-sm md:text-base">৳${revText}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5 my-2 overflow-hidden">
                            <div class="${barColor} h-full rounded-full transition-all duration-1000 ease-out" style="width: 0%" data-width="${percentage}%"></div>
                        </div>
                        <div class="flex justify-between items-center text-xs text-gray-400 font-medium">
                            <span>${p.qty.toLocaleString()} units</span>
                            <span>Rank #${i + 1}</span>
                        </div>
                    </div>
                </div>
                `;
            }).join('');
            
            setTimeout(() => {
                listContainer.querySelectorAll('[data-width]').forEach(el => {
                    el.style.width = el.getAttribute('data-width');
                });
            }, 50);

        } else {
            listContainer.innerHTML = '<div class="text-center py-4 text-gray-500">No product data available.</div>';
        }

        const lowStockContainer = document.getElementById('dyn-low-stock');
        if (lowStockProducts.length > 0) {
            lowStockProducts.sort((a,b) => a.stock - b.stock);
            lowStockContainer.innerHTML = lowStockProducts.slice(0, 6).map(p => `
                <div class="flex items-center p-3 border border-red-100 bg-red-50 rounded-xl hover:bg-red-100 transition cursor-pointer">
                    <div class="w-10 h-10 rounded-full bg-white text-red-500 shadow-sm flex items-center justify-center mr-3 shrink-0">
                        <i class="fas fa-exclamation text-lg"></i>
                    </div>
                    <div class="overflow-hidden">
                        <h4 class="font-bold text-gray-800 text-sm truncate">${p.name}</h4>
                        <p class="text-xs font-semibold text-red-600 mt-0.5">Only ${p.stock} left in stock</p>
                    </div>
                </div>
            `).join('');
        } else {
            lowStockContainer.innerHTML = '<div class="col-span-full text-center text-gray-500 py-2">No low stock items.</div>';
        }

        const catLabels = Object.keys(categoryValue);
        const catDataValues = Object.values(categoryValue);
        const ctx = document.getElementById('categoryChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: catLabels,
                datasets: [{
                    data: catDataValues,
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(139, 92, 246, 0.8)'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right' }
                }
            }
        });

    } catch (err) {
        console.error('Error fetching CRM data:', err);
        document.getElementById('dyn-top-products-body').innerHTML = '<div class="text-center py-4 text-red-500">Failed to load API data.</div>';
    }
}
fetchCrmData();
</script>
