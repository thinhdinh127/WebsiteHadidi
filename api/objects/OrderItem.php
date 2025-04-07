<?php

class OrderItem {
    private $conn;
    private $table_name = "order_items";

    public int $id;
    public string $orderId;
    public int $menuItemId;
    public string $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (order_id, menu_item_id, status) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }
        
        $stmt->bind_param("sis", $this->orderId, $this->menuItemId, $this->status);
        $result = $stmt->execute();
        
        if ($result === false) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $stmt->close();
        return $result;
    }
    // Read all order items
    public function read() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    // Delete an order item
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }  
    public function deleteByOrderId($orderId) {
        $query = "DELETE FROM order_items WHERE order_id = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Prepare failed: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("s", $orderId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    public function update() {
        $query = "UPDATE order_items 
                  SET menu_item_id = ?, status = ? 
                  WHERE id = ? AND order_id = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Prepare failed: " . $this->conn->error);
            return false;
        }
    
        $stmt->bind_param("isis", $this->menuItemId, $this->status, $this->id, $this->orderId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    public function deleteByOrderItemId($orderItemId) {
        $query = "DELETE FROM order_items WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        
        if ($stmt === false) {
            error_log("Prepare failed: " . $this->conn->error);
            return false;
        }
    
        $stmt->bind_param("i", $orderItemId);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
}

?>
