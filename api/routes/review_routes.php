<?php
// Include database and object files
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../controllers/Review_Controller.php';
// Get request method
$method = $_SERVER['REQUEST_METHOD'];
// Get review ID from URL if present
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);
$path_parts = explode('/', trim($path, '/'));
$id = end($path_parts);
$is_numeric = is_numeric($id);

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize controller
$controller = new Review_Controller($db);

// Handle different HTTP methods
switch ($method) {
    case 'GET':
        if ($is_numeric) {
            $controller->readOne($id);
        } else {
            $controller->read();
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        $controller->create($data);
        break;
        
    case 'PUT':
        if ($is_numeric) {
            $data = json_decode(file_get_contents("php://input"));
            $controller->update($id, $data);
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Missing review ID"));
        }
        break;
        
    case 'DELETE':
        if ($is_numeric) {
            $controller->delete($id);
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Missing review ID"));
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed"));
        break;
}
?> 