<?php
require_once '../api/objects/Orders.php';
require_once '../api/objects/OrderItem.php'; // Thêm dòng này để include OrderItem class

class OrderController
{
    private $db;
    private $order;
    private $orderItem; // Thêm biến này để xử lý order items

    public function __construct($database)
    {
        $this->db = $database->getConnection();
        $this->order = new Orders($this->db);
        $this->orderItem = new OrderItem($this->db); // Khởi tạo OrderItem
    }
    public function getOrders()
    {
        try {
            // Lấy tất cả orders
            $orders = $this->order->readAll();

            if ($orders && !empty($orders)) {
                $orders_array = [];

                foreach ($orders as $order) {
                    if (!isset($order['id'])) {
                        continue;
                    }

                    // Chuẩn bị query cho order items với MySQLi
                    $query = "SELECT oi.id, oi.order_id, oi.menu_item_id, oi.status 
                             FROM order_items oi 
                             WHERE oi.order_id = ?";

                    $stmt = $this->db->prepare($query);

                    if ($stmt === false) {
                        throw new Exception("Failed to prepare statement: " . $this->db->error);
                    }

                    // Bind parameter với MySQLi
                    $stmt->bind_param("s", $order['id']);
                    $stmt->execute();

                    // Lấy kết quả với MySQLi
                    $result = $stmt->get_result();
                    $order_items = $result->fetch_all(MYSQLI_ASSOC);

                    // Thêm order items vào dữ liệu order
                    $order['order_items'] = $order_items ? $order_items : [];
                    $orders_array[] = $order;

                    $stmt->close();
                }

                http_response_code(200);
                echo json_encode($orders_array);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Không tìm thấy đơn hàng."]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "message" => "Lỗi khi lấy dữ liệu đơn hàng",
                "error" => $e->getMessage()
            ]);
        }
    }
    public function createOrder()
    {
        try {
            // Nhận dữ liệu từ JSON
            $jsonData = json_decode(file_get_contents("php://input"), true);
            if (!is_array($jsonData) || !isset($jsonData['user_id'])) {
                throw new Exception("Dữ liệu đầu vào không hợp lệ: user_id là bắt buộc.");
            }
            // Kiểm tra user_id có tồn tại không
            $userId = $this->db->real_escape_string($jsonData['user_id']);
            $userCheckQuery = "SELECT id FROM user WHERE id = ?";
            $stmt = $this->db->prepare($userCheckQuery);
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 0) {
                throw new Exception("User ID không tồn tại: " . $userId);
            }
            $stmt->close();
            $this->order->userId = $userId;
            $this->order->totalPrice = (float) ($jsonData['total_price'] ?? 0.00);
            $this->order->numPeople = (int) ($jsonData['num_people'] ?? 1);
            $this->order->specialRequest = $jsonData['special_request'] ?? "";
            $this->order->customerName = $jsonData['customer_name'] ?? "Khách hàng";
            $this->order->orderDate = $jsonData['order_date'] ?? date("Y-m-d");
            $this->order->orderTime = $jsonData['order_time'] ?? date("H:i:s");
            $this->order->style_tiec = $jsonData['style_tiec'] ?? "Đặt bàn thường";
    
            // Bắt đầu transaction
            $this->db->begin_transaction();
    
            // Tạo đơn hàng
            if (!$this->order->create()) {
                throw new Exception("Không thể tạo đơn hàng.");
            }
    
            // Lấy ID đơn hàng vừa tạo
            $orderId = $this->order->id;
            if (!$orderId) {
                throw new Exception("Không thể lấy ID của đơn hàng vừa tạo." . gettype($orderId));
            }
    
            // Thêm sản phẩm vào order_items nếu có
            if (!empty($jsonData['order_items']) && is_array($jsonData['order_items'])) {
                foreach ($jsonData['order_items'] as $item) {
                    if (!isset($item['menu_item_id'])) {
                        throw new Exception("Dữ liệu item không hợp lệ: menu_item_id là bắt buộc.");
                    }
                    $this->orderItem->orderId = $orderId;
                    $this->orderItem->menuItemId = (int) $item['menu_item_id'];
                    $this->orderItem->status = $item['status'] ?? 'pending';
                    if (!$this->orderItem->create()) {
                        throw new Exception("Không thể tạo order item cho menu_item_id: " . $item['menu_item_id']);
                    }
                }
            }
    
            // Commit transaction
            $this->db->commit();
            http_response_code(201);
            echo json_encode([
                "message" => "Đơn hàng đã được tạo.",
                "order_id" => $orderId
            ]);
    
        } catch (Exception $e) {
            $this->db->rollback();
            http_response_code(503);
            echo json_encode([
                "message" => "Không thể tạo đơn hàng.",
                "error" => $e->getMessage()
            ]);
        }
    }
    

public function updateOrder($orderId, $data)
{
    try {
        // Kiểm tra JSON đầu vào
        if (!is_array($data)) {
            throw new Exception("Dữ liệu JSON không hợp lệ.");
        }

        if (empty($data['user_id'])) {
            throw new Exception("Dữ liệu đầu vào không hợp lệ: user_id là bắt buộc.");
        }

        // Lấy user_id và kiểm tra sự tồn tại
        $userId = $this->db->real_escape_string($data['user_id']);
        $stmt = $this->db->prepare("SELECT id FROM user WHERE id = ?");
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception("User ID không tồn tại: " . $userId);
        }
        $stmt->close();

        // Gán giá trị cho đối tượng order
        $this->order->id = (int) $orderId;
        $this->order->userId = $userId;
        $this->order->totalPrice = isset($data['total_price']) ? (float) $data['total_price'] : $this->order->totalPrice;
        $this->order->numPeople = isset($data['num_people']) ? (int) $data['num_people'] : $this->order->numPeople;
        $this->order->specialRequest = $data['special_request'] ?? $this->order->specialRequest;
        $this->order->customerName = $data['customer_name'] ?? $this->order->customerName;

        // Bắt đầu transaction
        $this->db->begin_transaction();

        // Cập nhật thông tin order
        if (!$this->order->update()) {
            throw new Exception("Không thể cập nhật đơn hàng.");
        }

        // Nếu có order_items, xử lý cập nhật hoặc thêm mới
        if (!empty($data['order_items']) && is_array($data['order_items'])) {
            foreach ($data['order_items'] as $item) {
                if (empty($item['menu_item_id'])) {
                    throw new Exception("Dữ liệu order_items không hợp lệ: menu_item_id là bắt buộc.");
                }

                $orderItemId = $item['id'] ?? null;
                $menuItemId = (int) $item['menu_item_id'];
                $status = $item['status'] ?? 'pending';

                if ($orderItemId) {
                    // Cập nhật item đã có ID
                    $this->orderItem->id = (int) $orderItemId;
                    $this->orderItem->orderId = $orderId;
                    $this->orderItem->menuItemId = $menuItemId;
                    $this->orderItem->status = $status;

                    if (!$this->orderItem->update()) {
                        throw new Exception("Không thể cập nhật order item ID: " . $orderItemId);
                    }
                } else {
                    // Thêm mới item nếu chưa có ID
                    $this->orderItem->orderId = $orderId;
                    $this->orderItem->menuItemId = $menuItemId;
                    $this->orderItem->status = $status;

                    if (!$this->orderItem->create()) {
                        throw new Exception("Không thể tạo order item cho menu_item_id: " . $menuItemId);
                    }
                }
            }
        }

        // Commit transaction nếu không có lỗi
        $this->db->commit();
        http_response_code(200);
        echo json_encode([
            "message" => "Đơn hàng và order_items đã được cập nhật thành công.",
            "order_id" => $orderId
        ]);
    } catch (Exception $e) {
        // Rollback nếu có lỗi
        $this->db->rollback();
        http_response_code(503);
        echo json_encode([
            "message" => "Không thể cập nhật đơn hàng.",
            "error" => $e->getMessage()
        ]);
    }
}


    public function deleteOrder($orderId)
    {
        if (is_object($orderId) && isset($orderId->orderId)) {
            $orderIdValue = $orderId->orderId;
        } else {
            $orderIdValue = $orderId;
        }

        if (!is_numeric($orderIdValue)) {
            http_response_code(400);
            echo json_encode(["message" => "ID đơn hàng không hợp lệ."]);
            return;
        }
        $this->order->id = (int) $orderIdValue;
        if ($this->order->delete()) {
            http_response_code(200);
            echo json_encode(["message" => "Đơn hàng đã được xoá."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Không thể xoá đơn hàng."]);
        }
    }
    public function deleteOrderItem($orderItemId)
    {
        try {
            if (!$this->orderItem->deleteByOrderItemId($orderItemId)) {
                throw new Exception("Không thể xóa order_item.");
            }

            http_response_code(200);
            echo json_encode(["message" => "Order item đã được xóa.", "order_item_id" => $orderItemId]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["message" => "Lỗi khi xóa order item.", "error" => $e->getMessage()]);
        }
    }
    public function updateOrderStatus($orderId, $newStatus)
{
    try {
        // Get the current status of the order
        $orderDetails = $this->order->readById($orderId);
        $currentStatus = $orderDetails['status'];
        // Check if the current status is 0 (Chờ duyệt)
        if ($currentStatus !== 0) {
            throw new Exception("Chỉ có thể cập nhật trạng thái khi trạng thái hiện tại là Chờ duyệt (0).");
        }

        // Update the order status
        if ($this->order->changeStatus($orderId, $newStatus)) {
            http_response_code(200);
            echo json_encode([
                "message"   => "Trạng thái đơn hàng đã được cập nhật thành công.",
                "newStatus" => $newStatus
            ]);
        } else {
            throw new Exception("Cập nhật trạng thái đơn hàng thất bại.");
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            "message" => "Lỗi khi cập nhật trạng thái đơn hàng.",
            "error"   => $e->getMessage()
        ]);
    }
}


    public function getOrdersByStatus($status)
    {
        
    }
    
    public function addOrderItems($orderId, $data)
    {
        try {
            if (!is_numeric($orderId) || $orderId <= 0) {
                throw new Exception("ID đơn hàng không hợp lệ.");
            }

            if (!isset($data['items']) || !is_array($data['items'])) {
                throw new Exception("Dữ liệu đầu vào không hợp lệ: items là bắt buộc.");
            }

            $this->db->begin_transaction();

            foreach ($data['items'] as $item) {
                if (!isset($item['menu_item_id']) || !isset($item['status'])) {
                    throw new Exception("Dữ liệu item không hợp lệ: menu_item_id và status là bắt buộc.");
                }

                $this->orderItem->orderId = $orderId;
                $this->orderItem->menuItemId = (int) $item['menu_item_id'];
                $this->orderItem->status = $item['status'];

                if (!$this->orderItem->create()) {
                    throw new Exception("Không thể thêm order item cho menu_item_id: " . $item['menu_item_id']);
                }
            }

            $this->db->commit();
            http_response_code(201);
            echo json_encode([
                "message" => "Đã thêm order items vào đơn hàng.",
                "order_id" => $orderId
            ]);
        } catch (Exception $e) {
            $this->db->rollback();
            http_response_code(400);
            echo json_encode([
                "message" => "Không thể thêm order items.",
                "error" => $e->getMessage()
            ]);
        }
    }
    public function getOrderById($orderId)
    {
        try {
            $order = $this->order->readById($orderId);
            if (!$order) {
                http_response_code(404);
                echo json_encode(["message" => "Đơn hàng không tồn tại."]);
                return;
            }

            // Lấy danh sách các item trong order
            $query = "SELECT oi.id, oi.order_id, oi.menu_item_id, oi.status 
                      FROM order_items oi 
                      WHERE oi.order_id = ?";

            $stmt = $this->db->prepare($query);
            if ($stmt === false) {
                throw new Exception("Lỗi chuẩn bị truy vấn: " . $this->db->error);
            }

            $stmt->bind_param("s", $orderId);
            $stmt->execute();
            $result = $stmt->get_result();
            $orderItems = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            // Gán danh sách order_items vào order
            $order['order_items'] = $orderItems ?: [];

            http_response_code(200);
            echo json_encode($order);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "message" => "Lỗi khi lấy dữ liệu đơn hàng.",
                "error"   => $e->getMessage()
            ]);
        }
    }
}
