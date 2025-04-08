<?php
class Review {
    private $conn;
    private $table_name = "reviews";
    // Object properties
    public $id;
    public $customerName;
    public $rating;
    public $title;
    public $content;
    public $date;
    public $average_rating;    // Added for getAverageRating method
    public $total_reviews;     // Added for getAverageRating method

    public function __construct($db) {
        $this->conn = $db;
    }
    // Create review
    public function create() {
        // Validate required fields
        if(empty($this->customerName) || empty($this->rating) || empty($this->title) || empty($this->content)) {
            return false;
        }

        $query = "INSERT INTO " . $this->table_name . " 
                (customerName, rating, title, content, date) 
                VALUES 
                (?, ?, ?, ?, NOW())";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->customerName = $this->conn->real_escape_string($this->customerName);
        $this->rating = (int)$this->rating;
        $this->title = $this->conn->real_escape_string($this->title);
        $this->content = $this->conn->real_escape_string($this->content);

        // Bind values
        $stmt->bind_param("siis", $this->customerName, $this->rating, $this->title, $this->content);

        if($stmt->execute()) {
            $this->id = $this->conn->insert_id;
            return true;
        }
        return false;
    }

    // Read all reviews
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY date DESC";
        $result = $this->conn->query($query);
        
        if($result && $result->num_rows > 0) {
            $reviews = array();
            while($row = $result->fetch_assoc()) {
                $reviews[] = $row;
            }
            return $reviews;
        }
        return array();
    }

    // Read single review
    public function readOne() {
        if(empty($this->id)) {
            return null;
        }

        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }

    // Update review
    public function update() {
        // Validate required fields
        if(empty($this->id) || empty($this->customerName) || empty($this->rating) || empty($this->title) || empty($this->content)) {
            return false;
        }

        $query = "UPDATE " . $this->table_name . "
                SET customerName = ?, rating = ?, title = ?, content = ?
                WHERE id = ?";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->customerName = $this->conn->real_escape_string($this->customerName);
        $this->rating = (int)$this->rating;
        $this->title = $this->conn->real_escape_string($this->title);
        $this->content = $this->conn->real_escape_string($this->content);
        $this->id = (int)$this->id;

        // Bind values
        $stmt->bind_param("siisi", $this->customerName, $this->rating, $this->title, $this->content, $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete review
    public function delete() {
        if(empty($this->id)) {
            return false;
        }

        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Get average rating
    public function getAverageRating() {
        $query = "SELECT AVG(rating) as average_rating, COUNT(*) as total_reviews 
                FROM " . $this->table_name;
        $result = $this->conn->query($query);
        
        if($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $this->average_rating = round($row['average_rating'], 1);
            $this->total_reviews = $row['total_reviews'];
            return $this;
        }
        return false;
    }

    // Convert to array
    public function toArray() {
        return array(
            "id" => $this->id,
            "customerName" => $this->customerName,
            "rating" => $this->rating,
            "title" => $this->title,
            "content" => $this->content,
            "date" => $this->date
        );
    }
}
?> 