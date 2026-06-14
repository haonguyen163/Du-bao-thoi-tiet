<?php
session_start(); // 1. Khởi động session để biết đang hủy cái nào

// 2. Xóa tất cả các biến trong session (user_id, email...)
session_unset(); 

// 3. Hủy hoàn toàn phiên làm việc
session_destroy(); 

// 4. Chuyển hướng về trang đăng nhập
header("Location: login.php");
exit();
?>