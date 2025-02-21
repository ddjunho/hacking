<?php
// 데이터베이스 연결
$servername = "localhost";
$username = "root"; // 데이터베이스 사용자 이름
$password = "U3DYRePeDDr:"; // 데이터베이스 비밀번호
$dbname = "my_database"; // 사용 중인 데이터베이스 이름

// MySQL 연결 생성
$conn = new mysqli($servername, $username, $password, $dbname);

// 연결 확인
if ($conn->connect_error) {
    die("연결 실패: " . $conn->connect_error);
}

// 사용자 목록 조회
$sql = "SELECT * FROM users";
$result = $conn->query($sql);


// 사용자 삭제 처리
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']); // GET 요청으로부터 사용자 ID를 받아옵니다.
    $delete_sql = "DELETE FROM users WHERE id = $delete_id"; // 사용자 삭제 SQL 쿼리

    if ($conn->query($delete_sql) === TRUE) {
        echo "사용자가 삭제되었습니다.";
        // 사용자가 삭제된 후 페이지 새로 고침
        header("Location: users.php");
        exit();
    } else {
        echo "사용자 삭제 오류: " . $conn->error;
    }
}

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>회원 목록</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>회원 목록</h1>
    <ul>
        <?php
        // 사용자 이름을 목록으로 출력
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<li>아이디: " . $row["id"] . " - 사용자 이름: " . $row["username"] . " - 비밀번호: " . $row["password"] . "</li>";
                // echo "<li>아이디: " . $row["id"] . " - 사용자 이름: " . $row["username"] . " - 비밀번호: " . $row["password"] . "<a href='users.php?delete_id=" . $row["id"] . "' onclick=\"return confirm('정말로 이 사용자를 삭제하시겠습니까?');\">삭제</a>
                // </li>";
            }
        } else {
            echo "<p>등록된 사용자가 없습니다.</p>";
        }
        ?>
    </ul>
    <p><a href="index.html">홈으로 돌아가기</a></p>
</body>
</html>

<?php
// 연결 종료
$conn->close();
?>
