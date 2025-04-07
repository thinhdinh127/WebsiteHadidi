<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;

class JWTHandler {
    private static $secret_key;
    private static $algo = 'HS256';

    public static function init() {
        self::$secret_key = getenv("JWT_SECRET") ?: "0bbe045e916673a9c8eab7dcd0dea0112509f466c47121ad86759d9a22d7c7fd";
    }

    public static function generateToken($user_id, $email, $username) {
        $payload = [
            "sub" => $user_id,
            "username" => $username,
            "email" => $email,
            "role" => "USER",
            "iat" => time(),
            "exp" => time() + 3600
        ];
        return JWT::encode($payload, self::$secret_key, self::$algo);
    }

    public static function generateTokenForAdmin($email, $password, $role) {
        $payload = [
            "email" => $email,
            "password" => $password,
            "role" => $role,
            "iat" => time(),
            "exp" => time() + 3600
        ];
        return JWT::encode($payload, self::$secret_key, self::$algo);
    }

    public static function generateTokenForResetPass() {
        $payload = [
            "iat" => time(),
            "exp" => time() + 3600
        ];
        return JWT::encode($payload, self::$secret_key, self::$algo);
    }

    public static function verifyToken($token) {
        try {
            return JWT::decode($token, new Key(self::$secret_key, self::$algo));
        } catch (ExpiredException $e) {
            return ["error" => "Token đã hết hạn"];
        } catch (SignatureInvalidException $e) {
            return ["error" => "Chữ ký không hợp lệ"];
        } catch (BeforeValidException $e) {
            return ["error" => "Token chưa hợp lệ"];
        } catch (UnexpectedValueException $e) {
            return ["error" => "Token không hợp lệ"];
        }
    }
}

JWTHandler::init();
?>
