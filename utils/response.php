<?php
class APIResponse {
    public static function success($data, $message = "Thành công", $status = 200) {
        http_response_code($status);
        echo json_encode([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ]);
    }

    public static function error($message, $status = 400) {
        http_response_code($status);
        echo json_encode([
            'status' => 'error',
            'message' => $message
        ]);
    }

    public static function json($status, $message, $data = []) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode([
            "status" => $status,
            "message" => $message,
            "data" => $data
        ]);
        exit;
    }
}
?>