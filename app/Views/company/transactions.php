<script>document.getElementById('pageTitle').innerText = 'Company Transactions';</script>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Company Stats Chart -->
    <div class="glass-panel rounded-2xl p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Company Revenue Overview</h3>
        <div class="h-64">
            <canvas id="companyChart"></canvas>
        </div>
    </div>

    <!-- Latest Deposits -->
    <div class="glass-panel rounded-2xl p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2 flex justify-between items-center">
            Recent Deposits
            <a href="<?php echo URLROOT; ?>/import" class="text-xs bg-primary text-white px-3 py-1 rounded hover:bg-indigo-700 transition">Add Deposit</a>
        </h3>
        <div class="overflow-y-auto h-52 custom-scrollbar">
            <?php if(empty($data['deposits'])): ?>
                <p class="text-sm text-gray-500">No deposits recorded yet.</p>
            <?php else: ?>
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 sticky top-0">
                        <tr>
                            <th class="px-4 py-2">Date</th>
                            <th class="px-4 py-2">Company</th>
                            <th class="px-4 py-2 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data['deposits'] as $dep): ?>
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-4 py-2 font-medium text-gray-900"><?php echo date('M d, Y', strtotime($dep->deposit_date)); ?></td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($dep->company_name); ?></td>
                            <td class="px-4 py-2 text-right font-bold text-green-600">$<?php echo number_format($dep->amount, 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Transactions Data Table -->
<div class="glass-panel rounded-2xl p-6">
    <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">All Transactions</h3>
    <div class="overflow-x-auto">
        <table id="transactionsTable" class="w-full text-sm text-left text-gray-500 stripe hover" style="width:100%">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                <tr>
                    <th class="px-4 py-3">ID</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Company</th>
                    <th class="px-4 py-3">CRM IDs</th>
                    <th class="px-4 py-3 text-right">Out Value</th>
                    <th class="px-4 py-3 text-right">In Value</th>
                    <th class="px-4 py-3 text-right">Profit/Loss</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($data['transactions'])): ?>
                    <?php foreach($data['transactions'] as $trans): 
                        $pl = $trans->total_in_value - $trans->total_out_value;
                        $plClass = $pl >= 0 ? 'text-green-600' : 'text-red-600';
                    ?>
                    <tr>
                        <td class="px-4 py-3 font-medium text-gray-900">#<?php echo $trans->id; ?></td>
                        <td class="px-4 py-3"><?php echo date('M d, Y', strtotime($trans->transaction_date)); ?></td>
                        <td class="px-4 py-3 font-semibold text-primary"><?php echo htmlspecialchars($trans->company_name); ?></td>
                        <td class="px-4 py-3 text-xs"><?php echo htmlspecialchars(substr($trans->crm_ids, 0, 30) . (strlen($trans->crm_ids) > 30 ? '...' : '')); ?></td>
                        <td class="px-4 py-3 text-right text-gray-600">$<?php echo number_format($trans->total_out_value, 2); ?></td>
                        <td class="px-4 py-3 text-right text-green-600">$<?php echo number_format($trans->total_in_value, 2); ?></td>
                        <td class="px-4 py-3 text-right font-bold <?php echo $plClass; ?>">$<?php echo number_format($pl, 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    $('#transactionsTable').DataTable({
        "order": [[ 1, "desc" ]],
        "language": {
            "search": "",
            "searchPlaceholder": "Search transactions..."
        }
    });

    // Prepare chart data from PHP
    <?php
    $labels = [];
    $dataLots = [];
    $dataDeposits = [];
    $dataRevenue = [];
    
    if(!empty($data['combinedStats'])) {
        foreach($data['combinedStats'] as $stat) {
            $labels[] = $stat['company_name'];
            $dataLots[] = $stat['total_lots'];
            $dataDeposits[] = $stat['total_deposits'];
            $dataRevenue[] = $stat['total_in'];
        }
    }
    ?>

    const ctx = document.getElementById('companyChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [
                {
                    label: 'Total Lots Value',
                    data: <?php echo json_encode($dataLots); ?>,
                    backgroundColor: 'rgba(59, 130, 246, 0.7)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 1
                },
                {
                    label: 'Total Deposits',
                    data: <?php echo json_encode($dataDeposits); ?>,
                    backgroundColor: 'rgba(34, 197, 94, 0.7)',
                    borderColor: 'rgb(34, 197, 94)',
                    borderWidth: 1
                },
                {
                    label: 'Total Revenue (In)',
                    data: <?php echo json_encode($dataRevenue); ?>,
                    backgroundColor: 'rgba(168, 85, 247, 0.7)',
                    borderColor: 'rgb(168, 85, 247)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
