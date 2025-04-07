<?php
// middlewares/validate_middleware.php
class ValidateMiddleware
{
    public static function handle($request, $requiredFields)
    {
        // Kiểm tra xem tất cả các trường bắt buộc có tồn tại trong request không
        foreach ($requiredFields as $field) {
            if (empty($request[$field])) {
                // Nếu có bất kỳ trường nào bị thiếu, trả về lỗi
                http_response_code(400); // Bad Request
                echo json_encode(["error" => "Missing required field: $field"]);
                return false;
            }
        }
        return true;
    }
}
