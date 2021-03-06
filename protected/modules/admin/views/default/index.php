<div class="block">
    <div class="block_head">
        <div class="bheadl"></div>
        <div class="bheadr"></div>
        <h2>Last Orders</h2>
        <ul class="tabs">
            <li><a href="/admin/order">View orders</a></li>
        </ul>
    </div>
    <div class="block_content">
        <?php if (!$lastOrders OR count($lastOrders) == 0): ?>
        <div class="message info"><p>No orders found!</p></div>
        <?php else: ?>
        <table cellpadding="0" cellspacing="0" width="100%" class="sortable">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date created</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lastOrders AS $lastOrder): 
                $orderDetail = $lastOrder->orderDetail;
                $fullname = $orderDetail->b_name.' '.$orderDetail->b_surname;
                ?>
                <tr>
                    <td><?php echo $fullname; ?></td>
                    <td><?php echo $lastOrder->quantity; ?></td>
                    <td><?php echo $lastOrder->total; ?></td>
                    <td><?php echo $lastOrder->status; ?></td>
                    <td><?php echo date("Y-m-d", strtotime($lastOrder->created)); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
    <div class="bendl"></div>
    <div class="bendr"></div>
</div>