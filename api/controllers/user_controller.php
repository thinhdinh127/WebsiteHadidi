<?php
require_once __DIR__ . '/../../api/objects/user.php';
require_once __DIR__ . '/../../utils/validator.php';
require_once __DIR__ . '/../../utils/response.php';
require_once __DIR__ . '/../../middlewares/auth_middleware.php';
require_once __DIR__ . '/../../middlewares/validate_middlware.php';
require_once __DIR__ . '/../../utils/jwt.php';
require_once __DIR__ . '/../../services/email_service.php';

class UserController
{
    private $db;
    private $user;

    public function __construct($database)
    {
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    public function getUser()
    {
        $userData = AuthMiddleware::verifyToken();
        if (count((array) $userData) == 0) {
            http_response_code(401);
            return APIResponse::error("Unauthorized");
        } else {
            return APIResponse::success($userData);
        }
    }

    public function getUsers()
    {
        $stmt = $this->user->readAll(PDO::FETCH_ASSOC);
        $users_arr = array();
        $result = $stmt->get_result();

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        foreach ($users as $row) {
            $user_item = array(
                "id" => $row['id'],
                "username" => $row['username'],
                "password" => $row['password'],
                "email" => $row['email'],
            );
            array_push($users_arr, $user_item);
        }

        http_response_code(200);
        echo json_encode($users_arr);
    }

    public function createUser($data)
    {
        $requiredFields = ['username', 'password', 'password'];

        if (!ValidateMiddleware::handle($data, $requiredFields)) {
            return;
        }

        if (!Validator::validateUsername($data['username'])) {
            return APIResponse::error("Username phải có ít nhất 8 ký tự");
        }

        if (!Validator::validatePassword($data['password'])) {
            return APIResponse::error("Password phải có ít nhất 8 ký tự, chứa chữ hoa, chữ thường, số và ký tự đặc biệt.");
        }

        if (!Validator::validateEmail($data['email'])) {
            return APIResponse::error("Email không hợp lệ.");
        }

        $stmt = $this->user->searchByUsername($data);
        if (count($stmt) != 0) {
            return APIResponse::error("Username đã tồn tại.");
        }

        $stmt = $this->user->searchByEmail($data);
        if (count($stmt) != 0) {
            return APIResponse::error("Email đã tồn tại.");
        }


        if ($this->user->create($data)) {
            http_response_code(201);
            echo json_encode("Người dùng được tạo thành công.");
        
        } else {
            http_response_code(503);
            echo json_encode("Không thể tạo tài khoản.");
        }
        
    }

    public function login($data)
    {
        $requiredFields = ['password'];

        if (!ValidateMiddleware::handle($data, $requiredFields)) {
            return;
        }
        $stmt = $this->user->searchByUsername($data);
        if (count($stmt) != 0) {
            $stmt = $this->user->loginByUsername($data);
        } else {
            $stmt = $this->user->searchByEmail($data);
            if (count($stmt) != 0) {
                $stmt = $this->user->loginByEmail($data);
            } else {
                return APIResponse::error("Tài khoản không tồn tại.");
            }
        }
        if (count($stmt) == 0) {
            return APIResponse::error("Password incorrect");
        }
        $token = JWTHandler::generateToken($stmt[0]["id"], $stmt[0]["email"], $stmt[0]["username"]);

        return APIResponse::success($token);
    }

    public function updateUser($data)
    {
        $requiredFields = ['username', 'password'];

        if (!ValidateMiddleware::handle($data, $requiredFields)) {
            return;
        }
        $stmt = $this->user->searchByUsername($data);
        if (count($stmt) == 0) {
            return APIResponse::error("Username không tồn tại.");
        }
        $stmt = $this->user->searchByEmail($data);
        if (count($stmt) != 0) {
            return APIResponse::error("Email đã tồn tại.");
        }
        $stmt = $this->user->updateEmail($data);
        if (!$stmt) {
            return APIResponse::error("Không thể cập nhật thông tin người dùng.");
        }
        return APIResponse::success($stmt);
    }

    public function forgetPassword($data)
    {
        $requiredFields = ['email'];

        if (!ValidateMiddleware::handle($data, $requiredFields)) {
            return;
        }
        $requiredFields = ['to', 'sub', 'body'];

        if (!ValidateMiddleware::handle($data, $requiredFields)) {
            return;
        }
        $stmt = $this->user->searchByEmail($data);
        if (count($stmt) == 0) {
            return APIResponse::error("Email không tồn tại.");
        }
        $to = isset($data["to"]) ? $data["to"] : null;
        $sub = isset($data["sub"]) ? $data["sub"] : null;
        $body = isset($data["body"]) ? $data["body"] : null;

        $token = JWTHandler::generateTokenForResetPass();
        $newBody = $body . $token;
        $emailService = new EmailService();
        $emailService->sendEmail($to, $sub, $newBody);
        return APIResponse::success("Đã gửi email reset Password");
    }

    public function resetPassword($data)
    {
        $requiredFields = ['username', 'password', 'repassword'];

        if (!ValidateMiddleware::handle($data, $requiredFields)) {
            return ;
        }
        $userData = AuthMiddleware::verifyToken();
        if (count((array) $userData) == 0) {
            return APIResponse::error("Token không hợp lệ.");
        }
        $data["username"] = $userData["username"];
        if ($data["password"] != $data["repassword"]) {
            return APIResponse::error("Mật khẩu không khớp.");
        }
        $stmt = $this->user->updatePassword($data);
        if (!$stmt) {
            return APIResponse::error("Không thể cập nhật thông tin người dùng.");
        }
    }
}
