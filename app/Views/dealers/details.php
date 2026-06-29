<script>document.getElementById('pageTitle').innerText = 'Dealers Details & Payments';</script>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Dealer Stats List -->
    <div class="glass-panel rounded-2xl p-6 lg:col-span-1">
        <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Top Dealers by Payments</h3>
        <div class="overflow-y-auto max-h-[400px] custom-scrollbar">
            <?php if(empty($data['dealerStats'])): ?>
                <p class="text-sm text-gray-500">No dealer data available.</p>
            <?php else: ?>
                <ul class="space-y-3">
                    <?php 
                    // Sort by total paid descending
                    $stats = $data['dealerStats'];
                    usort($stats, function($a, $b) {
                        return $b->total_paid <=> $a->total_paid;
                    });
                    foreach($stats as $stat): 
                    ?>
                    <li class="flex items-center justify-between p-3 bg-white rounded-lg shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center mr-3 font-bold">
                                <?php echo strtoupper(substr($stat->dealer_name, 0, 1)); ?>
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-gray-700"><?php echo htmlspecialchars($stat->dealer_name); ?></h4>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="block text-sm font-bold text-gray-800">$<?php echo number_format($stat->total_paid, 2); ?></span>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <!-- Payments Data Table -->
    <div class="glass-panel rounded-2xl p-6 lg:col-span-2">
        <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2 flex justify-between items-center">
            Payment History
            <a href="<?php echo URLROOT; ?>/import" class="text-xs bg-primary text-white px-3 py-1 rounded hover:bg-indigo-700 transition">Add Payment</a>
        </h3>
        <div class="overflow-x-auto">
            <table id="paymentsTable" class="w-full text-sm text-left text-gray-500 stripe hover" style="width:100%">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Dealer</th>
                        <th class="px-4 py-3">CRM ID</th>
                        <th class="px-4 py-3 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($data['payments'])): ?>
                        <?php foreach($data['payments'] as $pay): ?>
                        <tr>
                            <td class="px-4 py-3"><?php echo date('M d, Y', strtotime($pay->payment_date)); ?></td>
                            <td class="px-4 py-3 font-semibold text-gray-800"><?php echo htmlspecialchars($pay->dealer_name); ?></td>
                            <td class="px-4 py-3 text-xs text-gray-400"><?php echo htmlspecialchars($pay->dealer_crm_id); ?></td>
                            <td class="px-4 py-3 text-right font-bold text-orange-600">$<?php echo number_format($pay->amount, 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    $('#paymentsTable').DataTable({
        "order": [[ 0, "desc" ]],
        "language": {
            "search": "",
            "searchPlaceholder": "Search payments..."
        }
    });
});
</script>
