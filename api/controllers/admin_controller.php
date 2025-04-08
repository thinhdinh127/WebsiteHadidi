<?php
require_once __DIR__ . '/../../api/objects/admin.php';
require_once __DIR__ . '/../../utils/validator.php';
require_once __DIR__ . '/../../utils/response.php';
require_once __DIR__ . '/../../middlewares/auth_middleware.php';
require_once __DIR__ . '/../../middlewares/validate_middlware.php';
require_once __DIR__ . '/../../utils/jwt.php';
require_once __DIR__ . '/../../services/email_service.php';
class AdminController
{
    private $db;
    private $admin;

    public function __construct($database)
    {
        $this->db = $database->getConnection();
        $this->admin = new Admin($this->db);
    }

    public function login($data)
    {
        $requiredFields = ['email', 'password'];

        if (!ValidateMiddleware::handle($data, $requiredFields)) {
            return;
        }
        // check email
        $stmt = $this->admin->searchByEmail($data);
        if (count($stmt) != 0) {
            $stmt = $this->admin->loginByEmail($data);
        } else {
            return APIResponse::error("Tài khoản không tồn tại.");
        }
        if (count($stmt) == 0) {
            return APIResponse::error("Password incorrect");
        }
        $token = JWTHandler::generateTokenForAdmin($stmt[0]["email"], $stmt[0]["password"], $stmt[0]["role"]);

        return APIResponse::success($token);
    }

    public function getAdmin() {
        $admin = AuthMiddleware::verifyToken();
        if (count((array) $admin) == 0) {
            http_response_code(401);
            return APIResponse::error("Unauthorized");
        } else {
            return APIResponse::success($admin);
        }
    }
}
