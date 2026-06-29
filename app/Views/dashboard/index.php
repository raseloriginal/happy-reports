<script>document.getElementById('pageTitle').innerText = 'Dashboard & P&L Engine';</script>

<!-- Key Metrics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Revenue -->
    <div class="glass-panel rounded-2xl p-6 border-l-4 border-green-500 hover:shadow-lg transition">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm text-gray-500 font-medium mb-1">Total Revenue</p>
                <h3 class="text-2xl font-bold text-gray-800">$<?php echo number_format($data['totalRevenue'], 2); ?></h3>
            </div>
            <div class="p-3 bg-green-100 rounded-lg text-green-600">
                <i class="fa-solid fa-arrow-trend-up"></i>
            </div>
        </div>
    </div>

    <!-- Lots Value -->
    <div class="glass-panel rounded-2xl p-6 border-l-4 border-blue-500 hover:shadow-lg transition">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm text-gray-500 font-medium mb-1">Total Lots Value</p>
                <h3 class="text-2xl font-bold text-gray-800">$<?php echo number_format($data['totalLots'], 2); ?></h3>
            </div>
            <div class="p-3 bg-blue-100 rounded-lg text-blue-600">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
        </div>
    </div>

    <!-- Dealer Payments -->
    <div class="glass-panel rounded-2xl p-6 border-l-4 border-orange-500 hover:shadow-lg transition">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm text-gray-500 font-medium mb-1">Dealer Payments</p>
                <h3 class="text-2xl font-bold text-gray-800">$<?php echo number_format($data['totalDealerPayments'], 2); ?></h3>
            </div>
            <div class="p-3 bg-orange-100 rounded-lg text-orange-600">
                <i class="fa-solid fa-handshake"></i>
            </div>
        </div>
    </div>

    <!-- Net Profit -->
    <div class="glass-panel rounded-2xl p-6 border-l-4 border-indigo-500 hover:shadow-lg transition">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm text-gray-500 font-medium mb-1">Net Profit (incl. CRM Expenses)</p>
                <h3 class="text-2xl font-bold <?php echo $data['netProfit'] >= 0 ? 'text-indigo-600' : 'text-red-600'; ?>">
                    $<?php echo number_format($data['netProfit'], 2); ?>
                </h3>
            </div>
            <div class="p-3 bg-indigo-100 rounded-lg text-indigo-600">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- P&L Breakdown Chart -->
    <div class="lg:col-span-2 glass-panel rounded-2xl p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Profit & Loss Breakdown</h3>
        <div class="h-80">
            <canvas id="pnlChart"></canvas>
        </div>
    </div>

    <!-- Inventory Monitor -->
    <div class="glass-panel rounded-2xl p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Inventory Monitor</h3>
        <div class="overflow-y-auto max-h-80 pr-2 custom-scrollbar">
            <?php if(empty($data['inventoryStats'])): ?>
                <p class="text-sm text-gray-500">No inventory data available.</p>
            <?php else: ?>
                <ul class="space-y-4">
                    <?php foreach($data['inventoryStats'] as $stat): ?>
                    <li class="flex items-center justify-between p-3 bg-slate-50 rounded-lg border border-slate-100 hover:border-primary transition cursor-pointer">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mr-3">
                                <i class="fa-solid fa-warehouse"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-gray-700"><?php echo htmlspecialchars($stat->warehouse_name); ?></h4>
                                <p class="text-xs text-gray-500"><?php echo $stat->lot_count; ?> Lots</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="block text-sm font-bold text-gray-800">$<?php echo number_format($stat->total_value, 2); ?></span>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('pnlChart').getContext('2d');
    
    // Gradient for Revenue
    let revenueGradient = ctx.createLinearGradient(0, 0, 0, 400);
    revenueGradient.addColorStop(0, 'rgba(34, 197, 94, 0.8)');
    revenueGradient.addColorStop(1, 'rgba(34, 197, 94, 0.2)');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Revenue', 'Lots Cost', 'Dealer Pay', 'CRM Expenses', 'Net Profit'],
            datasets: [{
                label: 'Financial Flow',
                data: [
                    <?php echo $data['totalRevenue']; ?>,
                    -<?php echo $data['totalLots']; ?>,
                    -<?php echo $data['totalDealerPayments']; ?>,
                    -<?php echo $data['crmExpenses']; ?>,
                    <?php echo $data['netProfit']; ?>
                ],
                backgroundColor: [
                    revenueGradient,
                    'rgba(239, 68, 68, 0.8)', // Red for costs
                    'rgba(249, 115, 22, 0.8)', // Orange
                    'rgba(107, 114, 128, 0.8)', // Gray
                    'rgba(79, 70, 229, 0.8)' // Indigo for profit
                ],
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) { label += ': '; }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: {
                        callback: function(value, index, values) {
                            if(value >= 1000) return '$' + value/1000 + 'k';
                            return '$' + value;
                        }
                    }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
});
</script>
