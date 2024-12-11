<?php
session_start();
require_once '../classes/orderClass.php';

$orderObj = new Order();
$orders = [];

try {
    if (isset($_SESSION['role']) && $_SESSION['role'] == 'manager') {
        $canteenId = $_SESSION['canteen_id'] ?? null;
        if (!$canteenId) {
            throw new Exception("Canteen ID not found in session");
        }
        $orders = $orderObj->fetchOrdersByCanteen($canteenId);
    } else {
        $orders = $orderObj->fetchOrders();
    }
} catch (Exception $e) {
    error_log("Error fetching orders: " . $e->getMessage());
}
?>

<div id="orderTable">
    <h4>All Orders</h4>
    <div class="filter-group">
        <label for="status">Status</label>
        <select id="status" class="form-select w-auto" onchange="filterOrderStatus()">
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="accepted">Accepted</option>
            <option value="cancelled">Cancelled</option>
            <option value="completed">Completed</option>
        </select>
    </div>
    <?php if (!empty($orders)): ?>
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
            <?php foreach ($orders as $order): ?>
                <tr id="order-<?= htmlspecialchars($order['order_id']) ?>">
                    <td><?= htmlspecialchars($order['order_id']) ?></td>
                    <td><?= htmlspecialchars($order['username']) ?></td>
                    <td><?= htmlspecialchars($order['customer_name']) ?></td>
                    <?php if ($_SESSION['role'] != 'manager'): ?>
                        <td><?= htmlspecialchars($order['canteen_name']) ?></td>
                    <?php endif; ?>
                    <td><?= htmlspecialchars($order['product_names']) ?></td>
                    <td><?= htmlspecialchars($order['total_quantity']) ?></td>
                    <td><?= number_format($order['total_price'], 2) ?></td>
                    <td><?= htmlspecialchars($order['status']) ?></td>
                    <td><?= htmlspecialchars($order['queue_number']) ?></td>
                    <td>
                        <button class="btn btn-warning btn-sm" onclick="openEditModal(<?= $order['order_id'] ?>)">Edit</button>
                        <button class="btn btn-danger btn-sm" onclick="openDeleteModal(<?= $order['order_id'] ?>)">Delete</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <div class="alert alert-info">No orders found.</div>
    <?php endif; ?>
</div>

<script src="../js/search.js"></script>
