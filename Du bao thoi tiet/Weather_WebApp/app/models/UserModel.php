
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

    // 🛠️ SỬA TẠI ĐÂY: Khai báo và băm mật khẩu chuẩn mã hóa trước khi INSERT vào DB
    public function registerUser($username, $email, $password) {
        // Vì trong code cũ của cậu dùng biến $hashed_password nhưng chưa băm, 
        // ở đây tớ sẽ băm luôn bằng MD5 hoặc Password_Hash (tùy DB cũ của cậu lưu dạng nào).
        // Ví dụ dùng Password_Hash chuẩn PHP:
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        
        $sql = "INSERT INTO data_user (username, email, password) VALUES (?, ?, ?)";
        return $this->db->execute($sql, 'sss', [$username, $email, $hashed_password]);
    }
}