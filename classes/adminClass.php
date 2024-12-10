<?php
require_once '../classes/databaseClass.php';

class Admin
{
    protected $db;

    function __construct()
    {
        $this->db = new Database();
    }

    function reports() {
        $sql_users = "SELECT COUNT(user_id) AS user_count 
                      FROM users 
                      WHERE is_student = 1 OR is_employee = 1";
        
        $query_users = $this->db->connect()->prepare($sql_users);
        $userCount = 0;
        
        if ($query_users->execute()) {
            $userCount = $query_users->fetchColumn();
        }
        
        $sql_users_last_month = "SELECT COUNT(user_id) AS user_count 
                                  FROM users 
                                  WHERE (is_student = 1 OR is_employee = 1)
                                  AND MONTH(created_at) = MONTH(CURRENT_DATE) - 1";
        
        $query_users_last_month = $this->db->connect()->prepare($sql_users_last_month);
        $userCountLastMonth = 0;
        
        if ($query_users_last_month->execute()) {
            $userCountLastMonth = $query_users_last_month->fetchColumn();
        }
    
        $sql_orders = "SELECT COUNT(order_id) AS order_count 
                       FROM orders 
                       WHERE status = 'completed'";
        
        $query_orders = $this->db->connect()->prepare($sql_orders);
        $orderCount = 0;
        
        if ($query_orders->execute()) {
            $orderCount = $query_orders->fetchColumn();
        }
        
        $sql_orders_last_month = "SELECT COUNT(order_id) AS order_count 
                                   FROM orders 
                                   WHERE status = 'completed'
                                   AND MONTH(created_at) = MONTH(CURRENT_DATE) - 1";
        
        $query_orders_last_month = $this->db->connect()->prepare($sql_orders_last_month);
        $orderCountLastMonth = 0;
        
        if ($query_orders_last_month->execute()) {
            $orderCountLastMonth = $query_orders_last_month->fetchColumn();
        }
    
        $sql_canteens = "SELECT COUNT(canteen_id) AS canteen_count 
                         FROM canteens";
        
        $query_canteens = $this->db->connect()->prepare($sql_canteens);
        $canteenCount = 0;
        
        if ($query_canteens->execute()) {
            $canteenCount = $query_canteens->fetchColumn();
        }
        
        $sql_canteens_last_month = "SELECT COUNT(canteen_id) AS canteen_count 
                                     FROM canteens";
        
        $query_canteens_last_month = $this->db->connect()->prepare($sql_canteens_last_month);
        $canteenCountLastMonth = 0;
        
        if ($query_canteens_last_month->execute()) {
            $canteenCountLastMonth = $query_canteens_last_month->fetchColumn();
        }
    
        return [
            'user_count' => $userCount,
            'order_count' => $orderCount,
            'canteen_count' => $canteenCount,
        ];
    }
    
     
    function getTopSellingProducts()
    {
        $sql = "SELECT p.name AS product_name, c.name AS canteen_name, SUM(oi.quantity) AS total_sold
                FROM order_items oi
                JOIN products p ON oi.product_id = p.product_id
                JOIN canteens c ON p.canteen_id = c.canteen_id
                GROUP BY p.name, c.name
                ORDER BY total_sold DESC
                LIMIT 5";
    
        $query = $this->db->connect()->prepare($sql);
    
        if ($query->execute()) {
            return $query->fetchAll();
        } else {
            return null; 
        }
    }
    function getTotalOrdersByCollege()
    {
        $sql = "SELECT p.college, 
                YEAR(o.created_at) AS order_year, 
                MONTH(o.created_at) AS order_month, 
                COUNT(o.order_id) AS total_orders
                FROM orders o
                JOIN programs p ON o.user_id = p.program_id
                WHERE o.status = 'completed' 
                GROUP BY p.college, order_year, order_month
                ORDER BY order_year DESC, order_month DESC, total_orders DESC";
        
        $query = $this->db->connect()->prepare($sql);
        $data = [];
    
        if ($query->execute()) {
            $data = $query->fetchAll();
        }
    
        return $data;
    }
    
}
?>
