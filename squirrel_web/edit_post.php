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
$sql = "SELECT * FROM board_posts WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();


if (isset($_COOKIE['auth_token']) && !empty($_COOKIE['auth_token'])) {
    try {
        $jwt = $_COOKIE['auth_token'];
        $decoded = JWT::decode($jwt, new Key('YOUR_SECRET_KEY', 'HS256'));
        if ($decoded->data->username === $post['author']) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $title = $_POST['title'];
                $content = $_POST['content'];
                $file_name = $post['file_name'];
                
                if (!empty($_FILES['file']['name'])) {
                    $upload_dir = 'uploads/';
                    $file_name = basename($_FILES['file']['name']);
                    $target_path = $upload_dir . $file_name;
                    move_uploaded_file($_FILES['file']['tmp_name'], $target_path);
                }
                
                $sql = "UPDATE board_posts SET title = ?, content = ?, file_name = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssi", $title, $content, $file_name, $post_id);
                if ($stmt->execute()) {
                    echo "<p>글이 수정되었습니다!</p>";
                    echo "<script>
                        setTimeout(function() {
                            window.location.href = 'view_post.php?id=$post_id';
                        }, 1000);
                    </script>";
                } else {
                    echo "<p>글 수정에 실패했습니다.</p>";
                }
            }
        } else {
            echo "<p>수정 권한이 없습니다.</p>";
        }
    } catch (Exception $e) {
        echo "<p>세션이 만료되었습니다. 다시 로그인해 주세요.</p>";
    }
} else {
    echo "<p>로그인이 필요합니다.</p>";
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>게시물 수정</title>
    
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>게시물 수정</h2>

        <?php if (isset($errorMessage)) { echo "<p class='error-message'>$errorMessage</p>"; } ?>

        <form method="POST" enctype="multipart/form-data">
            제목: <input type="text" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required><br>
            내용: <textarea name="content" required><?php echo htmlspecialchars($post['content']); ?></textarea><br>
            파일: <input type="file" name="file"><br>
            <input type="submit" value="수정">
        </form>

        <p class="back-link"><a href="view_post.php?id=<?php echo $post['id']; ?>">돌아가기</a></p>
    </div>
</body>
</html>

