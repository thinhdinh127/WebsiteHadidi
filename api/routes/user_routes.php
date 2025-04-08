<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../api/controllers/user_controller.php';

$database = new Database();
$user_controller = new UserController($database);

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri_segments = explode('/', trim($uri, '/'));

$id = isset($uri_segments[2]) ? $uri_segments[2] : null;

switch ($method) {
    case 'GET':
        if (isset($uri_segments[2]) && $uri_segments[2] == 'response') {
            AuthMiddleware::checkUser();
            $user_controller->getUser(); 
        } else if(isset($uri_segments[3])) {
            echo json_encode(["message" => "Invalid request method"]);
        } else {
            $user_controller->getUsers(); 
        }
        break;
 
    case 'POST':
        if(isset($uri_segments[3])) {
            echo json_encode(["message" => "Invalid request method"]);
            break;
        }
        if(isset($uri_segments[2]) && $uri_segments[2] == 'login') {
            $data = json_decode(file_get_contents("php://input"), true);
            $user_controller->login($data);
        }else if (isset($uri_segments[2]) && $uri_segments[2] == 'forgetPassword') {
            $data = json_decode(file_get_contents("php://input"), true);
            $user_controller->forgetPassword($data);
        }else if (isset($uri_segments[2]) && $uri_segments[2] == 'resetPassword') {
            $data = json_decode(file_get_contents("php://input"), true);
            $user_controller->resetPassword($data);
        } else  {
            $data = json_decode(file_get_contents("php://input"), true);
            $user_controller->createUser($data); // Tạo user mới
        }
        break;

    case 'PUT':
        if(isset($uri_segments[3])) {
            echo json_encode(["message" => "Invalid request method"]);
        } else
        if (isset($uri_segments[2]) && $uri_segments[2] == 'update') {
            AuthMiddleware::checkUser();
            $data = json_decode(file_get_contents("php://input"), true);
            $user_controller->updateUser($data); // Cập nhật user
        }  else {
            echo json_encode(["message" => "Invalid request method"]);
        }
        break;

    default:
        echo json_encode(["message" => "Invalid request method"]);
        break;
}
?>
