ALTER TABLE users ADD COLUMN status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending' AFTER role; 

UPDATE users SET role = 'manager' WHERE is_manager = 1 AND role IS NULL;
UPDATE users SET role = 'admin' WHERE is_admin = 1 AND role IS NULL;
UPDATE users SET role = 'student' WHERE is_student = 1 AND role IS NULL;
UPDATE users SET role = 'employee' WHERE is_employee = 1 AND role IS NULL; 

-- Make sure orders table has the correct structure
CREATE TABLE IF NOT EXISTS orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    canteen_id INT NOT NULL,
    status ENUM('pending','accepted','cancelled','completed') DEFAULT 'pending',
    queue_number VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (canteen_id) REFERENCES canteens(canteen_id)
);

-- Add canteen_id if it's missing
ALTER TABLE orders 
ADD COLUMN IF NOT EXISTS canteen_id INT NOT NULL AFTER user_id,
ADD FOREIGN KEY (canteen_id) REFERENCES canteens(canteen_id); 

-- Add canteen_id to order_items table
ALTER TABLE order_items 
ADD COLUMN canteen_id INT,
ADD FOREIGN KEY (canteen_id) REFERENCES canteens(canteen_id);

-- Update existing order_items with canteen_id from products
UPDATE order_items oi
JOIN products p ON oi.product_id = p.product_id
SET oi.canteen_id = p.canteen_id; 

ALTER TABLE cart_items
ADD FOREIGN KEY (product_id) REFERENCES products(product_id); 

-- Remove duplicate foreign key constraints
ALTER TABLE orders DROP CONSTRAINT orders_ibfk_2;
ALTER TABLE orders DROP CONSTRAINT orders_ibfk_3;

-- Add the correct constraint
ALTER TABLE orders 
  ADD CONSTRAINT orders_ibfk_2 FOREIGN KEY (canteen_id) 
  REFERENCES canteens (canteen_id) ON DELETE SET NULL;

-- Remove duplicate foreign key constraints for order_items
ALTER TABLE order_items DROP CONSTRAINT order_items_ibfk_1;
ALTER TABLE order_items DROP CONSTRAINT order_items_ibfk_2;

-- Add the correct constraints
ALTER TABLE order_items 
  ADD CONSTRAINT order_items_ibfk_1 FOREIGN KEY (canteen_id) 
  REFERENCES canteens (canteen_id) ON DELETE SET NULL;