<?php
    namespace Models;
    require_once 'db.php';
    class RegisterValidasi {
        private $pdo;
        public function __construct($pdo) {
            $this->pdo = $pdo;
        }
        private function cekWhitespace($str) {
            for ($i = 0; $i < strlen($str); $i++) {
                if ($str[$i] != ' ') return false;
            }
            return true;
        }
        public function isAllFieldsFilled($data) {
            foreach ($data as $value) {
                if ($value === '' || $this->cekWhitespace($value)) return false;
            }
            return true;
        }
        public function isPasswordConfirmed($pass, $confirm) {
            return $pass === $confirm;
        }
        public function isValidEmail($email) {
            $length = strlen($email);
            $hasAt = false;
            $hasDotAfterAt = false;
            $atIndex = -1;
            for ($i = 0; $i < $length; $i++) {
                if ($email[$i] == '@' && !$hasAt) {
                    $hasAt = true;
                    $atIndex = $i;
                } elseif ($email[$i] == '.' && $hasAt && $i > $atIndex + 1) {
                    $hasDotAfterAt = true;
                }
            }
            if ($atIndex <= 0 || $atIndex >= $length - 1) return false;
            if ($email[0] == '.' || $email[$length - 1] == '.') return false;
            return $hasAt && $hasDotAfterAt;
        }
        public function isEmailOrUsernameTaken($username, $email) {
            $query = $this->pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $query->execute([$username, $email]);
            return $query->fetch() ? true : false;
        }
        public function insertNewUser($email, $display_name, $username, $password, $role) {
            $query = $this->pdo->prepare("INSERT INTO users (email, display_name, username, password, profile_picture, saldo, role, status) VALUES (?, ?, ?, ?, 'default_img.png', 0, ?, 'active')");
            $query->execute([$email, $display_name, $username, $password, $role]);
        }        
    }
?>
