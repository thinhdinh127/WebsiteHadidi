<?php
$config = [
    'db_host' => 'localhost',
    'db_user' => 'root',
    'db_pass' => '',
    'db_name' => 'restaurant',
];

class Database {
    private $conn;
    
    public function __construct() {
        global $config;
        
        $this->conn = new mysqli(
            $config['db_host'],
            $config['db_user'],
            $config['db_pass'],
            $config['db_name']
        );
        
        if ($this->conn->connect_error) {
            die('Database connection failed: ' . $this->conn->connect_error);
        }
    }
    
    public function getConnection() {
        return $this->conn;
    }
}
?>