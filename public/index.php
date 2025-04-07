<?php
// Cấu hình headers để hỗ trợ CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$request_uri = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
$resource = isset($request_uri[1]) ? $request_uri[1] : null;

switch ($resource) {
    case 'users':
        require_once '../api/routes/user_routes.php';
        break;
    case 'order':
        require_once '../api/routes/order_route.php';
        break;
    case 'products':
        require_once '../api/routes/menu_routes.php';
        break;
    case 'reviews':
        require_once '../api/routes/review_routes.php';
        break;
    case 'admin':
        require_once '../api/routes/admin_routes.php';
        break;
    default:
        echo json_encode(["message" => "Invalid request method"]);
        break;
}
?>