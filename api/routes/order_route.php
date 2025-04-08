<?php
require_once '../config/database.php';
require_once '../api/controllers/Order_Controller.php';

// Khởi tạo database và controller
$database = new Database();
$orderController = new OrderController($database);

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri_segments = explode('/', trim($uri, '/'));

// Lấy ID từ URL nếu có (ví dụ: /orders/1)
$id = isset($uri_segments[2]) ? intval($uri_segments[2]) : null;

switch ($method) {
    case 'GET':
        // Lấy URI và loại bỏ dấu "/" ở đầu và cuối
        $path = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
    
        // Tìm vị trí của "order" trong URL
        $index = array_search('order', $path);
    
        // Nếu "order" tồn tại và có một phần tử tiếp theo, kiểm tra xem nó có phải là ID hợp lệ không
        if ($index !== false && isset($path[$index + 1])) {
            $id = $path[$index + 1];
    
            // Kiểm tra nếu ID có đúng 6 ký tự chữ/số (ví dụ: 'I06LB5')
            if (preg_match('/^[A-Za-z0-9]{6}$/', $id)) {
                $orderController->getOrderById($id);
                break;
            }
        }
    
        // Nếu không có ID, lấy toàn bộ danh sách đơn hàng
        $orderController->getOrders();
        break;
    
    

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $requestUri = $_SERVER['REQUEST_URI']; // Định nghĩa $requestUri trước khi sử dụng

        if (preg_match('/order\/items\/(\d+)/', $requestUri, $matches)) {
            $orderId = (int) $matches[1]; // Lấy ID từ URL
            $orderController->addOrderItems($orderId, $data); // Thêm order items vào đơn hàng
        } else {
            $orderController->createOrder($data); // Tạo đơn hàng mới
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        if (!is_array($data)) {
            http_response_code(400);
            echo json_encode(["message" => "Dữ liệu đầu vào không hợp lệ"]);
            break;
        }

        // Nếu URL có dạng: /restapirestaurant/order/status/{id}
        if (isset($uri_segments[2]) && $uri_segments[2] === 'status') {
            if (isset($uri_segments[3]) && preg_match('/^[A-Za-z0-9]{6}$/', $uri_segments[3])) {
                $orderId = $uri_segments[3]; // ID có 6 ký tự chữ và số
                if (isset($data['newStatus'])) {
                    $orderController->updateOrderStatus($orderId, $data['newStatus']);
                } else {
                    http_response_code(400);
                    echo json_encode(["message" => "Thiếu tham số newStatus"]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Thiếu hoặc sai định dạng order_id trong URL"]);
            }
        }
        // Nếu URL có dạng: /restapirestaurant/order/{id} -> cập nhật thông tin đơn hàng
        else if (isset($uri_segments[2]) && preg_match('/^[A-Za-z0-9]{6}$/', $uri_segments[2])) {
            $orderId = $uri_segments[2]; // ID có 6 ký tự chữ và số
            $orderController->updateOrder($orderId, $data);
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Thiếu hoặc sai định dạng order_id trong URL"]);
        }
        break;


    case 'DELETE':
        if (isset($uri_segments[1]) && $uri_segments[1] === 'order' && isset($uri_segments[2])) {
            if ($uri_segments[2] === 'item' && isset($uri_segments[3])) {
                $orderItemId = intval($uri_segments[3]);
                $orderController->deleteOrderItem($orderItemId);
            } else {
                $orderId = intval($uri_segments[2]);
                $orderController->deleteOrder((object)["orderId" => $orderId]);
            }
        } else {
            http_response_code(400); // Bad Request
            echo json_encode(["message" => "Order ID or Item ID required"]);
        }
        break;

    default:
        echo json_encode(["message" => "Invalid request method"]);
        break;
}
