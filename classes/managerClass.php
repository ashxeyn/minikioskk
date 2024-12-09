<?php
require_once '../classes/databaseClass.php';

class Manager
{
    protected $db;
    private $canteenId;

    function __construct($canteenId)
    {
        $this->db = new Database();
        $this->canteenId = $canteenId;
    }

    function getTotalSales()
    {
        $sql = "SELECT SUM(oi.total_price) AS total_sales 
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.order_id
                WHERE o.canteen_id = :canteen_id AND o.status = 'completed'";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindValue(':canteen_id', $this->canteenId); 
    
        $totalSales = 0;
        if ($query->execute()) {
            $totalSales = $query->fetchColumn();
        }
    
        return $totalSales ?? 0;
    }

    function getCustomerCount()
    {
        $sql = "SELECT COUNT(DISTINCT o.user_id) AS customer_count 
                FROM orders o
                WHERE o.canteen_id = :canteen_id";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindValue(':canteen_id', $this->canteenId); 

        $customerCount = 0;
        if ($query->execute()) {
            $customerCount = $query->fetchColumn();
        }

        return $customerCount ?? 0;
    }

    function getCompletedOrders()
    {
        $sql = "SELECT COUNT(*) AS completed_orders 
                FROM orders 
                WHERE canteen_id = :canteen_id AND status = 'completed'";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindValue(':canteen_id', $this->canteenId); 

        $completedOrders = 0;
        if ($query->execute()) {
            $completedOrders = $query->fetchColumn();
        }

        return $completedOrders ?? 0;
    }

    function getTopSellingProducts()
    {
        $sql = "SELECT p.name AS product_name, SUM(oi.quantity) AS total_sold
                FROM order_items oi
                JOIN products p ON oi.product_id = p.product_id
                JOIN orders o ON oi.order_id = o.order_id
                WHERE o.canteen_id = :canteen_id 
                AND o.status = 'completed'
                GROUP BY p.name
                ORDER BY total_sold DESC
                LIMIT 10";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindValue(':canteen_id', $this->canteenId); 

        if ($query->execute()) {
            return $query->fetchAll();
        }

        return null;
    }

    function getMonthlySales() {
        $sql = "SELECT MONTH(o.created_at) AS month, SUM(oi.total_price) AS sales
                FROM orders o
                JOIN order_items oi ON o.order_id = oi.order_id
                WHERE o.canteen_id = :canteen_id AND o.status = 'completed'
                GROUP BY MONTH(o.created_at)";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':canteen_id', $this->canteenId);
        $query->execute();
        
        $result = $query->fetchAll();
        
        $sales = array_fill(0, 12, 0); 

        foreach ($result as $row) {
            $sales[$row['month'] - 1] = $row['sales']; 
        }
        
        return $sales;
    }
}
?>
