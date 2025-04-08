<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../api/controllers/admin_controller.php';
require_once __DIR__ . '/../../middlewares/auth_middleware.php';

$database = new Database();
$admin_controller = new AdminController($database);

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri_segments = explode('/', trim($uri, '/'));

$id = isset($uri_segments[2]) ? $uri_segments[2] : null;

switch ($method) {
    case 'GET':
        AuthMiddleware::checkAdmin();
        $admin_controller->getAdmin();
        break;

    case 'POST':
        if(isset($uri_segments[2]) && isset($uri_segments[2]) == "login") {
            $data = json_decode(file_get_contents("php://input"), true);
            $admin_controller->login($data);
        }
        break;

    case 'PUT':
        // Handle PUT requests
        break;

    case 'DELETE':
        // Handle DELETE requests
        break;

    default:
        echo json_encode(["message" => "Invalid request method"]);
        break;
}
