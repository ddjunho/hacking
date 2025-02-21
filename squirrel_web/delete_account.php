<?php
// 계정 삭제 처리 코드
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require 'vendor/autoload.php';
    use \Firebase\JWT\JWT;
    use \Firebase\JWT\Key;

    $jwt = $_COOKIE['auth_token'];
    $decoded = JWT::decode($jwt, new Key('YOUR_SECRET_KEY', 'HS256'));
    $username = $decoded->data->username;

    // 데이터베이스에서 사용자 삭제 작업 (예시)
    // 예: "DELETE FROM users WHERE username = '$username'"

    // JWT 로그아웃 처리 (쿠키 삭제)
    setcookie('auth_token', '', time() - 3600, '/');
    echo "계정이 삭제되었습니다.";
}
?>
