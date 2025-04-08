<?php
require_once '../config/database.php';
require_once '../api/controllers/menu_controller.php';

// Khởi tạo database và controller
$database = new Database();
$menu_controller = new MenuController($database);

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri_segments = explode('/', trim($uri, '/'));

// Lấy ID từ URL nếu có (ví dụ: /menu/1)
$id = isset($uri_segments[2]) ? intval($uri_segments[2]) : null;

switch ($method) {
    case 'GET':
        if ($id) {
            if (isset($uri_segments[3]) && $uri_segments[3] === 'status') {
                $menu_controller->checkMenuItemStatus($id); // Kiểm tra trạng thái món ăn
            } else {
                $menu_controller->getMenuItemById($id); // Lấy món ăn theo ID
            }
        } elseif (isset($uri_segments[2]) && $uri_segments[2] === 'search') {
            $data = json_decode(file_get_contents("php://input"), true);
            $menu_controller->getMenuItemByName($data); // Tìm kiếm món ăn theo tên
        } else {
            $menu_controller->getMenuItems(); // Lấy danh sách món ăn
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $menu_controller->createMenuItem($data); // Tạo món ăn mới
        break;

    case 'PUT':
        if ($id) {
            $data = json_decode(file_get_contents("php://input"), true);
            $menu_controller->updateMenuItem($id, $data); // Cập nhật món ăn
        } else {
            echo json_encode(["message" => "Yêu cầu ID món ăn"]);
        }
        break;

    case 'DELETE':
        if ($id) {
            $menu_controller->deleteMenuItem($id); // Xóa món ăn
        } else {
            echo json_encode(["message" => "Yêu cầu ID món ăn"]);
        }
        break;

    default:
        echo json_encode(["message" => "Phương thức yêu cầu không hợp lệ"]);
        break;
}
?>