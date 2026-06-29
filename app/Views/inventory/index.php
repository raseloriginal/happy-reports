<script>document.getElementById('pageTitle').innerText = 'Inventory Monitor';</script>

<!-- Top Level Metrics -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="glass-panel rounded-2xl p-6 border-l-4 border-blue-500 hover:shadow-lg transition">
        <p class="text-sm text-gray-500 font-medium mb-1">Total SKUs</p>
        <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($data['apiData']['total_skus'] ?? 0); ?></h3>
    </div>
    <div class="glass-panel rounded-2xl p-6 border-l-4 border-green-500 hover:shadow-lg transition">
        <p class="text-sm text-gray-500 font-medium mb-1">Total Inventory Value</p>
        <h3 class="text-2xl font-bold text-gray-800">$<?php echo number_format($data['apiData']['total_inventory_value'] ?? 0, 2); ?></h3>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Warehouse Breakdown -->
    <div class="glass-panel rounded-2xl p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Warehouse Breakdown</h3>
        <div class="h-64">
            <canvas id="warehouseChart"></canvas>
        </div>
    </div>

    <!-- Top Selling Products -->
    <div class="glass-panel rounded-2xl p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2 flex justify-between items-center">
            Top Selling Products
            <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded-full">By Quantity</span>
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-4 py-2">Product Name</th>
                        <th class="px-4 py-2 text-right">Qty</th>
                        <th class="px-4 py-2 text-right">Revenue</th>
                        <th class="px-4 py-2 text-right">Profit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($data['apiData']['top_products'])): ?>
                        <?php foreach($data['apiData']['top_products'] as $product): ?>
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-4 py-2 font-medium text-gray-900"><?php echo htmlspecialchars($product['name'] ?? ''); ?></td>
                            <td class="px-4 py-2 text-right font-bold text-blue-600"><?php echo number_format($product['qty'] ?? 0); ?></td>
                            <td class="px-4 py-2 text-right text-green-600">$<?php echo number_format($product['revenue'] ?? 0, 2); ?></td>
                            <td class="px-4 py-2 text-right font-bold text-indigo-600">$<?php echo number_format($product['profit'] ?? 0, 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center py-4">No product data available.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php
    $wLabels = [];
    $wValues = [];
    if(!empty($data['warehouseStats'])) {
        foreach($data['warehouseStats'] as $wStat) {
            $wLabels[] = $wStat->warehouse_name;
            $wValues[] = $wStat->total_value;
        }
    }
    ?>

    const ctx = document.getElementById('warehouseChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($wLabels); ?>,
            datasets: [{
                data: <?php echo json_encode($wValues); ?>,
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
});
</script>
