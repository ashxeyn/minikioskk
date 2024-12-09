<?php
session_start();
require_once '../classes/orderClass.php';

$orderObj = new Order();

if (isset($_SESSION['role']) && $_SESSION['role'] == 'manager') {
    $canteenId = $_SESSION['canteen_id'];
    $orders = $orderObj->fetchOrdersByCanteen($canteenId);
} else {
    $orders = $orderObj->fetchOrders();
}
?>

<div id="orderTable">
    <h4>All Orders</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Username</th>
                <th>Customer Name</th>
                <?php if ($_SESSION['role'] != 'manager'): ?>
                    <th>Canteen Name</th>
                <?php endif; ?>
                <th>Products</th>
                <th>Total Quantity</th>
                <th>Total Price</th>
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
                        <?php if ($_SESSION['role'] != 'manager'): ?>
                            <td><?= $order['canteen_name'] ?></td>
                        <?php endif; ?>
                        <td><?= $order['product_names'] ?></td>
                        <td><?= $order['total_quantity'] ?></td>
                        <td><?= number_format($order['total_price'], 2) ?></td>
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
