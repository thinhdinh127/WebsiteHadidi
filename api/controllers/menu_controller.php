<?php
require_once '../api/objects/menu_item.php';

class MenuController {
    private $db;
    private $menu_item;

    public function __construct($database) {
        $this->db = $database->getConnection();
        $this->menu_item = new MenuItem($this->db);
    }

    // Lấy tất cả món ăn
    public function getMenuItems() {
        $stmt = $this->menu_item->readAll();
        $menu_items_arr = array();
        $result = $stmt->get_result();

        $menu_items = [];
        while ($row = $result->fetch_assoc()) {
            $menu_items[] = $row;
        }
        foreach ($menu_items as $row) {
            $menu_item = array(
                "id" => $row['id'],
                "name" => $row['name'],
                "price" => $row['price'],
                "description" => $row['description'],
                "image" => $row['image'],
                "detail" => $row['detail'],
                "category_id" => $row['category_id'],
                "category_name" => $row['category_name'],
                "status" => (bool)$row['status'],
            );
            array_push($menu_items_arr, $menu_item);
        }
    
        http_response_code(200);
        echo json_encode($menu_items_arr);
    }

    // Lấy một món ăn theo ID
    public function getMenuItemById($id) {
        $this->menu_item->id = $id;
        $result = $this->menu_item->readOne();

        if ($result) {
            $menu_item = array(
                "id" => $result['id'],
                "name" => $result['name'],
                "price" => $result['price'],
                "description" => $result['description'],
                "image" => $result['image'],
                "detail" => $result['detail'],
                "category_id" => $result['category_id'],
                "category_name" => $result['category_name'],
                "status" => (bool)$result['status'],
            );
            http_response_code(200);
            echo json_encode($menu_item);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Không tìm thấy món ăn."));
        }
    }

    // Kiểm tra trạng thái món ăn theo ID
    public function checkMenuItemStatus($id) {
        $this->menu_item->id = $id;
        $result = $this->menu_item->checkStatus();

        if ($result) {
            $menu_item = array(
                "id" => $result['id'],
                "name" => $result['name'],
                "status" => (bool)$result['status'],
                "message" => (bool)$result['status'] ? "Món ăn còn hàng." : "Món ăn đã hết."
            );
            http_response_code(200);
            echo json_encode($menu_item);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Không tìm thấy món ăn."));
        }
    }

    // Tạo một món ăn mới
    public function createMenuItem($data) {
        $this->menu_item->name = $data['name'];
        $this->menu_item->price = $data['price'];
        $this->menu_item->description = isset($data['description']) ? $data['description'] : null;
        $this->menu_item->image = isset($data['image']) ? $data['image'] : null;
        $this->menu_item->detail = isset($data['detail']) ? $data['detail'] : null;
        $this->menu_item->category_id = isset($data['category_id']) ? $data['category_id'] : null;
        $this->menu_item->status = isset($data['status']) ? $data['status'] : true;

        if ($this->menu_item->create()) {
            http_response_code(201);
            echo json_encode(array("message" => "Món ăn được tạo thành công."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Không thể tạo món ăn."));
        }
    }

    // Cập nhật một món ăn
    public function updateMenuItem($id, $data) {
        $this->menu_item->id = $id;
        $this->menu_item->name = $data['name'];
        $this->menu_item->price = $data['price'];
        $this->menu_item->description = isset($data['description']) ? $data['description'] : null;
        $this->menu_item->image = isset($data['image']) ? $data['image'] : null;
        $this->menu_item->detail = isset($data['detail']) ? $data['detail'] : null;
        $this->menu_item->category_id = isset($data['category_id']) ? $data['category_id'] : null;
        $this->menu_item->status = isset($data['status']) ? $data['status'] : true;

        if ($this->menu_item->update()) {
            http_response_code(200);
            echo json_encode(array("message" => "Món ăn được cập nhật thành công."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Không thể cập nhật món ăn."));
        }
    }

    // Xóa một món ăn
    public function deleteMenuItem($id) {
        $this->menu_item->id = $id;

        if ($this->menu_item->delete()) {
            http_response_code(200);
            echo json_encode(array("message" => "Món ăn đã được xóa."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Không thể xóa món ăn."));
        }
    }

    // Tìm kiếm món ăn theo tên
    public function getMenuItemByName($data) {
        $result = $this->menu_item->searchByName($data['name']);

        if ($result) {
            $menu_items_arr = array();
            $result = $result->get_result();
            while ($row = $result->fetch_assoc()) {
                $menu_item = array(
                    "id" => $row['id'],
                    "name" => $row['name'],
                    "price" => $row['price'],
                    "description" => $row['description'],
                    "image" => $row['image'],
                    "detail" => $row['detail'],
                    "category_id" => $row['category_id'],
                    "category_name" => $row['category_name'],
                    "status" => (bool)$row['status'],
                );
                array_push($menu_items_arr, $menu_item);
            }
            http_response_code(200);
            echo json_encode($menu_items_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Không tìm thấy món ăn."));
        }
    }
}
?>