<?php
require_once __DIR__ . '/../../utils/helpers.php';
class User
{
    private $conn;
    private $table_name = "user";

    private $id;
    private $username;
    private $password;
    private $email;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function readAll()
    {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function create($data)
    {
        $query = "INSERT INTO " . $this->table_name . " 
                  (id, username, password, email) 
                  VALUES (?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);

        $username = isset($data['username']) ? $data['username'] : '';
        $password = isset($data['password']) ? $data['password'] : '';
        $email = isset($data['email']) ? $data['email'] : '';

        $this->username = htmlspecialchars(strip_tags($username));
        $this->password = htmlspecialchars(strip_tags($password));
        $this->email = htmlspecialchars(strip_tags($email));

        $id = GenerateRandomAlphaNumeric();
        $hashedPassword = HashPassword($this->password);

        $stmt->bind_param("ssss", $id, $this->username, $hashedPassword, $this->email);

        return $stmt->execute() ? true : false;
    }

    public function updateEmail($data)
    {
        $username = isset($data['username']) ? $data['username'] : '';
        $email = isset($data['email']) ? $data['email'] : '';

        $username = htmlspecialchars(strip_tags($username));
        $email = htmlspecialchars(strip_tags($email));
        $query = "UPDATE " . $this->table_name . " SET email = ? WHERE username = ?";

        $stmt = $this->conn->prepare($query);

        $stmt->bind_param("ss", $email, $username);

        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        } else {
            return false;
        }
    }

    public function updatePassword($data)
    {
        $username = isset($data['username']) ? $data['username'] : '';
        $password = isset($data['password']) ? $data['password'] : '';

        $username = htmlspecialchars(strip_tags($username));
        $password = htmlspecialchars(strip_tags($password));
        $query = "UPDATE " . $this->table_name . " SET password = ? WHERE username = ?";

        $stmt = $this->conn->prepare($query);

        $hashedPassword = HashPassword($password);

        $stmt->bind_param("ss", $hashedPassword, $username);

        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        } else {
            return false;
        }
    }

    public function loginByUsername($data)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE username = ? AND password = ?";

        $stmt = $this->conn->prepare($query);

        $username = isset($data['username']) ? $data['username'] : '';
        $this->username = htmlspecialchars(strip_tags($username));
        $password = isset($data['password']) ? $data['password'] : '';
        $this->password = htmlspecialchars(strip_tags($password));
        $hashedPassword = HashPassword($password);
        $stmt->bind_param("ss", $this->username, $hashedPassword);

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


    public function searchByUsername($data)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE username = ?";

        $stmt = $this->conn->prepare($query);

        $username = isset($data['username']) ? $data['username'] : '';
        $this->username = htmlspecialchars(strip_tags($username));
        $stmt->bind_param("s", $this->username);

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

    public function searchById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
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
