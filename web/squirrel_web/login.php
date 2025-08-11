<?php
session_start();
require 'vendor/autoload.php'; // Composer autoload 파일 로드
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 비밀 키 설정 (안전하게 관리)
$secret_key = "YOUR_SECRET_KEY";
$issuer = $_SERVER['HTTP_HOST'];
$audience = $issuer;
$issued_at = time();
$expiration_time = $issued_at + (60 * 60); // 만료 시간 (1시간)

// DB 연결
$host = 'localhost';
$db = 'my_database';
$user = 'root';
$pass = 'U3DYRePeDDr:';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("연결 실패: " . $conn->connect_error);
}

// 메시지 변수
$message = '';
$sql_query = '';

// 로그인 시도 제한: 5회 이상 틀릴 경우 30초 제한
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

if (isset($_SESSION['lock_time']) && time() < $_SESSION['lock_time'] + 30) {
    $remaining_time = ($_SESSION['lock_time'] + 30) - time();
    $message = "로그인 시도가 5회 실패하여 $remaining_time 초 후 다시 시도해주세요.";
    header("Location: login_form.php?message=" . urlencode($message));
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['logout'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 사용자 정보 조회
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // 비밀번호 확인
        if ($password == $row['password']) {
            $_SESSION['login_attempts'] = 0; // 로그인 성공 시 초기화
            $message = "로그인 성공! (일반 비밀번호)";
            $sql_query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
        } 
        else if (password_verify($password, $row['password'])) {
            $_SESSION['login_attempts'] = 0; // 로그인 성공 시 초기화

            // JWT 생성
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

            $jwt = JWT::encode($payload, $secret_key, 'HS256');
            setcookie("auth_token", $jwt, $expiration_time, "/", "", true, true);

            $message = "로그인 성공! (해시화된 비밀번호)";
            $sql_query = "SELECT * FROM users WHERE username = '$username' AND password = '{$row['password']}'";
        } 
        else {
            $_SESSION['login_attempts']++;

            if ($_SESSION['login_attempts'] >= 5) {
                $_SESSION['lock_time'] = time();
                $message = "로그인 시도가 5회 실패하여 30초 동안 로그인할 수 없습니다.";
            } else {
                $remaining_attempts = 5 - $_SESSION['login_attempts'];
                $message = "잘못된 비밀번호. 남은 로그인 시도 횟수: $remaining_attempts 회";
            }
        }
    } else {
        $_SESSION['login_attempts']++;

        if ($_SESSION['login_attempts'] >= 5) {
            $_SESSION['lock_time'] = time();
            $message = "로그인 시도가 5회 실패하여 30초 동안 로그인할 수 없습니다.";
        } else {
            $remaining_attempts = 5 - $_SESSION['login_attempts'];
            $message = "존재하지 않는 사용자입니다. 남은 로그인 시도 횟수: $remaining_attempts 회";
        }
    }

    header("Location: login_form.php?message=" . urlencode($message) . "&sql=" . urlencode($sql_query));
    exit();
}

// 로그인 상태 확인
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
    $message = "로그인이 필요합니다.";
}

// 로그아웃 처리
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['logout'])) {
    setcookie("auth_token", "", time() - 3600, "/");
    $_SESSION['login_attempts'] = 0;
    $message = "로그아웃 성공!";
    header("Location: login_form.php?message=" . urlencode($message));
    exit();
}

header("Location: login_form.php?message=" . urlencode($message));
exit();
?>
