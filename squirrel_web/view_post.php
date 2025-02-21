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

$post_id = $_GET['id'];
$sql = "SELECT * FROM board_posts WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>게시물 상세보기</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2><?php echo htmlspecialchars($post['title']); ?></h2>
        <div class="post-meta">
            <p>작성자: <?php echo htmlspecialchars($post['author']); ?></p>
            <p>작성일: <?php echo htmlspecialchars($post['created_at']); ?></p>
        </div>
        <div class="content">
            <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
        </div>

        <?php
        // 파일 다운로드 링크 추가
        if (!empty($post['file_name'])) {
            echo "<p>첨부 파일: <a href='uploads/" . htmlspecialchars($post['file_name']) . "' download>" . htmlspecialchars($post['file_name']) . "</a></p>";
        }
        ?>

        <?php
        if (isset($_COOKIE['auth_token']) && !empty($_COOKIE['auth_token'])) {
            try {
                $jwt = $_COOKIE['auth_token'];
                $decoded = JWT::decode($jwt, new Key('YOUR_SECRET_KEY', 'HS256'));

                if ($decoded->data->username === $post['author']) {
                    echo "<div class='action-links'>";
                    echo "<a href='edit_post.php?id=" . $post['id'] . "'>수정</a> | ";
                    echo "<a href='delete_post.php?id=" . $post['id'] . "'>삭제</a>";
                    echo "</div>";
                }
            } catch (Exception $e) {
                echo "<p>세션이 만료되었습니다. 다시 로그인해 주세요.</p>";
            }
        }
        ?>

        <p class="back-link"><a href="board_list.php">목록으로 돌아가기</a></p>
    </div>
</body>
</html>
