<?php
require 'vendor/autoload.php'; // Composer autoload 파일 로드
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 비밀 키 설정 (안전하게 관리하세요)
$secret_key = "YOUR_SECRET_KEY"; // 실제 환경에서는 안전하게 관리
$issuer = $_SERVER['HTTP_HOST']; // 발행자 정보
$audience = $issuer; // 청중 정보와 발행자 정보를 동일하게 설정
$issued_at = time(); // 발행 시간
$expiration_time = $issued_at + (60 * 60); // 만료 시간 (1시간)

$host = 'localhost';
$db = 'my_database';
$user = 'root';
$pass = 'U3DYRePeDDr:';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("연결 실패: " . $conn->connect_error);
}

// 메시지를 담을 변수
$message = '';
$sql_query = ''; // 쿼리 저장 변수
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['logout'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 사용자 정보 확인
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // 비밀번호 확인
        if ($password == $row['password']) {
            $message = "로그인 성공! (일반 비밀번호)";
            $sql_query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
        } 
        else if (password_verify($password, $row['password'])) {
            // JWT Payload 생성
            $payload = array(
                "iss" => $issuer,
                "aud" => $audience,
                "iat" => $issued_at,
                "exp" => $expiration_time,
                "data" => array(
                    "id" => $row['id'],
                    "username" => $row['username']
                )
            );

            // JWT 생성
            $jwt = JWT::encode($payload, $secret_key, 'HS256');
            
            // 쿠키에 JWT 저장
            setcookie("auth_token", $jwt, $expiration_time, "/", "", false, false);

            $message = "로그인 성공! (해시화된 비밀번호)";
            $sql_query = "SELECT * FROM users WHERE username = '$username' AND password = '{$row['password']}'";
        } 
        else {
            $message = "잘못된 비밀번호.";
        }
    } else {
        $message = "존재하지 않는 사용자입니다.";
    }

    // 로그인 후 리다이렉션 처리 전에 쿠키를 설정하므로 쿠키가 제대로 반영되도록 함
    header("Location: login_form.php?message=" . urlencode($message) . "&sql=" . urlencode($sql_query));
    exit();
}

if (isset($_COOKIE['auth_token']) && !empty($_COOKIE['auth_token'])) {
    try {
        $jwt = $_COOKIE['auth_token'];
        $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
        $message = "환영합니다, " . htmlspecialchars($decoded->data->username) . "!";
    } catch (Exception $e) {
        $message = "세션이 만료되었습니다. 다시 로그인해 주세요.";
        setcookie("auth_token", "", time() - 3600, "/");
    }
} else {
    // 쿠키가 없으면 로그인 페이지에서 로그인이 필요하다고 메시지를 보여줌
    $message = "로그인이 필요합니다.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['logout'])) {
    setcookie("auth_token", "", time() - 3600, "/");
    $message = "로그아웃 성공!";
}

header("Location: login_form.php?message=" . urlencode($message) . "&sql=" . urlencode($sql_query));
exit();
?>
