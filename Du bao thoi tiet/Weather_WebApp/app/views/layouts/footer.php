<script>
function confirmLogout() {
    Swal.fire({
        title: 'Đăng xuất?',
        text: "Bạn có chắc chắn muốn thoát phiên làm việc?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Đăng xuất ngay',
        cancelButtonText: 'Ở lại'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '/auth/logout'; // Trỏ về action logout của AuthController
        }
    })
}
</script>