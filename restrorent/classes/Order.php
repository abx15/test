<?php
class Order {
    private $conn;
    private $table_name = "orders";

    public $id;
    public $order_number;
    public $customer_name;
    public $customer_email;
    public $customer_phone;
    public $customer_address;
    public $order_type;
    public $total_amount;
    public $status;
    public $special_instructions;
    public $delivery_time;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new order
    public function create() {
        $this->conn->beginTransaction();
        
        try {
            // Insert order
            $query = "INSERT INTO " . $this->table_name . " 
                      SET order_number=:order_number, customer_name=:customer_name, 
                          customer_email=:customer_email, customer_phone=:customer_phone,
                          customer_address=:customer_address, order_type=:order_type,
                          total_amount=:total_amount, status=:status,
                          special_instructions=:special_instructions, delivery_time=:delivery_time";

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':order_number', $this->order_number);
            $stmt->bindParam(':customer_name', $this->customer_name);
            $stmt->bindParam(':customer_email', $this->customer_email);
            $stmt->bindParam(':customer_phone', $this->customer_phone);
            $stmt->bindParam(':customer_address', $this->customer_address);
            $stmt->bindParam(':order_type', $this->order_type);
            $stmt->bindParam(':total_amount', $this->total_amount);
            $stmt->bindParam(':status', $this->status);
            $stmt->bindParam(':special_instructions', $this->special_instructions);
            $stmt->bindParam(':delivery_time', $this->delivery_time);

            $stmt->execute();
            $this->id = $this->conn->lastInsertId();
            
            $this->conn->commit();
            return true;
            
        } catch(Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    // Add order items
    public function addItems($items) {
        try {
            $query = "INSERT INTO order_items 
                      SET order_id=:order_id, menu_item_id=:menu_item_id, 
                          item_name=:item_name, item_price=:item_price,
                          quantity=:quantity, subtotal=:subtotal, special_notes=:special_notes";

            $stmt = $this->conn->prepare($query);

            foreach($items as $item) {
                $stmt->bindParam(':order_id', $this->id);
                $stmt->bindParam(':menu_item_id', $item['menu_item_id']);
                $stmt->bindParam(':item_name', $item['item_name']);
                $stmt->bindParam(':item_price', $item['item_price']);
                $stmt->bindParam(':quantity', $item['quantity']);
                $stmt->bindParam(':subtotal', $item['subtotal']);
                $stmt->bindParam(':special_notes', $item['special_notes']);
                $stmt->execute();
            }
            
            return true;
        } catch(Exception $e) {
            return false;
        }
    }

    // Get all orders
    public function getAll($status = null, $limit = null) {
        $query = "SELECT * FROM " . $this->table_name;
        
        if ($status) {
            $query .= " WHERE status = :status";
        }
        
        $query .= " ORDER BY created_at DESC";
        
        if ($limit) {
            $query .= " LIMIT :limit";
        }

        $stmt = $this->conn->prepare($query);
        
        if ($status) {
            $stmt->bindParam(':status', $status);
        }
        
        if ($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt;
    }

    // Get order by ID with items
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt;
    }

    // Get order items
    public function getOrderItems($order_id) {
        $query = "SELECT * FROM order_items WHERE order_id = :order_id ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $order_id);
        $stmt->execute();
        return $stmt;
    }

    // Update order status
    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status=:status, updated_at=CURRENT_TIMESTAMP 
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Get order statistics
    public function getStats() {
        $query = "SELECT 
                    COUNT(*) as total_orders,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_orders,
                    COUNT(CASE WHEN status = 'confirmed' THEN 1 END) as confirmed_orders,
                    COUNT(CASE WHEN status = 'preparing' THEN 1 END) as preparing_orders,
                    COUNT(CASE WHEN status = 'ready' THEN 1 END) as ready_orders,
                    COUNT(CASE WHEN status = 'delivered' THEN 1 END) as delivered_orders,
                    SUM(CASE WHEN status != 'cancelled' THEN total_amount ELSE 0 END) as total_revenue
                  FROM " . $this->table_name;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>
