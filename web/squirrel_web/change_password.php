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

// 메시지 및 SQL 쿼리 로그
$message = '';
$sql_query = '';

// 로그인된 사용자만 비밀번호 변경 가능
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // 새 비밀번호 확인
    if ($new_password !== $confirm_password) {
        header("Location: login_form.php?message=" . urlencode("새 비밀번호가 일치하지 않습니다."));
        exit();
    }

    // 현재 비밀번호 검증
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

    // 비밀번호 비교
    if (!password_verify($current_password, $db_password)) {
        header("Location: login_form.php?message=" . urlencode("현재 비밀번호가 올바르지 않습니다."));
        exit();
    }

    // 새 비밀번호 해싱 후 저장
    $hashed_new_password = password_hash($new_password, PASSWORD_BCRYPT);
    $update_sql = "UPDATE users SET password = ? WHERE username = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ss", $hashed_new_password, $username);

    if ($update_stmt->execute()) {
        $message = "비밀번호가 성공적으로 변경되었습니다.";
        $sql_query = "UPDATE users SET password = '[ENCRYPTED]' WHERE username = '$username'";
        
        // 비밀번호 변경 후 재로그인을 위해 JWT 삭제
        setcookie("auth_token", "", time() - 3600, "/");
    } else {
        $message = "비밀번호 변경 중 오류 발생: " . $conn->error;
    }
}

// 결과 메시지를 전달하며 로그인 화면으로 리디렉션
header("Location: login_form.php?message=" . urlencode($message) . "&sql=" . urlencode($sql_query));
exit();
?>
