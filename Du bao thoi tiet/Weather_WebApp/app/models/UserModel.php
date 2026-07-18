<?php
class UserModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getUserByEmail($email) {
        $sql = "SELECT * FROM data_user WHERE email = ?";
        $result = $this->db->select($sql, 's', [$email]);
        return !empty($result) ? $result[0] : null;
    }

    public function checkUserExist($username, $email) {
        $sql = "SELECT id FROM data_user WHERE username = ? OR email = ?";
        $result = $this->db->select($sql, 'ss', [$username, $email]);
        return !empty($result);
    }

    
    public function registerUser($username, $email, $password) {
        // Băm mật khẩu bằng Bcrypt bảo mật[cite: 6]
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        
        // Thêm trường role vào câu lệnh SQL
        $sql = "INSERT INTO data_user (username, email, password, role) VALUES (?, ?, ?, 'user')";
        return $this->db->execute($sql, 'sss', [$username, $email, $hashed_password]);
    }
}