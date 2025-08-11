<?php
$is_logged_in = isset($_COOKIE['auth_token']) && !empty($_COOKIE['auth_token']);
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>로그인 페이지</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>로그인</h2>

    <?php
    // 메시지가 있을 경우 출력
    if (isset($_GET['message'])) {
        echo "<p>" . htmlspecialchars($_GET['message']) . "</p>";
        //console.log(htmlspecialchars($_GET['sql_query'])); //식별인증동시처리확인
    }
    ?>

    <?php if ($is_logged_in): ?>
        <p>현재 로그인 상태입니다.</p>
        <form method="POST" action="login.php">
            <input type="submit" name="logout" value="로그아웃">
        </form>
        <p><a href="account_management.php">계정 관리</a></p>
        <p><a href="board_list.php">게시판 보기</a></p>
    <?php else: ?>
        <form method="POST" action="login.php">
            사용자 이름: <input type="text" name="username" id="username" required><br>
            비밀번호: <input type="password" name="password" id="password" required><br>
            <input type="submit" value="로그인">
        </form>
    <?php endif; ?>
    <p><a href="index.html">홈으로 가기</a></p>
    
</body>
</html>
