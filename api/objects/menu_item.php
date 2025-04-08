<?php
class MenuItem {
    private $conn;
    private $table_name = "menu_items";

    public $id;
    public $name;
    public $price;
    public $description;
    public $image;
    public $detail;
    public $category_id;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Phương thức đọc tất cả món ăn
    public function readAll() {
        $query = "SELECT mi.*, c.name as category_name 
                  FROM " . $this->table_name . " mi 
                  LEFT JOIN categories c ON mi.category_id = c.id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Phương thức đọc phân trang
    public function read($page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;

        $query = "SELECT mi.id, mi.name, mi.price, mi.description, mi.image, mi.detail, mi.category_id, mi.status, c.name as category_name 
                  FROM {$this->table_name} mi 
                  LEFT JOIN categories c ON mi.category_id = c.id 
                  ORDER BY mi.id DESC LIMIT ?, ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ii', $offset, $limit);
        $stmt->execute();

        $result = $stmt->get_result();
        $menu_items = [];
        while ($row = $result->fetch_assoc()) {
            $menu_items[] = $row;
        }

        return $menu_items;
    }

    // Đếm tổng số món ăn
    public function count() {
        $query = "SELECT COUNT(*) as total FROM {$this->table_name}";
        $result = $this->conn->query($query);
        $row = $result->fetch_assoc();

        return (int)$row['total'];
    }

    // Đọc một món ăn theo ID
    public function readOne() {
        $query = "SELECT mi.*, c.name as category_name 
                  FROM " . $this->table_name . " mi 
                  LEFT JOIN categories c ON mi.category_id = c.id 
                  WHERE mi.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $this->id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Kiểm tra trạng thái món ăn theo ID
    public function checkStatus() {
        $query = "SELECT id, name, status 
                  FROM " . $this->table_name . " 
                  WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $this->id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Tạo một món ăn mới
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, price, description, image, detail, category_id, status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
    
        $stmt = $this->conn->prepare($query);
    
        // Làm sạch dữ liệu
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->image = htmlspecialchars(strip_tags($this->image));
        $this->detail = htmlspecialchars(strip_tags($this->detail));
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        $this->status = (bool)$this->status; // Đảm bảo status là boolean
    
        // Bind các giá trị (s = string, d = double, i = integer)
        $stmt->bind_param("sdsssii", $this->name, $this->price, $this->description, $this->image, $this->detail, $this->category_id, $this->status);
    
        return $stmt->execute() ? true : false;
    }

    // Cập nhật một món ăn
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET name = ?, 
                      price = ?, 
                      description = ?,
                      image = ?,
                      detail = ?,
                      category_id = ?,
                      status = ?
                  WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        // Làm sạch dữ liệu
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->image = htmlspecialchars(strip_tags($this->image));
        $this->detail = htmlspecialchars(strip_tags($this->detail));
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        $this->status = (bool)$this->status;
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind các giá trị
        $stmt->bind_param("sdsssiii", $this->name, $this->price, $this->description, $this->image, $this->detail, $this->category_id, $this->status, $this->id);
        
        return $stmt->execute() ? true : false;
    }

    // Xóa một món ăn
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        $stmt->bind_param('i', $this->id);
        
        return $stmt->execute() ? true : false;
    }

    // Tìm kiếm món ăn theo tên
    public function searchByName($keywords) {
        $query = "SELECT mi.*, c.name as category_name 
                  FROM " . $this->table_name . " mi 
                  LEFT JOIN categories c ON mi.category_id = c.id 
                  WHERE mi.name LIKE ?";
        
        $stmt = $this->conn->prepare($query);
        
        $keywords = "%{$keywords}%";
        $stmt->bind_param('s', $keywords);
        
        $stmt->execute();
        return $stmt;
    }
}
?>