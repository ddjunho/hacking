<?php
require 'vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 비밀 키 설정
$secret_key = "YOUR_SECRET_KEY";
$host = 'localhost';
$db = 'my_database';
$user = 'root';
$pass = 'U3DYRePeDDr:';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("연결 실패: " . $conn->connect_error);
}

// 로그인된 사용자만 계정 삭제 가능
if (!isset($_COOKIE['auth_token']) || empty($_COOKIE['auth_token'])) {
    header("Location: login_form.php?message=" . urlencode("로그인이 필요합니다."));
    exit();
}

try {
    $jwt = $_COOKIE['auth_token'];
    $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
    $username = $decoded->data->username;
} catch (Exception $e) {
    setcookie("auth_token", "", time() - 3600, "/");
    header("Location: login_form.php?message=" . urlencode("세션이 만료되었습니다. 다시 로그인해 주세요."));
    exit();
}

// 계정 삭제 처리
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 사용자의 비밀번호 확인 (선택 사항)
    $password = $_POST['password'];

    // 데이터베이스에서 사용자 정보 확인
    $sql = "SELECT password FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header("Location: login_form.php?message=" . urlencode("사용자를 찾을 수 없습니다."));
        exit();
    }

    $row = $result->fetch_assoc();
    $db_password = $row['password'];

    // 비밀번호 확인
    if (!password_verify($password, $db_password)) {
        header("Location: login_form.php?message=" . urlencode("비밀번호가 올바르지 않습니다."));
        exit();
    }

    // 사용자 삭제
    $delete_sql = "DELETE FROM users WHERE username = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("s", $username);
    
    if ($delete_stmt->execute()) {
        // 계정 삭제 후 JWT 쿠키 삭제
        setcookie("auth_token", "", time() - 3600, "/");
        header("Location: login_form.php?message=" . urlencode("계정이 성공적으로 삭제되었습니다."));
    } else {
        header("Location: login_form.php?message=" . urlencode("계정 삭제 중 오류가 발생했습니다."));
    }
    exit();
}

?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>계정 삭제</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>계정 삭제</h2>
    <p>이 작업은 되돌릴 수 없습니다. 계정을 삭제하려면 비밀번호를 입력해주세요.</p>
    
    <form method="POST" action="delete_account.php">
        <label for="password">비밀번호:</label>
        <input type="password" name="password" required><br><br>
        <input type="submit" value="계정 삭제">
    </form>

    <p><a href="account_manage.php">계정 관리 페이지로 돌아가기</a></p>
</body>
</html>
