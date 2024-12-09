<?php
require_once '../classes/orderClass.php';

$orderObj = new Order();
$orders = $orderObj->fetchOrders();
?>

    <div id="orderTable">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Username</th>
                    <th>Customer Name</th>
                    <th>Canteen Name</th>
                    <th>Products</th>
                    <th>Total Quantity</th> <!-- Display Total Quantity -->
                    <th>Total Price</th> <!-- Display Total Price -->
                    <th>Status</th>
                    <th>Queue Number</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $order): ?>
                        <tr id="order-<?= $order['order_id'] ?>">
                            <td><?= $order['order_id'] ?></td>
                            <td><?= $order['username'] ?></td>
                            <td><?= $order['customer_name'] ?></td>
                            <td><?= $order['canteen_name'] ?></td>
                            <td><?= $order['product_names'] ?></td>
                            <td><?= $order['total_quantity'] ?></td> <!-- Display Total Quantity -->
                            <td><?= number_format($order['total_price'], 2) ?></td> <!-- Display Total Price -->
                            <td><?= $order['status'] ?></td>
                            <td><?= $order['queue_number'] ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm" onclick="openEditModal(<?= $order['order_id'] ?>)">Edit</button>
                                <button class="btn btn-danger btn-sm" onclick="openDeleteModal(<?= $order['order_id'] ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10">No orders found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
