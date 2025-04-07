<?php
    class Validator  {
        public static function validatePassword($password) {
            $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
            return preg_match($pattern, $password);
        }
        public static function validateUsername($username) {
            $pattern = '/^.{8,}$/';
            return preg_match($pattern, $username);
        }
        public static function validateEmail($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        }
        
    }