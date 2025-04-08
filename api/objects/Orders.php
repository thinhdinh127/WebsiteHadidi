<?php

class Orders
{
    private $conn;
    private $table_name = "orders";
    public string $id;
    public string $userId; // Chuyển sang kiểu string
    public array $items;
    public float $totalPrice;
    public int $numPeople;
    public string $specialRequest;
    public string $customerName;
    public string $orderDate;
    public string $orderTime;
    public string $style_tiec;
    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        // Truy vấn SQL chèn dữ liệu vào bảng "orders"
        $query = "INSERT INTO " . $this->table_name . " (id, user_id, total_price, num_people, special_request, customer_name, order_date, order_time,style_tiec) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?,?)";

        // Chuẩn bị truy vấn
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }
        $randomId = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'), 0, 6);
        $this->id = $randomId; 
        $stmt->bind_param(
            "ssdisssss",  // Chuỗi định nghĩa kiểu, gồm 9 ký tự
            $this->id,    // id (string)
            $this->userId, // user_id (string)
            $this->totalPrice, // total_price (double)
            $this->numPeople, // num_people (integer)
            $this->specialRequest, // special_request (string)
            $this->customerName, // customer_name (string)
            $this->orderDate, // order_date (string)
            $this->orderTime,  // order_time (string)
            $this->style_tiec // style_tiec (string)
        );

        // Thực thi truy vấn
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        // Đóng kết nối
        $stmt->close();
        return true;
    }

    // Lấy tất cả đơn hàng
    public function read()
    {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Lấy đơn hàng theo ID
    public function readOne()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE order_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $this->id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Cập nhật đơn hàng
    public function update()
    {
        $query = "UPDATE " . $this->table_name . " 
                  SET user_id = ?, total_price = ?, num_people = ?, special_request = ?, customer_name = ?
                  WHERE id = ?";

        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param(
            "sdissi",
            $this->userId,
            $this->totalPrice,
            $this->numPeople,
            $this->specialRequest,
            $this->customerName,
            $this->id
        );

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $stmt->close();
        return true;
    }

    // Xóa đơn hàng
    public function delete()
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("s", $this->id);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $stmt->close();
        return true;
    }

    // Lấy tất cả đơn hàng với thông tin người dùng
    public function readAll()
    {
        $query = "SELECT o.id, o.user_id, u.username, u.email, o.total_price, o.num_people, 
                         o.special_request, o.customer_name, o.order_date, o.order_time, o.status , o.style_tiec
                  FROM " . $this->table_name . " o
                  JOIN user u ON o.user_id = u.id";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Lấy đơn hàng theo ID với thông tin người dùng
    public function readById($id)
    {
        $query = "SELECT o.id, o.user_id, u.username, u.email, o.total_price, o.num_people, 
                         o.special_request, o.customer_name, o.order_date, o.order_time,o.status ,o.style_tiec
                  FROM orders o
                  JOIN user u ON o.user_id = u.id
                  WHERE o.id = ?";

        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Lỗi chuẩn bị truy vấn: " . $this->conn->error);
        }

        $stmt->bind_param("s", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Thay đổi trạng thái đơn hàng
    public function changeStatus($id, $newStatus)
    {
        $query = "UPDATE " . $this->table_name . " SET status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("is", $newStatus, $id);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        return true;
    }
    
}
