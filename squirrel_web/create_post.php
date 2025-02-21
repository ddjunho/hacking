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

if (isset($_COOKIE['auth_token']) && !empty($_COOKIE['auth_token'])) {
    try {
        $jwt = $_COOKIE['auth_token'];
        $decoded = JWT::decode($jwt, new Key('YOUR_SECRET_KEY', 'HS256'));

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $title = $_POST['title'];
            $content = $_POST['content'];
            $author = $decoded->data->username;
            $upload_dir = "uploads/";
            $file_name = "";

            // 파일 업로드 처리
            if (!empty($_FILES['file']['name'])) {
                $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
                $file_tmp = $_FILES['file']['tmp_name'];
                $file_type = mime_content_type($file_tmp);
                
                if (in_array($file_type, $allowed_types)) {
                    $file_name = time() . "_" . basename($_FILES['file']['name']);
                    $file_path = $upload_dir . $file_name;
                    move_uploaded_file($file_tmp, $file_path);
                } else {
                    echo "<p>허용되지 않은 파일 형식입니다.</p>";
                    exit;
                }
            }

            $sql = "INSERT INTO board_posts (title, content, author, file_name, created_at) VALUES (?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $title, $content, $author, $file_name);
            if ($stmt->execute()) {
                echo "<p>글이 작성되었습니다!</p>";
                echo "<p><a href='board_list.php'>목록으로 돌아가기</a></p>";
            } else {
                echo "<p>글 작성에 실패했습니다.</p>";
            }
        }
    } catch (Exception $e) {
        echo "<p>세션이 만료되었습니다. 다시 로그인해 주세요.</p>";
    }
} else {
    echo "<p>로그인이 필요합니다.</p>";
    echo "<p><a href='login_form.php'>로그인</a></p>";
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>글 작성</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>글 작성</h2>
        <form method="POST" action="create_post.php" enctype="multipart/form-data">
            제목: <input type="text" name="title" required><br>
            내용: <textarea name="content" required></textarea><br>
            파일 업로드: <input type="file" name="file"><br>
            <input type="submit" value="작성">
        </form>
        <p><a href="board_list.php">목록으로 돌아가기</a></p>
    </div>
</body>
</html>
