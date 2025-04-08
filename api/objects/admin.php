<?php
require_once __DIR__ . '/../../utils/helpers.php';
class Admin {
    private $conn;
    private $table_name = "admin";
    private $email;
    private $password;

    function __construct($db)
    {
        $this->conn = $db;
    }

    public function searchByEmail($data)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = ?";

        $stmt = $this->conn->prepare($query);

        $email = isset($data['email']) ? $data['email'] : '';
        $this->email = htmlspecialchars(strip_tags($email));

        $stmt->bind_param("s", $this->email);

        $stmt->execute();
        $result = $stmt->get_result();
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        $result->free();
        $stmt->close();

        return $users;
    }
    public function loginByEmail($data)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = ? AND password = ?";

        $stmt = $this->conn->prepare($query);

        $email = isset($data['email']) ? $data['email'] : '';
        $this->email = htmlspecialchars(strip_tags($email));
        $password = isset($data['password']) ? $data['password'] : '';
        $this->password = htmlspecialchars(strip_tags($password));
        $hashedPassword = HashPassword($password);
        $stmt->bind_param("ss", $this->email, $hashedPassword);

        $stmt->execute();
        $result = $stmt->get_result();
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        $result->free();
        $stmt->close();

        return $users;
    }
}