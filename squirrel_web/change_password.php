<?php
// 비밀번호 변경 처리 코드
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // 비밀번호 확인
    if ($new_password !== $confirm_password) {
        echo "새 비밀번호가 일치하지 않습니다.";
        exit;
    }

    // 사용자 인증 후 비밀번호 변경 작업 (예: 데이터베이스에서 확인 후 변경)
    require 'vendor/autoload.php';
    use \Firebase\JWT\JWT;
    use \Firebase\JWT\Key;

    $jwt = $_COOKIE['auth_token'];
    $decoded = JWT::decode($jwt, new Key('YOUR_SECRET_KEY', 'HS256'));
    $username = $decoded->data->username;

    // 비밀번호 변경 로직 (예시)
    // 데이터베이스에서 사용자 정보 확인 후 비밀번호 변경
    echo "비밀번호가 변경되었습니다.";
}
?>
