<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>게시물 삭제</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>게시물 삭제</h1>

<?php
require 'vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = 'localhost';
$db = 'my_database';
$user = 'root';
$pass = 'U3DYRePeDDr:';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("연결 실패: " . $conn->connect_error);
}

// 게시물 ID 받기
$post_id = $_GET['id'];

if (isset($_COOKIE['auth_token']) && !empty($_COOKIE['auth_token'])) {
    try {
        $jwt = $_COOKIE['auth_token'];
        $decoded = JWT::decode($jwt, new Key('YOUR_SECRET_KEY', 'HS256'));
        
        $sql = "SELECT * FROM board_posts WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $post = $result->fetch_assoc();
        
        if ($decoded->data->username === $post['author']) {
            if (!empty($post['file'])) {
                $file_path = 'uploads/' . $post['file'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            
            $sql = "DELETE FROM board_posts WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $post_id);
            if ($stmt->execute()) {
                echo "<p>게시물이 삭제되었습니다.</p>";
            } else {
                echo "<p>게시물 삭제에 실패했습니다.</p>";
            }
        } else {
            echo "<p>삭제 권한이 없습니다.</p>";
        }
    } catch (Exception $e) {
        echo "<p>세션이 만료되었습니다. 다시 로그인해 주세요.</p>";
    }
} else {
    echo "<p>로그인이 필요합니다.</p>";
}
?>
 <p><a href="board_list.php">게시판 목록</a></p>
    </div>
</body>
</html>