<?php
require_once __DIR__ . '/../utils/jwt.php';
require_once __DIR__ . '/../utils/response.php';

class AuthMiddleware
{
    public static function verifyToken()
    {
        $headers = getallheaders();
        if (!isset($headers["Authorization"])) {
            http_response_code(401);
            echo json_encode(["error" => "Token không được cung cấp"]);
            exit();
        }

        $token = str_replace("Bearer ", "", $headers["Authorization"]);
        $decoded = (array) JWTHandler::verifyToken($token);

        if (isset($decoded["error"])) {
            http_response_code(401);
            echo json_encode(["error" => $decoded["error"]]);
            exit();
        }

        return $decoded;
    }

    public static function checkAdmin() {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            APIResponse::json(401, "Unauthorized", ["error" => "Token is required"]);
            exit;
        }

        $token = str_replace("Bearer ", "", $headers['Authorization']);
        $decoded = JWTHandler::verifyToken($token);

        if (!$decoded) {
            APIResponse::json(401, "Unauthorized", ["error" => "Invalid token"]);
            exit;
        }

        if (!isset($decoded->role) || $decoded->role !== 'ADMIN') {
            APIResponse::json(403, "Forbidden", ["error" => "Access denied"]);
            exit;
        }
    }

    public static function checkUser() {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            APIResponse::json(401, "Unauthorized", ["error" => "Token is required"]);
        }

        $token = str_replace("Bearer ", "", $headers['Authorization']);
        $decoded = JWTHandler::verifyToken($token);

        if (!$decoded) {
            APIResponse::json(401, "Unauthorized", ["error" => "Invalid token"]);
        }

        // Nếu không có role hoặc role không phải "user" hoặc "admin", từ chối truy cập
        if (!isset($decoded->role) || !in_array($decoded->role, ['user', 'admin'])) {
            APIResponse::json(403, "Forbidden", ["error" => "Access denied"]);
        }

        return $decoded; // Trả về thông tin người dùng
    }}
