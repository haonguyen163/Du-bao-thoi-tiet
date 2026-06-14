<?php
class Database
{
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "du_bao_thoi_tiet";
    private $conn;

    public function __construct()
    {
        try {
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->dbname);
            $this->conn->set_charset("utf8");
        } catch (mysqli_sql_exception $e) {
            die("Kết nối thất bại: " . $e->getMessage());
        }
    }
    public function select($sql, $type = "", $param = [])
    {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            die("Lỗi SQL (Select): " . $this->conn->error . " | SQL: " . $sql);
        }

        if (!empty($param)) {
            $stmt->bind_param($type, ...$param);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        return $data;
    }
    public function execute($sql, $type = "", $param = [])
    {
        $stmt = $this->conn->prepare($sql); {
            die("Lỗi SQL (Prepare Failed): " . $this->conn->error . " <br> SQL: " . $sql);
        }
        if (!empty($param)) {

            if (strlen($type) !== count($param)) {
                die("Lỗi Code: Số lượng kiểu dữ liệu ('$type') không khớp với số lượng tham số truyền vào (" . count($param) . ").");
            }
            $stmt->bind_param($type, ...$param);
        }
        $success = $stmt->execute();
        if (!$success) {
            die("Lỗi Thực Thi (Execute Failed): " . $stmt->error);
        }
        $stmt->close();
        return $success;
    }
    public function count($sql, $type = "", $param = [])
    {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            die("Lỗi SQL (Count): " . $this->conn->error);
        }
        if (!empty($param)) {
            $stmt->bind_param($type, ...$param);
        }
        $stmt->execute();
        $total = 0;
        $stmt->bind_result($total);
        $stmt->fetch();
        $stmt->close();
        return $total ?? 0;
    }
    public function getLastId()
    {
        return $this->conn->insert_id;
    }
}
?>