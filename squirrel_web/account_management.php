<?php
$is_logged_in = isset($_COOKIE['auth_token']) && !empty($_COOKIE['auth_token']);

if (!$is_logged_in) {
    header("Location: login_form.php?message=로그인 후 이용 가능합니다.");
    exit;
}

// 인증된 사용자의 정보를 가져오는 코드 (예: 사용자 이름)
require 'vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

$jwt = $_COOKIE['auth_token'];
$decoded = JWT::decode($jwt, new Key('YOUR_SECRET_KEY', 'HS256'));
$username = $decoded->data->username;
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>계정 관리</title>
    
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>계정 관리</h2>
    <p>로그인한 사용자: <?php echo htmlspecialchars($username); ?></p>

    <form method="POST" action="change_password.php">
        <h3>비밀번호 변경</h3>
        기존 비밀번호: <input type="password" name="current_password" required><br>
        새 비밀번호: <input type="password" name="new_password" required><br>
        새 비밀번호 확인: <input type="password" name="confirm_password" required><br>
        <input type="submit" value="비밀번호 변경">
    </form>

    <form method="POST" action="delete_account.php">
        <h3>계정 삭제</h3>
        <input type="submit" value="계정 삭제">
    </form>

    <p><a href="login_form.php">로그인 화면으로 가기</a></p>
    <p><a href="board_list.php">게시판 보기</a></p>
</body>
</html>
