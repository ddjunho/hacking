<?php
// 데이터베이스 연결 설정
$host = 'localhost';
$db = 'my_database';
$user = 'root';
$pass = 'U3DYRePeDDr:';

$conn = new mysqli($host, $user, $pass, $db);

// 연결 확인
if ($conn->connect_error) {
    die("연결 실패: " . $conn->connect_error);
}

// 아이디 중복 검사 함수
function isUsernameTaken($conn, $username) {
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}
// 비밀번호 강도 검증 함수
function isValidPassword($password) {
    // 최소 8자 이상, 대소문자, 숫자, 특수문자 포함
    $pattern = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[!@#$%^&*(),.?":{}|<>]).{8,}$/';
    return preg_match($pattern, $password);
}

// // 일반 회원가입 처리
// if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_type']) && $_POST['register_type'] === '일반 가입') {
//     $username = $_POST['username'];
//     $password = $_POST['password'];

//     if (isUsernameTaken($conn, $username)) {
//         echo "이미 사용 중인 아이디입니다.";
//     } else {
//         $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
//         $stmt = $conn->prepare($sql);
//         $stmt->bind_param("ss", $username, $password);

//         if ($stmt->execute()) {
//             echo "일반 회원가입: 사용자가 등록되었습니다!";
//         } else {
//             echo "사용자 등록 실패: " . $stmt->error;
//         }
//     }
// }

// 비밀번호 해시화 회원가입 처리
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_type']) && $_POST['register_type'] === '해시화 가입') {
    $username = $_POST['username_hashed'];
    $password = $_POST['password_hashed'];
// 비밀번호 강도 검사
    if (!isValidPassword($password)) {
        echo "비밀번호는 최소 8자 이상이어야 하며, 대소문자, 숫자 및 특수문자를 포함해야 합니다.";
    } else {
        $password = password_hash($password, PASSWORD_DEFAULT);

        if (isUsernameTaken($conn, $username)) {
            echo "이미 사용 중인 아이디입니다.";
        } else {
            $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $username, $password);

            if ($stmt->execute()) {
                echo "해시화 회원가입: 사용자가 등록되었습니다!";
            } else {
                echo "사용자 등록 실패: " . $stmt->error;
            }
        }
    }
}
?>
<?php
$is_logged_in = isset($_COOKIE['auth_token']) && !empty($_COOKIE['auth_token']);
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>회원가입 페이지</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- <h2>일반 회원가입</h2>
    <form method="POST" action="">
        사용자 이름: <input type="text" name="username" required><br>
        비밀번호: <input type="password" name="password" required><br>
        <input type="submit" name="register_type" value="일반 가입">
    </form>
    <br><br> -->
    <?php if ($is_logged_in): ?>
        <p>현재 로그인 상태입니다.</p>
        <form method="POST" action="login.php">
            <input type="submit" name="logout" value="로그아웃">
        </form>
        <p><a href="account_management.php">계정 관리</a></p>
        <p><a href="board_list.php">게시판 보기</a></p>
    <?php else: ?>        
        <h2>비밀번호 해시화 회원가입</h2>
        <form method="POST" action="">
            사용자 이름: <input type="text" name="username_hashed" required><br>
            비밀번호: <input type="password" name="password_hashed" required><br>
            <input type="submit" name="register_type"  value="해시화 가입">
        </form>
    <?php endif; ?>
    <p><a href="index.html">홈으로 가기</a></p>
    </body>
</html>
